# Zero Scale WordPress

## License

These codes are licensed under CC0 or MIT. You can choose whichever suits your needs.
However, this repository contains some files that are **NOT** licensed under CC0 or MIT, e.g., files in the `themes` directory.

[![CC0](http://i.creativecommons.org/p/zero/1.0/88x31.png "CC0")](http://creativecommons.org/publicdomain/zero/1.0/deed.en)
[MIT](https://opensource.org/licenses/MIT) (If you need, use `Copyright (c) 2025 takotakot`)

## Overview

WordPress is one of the best blog CMSs, but it requires an RDBMS such as MySQL or MariaDB. These databases usually need to be running at all times, making it difficult to "stop WordPress-related resources when not needed." This project introduces and provides a mechanism to stop the RDBMS instance when not needed, enabling WordPress to operate in a zero-scale manner.

For backend servers, using Google Cloud's Cloud Run allows you to start only when needed and stop when not needed. Even if not on Google Cloud, the same can be achieved on any Platform as a Service (PaaS) that supports containers.

For the RDBMS, we have created a mechanism to start it when the container starts and stop it when the container instance count reaches zero. This enables WordPress to scale down to zero except for the persistent disk.

As of 2025-05, using a T2D Spot instance, you can operate the RDBMS instance for $3.10 + standard persistent disk $0.40 even if it is always running. If you succeed in stopping the instance, you can operate it even more cheaply. Note that backup costs are not included in this price.

## How Zero Scale Works

- Run WordPress on Cloud Run
- Use a Startup Probe on Cloud Run to start the MySQL Server
  - If the instance is already running, the Startup Probe succeeds
  - Cloud SQL or Compute Engine can be used, but Compute Engine was faster in practice, so it is used here
  - If connection fails, return an error
- Some minutes (e.g. 10 minutes) after the container stops, Monitoring alerts notify Pub/Sub
  - Cloud Run functions stop the Compute Engine instance upon receiving the Pub/Sub notification

## Design Principles

- For WordPress operation, use standard containers for Apache/Nginx/PHP-fpm
  - This repository provides only the Apache version
- Manage WordPress source code and version control with `composer` and `composer.json`
- Files to be managed/overwritten at the top level (DocumentRoot) should be uploaded to Cloud Storage and copied during container build
  - For example, HTML files for site ownership verification, files not to be included in the repository, or files that differ by environment
- Mount wp-content/uploads to Cloud Storage

## Cloud Storage Bucket Structure

Cloud Storage Bucket `zero-wp-admin-bucket`:
- Place files to be managed/overwritten at the top level in `zero-wp-admin-bucket/root`
- Place theme zip files, etc., that cannot be managed by composer in `zero-wp-admin-bucket/themes`
- Place plugin zip files, etc., that cannot be managed by composer in `zero-wp-admin-bucket/plugins`

Cloud Storage Bucket `zero-wp-uploads`:
- Mount to `/var/www/html/wp-content/uploads`

## Cloud Build Structure

[cloudbuild.yaml](cloudbuild.yaml)

- Place the source code (e.g., in `workspace`)
- Run `composer install` in `workspace/structure`
- Copy `root_files` to `workspace/root_dir`
  - Place files you want to manage/overwrite at the root level and version control in `root_files`
- Run `composer install` in `workspace/root_dir`
- Run [`workspace/structure/install_themes.sh`](structure/install_themes.sh)
  - Copy necessary files to `workspace/root_dir/wp-content/themes/`
  - Copy files from Cloud Storage as needed
- Run [`workspace/structure/install_plugins.sh`](structure/install_plugins.sh)
  - Copy necessary files to `workspace/root_dir/wp-content/plugins/`
  - Copy files from Cloud Storage as needed
- Run [`workspace/structure/update_root.sh`](structure/update_root.sh)
  - Copy necessary files to `workspace/root_dir`
  - Copy files from Cloud Storage as needed
- The `Dockerfile` copies the external `workspace/root_dir` to `/var/www/html` inside the container

## Cloud Run Structure

### Cloud Run Environment Variables

Use as: `$_ENV['PROJECT_ID']`

- `PROJECT_ID`: Google Cloud Project ID
- `GCE_ZONE`: (Compute Engine Ver.) DB Instance Zone
- `WORDPRESS_DB_INSTANCE_ID`: DB Instance ID
- `WORDPRESS_DB_NAME`: WordPress Database Name
- `WORDPRESS_DB_USER`: WordPress Database User
- `WORDPRESS_DB_PASSWORD`: WordPress Database Password
- `WORDPRESS_DB_HOST`: WordPress Database Host
- `WORDPRESS_AUTH_KEYS`: WordPress Authentication Unique Keys and Salts
- `WORDPRESS_DEBUG`: WordPress Debug Mode

### Startup Probe

- Configure `startup_gce.php` as a Startup Probe
- Check MySQL connection
  - If OK, complete
  - If NG:
    - Start the instance if it is stopped
    - Return a 503 error

## Cloud Build, Dockerfile Notes

- The contents of `root_files/*` are copied to `root_dir/` in Cloud Build
- `composer install` is run as `php ../composer.phar install` in both `structure` and `root_dir`
- Shell scripts (such as `install_themes.sh`) are run with the `gcr.io/cloud-builders/gcloud` image in Cloud Build
- The `Dockerfile` adjusts permissions with `chown -R www-data:www-data /var/www/html`
- With `options: logging: CLOUD_LOGGING_ONLY`, Cloud Build logs are output only to Cloud Logging

## Cloud Run Functions Structure

### Stop Compute Engine

Triggered by an event, stops the Compute Engine instance [index.php](functions/stop_compute_engine/index.php)

Environment variables:
- `PROJECT_ID`: Google Cloud Project ID
- `GCE_ZONE`: (Compute Engine Ver.) DB Instance Zone
- `WORDPRESS_DB_INSTANCE_ID`: DB Instance ID

## Initial Setup

## 初期設定手順

For the initial setup, please refer to either [initial_setup.ja.md](docs/initial_setup.ja.md) or [initial_setup.en.md](docs/initial_setup.en.md).
