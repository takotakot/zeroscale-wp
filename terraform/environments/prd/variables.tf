variable "project_id" {
  type    = string
  default = ""
}

variable "region" {
  type    = string
  default = ""
}

variable "gce_zone" {
  type    = string
  default = "us-west1-c"
}

variable "gce_instance_id" {
  type    = string
  default = "zero-wp-db"
}

variable "gce_ip" {
  type    = string
  default = "10.138.0.2"
}

variable "wordpress_db_name" {
  type    = string
  default = "wordpress"
}

variable "wordpress_db_user" {
  type    = string
  default = "wordpress"
}

variable "startup_probe_path" {
  type    = string
  default = "/startup_gce.php"
}

variable "use_suspend" {
  type    = bool
  default = true
}
