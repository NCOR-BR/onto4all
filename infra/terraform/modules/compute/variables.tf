variable "project_name" {
  type        = string
  description = "Project prefix for naming AWS resources."
}

variable "branch" {
  type        = string
  description = "Branch name used for tagging and resource naming."
}

variable "vpc_id" {
  type        = string
  description = "VPC ID where the instance will run."
}

variable "subnet_id" {
  type        = string
  description = "Subnet ID where the instance will run."
}

variable "instance_type" {
  type        = string
  description = "EC2 instance type."
}

variable "key_name" {
  type        = string
  description = "EC2 key pair name."
}

variable "allowed_ssh_cidr" {
  type        = string
  description = "CIDR block allowed to access SSH."
}

variable "allowed_http_cidr" {
  type        = string
  description = "CIDR block allowed to access HTTP."
}

variable "region" {
  type        = string
  description = "AWS region."
}

variable "ecr_repo_url" {
  type        = string
  description = "ECR repository URL for the app image."
}

variable "image_tag" {
  type        = string
  description = "Docker image tag to deploy."
}

variable "app_env" {
  type        = string
  description = "Laravel APP_ENV."
}

variable "app_debug" {
  type        = string
  description = "Laravel APP_DEBUG."
}

variable "db_name" {
  type        = string
  description = "MySQL database name."
}

variable "db_user" {
  type        = string
  description = "MySQL user."
}

variable "db_password" {
  type        = string
  description = "MySQL password."
  sensitive   = true
}
