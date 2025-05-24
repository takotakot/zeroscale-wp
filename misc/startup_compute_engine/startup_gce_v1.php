<?php
/**
 * Cloud Run Startup Probe for WordPress with Compute Engine (MySQL Host) Auto-Activation.
 *
 * This script performs the following actions:
 * 1. Attempts to connect to the MySQL database hosted on a Compute Engine instance
 * with a short timeout.
 * 2. If the database connection is successful, it exits with HTTP 200 (OK).
 * 3. If the database connection fails, it checks the state of the specified Compute Engine instance.
 * 4. If the Compute Engine instance is 'TERMINATED' (stopped), it sends an asynchronous API call
 * to start the instance.
 * 5. In all cases where the database is not immediately ready (GCE starting, MySQL starting, etc.),
 * it exits with HTTP 503 (Service Unavailable) to signal Cloud Run to retry the probe.
 *
 * Required Environment Variables (accessible via $_SERVER):
 * - PROJECT_ID: Your Google Cloud Project ID.
 * - GCE_ZONE: The zone where the Compute Engine instance resides (e.g., 'us-central1-a').
 * - GCE_INSTANCE_NAME: The name of the Compute Engine instance hosting MySQL.
 * - WP_DB_NAME: The name of the WordPress database.
 * - WP_DB_USER: The MySQL database username.
 * - WP_DB_PASSWORD: The MySQL database password.
 * - WP_DB_HOST: The hostname or IP address of the MySQL server on the GCE instance.
 */

// --- Autoloader ---
require_once __DIR__ . '/vendor/autoload.php';

// --- Use Statements for Google Cloud SDK ---
// For Compute Engine API
// use Google\Cloud\Compute\V1\InstancesClient;
use Google\Cloud\Compute\V1\Client\InstancesClient;
use Google\Cloud\Compute\V1\Instance\Status as GceInstanceStatus; // Alias to avoid conflict if other Status enums are used
use Google\Cloud\Compute\V1\GetInstanceRequest;
use Google\Cloud\Compute\V1\StartInstanceRequest;
// For general API core exceptions
use Google\ApiCore\ApiException;

// --- Configuration Constants ---
define('PROJECT_ID', $_SERVER['PROJECT_ID'] ?? null);
define('GCE_ZONE', $_SERVER['GCE_ZONE'] ?? null);
define('GCE_INSTANCE_NAME', $_SERVER['WORDPRESS_DB_INSTANCE_ID'] ?? null);
define('WORDPRESS_DB_NAME', $_SERVER['WORDPRESS_DB_NAME'] ?? null);
define('WORDPRESS_DB_USER', $_SERVER['WORDPRESS_DB_USER'] ?? null);
define('WORDPRESS_DB_PASSWORD', $_SERVER['WORDPRESS_DB_PASSWORD'] ?? null);
define('WORDPRESS_DB_HOST', $_SERVER['WORDPRESS_DB_HOST'] ?? null);
define('WORDPRESS_DB_CONNECT_TIMEOUT_SECONDS', 5); // MySQLi connection timeout in seconds

// --- Helper Functions ---

/**
 * Logs a message to PHP's error log with a specified severity level.
 *
 * @param string $level Log level (e.g., DEBUG, INFO, WARNING, ERROR, CRITICAL).
 * @param string $message The message to log.
 */
function logMessage($level, $message) {
    // Using a distinct prefix for GCE version logs
    error_log("[$level] CloudRunStartupProbeGCE: $message");
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
if (PROJECT_ID === null || GCE_ZONE === null || GCE_INSTANCE_NAME === null || WP_DB_NAME === null || WP_DB_USER === null || WP_DB_PASSWORD === null || WP_DB_HOST === null) {
    http_response_code(500);
    logMessage('CRITICAL', 'One or more required environment variables are not set. Please check PROJECT_ID, GCE_ZONE, GCE_INSTANCE_NAME, WP_DB_NAME, WP_DB_USER, WP_DB_PASSWORD, WP_DB_HOST.');
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
    logMessage('WARNING', "Database connection established to GCE host '" . WP_DB_HOST . "', but 'SELECT 1' query failed or returned an unexpected result. MySQLi error: " . $dbQueryError);
    if ($mysqli) $mysqli->close();
} else {
    $mysqli_connect_error_code = $mysqli->connect_errno ?? 0;
    $mysqli_connect_error_msg = $mysqli->connect_error ?? "mysqli_real_connect failed (mysqli object might not be fully initialized or error message unavailable).";
    if ($mysqli_connect_error_code) {
         $mysqli_connect_error_msg = $mysqli_connect_error_code . ": " . $mysqli_connect_error_msg;
    }
    logMessage('INFO', "Initial database connection to GCE host '" . WP_DB_HOST . "' for DB '" . WP_DB_NAME . "' failed. MySQLi connect error: $mysqli_connect_error_msg");
}

// 3. Database connection failed. Proceed to check Compute Engine instance state.
logMessage('INFO', "Database on GCE not directly accessible. Checking GCE instance '" . GCE_INSTANCE_NAME . "' (Zone: '" . GCE_ZONE . "', Project: '" . PROJECT_ID . "') state.");

$gceClient = null;
$gceInstanceStatusStr = 'UNKNOWN_STATUS'; // Default for logging

try {
    $gceClient = new InstancesClient(); // Compute Engine InstancesClient
    logMessage('DEBUG', "Compute Engine InstancesClient instantiated.");

    // Get GCE instance details
    $getRequest = (new GetInstanceRequest())
    ->setProject(PROJECT_ID)
    ->setZone(GCE_ZONE)
    ->setInstance(GCE_INSTANCE_NAME);
    $instanceData = $gceClient->get($getRequest);
    // $instanceData = $gceClient->get(PROJECT_ID, GCE_ZONE, GCE_INSTANCE_NAME);
    $gceInstanceStatus = $instanceData->getStatus(); // Returns an enum value (integer)
    logMessage('DEBUG', "Raw GCE instance status value from getStatus(): " . var_export($gceInstanceStatus, true) . " (Type: " . gettype($gceInstanceStatus) . ")");

    // Convert enum integer to string for logging (is_callable check removed)
    $gceInstanceStatusStr = GceInstanceStatus::name($gceInstanceStatus);

    logMessage('INFO', "GCE instance '" . GCE_INSTANCE_NAME . "' current status: $gceInstanceStatusStr.");

    // Condition to start the GCE instance: if it's TERMINATED (stopped).
    // Other states like STOPPING, SUSPENDING should generally be allowed to complete.
    if ($gceInstanceStatus === GceInstanceStatus::TERMINATED) {
        logMessage('INFO', "GCE instance '" . GCE_INSTANCE_NAME . "' is TERMINATED. Attempting to start it (asynchronous API call)...");
        
        // The start method on InstancesClient is asynchronous and returns an Operation object.
        $startRequest = (new StartInstanceRequest())
            ->setProject(PROJECT_ID)
            ->setZone(GCE_ZONE)
            ->setInstance(GCE_INSTANCE_NAME);
        $operation = $gceClient->start($startRequest);
        // $operation = $gceClient->start(PROJECT_ID, GCE_ZONE, GCE_INSTANCE_NAME);
        
        // Log basic info about the operation. For GCE, the operation itself might not have a user-friendly 'name'
        // immediately, but its status can be tracked. For this probe, we just initiate.
        logMessage('INFO', "GCE instance '" . GCE_INSTANCE_NAME . "' start operation initiated. Operation status: " . $operation->getStatus());

    } elseif ($gceInstanceStatus === GceInstanceStatus::RUNNING) {
        logMessage('WARNING', "GCE instance '" . GCE_INSTANCE_NAME . "' is RUNNING, but DB connection via '" . WP_DB_HOST . "' failed. MySQL server on GCE might not be ready yet, or there could be network/firewall issues or incorrect WP_DB_HOST.");
    } else {
        // Instance is in another state (e.g., STOPPING, PROVISIONING, STAGING, REPAIRING, SUSPENDED).
        // In these cases, we wait for it to reach a stable state (RUNNING or TERMINATED).
        logMessage('INFO', "GCE instance '" . GCE_INSTANCE_NAME . "' is in state '$gceInstanceStatusStr'. Not attempting to start. Probe will retry.");
    }

} catch (ApiException $e) {
    logMessage('ERROR', 'Google Compute Engine API Error: ' . $e->getMessage() . ' (API Code: ' . $e->getCode() . ') - Trace: ' . $e->getTraceAsString());
} catch (\Error $e) { // Catch critical PHP errors (e.g., Class not found, type errors)
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

// If this point is reached, the database is not yet ready for WordPress.
terminateProbeAsServiceUnavailable("Database on GCE not ready. GCE instance '" . GCE_INSTANCE_NAME . "' current status: $gceInstanceStatusStr. Probe will retry.");
