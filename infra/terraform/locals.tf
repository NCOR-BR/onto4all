locals {
  repo_root        = abspath("${path.root}/../..")
  key_dir          = "${path.root}/keys"
  private_key_path = "${local.key_dir}/${var.project_name}-${var.branch}.pem"
  public_key_path  = "${local.key_dir}/${var.project_name}-${var.branch}.pub"
}
