output "public_ip" {
  description = "Public IPv4 of the EC2 instance."
  value       = module.compute.public_ip
}

output "public_dns" {
  description = "Public DNS of the EC2 instance."
  value       = module.compute.public_dns
}

output "app_url" {
  description = "HTTP URL to access the app."
  value       = "http://${module.compute.public_ip}"
}

output "ssh_command" {
  description = "SSH command to access the instance."
  value       = "ssh -i ${local.private_key_path} ec2-user@${module.compute.public_ip}"
}

output "ssh_private_key_path" {
  description = "Path to the generated private SSH key."
  value       = local.private_key_path
}

output "ssh_public_key_path" {
  description = "Path to the generated public SSH key."
  value       = local.public_key_path
}

output "ecr_repo_url" {
  description = "ECR repository URL for the app image."
  value       = aws_ecr_repository.app.repository_url
}
