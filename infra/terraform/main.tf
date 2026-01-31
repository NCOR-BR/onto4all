provider "aws" {
  region = var.region
}

module "network" {
  source             = "./modules/network"
  project_name       = var.project_name
  branch             = var.branch
  vpc_cidr           = var.vpc_cidr
  public_subnet_cidr = var.public_subnet_cidr
  availability_zone  = var.availability_zone
}

resource "tls_private_key" "ssh" {
  algorithm = "RSA"
  rsa_bits  = 4096
}

resource "local_file" "private_key" {
  filename             = local.private_key_path
  sensitive_content    = tls_private_key.ssh.private_key_pem
  file_permission      = "0600"
  directory_permission = "0700"
}

resource "local_file" "public_key" {
  filename             = local.public_key_path
  content              = tls_private_key.ssh.public_key_openssh
  file_permission      = "0644"
  directory_permission = "0700"
}

resource "aws_ecr_repository" "app" {
  name = var.ecr_repo_name

  image_scanning_configuration {
    scan_on_push = true
  }

  tags = {
    Project = var.project_name
    Branch  = var.branch
  }
}

resource "aws_key_pair" "default" {
  key_name   = "${var.project_name}-${var.branch}-key"
  public_key = tls_private_key.ssh.public_key_openssh

  tags = {
    Project = var.project_name
    Branch  = var.branch
  }
}

resource "null_resource" "build_and_push" {
  triggers = {
    image_tag = var.image_tag
    build_id  = timestamp()
  }

  provisioner "local-exec" {
    command = <<EOT
set -euo pipefail
aws ecr get-login-password --region ${var.region} | docker login --username AWS --password-stdin ${aws_ecr_repository.app.repository_url}
docker build -t ${aws_ecr_repository.app.repository_url}:${var.image_tag} ${local.repo_root}
docker push ${aws_ecr_repository.app.repository_url}:${var.image_tag}
EOT
    interpreter = ["/bin/bash", "-c"]
    working_dir = local.repo_root
  }

  depends_on = [aws_ecr_repository.app]
}

module "compute" {
  source            = "./modules/compute"
  project_name      = var.project_name
  branch            = var.branch
  vpc_id            = module.network.vpc_id
  subnet_id         = module.network.public_subnet_id
  instance_type     = var.instance_type
  key_name          = aws_key_pair.default.key_name
  allowed_ssh_cidr  = var.allowed_ssh_cidr
  allowed_http_cidr = var.allowed_http_cidr
  region            = var.region
  ecr_repo_url      = aws_ecr_repository.app.repository_url
  image_tag         = var.image_tag
  app_env           = var.app_env
  app_debug         = var.app_debug
  db_name           = var.db_name
  db_user           = var.db_user
  db_password       = var.db_password

  depends_on = [null_resource.build_and_push]
}
