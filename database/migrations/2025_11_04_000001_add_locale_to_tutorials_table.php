<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tutorials', function (Blueprint $table) {
            $table->string('locale', 5)->default('en')->after('html_content');
            $table->index(['locale', 'updated_at']);
        });

        DB::table('tutorials')->whereNull('locale')->update(['locale' => 'en']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tutorials', function (Blueprint $table) {
            $table->dropIndex('tutorials_locale_updated_at_index');
            $table->dropColumn('locale');
        });
    }
};
