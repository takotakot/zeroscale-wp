<?php
/**
 * Startup Probe with Cloud SQL Instance Activation
 *
 * This script checks if the associated Cloud SQL instance is running.
 * If not, it attempts to start the instance before checking the database connection.
 *
 * Required Environment Variables:
 * - GOOGLE_CLOUD_PROJECT: Your Google Cloud Project ID.
 * - CA_WP_DB_INSTANCE_ID: The ID of your Cloud SQL instance.
 * - CA_WP_DB_NAME: The name of the WordPress database.
 * - CA_WP_DB_USER: The MySQL database username.
 * - CA_WP_DB_PASSWORD: The MySQL database password.
 * - CA_WP_DB_HOST: The MySQL hostname (e.g., 127.0.0.1 if using Cloud SQL Proxy).
 */

// Load the Google Cloud PHP SDK autoloader
// Adjust the path if your vendor directory is located elsewhere.
require_once __DIR__ . '/vendor/autoload.php';

use Google\Cloud\Sql\V1beta4\SqlInstancesServiceClient;
use Google\Cloud\Sql\V1beta4\DatabaseInstance;
use Google\Cloud\Sql\V1beta4\Settings;
use Google\ApiCore\ApiException;

// --- Configuration from Environment Variables ---
$projectId = $_ENV['GOOGLE_CLOUD_PROJECT'] ?? null;
$instanceId = $_ENV['CA_WP_DB_INSTANCE_ID'] ?? null;
$dbName = $_ENV['CA_WP_DB_NAME'] ?? null;
$dbUser = $_ENV['CA_WP_DB_USER'] ?? null;
$dbPassword = $_ENV['CA_WP_DB_PASSWORD'] ?? null;
$dbHost = $_ENV['CA_WP_DB_HOST'] ?? null;

// Maximum time (in seconds) to wait for the SQL instance to become RUNNABLE
// This should be less than the Cloud Run startup probe's timeoutSeconds.
define('MAX_WAIT_SECONDS_FOR_SQL_STARTUP', isset($_ENV['MAX_WAIT_SECONDS_FOR_SQL_STARTUP']) ? (int)$_ENV['MAX_WAIT_SECONDS_FOR_SQL_STARTUP'] : 180); // Default 3 minutes

// --- Helper Functions ---
function log_message($level, $message) {
    error_log("[$level] StartupProbe: $message");
}

function terminate_probe($statusCode, $message, $logLevel = 'ERROR') {
    http_response_code($statusCode);
    log_message($logLevel, "Terminating probe with status $statusCode: $message");
    exit($message); // Cloud Run expects a response body for failures too
}

// --- Main Probe Logic ---
try {
    // Validate essential configuration
    if (!$projectId || !$instanceId || !$dbName || !$dbUser || !$dbPassword || !$dbHost) {
        terminate_probe(500, 'Missing one or more required environment variables (GOOGLE_CLOUD_PROJECT, CA_WP_DB_INSTANCE_ID, CA_WP_DB_NAME, CA_WP_DB_USER, CA_WP_DB_PASSWORD, CA_WP_DB_HOST).');
    }

    log_message('INFO', "Probe started. Project: $projectId, Instance: $instanceId");

    $sqlClient = null;
    $instanceWasActivatedInThisRequest = false;

    // --- 1. Check and Activate Cloud SQL Instance if Necessary ---
    try {
        $sqlClient = new SqlInstancesServiceClient(); // Assumes GOOGLE_APPLICATION_CREDENTIALS or Workload Identity is configured
        $instanceName = SqlInstancesServiceClient::databaseInstanceName($projectId, $instanceId);

        $instance = $sqlClient->get($instanceName);
        $currentState = $instance->getState();
        // Convert SqlInstanceState enum to string for logging if it's an object
        $currentStateStr = is_object($currentState) && method_exists($currentState, 'getValueDescriptor') ? $currentState->getValueDescriptor()->getName() : (string) $currentState;


        log_message('INFO', "Cloud SQL instance '$instanceId' current state: $currentStateStr");

        // Check if instance needs to be started.
        // States indicating it's not running: STOPPED (explicitly by user), SUSPENDED (billing issues etc.)
        // We primarily target 'STOPPED' or if activation policy is 'NEVER'
        $activationPolicy = $instance->getSettings()->getActivationPolicy();
        $activationPolicyStr = is_object($activationPolicy) && method_exists($activationPolicy, 'getValueDescriptor') ? $activationPolicy->getValueDescriptor()->getName() : (string) $activationPolicy;


        if ($currentState === DatabaseInstance\SqlInstanceState::STOPPED ||
            ($currentState !== DatabaseInstance\SqlInstanceState::RUNNABLE && $activationPolicy === Settings\ActivationPolicy::NEVER)) {

            log_message('INFO', "Cloud SQL instance '$instanceId' is not RUNNABLE (State: $currentStateStr, Policy: $activationPolicyStr). Attempting to start...");
            $instanceWasActivatedInThisRequest = true;

            $settingsToUpdate = new Settings();
            $settingsToUpdate->setActivationPolicy(Settings\ActivationPolicy::ALWAYS); // Set to ALWAYS to start

            $dbInstanceToUpdate = new DatabaseInstance();
            $dbInstanceToUpdate->setName($instanceName);
            $dbInstanceToUpdate->setSettings($settingsToUpdate);

            // The 'updateMask' ensures only 'settings.activationPolicy' is changed.
            // In a `patch` operation, the updateMask should list the fields to be updated.
            // For just changing activation policy, 'settings.activationPolicy' is correct.
            // However, the SqlInstancesServiceClient's patch method might require specific formatting for field masks.
            // Let's use a simpler approach by just setting the field and relying on the client library or API default behavior for patch.
            // A more robust way is to use FieldMask.
            // $fieldMask = new \Google\Protobuf\FieldMask();
            // $fieldMask->setPaths(['settings.activationPolicy']);
            // $operation = $sqlClient->patch($instanceName, $dbInstanceToUpdate, ['updateMask' => $fieldMask]);

            // Simpler patch, usually works for single settings if the API/client is lenient
            // However, for SQL Admin API, it's better to be explicit or use 'update' if replacing the whole settings object.
            // Let's assume we are only changing this one setting within the existing ones.
            // A common pattern is to get current settings, modify, then patch.
            $currentSettings = $instance->getSettings();
            $currentSettings->setActivationPolicy(Settings\ActivationPolicy::ALWAYS);
            $dbInstanceToUpdate->setSettings($currentSettings);

            $operation = $sqlClient->patch($instanceName, $dbInstanceToUpdate, ['settings.activationPolicy']); // Specify what is being patched

            log_message('INFO', "Cloud SQL instance '$instanceId' start operation initiated. Operation name: " . $operation->getName());

            // Wait for the instance to become RUNNABLE
            $startTime = time();
            $waitedTime = 0;
            while (true) {
                sleep(15); // Check every 15 seconds
                $waitedTime = time() - $startTime;

                $currentInstanceCheck = $sqlClient->get($instanceName);
                $loopState = $currentInstanceCheck->getState();
                $loopStateStr = is_object($loopState) && method_exists($loopState, 'getValueDescriptor') ? $loopState->getValueDescriptor()->getName() : (string) $loopState;


                if ($loopState === DatabaseInstance\SqlInstanceState::RUNNABLE) {
                    log_message('INFO', "Cloud SQL instance '$instanceId' is now RUNNABLE after {$waitedTime} seconds.");
                    break;
                }

                if ($waitedTime > MAX_WAIT_SECONDS_FOR_SQL_STARTUP) {
                    terminate_probe(503, "Cloud SQL instance '$instanceId' did not become RUNNABLE within " . MAX_WAIT_SECONDS_FOR_SQL_STARTUP . " seconds. Last state: $loopStateStr");
                }
                log_message('INFO', "Waiting for Cloud SQL instance '$instanceId' ($loopStateStr)... {$waitedTime}s / " . MAX_WAIT_SECONDS_FOR_SQL_STARTUP . "s");
            }
        } elseif ($currentState !== DatabaseInstance\SqlInstanceState::RUNNABLE) {
            // Instance is not stopped but also not runnable (e.g., PENDING_CREATE, MAINTENANCE, FAILED)
            terminate_probe(503, "Cloud SQL instance '$instanceId' is in a non-runnable state: $currentStateStr. Manual intervention may be required.");
        }
    } catch (ApiException $e) {
        terminate_probe(503, 'Google Cloud SQL API Error: ' . $e->getMessage() . ' (Code: ' . $e->getCode() . ')');
    } catch (Exception $e) { // Catch any other exceptions during SQL client interaction
        terminate_probe(500, 'Error during Cloud SQL instance management: ' . $e->getMessage());
    } finally {
        if ($sqlClient) {
            $sqlClient->close();
        }
    }

    // --- 2. Check Database Connection (Original healthz.php logic) ---
    log_message('INFO', "Proceeding to check database connection to $dbHost for database $dbName.");

    // Explicitly set mysqli error reporting to throw exceptions
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $mysqli = new mysqli($dbHost, $dbUser, $dbPassword, $dbName);
        // $mysqli->connect_errno will be 0 if successful, or an error code.
        // With MYSQLI_REPORT_ERROR, a connection error will throw an exception.
        log_message('INFO', "Successfully established mysqli connection to $dbHost.");

        $sql = 'SELECT 1';
        $result = $mysqli->query($sql);

        if ($result) {
            $row = $result->fetch_assoc();
            $dbcheck = $row['1'] ?? null;
            $result->free();

            if ($dbcheck === '1') {
                http_response_code(200);
                log_message('SUCCESS', 'Database connection successful and query `SELECT 1` returned `1`.');
                echo "OK"; // Response body for success
            } else {
                terminate_probe(500, 'Invalid value returned from `SELECT 1`. Expected "1", got: ' . var_export($dbcheck, true));
            }
        } else {
            // This block might not be reached if query errors throw exceptions due to mysqli_report
            terminate_probe(500, 'Failed to execute query `SELECT 1`. Error: ' . $mysqli->error);
        }
        $mysqli->close();

    } catch (mysqli_sql_exception $e) {
        $errorMessage = 'MySQLi Connection/Query Error: ' . $e->getMessage() . ' (Code: ' . $e->getCode() . ')';
        if ($instanceWasActivatedInThisRequest) {
            terminate_probe(503, "Failed to connect to database '$dbName' on '$dbHost' AFTER attempting to start Cloud SQL instance '$instanceId'. $errorMessage");
        } else {
            terminate_probe(503, "Failed to connect to database '$dbName' on '$dbHost'. $errorMessage");
        }
    } catch (Exception $e) { // Catch any other general exceptions
        terminate_probe(500, 'An unexpected error occurred during database check: ' . $e->getMessage());
    }

} catch (Exception $e) { // Catch exceptions from the very beginning (e.g., config validation)
    // This is a fallback, specific errors should be caught and handled earlier.
    http_response_code(500);
    log_message('CRITICAL', 'Unhandled critical error in startup probe: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
    exit('A critical error occurred in the startup probe.');
}
