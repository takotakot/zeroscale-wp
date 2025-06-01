# Initial Setup Procedure

## Initial Setup Before Terraform Execution

1. Create a Google Cloud project
2. Create a Cloud Storage bucket for saving the Terraform state
   1. Set to `bucket` in (`terraform/environments/prd/backend.tf`)[../terraform/environments/prd/backend.tf]
   2. If necessary, ignore changes with `git update-index --skip-worktree terraform/environments/prd/backend.tf` or create a working branch and commit
3. Create [`terraform/environments/prd/.tfvars`](../terraform/environments/prd/.tfvars)
   1. Copy `terraform/environments/prd/.tfvars.example` and set the necessary values

## Create Compute Engine Instance (Manual Task)

Create a Compute Engine instance and install MySQL or MariaDB.

Perform initial setup for MySQL or MariaDB, referencing `misc/mysql_setup.sh` and `misc/init_mysql.sql`. After installing the necessary packages, a global IP is not required.

Make a note of the wordpress user's password, as it will be saved to a Secret Manager secret later.

Set the instance's zone, instance name (ID), and IP address in `terraform/environments/prd/.tfvars`.

### In case of Cloud SQL

If using Cloud SQL, creating a Compute Engine instance is not necessary. Create a Cloud SQL instance and perform the necessary configurations.

## Terraform Installation (Details Omitted)

Install the version specified in `terraform/environments/prd/.terraform-version` or install by specifying the version.

## Resource Creation by Terraform (1st time)

1. Navigate to the `terraform/environments/prd` directory
2. Execute `terraform init`
3. Execute `terraform plan -var-file=.tfvars -target="google_monitoring_alert_policy.cloud_run_zero_instances" -target="google_secret_manager_secret.wordpress_db_password" -target="google_secret_manager_secret.wordpress_auth_keys" -target="google_artifact_registry_repository.gcr" -target="google_storage_bucket.zero-wp-admin-bucket" -target="google_storage_bucket.zero-wp-uploads" -target="google_project_iam_member.cloud_build_service_account" -target="google_project_iam_member.cloud_build_service_account_artifact_registry_writer" -target="google_storage_bucket_iam_member.cloud_run_bucket_access" -target="google_project_iam_member.zero-wp-stop-compute-engine_compute_instance_admin"` to confirm the plan
4. Execute `terraform apply -var-file=.tfvars -target="google_monitoring_alert_policy.cloud_run_zero_instances" -target="google_secret_manager_secret.wordpress_db_password" -target="google_secret_manager_secret.wordpress_auth_keys" -target="google_artifact_registry_repository.gcr" -target="google_storage_bucket.zero-wp-admin-bucket" -target="google_storage_bucket.zero-wp-uploads" -target="google_project_iam_member.cloud_build_service_account" -target="google_project_iam_member.cloud_build_service_account_artifact_registry_writer" -target="google_storage_bucket_iam_member.cloud_run_bucket_access" -target="google_project_iam_member.zero-wp-stop-compute-engine_compute_instance_admin"` to create resources (if it doesn't work, please appropriately increase or decrease the resources to be created)

## Create Cloud Build Trigger

1. Open (Triggers)[https://console.cloud.google.com/cloud-build/triggers]
2. Click "Create trigger"
3. For "Repository" under repository generation, select the repository (e.g., GitHub)
   1. If necessary, connect the repository using "Connect new repository" (the author confirmed with "1st generation")
4. For Service account, select "Zero WP Cloud Build Service Account"

## Execute Cloud Build Trigger

Execute the Cloud Build trigger configured above to build the container image.

## Deploy stop-compute-engine function

Setting appropriate values for the variables in `functions/stop_compute_engine/deploy.sh` and executing it should achieve the equivalent.

For the Web UI, perform the following:

1. Open (Cloud Run)[https://console.cloud.google.com/run]
2. Click "Create function"
3. Enter `stop-compute-engine` for "Service name"
4. Select "Region"
5. For "Runtime," select `PHP 8.3` (or another version)
6. Trigger settings
   1. From "Add trigger," select "Pub/Sub" and for "Topic," select `cloudrun-zero-instance-alert-topic`
   2. Select "Region" appropriately
   3. For "Service account," select `Zero WP Stop Compute Engine Service Account`
7. Leave others as default and click "Create"
8. Source code specification
   1. You should be taken to the source code specification screen
   2. Change the content of `index.php` to the content of `functions/stop_compute_engine/index.php`
   3. Change the content of `composer.json` to the content of `functions/stop_compute_engine/composer.json`
   4. Specify `stopComputeEngine` for "Function entry point"

Terraform import (2nd time)

Comment out the `import` block for `google_cloud_run_v2_service.stop-compute-engine` in `main.tf`, and execute `terraform apply -var-file=.tfvars -target="google_cloud_run_v2_service.stop-compute-engine"` to update the state.

### In case of Cloud SQL

It is necessary to change the function, but this repository does not provide a function for Cloud SQL.

## Set Secret Values

Prerequisite: Access [https://api.wordpress.org/secret-key/1.1/salt/](https://api.wordpress.org/secret-key/1.1/salt/) to generate WordPress Salt values and prepare "all values combined." It should be 512 characters.

1. Open (Secret Manager)[https://console.cloud.google.com/security/secret-manage]
2. Click the three dots for `WORDPRESS_DB_PASSWORD` and select "Add new version"
3. Enter the noted wordpress user's password and click "Save"
4. Click the three dots for `WORDPRESS_AUTH_KEYS` and select "Add new version"
5. Enter the values generated by the [WordPress.org secret key generation tool](https://api.wordpress.org/secret-key/1.1/salt/) and click "Save"

## Deploy Zero-wp Cloud Run service

Resource Creation by Terraform (3rd time)

1. Navigate to the `terraform/environments/prd` directory
2. Execute `terraform init`
3. Execute `terraform plan -var-file=.tfvars` to confirm the plan
4. Execute `terraform apply -var-file=.tfvars` to create resources

### In case of Cloud SQL

`startup_gce.php` is placed in `root_files`, but it does not include settings for Cloud SQL. Create `startup_sql.php` using the sample file [`startup_sql.php`](../misc/startup_cloud_sql/startup_sql.php) as a reference and place it in `root_files`.

After that, change `startup_probe_path` in `.tfvars` to `/startup_sql.php`, then execute the above procedure.
