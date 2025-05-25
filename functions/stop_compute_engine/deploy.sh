# Cloud Run (Functions Framework for PHP) で stopComputeEngine 関数をデプロイするスクリプト
# 必要な環境変数を定義し、gcloud コマンドでデプロイする

# === 必要な環境変数 ===
export PROJECT_ID="your-gcp-project-id"
export REGION="us-west1"
export FUNCTION_NAME="stop-compute-engine"
export GCE_ZONE="us-west1-c"
export GCE_INSTANCE_NAME="zero-wp-db"
export EVENT_SERVICE_ACCOUNT="event servie account"

# === Cloud Run サービスのデプロイ ===
gcloud run deploy "${FUNCTION_NAME}" \
    --project="${PROJECT_ID}" \
    --region="${REGION}" \
    --source=. \
    --function=stopComputeEngine \
    --set-env-vars="PROJECT_ID=${PROJECT_ID},GCE_ZONE=${GCE_ZONE},GCE_INSTANCE_NAME=${GCE_INSTANCE_NAME}" \
    --runtime=php83 \
    --memory=512Mi \
    --timeout=60s

gcloud eventarc triggers create stop-compute-engine \
    --location="${REGION}" \
    --destination-run-service="${FUNCTION_NAME}" \
    --destination-run-region="${REGION}" \
    --event-filters="type=google.cloud.pubsub.topic.v1.messagePublished" \
    --event-filters="resource.name=projects/${PROJECT_ID}/topics/YOUR_TOPIC_NAME" \
    --service-account="${EVENT_SERVICE_ACCOUNT}" \
