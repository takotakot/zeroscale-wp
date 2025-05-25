# Google Cloud サービスの有効化
locals {
  project_services = [
    "artifactregistry.googleapis.com", # Artifact Registry API
    "cloudbuild.googleapis.com",       # Cloud Build API
    "run.googleapis.com",              # Cloud Run API
    "iam.googleapis.com",              # IAM API
    "compute.googleapis.com",          # Compute Engine API
    "vpcaccess.googleapis.com",        # VPC Access API
    "secretmanager.googleapis.com",    # Secret Manager API
    "pubsub.googleapis.com",           # Pub/Sub API
    "monitoring.googleapis.com",       # Cloud Monitoring API
    "eventarc.googleapis.com",         # Eventarc API
  ]

  service_label_value = "zero-wp"
}

# Google Cloud サービスの有効化
resource "google_project_service" "services" {
  for_each = toset(local.project_services)

  project = var.project_id
  service = each.key

  disable_dependent_services = true
  disable_on_destroy         = false
}

# Get project number and other project details
data "google_project" "project" {
  project_id = var.project_id
}

# --- Pub/Sub トピックの作成 ---
resource "google_pubsub_topic" "cloud_run_zero_instance_alerts" {
  project = var.project_id
  name    = "cloudrun-zero-instance-alert-topic"
  labels = {
    service = local.service_label_value
  }
}

# --- 通知チャネルの作成 (Pub/Sub トピックを指す) ---
# Pub/SubトピックもTerraformで管理する場合、そのIDを参照
resource "google_monitoring_notification_channel" "pubsub_channel_for_cloud_run" {
  project      = var.project_id
  display_name = "Pub/Sub for Cloud Run Zero Instance Alerts"
  type         = "pubsub"
  labels = {
    topic = google_pubsub_topic.cloud_run_zero_instance_alerts.id
  }
  description = "Notification channel to send alerts to Pub/Sub when Cloud Run instances are zero."
}

# --- Cloud Monitoring アラートポリシーの作成 ---
resource "google_monitoring_alert_policy" "cloud_run_zero_instances" {
  project      = var.project_id
  display_name = "Cloud Run Service - Zero Instances for 10 Minutes"
  combiner     = "AND"

  # アラートの条件
  conditions {
    display_name = "Cloud Run instance count is 0 for 10 minutes"
    condition_threshold {
      # 監視対象のメトリクス
      filter = <<EOT
        metric.type="run.googleapis.com/container/instance_count"
        resource.type="cloud_run_revision"
        resource.label.service_name="zero-wp"
        -- resource.label.project_id="tako-test-general"
        -- 必要に応じて、resource.label.location や resource.label.revision_name も指定
        -- resource.state="active"
      EOT

      # 比較条件
      comparison      = "COMPARISON_LT" # Less Than (より小さい)
      threshold_value = 1               # しきい値: 1 (つまり、0になった場合)

      # 継続期間
      duration                = "600s" # 10分 (10 * 60秒 = 600秒)
      evaluation_missing_data = "EVALUATION_MISSING_DATA_ACTIVE"

      # 集計方法
      # インスタンス数は特定の時点での値なので、アライメントやリデューサーはシンプルで良い
      aggregations {
        alignment_period   = "60s"       # 60秒ごとにデータをアライメント
        per_series_aligner = "ALIGN_MAX" # 期間内の最大値（0のままであることを確認）
        # または ALIGN_COUNT_TRUE (0である状態のカウント) など、
        # メトリクスの特性と意図に合わせて調整
        # ここでは、各シリーズが期間中ずっと0であることを確認したい
        cross_series_reducer = "REDUCE_SUM"
      }

      # トリガー条件 (期間中に何回しきい値違反があればアラートとするか)
      # 継続期間 (duration) を満たせばアラートなので、count は 1 で良い
      trigger {
        count = 1
      }
    }
  }

  alert_strategy {
    auto_close = "1800s"
    notification_prompts = [
      "OPENED",
    ]
  }

  # ドキュメンテーション (オプション)
  documentation {
    content   = "The specified Cloud Run service has had zero instances for 10 minutes. This may trigger a GCE instance shutdown."
    mime_type = "text/markdown"
  }

  # 通知チャネル (上で作成したPub/Sub通知チャネルを指定)
  notification_channels = [
    google_monitoring_notification_channel.pubsub_channel_for_cloud_run.id,
  ]

  # 有効化
  enabled = true

  # アラートの重大度 (オプション)
  severity = "WARNING" # または "ERROR" など、状況に応じて

  # ユーザー定義ラベル (オプション)
  user_labels = {
    environment = "production"
    service     = "zero-wp"
  }
}

# --- Secret Manager シークレットの作成 ---
# Secret Managerのシークレット作成（パスワード用の「箱」）
resource "google_secret_manager_secret" "wordpress_db_password" {
  secret_id = "WORDPRESS_DB_PASSWORD"

  replication {
    auto {}
  }

  labels = {
    service = local.service_label_value
  }

  depends_on = [google_project_service.services["secretmanager.googleapis.com"]]
}

# Secret Managerからシークレットの最新バージョンを取得
# data "google_secret_manager_secret_version" "wordpress_db_password_latest" {
#   secret = google_secret_manager_secret.wordpress_db_password.id

#   depends_on = [google_secret_manager_secret.wordpress_db_password]
# }

resource "google_secret_manager_secret" "wordpress_auth_keys" {
  secret_id = "WORDPRESS_AUTH_KEYS"

  replication {
    auto {}
  }

  labels = {
    service = local.service_label_value
  }

  depends_on = [google_project_service.services["secretmanager.googleapis.com"]]
}

# data "google_secret_manager_secret_version" "wordpress_auth_keys_latest" {
#   secret = google_secret_manager_secret.wordpress_auth_keys.id

#   depends_on = [google_secret_manager_secret.wordpress_auth_keys]
# }

# --- Cloud Run サービスの作成 ---
