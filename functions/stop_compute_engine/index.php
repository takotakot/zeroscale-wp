<?php // phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols
/**
 * Pub/Sub Push Subscription Handler to Stop a Compute Engine Instance
 *
 * This script is intended to be deployed as a Cloud Run (or similar) endpoint
 * that receives Pub/Sub push messages (JSON, base64-encoded) and stops a specified
 * Compute Engine instance using the Google Cloud PHP SDK.
 *
 * Required Environment Variables (accessible via $_SERVER):
 * - PROJECT_ID: Your Google Cloud Project ID.
 * - GCE_ZONE: The zone where the Compute Engine instance resides (e.g., 'us-central1-a').
 * - GCE_INSTANCE_NAME: The name of the Compute Engine instance to stop.
 *
 * PHP version >= 8.0
 *
 * @category Google_Cloud
 * @package  ZeroScale_WordPress
 * @author   takotakot <takotakot@users.noreply.github.com>
 * @license  CC0-1.0 / MIT-X
 * @link     none
 */

require_once __DIR__ . '/vendor/autoload.php';

use CloudEvents\V1\CloudEventInterface;
use Google\CloudFunctions\FunctionsFramework;
use Google\Cloud\Compute\V1\Client\InstancesClient;
use Google\Cloud\Compute\V1\StopInstanceRequest;
use Google\ApiCore\ApiException;

// --- Helper Functions ---

/**
 * Logs a message to PHP's error log with a specified severity level.
 *
 * @param string $level Log level (e.g., DEBUG, INFO, WARNING, ERROR, CRITICAL).
 * @param string $message The message to log.
 */
function logMessage($level, $message) {
    error_log("[$level] StopGCE: $message");
}

// Register a CloudEvent function with the Functions Framework
FunctionsFramework::cloudEvent('stopComputeEngine', 'stopComputeEngine');

/**
 * Handles CloudEvent messages from Pub/Sub and stops a Compute Engine instance.
 *
 * @param CloudEventInterface $event The CloudEvent instance containing the event data.
 */
function stopComputeEngine(CloudEventInterface $event): void
{
    // 1. 環境変数の取得とバリデーション
    $projectId = $_SERVER['PROJECT_ID'] ?? null;
    $zone = $_SERVER['GCE_ZONE'] ?? null;
    $instanceId = $_SERVER['WORDPRESS_DB_INSTANCE_ID'] ?? null;

    if (!$projectId || !$zone || !$instanceId) {
        logMessage('CRITICAL', 'Missing required environment variables.');
        throw new RuntimeException('Missing required environment variables.');
    }

    // 2. Pub/Subメッセージの内容をログ出力（デバッグ用）
    $data = $event->getData();
    logMessage('INFO', 'Received CloudEvent data: ' . json_encode($data));

    // 3. Compute Engineインスタンスの停止処理
    try {
        // Compute Engine APIクライアントの初期化
        $client = new InstancesClient();
        // 停止リクエストの作成
        $stopRequest = (new StopInstanceRequest())
            ->setProject($projectId)
            ->setZone($zone)
            ->setInstance($instanceId);
        // 停止APIの呼び出し
        $operation = $client->stop($stopRequest);
        logMessage('INFO', "Stop operation started for instance '$instanceId' in zone '$zone'. Operation: " . $operation->getName());
    } catch (ApiException $e) {
        logMessage('ERROR', 'Google Compute Engine API Error: ' . $e->getMessage());
        throw new RuntimeException('Failed to stop Compute Engine instance: ' . $e->getMessage(), 0, $e);
    } catch (Exception $e) {
        logMessage('ERROR', 'Unexpected error: ' . $e->getMessage());
        throw $e;
    }
}
