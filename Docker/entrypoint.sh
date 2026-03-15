#!/bin/bash

set -eu

cd /var/www

echo "Bootstrapping application container"

ensure_writable_paths() {
    echo "Ensuring Laravel writable directories"
    mkdir -p \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache

    chown -R www-data:www-data storage bootstrap/cache
    chmod -R ug+rwX storage bootstrap/cache
}

if [ ! -f ".env" ]; then
    echo "Creating .env from .env.example"
    cp .env.example .env
else
    echo ".env already exists"
fi

if [ ! -f "vendor/autoload.php" ]; then
    echo "Installing Composer dependencies"
    composer install --optimize-autoloader --no-progress --no-interaction
else
    echo "Composer dependencies already installed"
fi

ensure_writable_paths

if ! grep -Eq '^APP_KEY=base64:' .env; then
    echo "Generating application key"
    php artisan key:generate --force
else
    echo "Application key already configured"
fi

wait_for_database() {
    if [ "${DB_CONNECTION:-mysql}" != "mysql" ]; then
        return 0
    fi

    echo "Waiting for MySQL at ${DB_HOST:-database}:${DB_PORT:-3306}"
    php <<'PHP'
<?php
$host = getenv('DB_HOST') ?: 'database';
$port = getenv('DB_PORT') ?: '3306';
$database = getenv('DB_DATABASE') ?: '';
$username = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';

$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s', $host, $port, $database);
$attempts = 30;

for ($i = 0; $i < $attempts; $i++) {
    try {
        new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        exit(0);
    } catch (Throwable $e) {
        fwrite(STDERR, "Database not ready yet, retrying...\n");
        sleep(2);
    }
}

fwrite(STDERR, "Timed out waiting for database connection.\n");
exit(1);
PHP
}

should_seed_database() {
    php <<'PHP'
<?php
if ((getenv('DB_CONNECTION') ?: 'mysql') !== 'mysql') {
    exit(0);
}

$host = getenv('DB_HOST') ?: 'database';
$port = getenv('DB_PORT') ?: '3306';
$database = getenv('DB_DATABASE') ?: '';
$username = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s', $host, $port, $database);

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $count = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    exit($count === 0 ? 0 : 1);
} catch (Throwable $e) {
    fwrite(STDERR, "Could not inspect users table for seed decision.\n");
    exit(0);
}
PHP
}

wait_for_database

echo "Running database migrations"
php artisan migrate --force

if should_seed_database; then
    echo "Seeding fresh database"
    php artisan db:seed --force
else
    echo "Skipping seed; users table already has data"
fi

echo "Clearing Laravel caches"
php artisan optimize:clear

exec docker-php-entrypoint "$@"
