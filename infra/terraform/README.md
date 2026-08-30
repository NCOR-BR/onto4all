# Terraform (ephemeral EC2)

This module creates a minimal single-AZ VPC and an EC2 instance that runs the app
and a MySQL container on the same host. It is optimized for short-lived branch
reviews using `terraform apply` and `terraform destroy`.

## Prereqs
- AWS credentials with permissions for VPC, EC2, IAM, and ECR.
- AWS CLI and Docker installed locally (the image build/push happens during apply).

## Deploy
```
cd infra/terraform
terraform init
terraform apply \
  -var="image_tag=<TAG>" \
  -var="allowed_ssh_cidr=<YOUR_IP>/32"
```

After apply, grab the public IP and SSH command:
```
terraform output app_url
terraform output ssh_command
```

## Destroy
```
cd infra/terraform
terraform destroy \
  -var="image_tag=<TAG>"
```

## Notes
- Every `terraform apply` rebuilds and pushes the image to ECR.
- The SSH keypair is generated under `infra/terraform/keys/`.
- The MySQL data is ephemeral (no volume attached).
- HTTP is exposed on port 80 via the instance public IP.
- The instance uses user_data to pull the image from ECR and run containers.
