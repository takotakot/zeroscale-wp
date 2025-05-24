<?php
/**
 * Cloud Run Startup Probe for WordPress with Cloud SQL Auto-Activation.
 *
 * This script performs the following actions:
 * 1. Attempts to connect to the WordPress MySQL database with a short timeout.
 * 2. If the database connection is successful, it exits with HTTP 200 (OK).
 * 3. If the database connection fails, it checks the state of the associated Cloud SQL instance.
 * 4. If the Cloud SQL instance is determined to be stopped by the user (state RUNNABLE
 * and activation policy NEVER), it sends an asynchronous API call to change the
 * activation policy to ALWAYS, effectively starting the instance. This patch operation
 * explicitly uses a FieldMask to update only the 'settings.activationPolicy' field.
 * 5. In all cases where the database is not immediately ready, it exits with
 * HTTP 503 (Service Unavailable) to signal Cloud Run to retry the probe.
 *
 * Required Environment Variables (accessible via $_SERVER):
 * - PROJECT_ID: Your Google Cloud Project ID.
 * - WP_DB_INSTANCE_ID: The ID of your Cloud SQL instance.
 * - WP_DB_NAME: The name of the WordPress database.
 * - WP_DB_USER: The MySQL database username.
 * - WP_DB_PASSWORD: The MySQL database password.
 * - WP_DB_HOST: The MySQL hostname (e.g., '127.0.0.1' if using Cloud SQL Proxy).
 */

// --- Autoloader ---
require_once __DIR__ . '/vendor/autoload.php';

// --- Use Statements for Google Cloud SDK ---
use Google\Cloud\Sql\V1\Client\SqlInstancesServiceClient;
use Google\Cloud\Sql\V1\DatabaseInstance;
use Google\Cloud\Sql\V1\Settings;
use Google\Cloud\Sql\V1\DatabaseInstance\SqlInstanceState;
use Google\Cloud\Sql\V1\Settings\SqlActivationPolicy;
use Google\Cloud\Sql\V1\SqlInstancesGetRequest;
use Google\Cloud\Sql\V1\SqlInstancesPatchRequest;
use Google\ApiCore\ApiException;
use Google\Protobuf\FieldMask; // FieldMask will be used explicitly

// --- Configuration Constants ---
define('PROJECT_ID', $_SERVER['PROJECT_ID'] ?? null);
define('WORDPRESS_DB_INSTANCE_ID', $_SERVER['WORDPRESS_DB_INSTANCE_ID'] ?? null);
define('WORDPRESS_DB_NAME', $_SERVER['WORDPRESS_DB_NAME'] ?? null);
define('WORDPRESS_DB_USER', $_SERVER['WORDPRESS_DB_USER'] ?? null);
define('WORDPRESS_DB_PASSWORD', $_SERVER['WORDPRESS_DB_PASSWORD'] ?? null);
define('WORDPRESS_DB_HOST', $_SERVER['WORDPRESS_DB_HOST'] ?? null);
define('WORDPRESS_DB_CONNECT_TIMEOUT_SECONDS', 3); // MySQLi connection timeout in seconds

// --- Helper Functions ---

/**
 * Logs a message to PHP's error log with a specified severity level.
 *
 * @param string $level Log level (e.g., DEBUG, INFO, WARNING, ERROR, CRITICAL).
 * @param string $message The message to log.
 */
function logMessage($level, $message) {
    error_log("[$level] CloudRunStartupProbe: $message");
}

/**
 * Terminates the probe script with an HTTP 503 Service Unavailable status.
 * This signals to Cloud Run that the application is not yet ready and the probe should be retried.
 *
 * @param string $message A message detailing why the service is unavailable.
 */
function terminateProbeAsServiceUnavailable($message) {
    http_response_code(503);
    logMessage('INFO', "Probe failed (503): $message. Cloud Run should retry.");
    echo "Service Unavailable: " . htmlspecialchars($message);
    exit;
}

// --- Main Probe Logic ---

// 1. Validate essential configuration
if (PROJECT_ID === null || WP_DB_INSTANCE_ID === null || WP_DB_NAME === null || WP_DB_USER === null || WP_DB_PASSWORD === null || WP_DB_HOST === null) {
    http_response_code(500);
    logMessage('CRITICAL', 'One or more required environment variables are not set. Please check PROJECT_ID, WP_DB_INSTANCE_ID, WP_DB_NAME, WP_DB_USER, WP_DB_PASSWORD, WP_DB_HOST.');
    echo "Critical Error: Missing required environment variables.";
    exit;
}

// 2. Attempt to connect to the WordPress database
$mysqli = mysqli_init();
if (!$mysqli) {
    http_response_code(500);
    logMessage('CRITICAL', "mysqli_init() failed. Cannot proceed with database connection attempt.");
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
            logMessage('SUCCESS', "Database connection successful and query 'SELECT 1' returned '1'. Probe succeeded.");
            echo "OK";
            exit;
        }
    }
    $dbQueryError = $mysqli->error ? $mysqli->errno . ": " . $mysqli->error : 'N/A';
    logMessage('WARNING', "Database connection established to '" . WP_DB_HOST . "', but 'SELECT 1' query failed or returned an unexpected result. MySQLi error: " . $dbQueryError);
    if ($mysqli) $mysqli->close();
} else {
    $mysqli_connect_error_code = $mysqli->connect_errno ?? 0;
    $mysqli_connect_error_msg = $mysqli->connect_error ?? "mysqli_real_connect failed (mysqli object might not be fully initialized or error message unavailable).";
    if ($mysqli_connect_error_code) {
         $mysqli_connect_error_msg = $mysqli_connect_error_code . ": " . $mysqli_connect_error_msg;
    }
    logMessage('INFO', "Initial database connection to host '" . WP_DB_HOST . "' for DB '" . WP_DB_NAME . "' failed. MySQLi connect error: $mysqli_connect_error_msg");
}

// 3. Database connection failed. Proceed to check Cloud SQL instance state.
logMessage('INFO', "Database not directly accessible. Checking Cloud SQL instance '" . WP_DB_INSTANCE_ID . "' (Project: '" . PROJECT_ID . "') state.");

$sqlClient = null;
$currentStateStr = 'UNKNOWN_STATE';
$currentActivationPolicyStr = 'UNKNOWN_POLICY';

try {
    $sqlClient = new \Google\Cloud\Sql\V1\Client\SqlInstancesServiceClient();
    logMessage('DEBUG', "SqlInstancesServiceClient instantiated.");

    $getRequest = new SqlInstancesGetRequest();
    $getRequest->setProject(PROJECT_ID);
    $getRequest->setInstance(WP_DB_INSTANCE_ID);
    logMessage('DEBUG', "SqlInstancesGetRequest prepared for project '" . PROJECT_ID . "', instance '" . WP_DB_INSTANCE_ID . "'.");

    $instance = $sqlClient->get($getRequest);
    logMessage('DEBUG', "sqlClient->get() method called successfully for instance '" . WP_DB_INSTANCE_ID . "'.");

    $currentState = $instance->getState();
    $currentSettings = $instance->getSettings();
    $currentActivationPolicy = $currentSettings ? $currentSettings->getActivationPolicy() : SqlActivationPolicy::SQL_ACTIVATION_POLICY_UNSPECIFIED;

    $currentStateStr = SqlInstanceState::name($currentState);
    $currentActivationPolicyStr = SqlActivationPolicy::name($currentActivationPolicy);

    logMessage('INFO', "Cloud SQL instance '" . WP_DB_INSTANCE_ID . "' current state: $currentStateStr, Activation Policy: $currentActivationPolicyStr.");

    if ($currentState === SqlInstanceState::RUNNABLE && $currentActivationPolicy === SqlActivationPolicy::NEVER) {
        logMessage('INFO', "Cloud SQL instance '" . WP_DB_INSTANCE_ID . "' is RUNNABLE with SqlActivationPolicy NEVER. Attempting to change ActivationPolicy to ALWAYS to start it...");
        
        $settingsToUpdate = new Settings();
        $settingsToUpdate->setActivationPolicy(SqlActivationPolicy::ALWAYS);
        
        $dbInstanceBodyForPatch = new DatabaseInstance();
        // For patch, the 'name' in the body should be the instance ID only, as per documentation.
        $dbInstanceBodyForPatch->setName(WP_DB_INSTANCE_ID);
        $dbInstanceBodyForPatch->setSettings($settingsToUpdate);
        
        // Explicitly define the FieldMask for the patch operation.
        $updateMask = new FieldMask();
        $updateMask->setPaths(['settings.activationPolicy']); // Specify only the field to be updated.

        $patchRequest = new SqlInstancesPatchRequest();
        $patchRequest->setProject(PROJECT_ID);
        $patchRequest->setInstance(WP_DB_INSTANCE_ID);
        $patchRequest->setBody($dbInstanceBodyForPatch);
        
        // Attempt to pass the FieldMask as an optional argument to the patch method.
        // The exact key for the updateMask in $optionalArgs ('updateMask' or 'update_mask')
        // might vary depending on the gRPC client generation specifics.
        // We'll try 'update_mask' as it's common in Google APIs.
        // If this doesn't work, the API might expect the FieldMask to be set on the
        // SqlInstancesPatchRequest object itself (if a setter like setUpdateMask exists),
        // or it might infer from fields present in the body (less explicit).
        $optionalArgs = [
            'update_mask' => $updateMask
        ];
        logMessage('DEBUG', "Preparing to call patch with update_mask for 'settings.activationPolicy'.");
        
        $operation = $sqlClient->patch($patchRequest, $optionalArgs);
        logMessage('INFO', "Cloud SQL instance '" . WP_DB_INSTANCE_ID . "' start operation (set ActivationPolicy to ALWAYS) initiated. Operation name: " . $operation->getName());

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
            logMessage('DEBUG', "SqlInstancesServiceClient closed for instance '" . WP_DB_INSTANCE_ID . "'.");
        } catch (Exception $closeException) {
            logMessage('ERROR', "Exception while closing SqlInstancesServiceClient for instance '" . WP_DB_INSTANCE_ID . "': " . $closeException->getMessage());
        }
    }
}

terminateProbeAsServiceUnavailable("Database not ready. Cloud SQL instance '" . WP_DB_INSTANCE_ID . "' current state: $currentStateStr, Activation Policy: $currentActivationPolicyStr. Probe will retry.");
