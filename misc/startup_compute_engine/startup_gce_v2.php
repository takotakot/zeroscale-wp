<?php
/**
 * Cloud Run Startup Probe for WordPress with Compute Engine (MySQL Host) Auto-Activation.
 * (Comments as before)
 */

// --- Autoloader ---
require_once __DIR__ . '/vendor/autoload.php';

// --- Use Statements for Google Cloud SDK ---
// Ensure these are the correct FQCNs as per your SDK version and documentation
use Google\Cloud\Compute\V1\Client\InstancesClient; // Using the 'Client' sub-namespace client
use Google\Cloud\Compute\V1\GetInstanceRequest;
use Google\Cloud\Compute\V1\StartInstanceRequest;
use Google\Cloud\Compute\V1\Instance\Status as GceInstanceStatusEnum;
use Google\ApiCore\ApiException;

// --- Configuration Constants ---
define('PROJECT_ID', $_SERVER['PROJECT_ID'] ?? null);
define('GCE_ZONE', $_SERVER['GCE_ZONE'] ?? null);
define('GCE_INSTANCE_NAME', $_SERVER['WORDPRESS_DB_INSTANCE_ID'] ?? null);
define('WORDPRESS_DB_NAME', $_SERVER['WORDPRESS_DB_NAME'] ?? null);
define('WORDPRESS_DB_USER', $_SERVER['WORDPRESS_DB_USER'] ?? null);
define('WORDPRESS_DB_PASSWORD', $_SERVER['WORDPRESS_DB_PASSWORD'] ?? null);
define('WORDPRESS_DB_HOST', $_SERVER['WORDPRESS_DB_HOST'] ?? null);
define('WORDPRESS_DB_CONNECT_TIMEOUT_SECONDS', 5);

// --- Helper Functions ---
function logMessage($level, $message) {
    error_log("[$level] CloudRunStartupProbeGCE: $message");
}

function terminateProbeAsServiceUnavailable($message) {
    http_response_code(503);
    logMessage('INFO', "Probe failed (503): $message. Cloud Run should retry.");
    echo "Service Unavailable: " . htmlspecialchars($message);
    exit;
}

// --- Main Probe Logic ---

if (PROJECT_ID === null || GCE_ZONE === null || GCE_INSTANCE_NAME === null || WP_DB_NAME === null || WP_DB_USER === null || WP_DB_PASSWORD === null || WP_DB_HOST === null) {
    http_response_code(500);
    logMessage('CRITICAL', 'One or more required environment variables are not set.');
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
    logMessage('WARNING', "Failed to set MYSQLI_OPT_CONNECT_TIMEOUT.");
}

logMessage('INFO', "Attempting DB connection to host '" . WP_DB_HOST . "' (on GCE) for DB '" . WP_DB_NAME . "' with timeout " . WP_DB_CONNECT_TIMEOUT_SECONDS . "s.");
mysqli_report(MYSQLI_REPORT_OFF);
@$mysqli->real_connect(WP_DB_HOST, WP_DB_USER, WP_DB_PASSWORD, WP_DB_NAME);

if ($mysqli && !$mysqli->connect_errno) {
    $result = @$mysqli->query('SELECT 1');
    if ($result && $row = $result->fetch_assoc()) {
        if (($row['1'] ?? null) === '1') {
            $mysqli->close();
            http_response_code(200);
            logMessage('SUCCESS', "Database connection to MySQL on GCE host '" . WP_DB_HOST . "' successful. Probe succeeded.");
            echo "OK";
            exit;
        }
    }
    $dbQueryError = $mysqli->error ? $mysqli->errno . ": " . $mysqli->error : 'N/A';
    logMessage('WARNING', "DB connection to GCE host '" . WP_DB_HOST . "' established, but 'SELECT 1' query failed. MySQLi error: " . $dbQueryError);
    if ($mysqli) $mysqli->close();
} else {
    $mysqli_connect_error_code = $mysqli->connect_errno ?? 0;
    $mysqli_connect_error_msg = $mysqli->connect_error ?? "mysqli_real_connect failed.";
    if ($mysqli_connect_error_code) {
         $mysqli_connect_error_msg = $mysqli_connect_error_code . ": " . $mysqli_connect_error_msg;
    }
    logMessage('INFO', "Initial DB connection to GCE host '" . WP_DB_HOST . "' for DB '" . WP_DB_NAME . "' failed. MySQLi connect error: $mysqli_connect_error_msg");
}

logMessage('INFO', "Database on GCE not directly accessible. Checking GCE instance '" . GCE_INSTANCE_NAME . "' (Zone: '" . GCE_ZONE . "', Project: '" . PROJECT_ID . "') state.");

$gceClient = null;
$gceInstanceStatusString = 'UNKNOWN_STATUS';
$gceInstanceStatusEnumValue = -1;

try {
    // Ensure we are using the correct client, matching the 'use' statement.
    $gceClient = new InstancesClient();
    logMessage('DEBUG', "Compute Engine InstancesClient instantiated.");

    // ★★★ Use GetInstanceRequest object for get() method ★★★
    $getRequest = (new GetInstanceRequest())
        ->setProject(PROJECT_ID)
        ->setZone(GCE_ZONE)
        ->setInstance(GCE_INSTANCE_NAME);
    logMessage('DEBUG', "GetInstanceRequest prepared. Calling gceClient->get().");
    $instanceData = $gceClient->get($getRequest);
    // ★★★ End of get() method change ★★★

    $statusStringFromApi = $instanceData->getStatus();
    logMessage('DEBUG', "Raw GCE instance status string from API: '" . $statusStringFromApi . "'");
    $gceInstanceStatusString = $statusStringFromApi;
    $gceInstanceStatusEnumValue = GceInstanceStatusEnum::value($statusStringFromApi);

    if ($gceInstanceStatusEnumValue === null && $statusStringFromApi !== '') {
        logMessage('WARNING', "Could not map GCE status string '" . $statusStringFromApi . "' to a known enum integer value.");
        $gceInstanceStatusEnumValue = -1;
    }
    logMessage('INFO', "GCE instance '" . GCE_INSTANCE_NAME . "' current status: $gceInstanceStatusString (Enum value: " . ($gceInstanceStatusEnumValue ?? 'null') . ")");

    if ($gceInstanceStatusEnumValue === GceInstanceStatusEnum::TERMINATED) {
        logMessage('INFO', "GCE instance '" . GCE_INSTANCE_NAME . "' is TERMINATED. Attempting to start it...");
        
        // ★★★ Use StartInstanceRequest object for start() method ★★★
        $startRequest = (new StartInstanceRequest())
            ->setProject(PROJECT_ID)
            ->setZone(GCE_ZONE)
            ->setInstance(GCE_INSTANCE_NAME);
        logMessage('DEBUG', "StartInstanceRequest prepared. Calling gceClient->start().");
        $operationResponse = $gceClient->start($startRequest);
        // ★★★ End of start() method change ★★★

        $operationName = $operationResponse->getName();
        $isDone = $operationResponse->isDone();

        logMessage('INFO', "GCE instance '" . GCE_INSTANCE_NAME . "' start operation initiated. Operation Name: " . ($operationName ?: 'N/A') . ", Is Done (immediately after call): " . ($isDone ? 'true' : 'false'));
   

    } elseif ($gceInstanceStatusEnumValue === GceInstanceStatusEnum::RUNNING) {
        logMessage('WARNING', "GCE instance '" . GCE_INSTANCE_NAME . "' is RUNNING, but DB connection via '" . WP_DB_HOST . "' failed.");
    } else {
        logMessage('INFO', "GCE instance '" . GCE_INSTANCE_NAME . "' is in state '$gceInstanceStatusString'. Not attempting to start. Probe will retry.");
    }

} catch (ApiException $e) {
    logMessage('ERROR', 'Google Compute Engine API Error: ' . $e->getMessage() . ' (API Code: ' . $e->getCode() . ') - Trace: ' . $e->getTraceAsString());
} catch (\Error $e) {
    logMessage('CRITICAL_ERROR', 'PHP Error: ' . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine() . " - Trace: " . $e->getTraceAsString());
} catch (Exception $e) {
    logMessage('ERROR', 'Unexpected generic exception: ' . $e->getMessage() . ' - Trace: ' . $e->getTraceAsString());
} finally {
    if ($gceClient) {
        try {
            $gceClient->close();
            logMessage('DEBUG', "Compute Engine InstancesClient closed for instance '" . GCE_INSTANCE_NAME . "'.");
        } catch (Exception $closeException) {
            logMessage('ERROR', "Exception while closing InstancesClient for GCE instance '" . GCE_INSTANCE_NAME . "': " . $closeException->getMessage());
        }
    }
}

terminateProbeAsServiceUnavailable("Database on GCE not ready. GCE instance '" . GCE_INSTANCE_NAME . "' current status: $gceInstanceStatusString. Probe will retry.");
