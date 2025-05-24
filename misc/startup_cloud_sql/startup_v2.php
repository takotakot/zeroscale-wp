<?php
/**
 * Startup Probe
 *
 * @category Google_Cloud
 * @package  WordPress
 * @author   takotakot <takotakot@users.noreply.github.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link
 */

/**
 * Startup Probe for Cloud Run with WordPress & Cloud SQL (Zero Scale Attempt)
 *
 * 1. Attempt to connect to the WordPress database.
 * 2. If successful, return HTTP 200 (OK).
 * 3. If connection fails:
 * a. Check the Cloud SQL instance's current state via Admin API.
 * b. If the instance state is 'STOPPED', send an asynchronous API call
 * to start the instance (by setting its activation policy to 'ALWAYS').
 * c. Regardless of other states (e.g., already RUNNABLE but proxy not ready,
 * PENDING_CREATE, etc.), return HTTP 503 (Service Unavailable).
 * 4. Cloud Run will then retry the probe according to its configuration,
 * eventually succeeding when the Cloud SQL instance is running and accessible.
 *
 * This approach keeps the probe script itself relatively lightweight and relies on
 * Cloud Run's native retry mechanism for the Cloud SQL instance to become fully ready.
 */

// Ensure the Google Cloud PHP SDK autoloader is available.
// Adjust the path if your vendor directory is located elsewhere in your container.
require_once __DIR__ . '/vendor/autoload.php';

use Google\Cloud\Sql\V1beta4\SqlInstancesServiceClient;
use Google\Cloud\Sql\V1beta4\DatabaseInstance;
use Google\Cloud\Sql\V1beta4\Settings;
use Google\ApiCore\ApiException;
use Google\Protobuf\FieldMask; // Required for specifying fields to update in a patch request

$projectId = $_SERVER['PROJECT_ID'] ?? null;         // Your Google Cloud Project ID
$instanceId = $_SERVER['WORDPRESS_DB_INSTANCE_ID'] ?? null; // The ID of your Cloud SQL instance (e.g., 'my-wordpress-db')
$dbName = $_SERVER['WORDPRESS_DB_NAME'] ?? null;            // WordPress database name
$dbUser = $_SERVER['WORDPRESS_DB_USER'] ?? null;            // WordPress database user
$dbPassword = $_SERVER['WORDPRESS_DB_PASSWORD'] ?? null;    // WordPress database password
$dbHost = $_SERVER['WORDPRESS_DB_HOST'] ?? null;            // WordPress database host (e.g., '127.0.0.1' if using Cloud SQL Proxy sidecar)

// --- Helper Functions ---
/**
 * Logs a message to PHP's error log.
 *
 * @param string $level   Log level (e.g., INFO, WARNING, ERROR, CRITICAL)
 * @param string $message The message to log.
 * @return void
 */
function logMessage($level, $message)
{
    error_log("[$level] CloudRunStartupProbe: $message");
}

/**
 * Terminates the probe with a 503 status code and a message.
 * This signals to Cloud Run that the service is not yet ready and should be retried.
 *
 * @param string $message The reason for the probe failure.
 * @return void
 */
function terminateProbeAsServiceUnavailable($message)
{
    http_response_code(503); // Service Unavailable
    logMessage('INFO', "Probe failed (503): $message. Cloud Run should retry.");
    // Cloud Run might expect a response body even for failures.
    echo "Service Unavailable: " . htmlspecialchars($message);
    exit;
}

// --- Main Probe Logic ---

// echo $projectId . "\n";
// echo $instanceId . "\n";
// echo $dbName . "\n";
// echo $dbUser . "\n";
// echo $dbPassword . "\n";
// echo $dbHost . "\n";

// 1. Validate essential configuration from environment variables.
if (!$projectId || !$instanceId || !$dbName || !$dbUser || !$dbPassword || !$dbHost) {
    http_response_code(500); // Internal Server Error for critical misconfiguration
    logMessage('CRITICAL', 'One or more required environment variables are missing. (PROJECT_ID, WP_DB_INSTANCE_ID, WP_DB_NAME, WP_DB_USER, WP_DB_PASSWORD, WP_DB_HOST)');
    echo "Critical Error: Missing required environment variables.";
    exit;
}

// 2. Attempt to connect to the WordPress database.
// Suppress mysqli connection errors initially to handle them gracefully.
mysqli_report(MYSQLI_REPORT_OFF);
$mysqli = @new mysqli($dbHost, $dbUser, $dbPassword, $dbName);

if ($mysqli && !$mysqli->connect_errno) {
    // Connection successful, now try a simple query.
    $result = @$mysqli->query('SELECT 1');
    if ($result && $row = $result->fetch_assoc()) {
        if (($row['1'] ?? null) === '1') {
            // Database is responsive. Probe succeeds.
            $mysqli->close();
            http_response_code(200);
            logMessage('SUCCESS', "Database connection successful and query 'SELECT 1' returned '1'. Probe succeeded.");
            echo "OK";
            exit;
        }
    }
    // If query failed or returned unexpected result, but connection was made.
    logMessage('WARNING', "Database connection established to '$dbHost', but 'SELECT 1' query failed or returned an unexpected result. MySQLi error: " . $mysqli->error);
    if ($mysqli) {
        $mysqli->close();
    }
    // Fall through to treat as unavailable, as WordPress might not function.
} else {
    // mysqli connection failed. Log the specific mysqli connection error if available.
    $mysqli_connect_error_msg = $mysqli ? $mysqli->connect_error : "mysqli object not created";
    if (function_exists('mysqli_connect_errno') && mysqli_connect_errno()) {
         $mysqli_connect_error_msg = mysqli_connect_errno() . ": " . mysqli_connect_error();
    }
    logMessage('INFO', "Initial database connection to host '$dbHost' for database '$dbName' failed. MySQLi connect error: $mysqli_connect_error_msg");
    // Proceed to check Cloud SQL instance state.
}

// 3. Database connection failed. Check Cloud SQL instance state and attempt to start if stopped.
logMessage('INFO', "Database not directly accessible. Checking Cloud SQL instance '$instanceId' (Project: '$projectId') state.");

$sqlClient = null;
try {
    // Initialize the Cloud SQL Admin API client.
    // Authentication is typically handled by Workload Identity or GOOGLE_APPLICATION_CREDENTIALS.
    $sqlClient = new SqlInstancesServiceClient();
    $instanceName = SqlInstancesServiceClient::databaseInstanceName($projectId, $instanceId);

    $instance = $sqlClient->get($instanceName);
    $currentState = $instance->getState();
    // For logging, get the string representation of the enum value if possible.
    $currentStateStr = is_object($currentState) && method_exists($currentState, 'getValueDescriptor') ? $currentState->getValueDescriptor()->getName() : (string) $currentState;

    logMessage('INFO', "Cloud SQL instance '$instanceId' current state: $currentStateStr.");

    if ($currentState === DatabaseInstance\SqlInstanceState::STOPPED) {
        logMessage('INFO', "Cloud SQL instance '$instanceId' is STOPPED. Attempting to start it (asynchronous API call)...");

        // Prepare the settings to update: set activation policy to ALWAYS.
        $settingsToUpdate = new Settings();
        $settingsToUpdate->setActivationPolicy(Settings\ActivationPolicy::ALWAYS);

        // Create a DatabaseInstance object for the patch request.
        // Only the name and the settings to be changed are strictly necessary for a patch.
        $dbInstanceToUpdate = new DatabaseInstance();
        $dbInstanceToUpdate->setName($instanceName); // Crucial for identifying which instance to patch.
        $dbInstanceToUpdate->setSettings($settingsToUpdate);

        // Specify that only 'settings.activationPolicy' field should be updated.
        // This prevents other settings from being inadvertently changed.
        $updateMask = new FieldMask();
        $updateMask->setPaths(['settings.activationPolicy']);

        // Make the asynchronous API call to patch the instance.
        $operation = $sqlClient->patch($instanceName, $dbInstanceToUpdate, ['updateMask' => $updateMask]);

        logMessage('INFO', "Cloud SQL instance '$instanceId' start operation initiated successfully. Operation name: " . $operation->getName() . ". The instance will start in the background.");
        // The probe will still fail this time (503), and Cloud Run will retry.
        // Subsequent retries will hopefully find the instance RUNNABLE and the DB connectable.

    } elseif ($currentState === DatabaseInstance\SqlInstanceState::RUNNABLE) {
        // Instance is RUNNABLE, but the earlier DB connection attempt failed.
        // This could be due to Cloud SQL Proxy not being ready yet, network issues,
        // or incorrect DB credentials/name (though the latter should ideally not happen here).
        logMessage('WARNING', "Cloud SQL instance '$instanceId' is reported as RUNNABLE by the Admin API, but the direct database connection via '$dbHost' failed. This might be a temporary issue (e.g., Cloud SQL Proxy هنوز آماده نیست).");
    } else {
        // Instance is in another state (e.g., PENDING_CREATE, MAINTENANCE, FAILED, UNKNOWN).
        // In these cases, we generally don't try to start it.
        // The probe will fail, and retries will continue. If it's a persistent FAILED state, manual intervention might be needed.
        logMessage('INFO', "Cloud SQL instance '$instanceId' is in state '$currentStateStr'. Not attempting to start. Probe will retry.");
    }

} catch (ApiException $e) {
    // Handle errors from the Cloud SQL Admin API.
    logMessage('ERROR', 'Google Cloud SQL Admin API Error: ' . $e->getMessage() . ' (API Code: ' . $e->getCode() . ')');
    // Fall through to terminateProbeAsServiceUnavailable
} catch (Exception $e) {
    // Handle any other unexpected exceptions.
    logMessage('ERROR', 'An unexpected exception occurred during Cloud SQL instance check/start: ' . $e->getMessage());
    // Fall through to terminateProbeAsServiceUnavailable
} finally {
    // Close the API client if it was initialized.
    if ($sqlClient) {
        $sqlClient->close();
    }
}

// If this point is reached, the database is not yet ready for WordPress.
// Signal to Cloud Run that the service is unavailable and should be retried.
terminateProbeAsServiceUnavailable("Database is not yet ready. Cloud SQL instance '$instanceId' may be starting or in an intermediate state.");
