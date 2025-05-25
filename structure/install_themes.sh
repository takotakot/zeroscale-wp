mkdir -p root_dir/wp-content/themes
cp -a themes/* root_dir/wp-content/themes/
gcloud storage cp --recursive --quiet gs://zero-wp-admin-bucket/themes/* ./root_dir/wp-content/themes/ || true
