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
  gce_zone            = "us-west1-c"
  gce_instance_id     = "zero-wp-db"
  gce_ip              = "10.138.0.2"
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

# Monitoring Service Agent に Pub/Sub パブリッシャーの権限を付与
resource "google_project_iam_member" "monitoring_service_agent_pubsub" {
  project = var.project_id
  role    = "roles/pubsub.publisher"
  member  = "serviceAccount:service-${data.google_project.project.number}@gcp-sa-monitoring-notification.iam.gserviceaccount.com"
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

# Artifact Registry Repository
resource "google_artifact_registry_repository" "gcr" {
  location      = "us"
  provider      = google
  repository_id = "gcr.io"
  format        = "docker"

  depends_on = [google_project_service.services["artifactregistry.googleapis.com"]]
}

output "artifact_registry_repository_gcr_url" {
  value = "gcr.io/${var.project_id}/"
}

import {
  id = "projects/${var.project_id}/locations/us/repositories/gcr.io"
  to = google_artifact_registry_repository.gcr
}

# --- Cloud Storage バケットの作成 ---
# zero-wp-admin-bucket
resource "google_storage_bucket" "zero-wp-admin-bucket" {
  name     = "zero-wp-admin-bucket"
  location = "US-WEST1"

  uniform_bucket_level_access = true
  force_destroy               = false

  labels = {
    service = local.service_label_value
  }

  lifecycle_rule {
    action {
      type = "Delete"
    }
    condition {
      age                = 0
      num_newer_versions = 2
      with_state         = "ARCHIVED"
    }
  }

  lifecycle_rule {
    action {
      type = "Delete"
    }
    condition {
      days_since_noncurrent_time = 7
      matches_prefix             = []
      matches_storage_class      = []
      matches_suffix             = []
    }
  }

  versioning {
    enabled = true
  }

  depends_on = [google_project_service.services["storage.googleapis.com"]]
}

# import {
#   id = "zero-wp-admin-bucket"
#   to = google_storage_bucket.zero-wp-admin-bucket
# }

resource "google_storage_bucket" "zero-wp-uploads" {
  name     = "zero-wp-uploads"
  location = "US-WEST1"

  uniform_bucket_level_access = true
  force_destroy               = false

  labels = {
    service = local.service_label_value
  }

  lifecycle_rule {
    action {
      type = "Delete"
    }
    condition {
      age                = 0
      num_newer_versions = 2
      with_state         = "ARCHIVED"
    }
  }

  lifecycle_rule {
    action {
      type = "Delete"
    }
    condition {
      days_since_noncurrent_time = 7
      matches_prefix             = []
      matches_storage_class      = []
      matches_suffix             = []
    }
  }

  versioning {
    enabled = true
  }

  depends_on = [google_project_service.services["storage.googleapis.com"]]
}

# import {
#   id = "zero-wp-uploads"
#   to = google_storage_bucket.zero-wp-uploads
# }

# --- Cloud Build サービスアカウントの作成 ---
resource "google_service_account" "zero-wp-cloud-build" {
  account_id   = "zero-wp-build"
  display_name = "Zero WP Cloud Build Service Account"
  project      = var.project_id

  depends_on = [google_project_service.services["cloudbuild.googleapis.com"]]
}

resource "google_project_iam_member" "cloud_build_service_account" {
  project = var.project_id
  role    = "roles/cloudbuild.builds.builder"
  member  = "serviceAccount:${google_service_account.zero-wp-cloud-build.email}"

  depends_on = [google_project_service.services["cloudbuild.googleapis.com"]]
}

resource "google_project_iam_member" "cloud_build_service_account_artifact_registry_writer" {
  project = var.project_id
  role    = "roles/artifactregistry.writer"
  member  = "serviceAccount:${google_service_account.zero-wp-cloud-build.email}"

  depends_on = [google_project_service.services["artifactregistry.googleapis.com"]]
}

# --- Cloud Run サービスの作成 ---
resource "google_service_account" "zero-wp-run" {
  account_id   = "zero-wp-run"
  display_name = "Zero WP Cloud Run Service Account"
  project      = var.project_id

  depends_on = [google_project_service.services["iam.googleapis.com"]]
}

resource "google_project_iam_member" "secret_accessor" {
  project = var.project_id
  role    = "roles/secretmanager.secretAccessor"
  member  = "serviceAccount:${google_service_account.zero-wp-run.email}"

  depends_on = [google_project_service.services["run.googleapis.com"]]
}

# IAM policy for Cloud Run to access the bucket
resource "google_storage_bucket_iam_member" "cloud_run_bucket_access" {
  bucket = google_storage_bucket.zero-wp-uploads.name
  role   = "roles/storage.objectAdmin"
  member = "serviceAccount:${google_service_account.zero-wp-run.email}"

  depends_on = [google_storage_bucket.zero-wp-uploads]
}

# IAM policy for Cloud Run to Start/Stop instances
resource "google_project_iam_member" "cloud_run_instance_admin" {
  project = var.project_id
  role    = "roles/compute.instanceAdmin.v1"
  member  = "serviceAccount:${google_service_account.zero-wp-run.email}"

  depends_on = [google_project_service.services["run.googleapis.com"]]
}
