# Zero Scale WordPress

## License

These codes are licensed under CC0 or MIT. You can choose whichever suits your needs.
But this repository contains some files those are **NOT** licensed under CC0 or MIT, e.g. files in `themes` directory.

[![CC0](http://i.creativecommons.org/p/zero/1.0/88x31.png "CC0")](http://creativecommons.org/publicdomain/zero/1.0/deed.ja)
[MIT](https://opensource.org/licenses/MIT) (If you need, use `Copyright (c) 2025 takotakot`)

## 概要

WordPress は優れたブログ CMS のひとつですが、MySQL あるいは MariaDB などの RDBMS を必要とします。これらデータベースは、通常は常時起動しておく必要があり、WordPress 関連リソースを「必要時以外は停止」することを難しくしていました。本プロジェクトでは、RDBMS インスタンスを必要時以外は停止し、WordPress をゼロスケールさせて動作させるための仕組みを紹介・提供します。

バックエンドのサーバについては、Google Cloud の Cloud Run を使用すると、必要時にのみ起動し、必要がなくなったら停止することができます。Google Cloud でなくても、Container を利用できる Platform as a Service (PaaS) であれば、同様のことが可能です。

RDBMS については、コンテナが起動したときに起動させ、コンテナインスタンスが0になったときに停止させる仕組みを作成しました。これにより、WordPress を永続ディスク以外はゼロにスケーリングさせて動作させることを可能としました。

2025-05 時点では、T2D の Spot インスタンスを使用した場合、RDBMS 用インスタンスが常時起動していても $3.10 + 標準永続ディスク $0.40 で運用できます。インスタンスの停止に成功すれば、これよりも安く運用できるというわけです。バックアップについては料金に含めていないため、その点は注意が必要です。

## ゼロスケールする仕組み

- Cloud Run 上で WordPress を動かす
- Cloud Run の Startup Probe で、MySQL Server を起動させる
  - インスタンスが起動済の場合は、Startup Probe は成功
  - Cloud SQL でも、Compute Engine でも良いが、実用上 Compute Engine の方が起動が高速であったため、Compute Engine を使用
  - 接続できない場合はエラーを返す
- コンテナ停止数分後（例: 10分）、Monitoring のアラートで、Pub/Sub に通知
  - Pub/Sub の通知を受けて、Cloud Run functions 関数が Compute Engine を停止

## 設計指針

- WordPress の動作のうち、Apache/Nginx/PHP-fpm については、標準コンテナを使用
  - 本リポジトリでは Apache バージョンのみ提供
- WordPress のソースコード・バージョンコントロールは、`composer`, `composer.json` で管理
- トップレベル (DocumentRoot) で管理・上書きしたいファイルは、Cloud Storage にアップロードしておき、コンテナビルド時にコピーする
  - サイト所有証明のための html ファイルなど、リポジトリに含めたくない、あるいは、環境ごとに異なるファイルを管理
- wp-content/uploads は Cloud Storage をマウント

## Cloud Storage バケットの構成

Cloud Storage Bucket `zero-wp-admin-bucket`:
- `zero-wp-admin-bucket/root` に、トップレベルで管理・上書きしたいファイルを配置
- `zero-wp-admin-bucket/themes` に、テーマの zip ファイルなど、composer で管理できないファイルを配置
- `zero-wp-admin-bucket/plugins` に、プラグインの zip ファイルなど、composer で管理できないファイルを配置

Cloud Storage Bucket `zero-wp-uploads`:
- `/var/www/html/wp-content/uploads` にマウントする

## Cloud Build の構成

[cloudbuild.yaml](cloudbuild.yaml)

- ソースコードを配置（仮に、`workspace` とする）
- `workspace/structure` で、`composer install` を実行
- `root_files` を `workspace/root_dir` にコピー
  - `root_files` には、ルートレベルで管理・上書きしたいファイルのうち、バージョン管理したいものを配置
- `workspace/root_dir` で、`composer install` を実行
- [`workspace/structure/install_themes.sh`](structure/install_themes.sh) を実行
  - `workspace/root_dir/wp-content/themes/` に、必要なファイルをコピー
  - 必要に応じて Cloud Storage からファイルをコピー
- [`workspace/structure/install_plugins.sh`](structure/install_plugins.sh) を実行
  - `workspace/root_dir/wp-content/plugins/` に、必要なファイルをコピー
  - 必要に応じて Cloud Storage からファイルをコピー
- [`workspace/structure/update_root.sh`](structure/update_root.sh) を実行
  - `workspace/root_dir` に、必要なファイルをコピー
  - 必要に応じて Cloud Storage からファイルをコピー
- `Dockerfile` は、外の `workspace/root_dir` を、コンテナ内の `/var/www/html` にコピー

## Cloud Run の構成

### Cloud Run の環境変数

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

## Cloud Build, Dockerfile 補足

- `root_files/*` の内容は Cloud Build で `root_dir/` にコピーされる
- `composer install` は `php ../composer.phar install` で `structure`, `root_dir` それぞれで実行される
- シェルスクリプト（`install_themes.sh` など）は Cloud Build の `gcr.io/cloud-builders/gcloud` イメージで実行される
- `Dockerfile` では `chown -R www-data:www-data /var/www/html` でパーミッション調整を行っている
- `options: logging: CLOUD_LOGGING_ONLY` により、Cloud Build のログは Cloud Logging のみ出力される

## Cloud Run functions の構成

### Stop Compute Engine

イベントによって起動し、Compute Engine インスタンスを停止 [index.php](functions/stop_compute_engine/index.php)

環境変数:
- `PROJECT_ID`: Google Cloud Project ID
- `GCE_ZONE`: (Compute Engine Ver.) DB Instance Zone
- `WORDPRESS_DB_INSTANCE_ID`: DB Instance ID

## 初期設定手順

[initial_setup.ja.md](docs/initial_setup.ja.md) あるいは[initial_setup.en.md](docs/initial_setup.en.md) を参照してください。
