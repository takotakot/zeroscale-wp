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
