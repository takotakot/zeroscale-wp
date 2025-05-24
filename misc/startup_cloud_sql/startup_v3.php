<?php
// --- Autoloader ---
require_once __DIR__ . '/vendor/autoload.php';

// --- Use Statements for Google Cloud SDK ---
use Google\Cloud\Sql\V1\Client\SqlInstancesServiceClient;
use Google\Cloud\Sql\V1\DatabaseInstance;
use Google\Cloud\Sql\V1\Settings;
use Google\Cloud\Sql\V1\DatabaseInstance\SqlInstanceState;
use Google\Cloud\Sql\V1\Settings\SqlActivationPolicy; // Corrected name
use Google\Cloud\Sql\V1\SqlInstancesGetRequest;
use Google\Cloud\Sql\V1\SqlInstancesPatchRequest;
// SqlInstancesPatchRequest は patch メソッドの直接の引数ではないため、ここでは不要かもしれません。
// ただし、FieldMask は更新内容の指定に間接的に関連します。
use Google\ApiCore\ApiException;
use Google\Protobuf\FieldMask; // FieldMask は update_mask としてオプションで渡す場合に使う

// --- Configuration from Environment Variables (defined as constants) ---
define('PROJECT_ID', $_SERVER['PROJECT_ID'] ?? null);
define('WORDPRESS_DB_INSTANCE_ID', $_SERVER['WORDPRESS_DB_INSTANCE_ID'] ?? null);
define('WORDPRESS_DB_NAME', $_SERVER['WORDPRESS_DB_NAME'] ?? null);
define('WORDPRESS_DB_USER', $_SERVER['WORDPRESS_DB_USER'] ?? null);
define('WORDPRESS_DB_PASSWORD', $_SERVER['WORDPRESS_DB_PASSWORD'] ?? null);
define('WORDPRESS_DB_HOST', $_SERVER['WORDPRESS_DB_HOST'] ?? null);
define('WORDPRESS_DB_CONNECT_TIMEOUT_SECONDS', 3);

// --- Helper Functions ---
function logMessage($level, $message) {
    error_log("[$level] CloudRunStartupProbe: $message");
}

function terminateProbeAsServiceUnavailable($message) {
    http_response_code(503);
    logMessage('INFO', "Probe failed (503): $message. Cloud Run should retry.");
    echo "Service Unavailable: " . htmlspecialchars($message);
    exit;
}

// --- Main Probe Logic ---
if (PROJECT_ID === null || WP_DB_INSTANCE_ID === null || WP_DB_NAME === null || WP_DB_USER === null || WP_DB_PASSWORD === null || WP_DB_HOST === null) {
    http_response_code(500);
    logMessage('CRITICAL', 'One or more required environment variables are not set. Please check PROJECT_ID, WP_DB_INSTANCE_ID, WP_DB_NAME, WP_DB_USER, WP_DB_PASSWORD, WP_DB_HOST.');
    echo "Critical Error: Missing required environment variables.";
    exit;
}

$mysqli = mysqli_init();
if (!$mysqli) {
    http_response_code(500);
    logMessage('CRITICAL', "mysqli_init() failed.");
    echo "Critical Error: Failed to initialize mysqli.";
    exit;
}
if (!$mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, WP_DB_CONNECT_TIMEOUT_SECONDS)) {
    logMessage('WARNING', "Failed to set MYSQLI_OPT_CONNECT_TIMEOUT. Connection attempt will use default timeout.");
}

logMessage('INFO', "Attempting DB connection to host '" . WP_DB_HOST . "' for DB '" . WP_DB_NAME . "' with timeout " . WP_DB_CONNECT_TIMEOUT_SECONDS . "s.");
mysqli_report(MYSQLI_REPORT_OFF);
@$mysqli->real_connect(WP_DB_HOST, WP_DB_USER, WP_DB_PASSWORD, WP_DB_NAME);

if ($mysqli && !$mysqli->connect_errno) {
    $result = @$mysqli->query('SELECT 1');
    if ($result && $row = $result->fetch_assoc()) {
        if (($row['1'] ?? null) === '1') {
            $mysqli->close();
            http_response_code(200);
            logMessage('SUCCESS', "Database connection successful. Probe succeeded.");
            echo "OK";
            exit;
        }
    }
    $dbQueryError = $mysqli->error ?? 'N/A';
    logMessage('WARNING', "Database connection established to '" . WP_DB_HOST . "', but 'SELECT 1' query failed or returned an unexpected result. MySQLi error: " . $dbQueryError);
    if ($mysqli) $mysqli->close();
} else {
    $mysqli_connect_error_code = $mysqli->connect_errno ?? 0;
    $mysqli_connect_error_msg = $mysqli->connect_error ?? "mysqli_real_connect failed, mysqli object might not be fully initialized or error message unavailable.";
    if ($mysqli_connect_error_code) {
         $mysqli_connect_error_msg = $mysqli_connect_error_code . ": " . $mysqli_connect_error_msg;
    }
    logMessage('INFO', "Initial database connection to host '" . WP_DB_HOST . "' for DB '" . WP_DB_NAME . "' failed. MySQLi connect error: $mysqli_connect_error_msg");
}

logMessage('INFO', "Database not directly accessible. Checking Cloud SQL instance '" . WP_DB_INSTANCE_ID . "' (Project: '" . PROJECT_ID . "') state.");

$sqlClient = null;
$currentStateStr = 'UNKNOWN';
$currentActivationPolicyStr = 'UNKNOWN';

try {
    $sqlClient = new \Google\Cloud\Sql\V1\Client\SqlInstancesServiceClient();
    logMessage('DEBUG', "SqlInstancesServiceClient instantiated successfully.");

    $getRequest = new SqlInstancesGetRequest();
    $getRequest->setProject(PROJECT_ID);
    $getRequest->setInstance(WP_DB_INSTANCE_ID);
    logMessage('DEBUG', "SqlInstancesGetRequest prepared for project '" . PROJECT_ID . "', instance '" . WP_DB_INSTANCE_ID . "'.");

    $instance = $sqlClient->get($getRequest);
    logMessage('DEBUG', "sqlClient->get() method called successfully.");

    $currentState = $instance->getState();
    $currentSettings = $instance->getSettings();
    $currentActivationPolicy = $currentSettings ? $currentSettings->getActivationPolicy() : SqlActivationPolicy::SQL_ACTIVATION_POLICY_UNSPECIFIED;

    $currentStateStr = is_callable([SqlInstanceState::class, 'name']) ? SqlInstanceState::name($currentState) : (string)$currentState;
    $currentActivationPolicyStr = is_callable([SqlActivationPolicy::class, 'name']) ? SqlActivationPolicy::name($currentActivationPolicy) : (string)$currentActivationPolicy;

    logMessage('INFO', "Cloud SQL instance '" . WP_DB_INSTANCE_ID . "' current state: $currentStateStr, Activation Policy: $currentActivationPolicyStr.");

    if ($currentState === SqlInstanceState::RUNNABLE && $currentActivationPolicy === SqlActivationPolicy::NEVER) {
        logMessage('INFO', "Cloud SQL instance '" . WP_DB_INSTANCE_ID . "' is RUNNABLE with SqlActivationPolicy NEVER. Attempting to change ActivationPolicy to ALWAYS to start it...");
        
        $settingsToUpdate = new Settings();
        $settingsToUpdate->setActivationPolicy(SqlActivationPolicy::ALWAYS);
        
        // $dbInstanceToUpdate = new DatabaseInstance();
        // // For a patch request, the body (DatabaseInstance) typically contains only the fields to be updated.
        // // The instance name itself is usually specified as a separate argument to the patch method
        // // or as part of a wrapper request object. Here, the client expects project & instance as direct args.
        // // It's good practice to also set the 'name' in the body if the API expects it for consistency,
        // // but often it's derived from the path parameters.
        // // Let's ensure the body *only* contains what we want to change, guided by the updateMask.
        // $dbInstanceToUpdate->setSettings($settingsToUpdate);
        
        $dbInstanceBodyForPatch = new DatabaseInstance();
        // body には、更新対象のインスタンス名と、変更する設定を含む
        $dbInstanceBodyForPatch->setName(WP_DB_INSTANCE_ID);
        $dbInstanceBodyForPatch->setSettings($settingsToUpdate);

        // The FieldMask specifies which fields of the 'body' resource should be updated.
        $updateMask = new FieldMask();
        $updateMask->setPaths(['settings.activationPolicy']);

        // ★★★ Corrected patch method call based on documentation for SqlInstancesServiceClient::patch ★★★
        // The patch method for this client expects:
        // patch(string $project, string $instance, \Google\Cloud\Sql\V1\DatabaseInstance $body, array $optionalArgs = [])
        $optionalArgs = [
            'updateMask' => $updateMask // Pass FieldMask as an optional argument if supported this way.
                                        // Often, the 'updateMask' key might need to be 'update_mask' (snake_case)
                                        // or the FieldMask object needs to be part of the $body (DatabaseInstance)
                                        // or part of a specific PatchRequest object if that were used.
                                        // The linked doc doesn't detail optionalArgs for patch well, but this is a common pattern.
                                        // If 'updateMask' is not a recognized key in $optionalArgs, the API might ignore it
                                        // or use a default behavior (e.g., update all fields present in $body).
                                        // Given we only set 'settings' in $dbInstanceToUpdate, it should be specific enough.
        ];
        
        // The full instance name might be needed if the body's 'name' field is used for identification,
        // but the patch method signature takes project and instance separately.
        // $fullInstanceName = "projects/" . PROJECT_ID . "/instances/" . WP_DB_INSTANCE_ID;
        // $dbInstanceToUpdate->setName($fullInstanceName); // Usually not needed if project/instance are path params

        // ★★★ Corrected patch method call using SqlInstancesPatchRequest ★★★
        $patchRequest = new SqlInstancesPatchRequest();
        $patchRequest->setProject(PROJECT_ID);         // どのプロジェクトか
        $patchRequest->setInstance(WP_DB_INSTANCE_ID); // どのインスタンスか (URLパス用)
        $patchRequest->setBody($dbInstanceBodyForPatch); // 更新内容 (DatabaseInstance オブジェクト)

        // Call patch with project, instance, body, and optionalArgs (containing updateMask)
        // $operation = $sqlClient->patch(PROJECT_ID, WP_DB_INSTANCE_ID, $dbInstanceToUpdate, $optionalArgs);
        $operation = $sqlClient->patch($patchRequest); 
        // ★★★ End of corrected patch method call ★★★
        
        logMessage('INFO', "Cloud SQL instance '" . WP_DB_INSTANCE_ID . "' activation policy change to ALWAYS initiated. Operation name: " . $operation->getName());

    } elseif ($currentState === SqlInstanceState::RUNNABLE) {
        logMessage('WARNING', "Cloud SQL instance '" . WP_DB_INSTANCE_ID . "' is RUNNABLE (Policy: $currentActivationPolicyStr), but DB connection via '" . WP_DB_HOST . "' failed. Possible Cloud SQL Proxy/network issue, or instance is still initializing.");
    } else {
        logMessage('INFO', "Cloud SQL instance '" . WP_DB_INSTANCE_ID . "' is in state '$currentStateStr'. Not attempting to modify activation policy. Probe will retry.");
    }

} catch (ApiException $e) {
    logMessage('ERROR', 'Google Cloud SQL API Error: ' . $e->getMessage() . ' (API Code: ' . $e->getCode() . ') - Trace: ' . $e->getTraceAsString());
} catch (\Error $e) {
    logMessage('CRITICAL_ERROR', 'PHP Error: ' . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine() . " - Trace: " . $e->getTraceAsString());
} catch (Exception $e) {
    logMessage('ERROR', 'Unexpected generic exception: ' . $e->getMessage() . ' - Trace: ' . $e->getTraceAsString());
} finally {
    if ($sqlClient) {
        try {
            $sqlClient->close();
            logMessage('DEBUG', "SqlInstancesServiceClient closed.");
        } catch (Exception $closeException) {
            logMessage('ERROR', "Exception while closing SqlInstancesServiceClient: " . $closeException->getMessage());
        }
    }
}

terminateProbeAsServiceUnavailable("Database not ready. Cloud SQL instance '" . WP_DB_INSTANCE_ID . "' current state: $currentStateStr, Activation Policy: $currentActivationPolicyStr. Probe will retry.");
