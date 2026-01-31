variable "project_name" {
  type        = string
  description = "Project prefix for naming AWS resources."
  default     = "onto4all"
}

variable "branch" {
  type        = string
  description = "Branch name used for tagging and resource naming."
  default     = "dev"
}

variable "region" {
  type        = string
  description = "AWS region."
  default     = "us-east-1"
}

variable "availability_zone" {
  type        = string
  description = "Single AZ for the public subnet."
  default     = "us-east-1a"
}

variable "vpc_cidr" {
  type        = string
  description = "CIDR block for the VPC."
  default     = "10.10.0.0/16"
}

variable "public_subnet_cidr" {
  type        = string
  description = "CIDR block for the public subnet."
  default     = "10.10.1.0/24"
}

variable "instance_type" {
  type        = string
  description = "EC2 instance type."
  default     = "t3.small"
}

variable "allowed_ssh_cidr" {
  type        = string
  description = "CIDR block allowed to access SSH."
  default     = "0.0.0.0/0"
}

variable "allowed_http_cidr" {
  type        = string
  description = "CIDR block allowed to access HTTP."
  default     = "0.0.0.0/0"
}

variable "ecr_repo_name" {
  type        = string
  description = "ECR repository name for the application image."
  default     = "onto4all"
}

variable "image_tag" {
  type        = string
  description = "Docker image tag to deploy (usually the branch name)."
  default     = "latest"
}

variable "app_env" {
  type        = string
  description = "Laravel APP_ENV."
  default     = "production"
}

variable "app_debug" {
  type        = string
  description = "Laravel APP_DEBUG."
  default     = "false"
}

variable "db_name" {
  type        = string
  description = "MySQL database name."
  default     = "onto4all"
}

variable "db_user" {
  type        = string
  description = "MySQL user."
  default     = "onto4all"
}

variable "db_password" {
  type        = string
  description = "MySQL password (ephemeral)."
  default     = "onto4all"
  sensitive   = true
}
