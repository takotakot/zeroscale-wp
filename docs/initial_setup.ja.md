# 初期設定手順

## Terraform 実行前の初期設定

1. Google Cloud プロジェクトを作成
2. Terraform state 保存用の Cloud Storage バケットを作成
   1. (`terraform/environments/prd/backend.tf`)[../terraform/environments/prd/backend.tf] の `bucket` に設定
   2. 必要に応じて、`git update-index --skip-worktree terraform/environments/prd/backend.tf` で変更を無視するか、作業用のブランチを作成してコミットする
3. [`terraform/environments/prd/.tfvars`](../terraform/environments/prd/.tfvars) を作成
   1. `terraform/environments/prd/.tfvars.example` をコピーして、必要な値を設定

## Compute Engine インスタンスの作成（手作業）

Compute Engine インスタンスを作成し、MySQL あるいは MariaDB をインストールする。

`misc/mysql_setup.sh` と `misc/init_mysql.sql` を参考に、MySQL あるいは MariaDB の初期設定を行う。必要なパッケージをインストールした後は、グローバル IP は不要である。

wordpress ユーザーのパスワードは、後で Secret Manager secret に保存するため、控えておく。

`terraform/environments/prd/.tfvars` に、インスタンスのゾーン、インスタンス名 (ID)、IP アドレスを設定する。

### Cloud SQL の場合

Cloud SQL を使用する場合は、Compute Engine インスタンスの作成は不要である。Cloud SQL インスタンスを作成し、必要な設定を行う。

## Terraform のインストール（詳細は省略）

`terraform/environments/prd/.terraform-version` に記載のバージョンをインストールするか、バージョンを指定してインストールする。

## Terraform によるリソースの作成（1回目）

1. `terraform/environments/prd` ディレクトリに移動
2. `terraform init` を実行
3. `terraform plan -var-file=.tfvars -target="google_monitoring_alert_policy.cloud_run_zero_instances" -target="google_secret_manager_secret.wordpress_db_password" -target="google_secret_manager_secret.wordpress_auth_keys" -target="google_artifact_registry_repository.gcr" -target="google_storage_bucket.zero-wp-admin-bucket" -target="google_storage_bucket.zero-wp-uploads" -target="google_project_iam_member.cloud_build_service_account" -target="google_project_iam_member.cloud_build_service_account_artifact_registry_writer" -target="google_storage_bucket_iam_member.cloud_run_bucket_access" -target="google_project_iam_member.zero-wp-stop-compute-engine_compute_instance_admin"` を実行して、計画を確認
4. `terraform apply -var-file=.tfvars -target="google_monitoring_alert_policy.cloud_run_zero_instances" -target="google_secret_manager_secret.wordpress_db_password" -target="google_secret_manager_secret.wordpress_auth_keys" -target="google_artifact_registry_repository.gcr" -target="google_storage_bucket.zero-wp-admin-bucket" -target="google_storage_bucket.zero-wp-uploads" -target="google_project_iam_member.cloud_build_service_account" -target="google_project_iam_member.cloud_build_service_account_artifact_registry_writer" -target="google_storage_bucket_iam_member.cloud_run_bucket_access" -target="google_project_iam_member.zero-wp-stop-compute-engine_compute_instance_admin"` を実行して、リソースを作成（うまくいかない場合は、作成するリソースを適切に増減してください）

## Cloud Build トリガーの作成

1. (トリガー)[https://console.cloud.google.com/cloud-build/triggers] を開く
2. 「トリガーを作成」をクリック
3. リポジトリの生成の「リポジトリ」を選択し、(GitHub などの) リポジトリを選択
   1. 必要に応じて、「新しいリポジトリに接続」を利用してリポジトリを接続（筆者は、「第1世代」で確認）
4. サービスアカウントに "Zero WP Cloud Build Service Account" を選択

## Cloud Build トリガーの実行

上記で設定した Cloud Build トリガーを実行して、コンテナイメージをビルドする。

## stop-compute-engine 関数のデプロイ

`functions/stop_compute_engine/deploy.sh` の変数に適切な値を設定し、実行すると、同等のことが可能なはずである。

Web UI の場合、以下を実行する。

1. (Cloud Run)[https://console.cloud.google.com/run] を開く
2. 「関数を作成」をクリック
3. 「サービス の名前」に `stop-compute-engine` を入力
4. 「リージョン」を選択
5. 「ランタイム」で `PHP 8.3`（あるいは他のバージョン）を選択
6. トリガーの設定
   1. 「トリガーを追加」から「Pub/Sub」を選択し、「トピック」に `cloudrun-zero-instance-alert-topic` を選択
   2. 「リージョン」を適切に選択
   3. 「サービスアカウント」に `Zero WP Stop Compute Engine Service Account` を選択
7. 他はデフォルトのままにして、「作成」をクリック
8. ソースコードの指定
   1. ソースコードの指定画面に遷移するはずである
   2. `index.php` の内容を `functions/stop_compute_engine/index.php` の内容に変更
   3. `composer.json` の内容を `functions/stop_compute_engine/composer.json` の内容に変更
   4. 「関数のエントリ ポイント」に `stopComputeEngine` を指定

Terraform による import（2回目）

`main.tf` の `google_cloud_run_v2_service.stop-compute-engine` についての `import` ブロックをコメントアウトし、`terraform apply -var-file=.tfvars -target="google_cloud_run_v2_service.stop-compute-engine"` を実行して、状態を更新する。

### Cloud SQL の場合

関数を変更する必要があるが、このリポジトリでは Cloud SQL 用の関数は提供していない。

## シークレット値の設定

事前準備: [https://api.wordpress.org/secret-key/1.1/salt/](https://api.wordpress.org/secret-key/1.1/salt/) にアクセスして、WordPress の Salt 値を生成し「すべての値を結合したもの」を用意しておく。512 文字になるはずである。

1. (Secret Manager)[https://console.cloud.google.com/security/secret-manage] を開く
2. `WORDPRESS_DB_PASSWORD` の3点リーダをクリックし「新しいバージョンを追加」を選択
3. 控えておいた wordpress ユーザーのパスワードを入力し、「保存」をクリック
4. `WORDPRESS_AUTH_KEYS` の3点リーダをクリックし「新しいバージョンを追加」を選択
5. [WordPress.org の秘密鍵生成ツール](https://api.wordpress.org/secret-key/1.1/salt/) で生成した値を入力し、「保存」をクリック

## Zero-wp Cloud Run サービスのデプロイ

Terraform によるリソースの作成（3回目）

1. `terraform/environments/prd` ディレクトリに移動
2. `terraform init` を実行
3. `terraform plan -var-file=.tfvars` を実行して、計画を確認
4. `terraform apply -var-file=.tfvars` を実行して、リソースを作成

### Cloud SQL の場合

`root_files` には、`startup_gce.php` が配置されているが、Cloud SQL 用の設定は含まれていない。`startup_sql.php` をサンプルファイル[`startup_sql.php`](../misc/startup_cloud_sql/startup_sql.php)を参考に作成し、`root_files` に配置する。

その後、`.tfvars` の `startup_probe_path` を `/startup_sql.php` に変更してから、上記の手順を実行する。
