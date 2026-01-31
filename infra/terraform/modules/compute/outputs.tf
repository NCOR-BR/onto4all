output "public_ip" {
  description = "Public IPv4 of the EC2 instance."
  value       = aws_instance.app.public_ip
}

output "public_dns" {
  description = "Public DNS of the EC2 instance."
  value       = aws_instance.app.public_dns
}
