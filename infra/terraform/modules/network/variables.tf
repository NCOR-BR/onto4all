variable "project_name" {
  type        = string
  description = "Project prefix for naming AWS resources."
}

variable "branch" {
  type        = string
  description = "Branch name used for tagging and resource naming."
}

variable "vpc_cidr" {
  type        = string
  description = "CIDR block for the VPC."
}

variable "public_subnet_cidr" {
  type        = string
  description = "CIDR block for the public subnet."
}

variable "availability_zone" {
  type        = string
  description = "Single AZ for the public subnet."
}
