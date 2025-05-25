gcloud storage cp --recursive --quiet gs://zero-wp-admin-bucket/root/* ./root_dir/ || true
rm root_dir/composer.lock root_dir/composer.json || true
