<?php // phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols
/**
 * Cloud Run Startup Probe for WordPress with Compute Engine (MySQL Host) Auto-Activation.
 *
 * This script performs the following actions:
 * 1. Attempts to connect to the MySQL database hosted on a Compute Engine instance
 * with a short timeout.
 * 2. If the database connection is successful, it exits with HTTP 200 (OK).
 * 3. If the database connection fails, it checks the state of the specified Compute Engine instance
 * using the Compute Engine API.
 * 4. If the Compute Engine instance is 'TERMINATED' (stopped), it sends an asynchronous API call
 * to start the instance.
 * 5. In all cases where the database is not immediately ready (e.g., GCE instance starting,
 * MySQL server starting), it exits with HTTP 503 (Service Unavailable) to signal
 * Cloud Run to retry the probe.
 *
 * Required Environment Variables (accessible via $_SERVER):
 * - PROJECT_ID: Your Google Cloud Project ID.
 * - GCE_ZONE: The zone where the Compute Engine instance resides (e.g., 'us-central1-a').
 * - GCE_INSTANCE_NAME: The name of the Compute Engine instance hosting MySQL.
 * - WP_DB_NAME: The name of the WordPress database.
 * - WP_DB_USER: The MySQL database username.
 * - WP_DB_PASSWORD: The MySQL database password.
 * - WP_DB_HOST: The hostname or IP address of the MySQL server on the GCE instance.
 *
 * PHP version >= 8.0
 *
 * @category Google_Cloud
 * @package  ZeroScale_WordPress
 * @author   takotakot <takotakot@users.noreply.github.com>
 * @license  CC0-1.0 / MIT-X
 * @link     none
 */

// --- Autoloader ---
require_once __DIR__ . '/vendor/autoload.php';

// --- Use Statements for Google Cloud SDK ---
use Google\Cloud\Compute\V1\Client\InstancesClient;
use Google\Cloud\Compute\V1\GetInstanceRequest;
use Google\Cloud\Compute\V1\StartInstanceRequest;
use Google\Cloud\Compute\V1\Instance\Status as GceInstanceStatusEnum;
use Google\ApiCore\ApiException;

// --- Configuration Constants ---
define('PROJECT_ID', $_SERVER['PROJECT_ID'] ?? $_ENV['PROJECT_ID'] ?? null);
define('GCE_ZONE', $_SERVER['GCE_ZONE'] ?? $_ENV['GCE_ZONE'] ?? null);
define('GCE_INSTANCE_NAME', $_SERVER['WORDPRESS_DB_INSTANCE_ID'] ?? $_ENV['WORDPRESS_DB_INSTANCE_ID'] ?? null);
define('WP_DB_NAME', $_SERVER['WORDPRESS_DB_NAME'] ?? $_ENV['WORDPRESS_DB_NAME'] ?? null);
define('WP_DB_USER', $_SERVER['WORDPRESS_DB_USER'] ?? $_ENV['WORDPRESS_DB_USER'] ?? null);
define('WP_DB_PASSWORD', $_SERVER['WORDPRESS_DB_PASSWORD'] ?? $_ENV['WORDPRESS_DB_PASSWORD'] ?? null);
define('WP_DB_HOST', $_SERVER['WORDPRESS_DB_HOST'] ?? $_ENV['WORDPRESS_DB_HOST'] ?? null);
define('WP_DB_CONNECT_TIMEOUT_SECONDS', 3); // MySQLi connection timeout in seconds

// --- Helper Functions ---

/**
 * Logs a message to PHP's error log with a specified severity level.
 *
 * @param string $level Log level (e.g., DEBUG, INFO, WARNING, ERROR, CRITICAL).
 * @param string $message The message to log.
 */
function logMessage($level, $message) {
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
$gceInstanceStatusString = 'UNKNOWN_STATUS';
$gceInstanceStatusEnumValue = -1; 

try {
    $gceClient = new InstancesClient();
    logMessage('DEBUG', "Compute Engine InstancesClient instantiated.");

    $getRequest = (new GetInstanceRequest())
        ->setProject(PROJECT_ID)
        ->setZone(GCE_ZONE)
        ->setInstance(GCE_INSTANCE_NAME);
    logMessage('DEBUG', "GetInstanceRequest prepared. Calling gceClient->get().");
    $instanceData = $gceClient->get($getRequest);
    
    $statusStringFromApi = $instanceData->getStatus();
    logMessage('DEBUG', "Raw GCE instance status string from API: '" . $statusStringFromApi . "'");

    $gceInstanceStatusString = $statusStringFromApi;
    $gceInstanceStatusEnumValue = GceInstanceStatusEnum::value($statusStringFromApi);

    if ($gceInstanceStatusEnumValue === null && $statusStringFromApi !== '') {
        logMessage('WARNING', "Could not map GCE status string '" . $statusStringFromApi . "' to a known enum integer value using GceInstanceStatusEnum::value().");
        $gceInstanceStatusEnumValue = -1;
    }

    logMessage('INFO', "GCE instance '" . GCE_INSTANCE_NAME . "' current status: $gceInstanceStatusString (Enum value: " . ($gceInstanceStatusEnumValue ?? 'null') . ")");

    if ($gceInstanceStatusEnumValue === GceInstanceStatusEnum::TERMINATED) {
        logMessage('INFO', "GCE instance '" . GCE_INSTANCE_NAME . "' is TERMINATED. Attempting to start it...");
        
        $startRequest = (new StartInstanceRequest())
            ->setProject(PROJECT_ID)
            ->setZone(GCE_ZONE)
            ->setInstance(GCE_INSTANCE_NAME);
        logMessage('DEBUG', "StartInstanceRequest prepared. Calling gceClient->start().");
        $operationResponse = $gceClient->start($startRequest);
        
        $operationName = $operationResponse->getName();
        $isDone = $operationResponse->isDone();

        logMessage('INFO', "GCE instance '" . GCE_INSTANCE_NAME . "' start operation initiated. Operation Name: " . ($operationName ?: 'N/A') . ", Is Done (immediately after call): " . ($isDone ? 'true' : 'false'));

    } elseif ($gceInstanceStatusEnumValue === GceInstanceStatusEnum::RUNNING) {
        logMessage('WARNING', "GCE instance '" . GCE_INSTANCE_NAME . "' is RUNNING, but DB connection via '" . WP_DB_HOST . "' failed. MySQL server on GCE might not be ready yet, or there could be network/firewall issues or incorrect WP_DB_HOST.");
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
