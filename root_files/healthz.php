<?php // phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols
/**
 * Health check
 *
 * Required Environment Variables (accessible via $_SERVER):
 * - WP_DB_NAME: The name of the WordPress database.
 * - WP_DB_USER: The MySQL database username.
 * - WP_DB_PASSWORD: The MySQL database password.
 * - WP_DB_HOST: The MySQL hostname (e.g., '127.0.0.1' if using Cloud SQL Proxy).
 *
 * PHP version >= 8.0
 *
 * @category Google_Cloud
 * @package  ZeroScale_WordPress
 * @author   takotakot <takotakot@users.noreply.github.com>
 * @license  CC0-1.0 / MIT-X
 * @link     none
 */

// `require_once './wp-config.php';` is removed because it's a heavy process.

/** The name of the database for WordPress */

define('DB_NAME', $_SERVER['WORDPRESS_DB_NAME'] ?? $_ENV['WORDPRESS_DB_NAME'] ?? null);

/** MySQL database username */
define('DB_USER', $_SERVER['WORDPRESS_DB_USER'] ?? $_ENV['WORDPRESS_DB_USER'] ?? null);

/** MySQL database password */
define('DB_PASSWORD', $_SERVER['WORDPRESS_DB_PASSWORD'] ?? $_ENV['WORDPRESS_DB_PASSWORD'] ?? null);

/** MySQL hostname */
define('DB_HOST', $_SERVER['WORDPRESS_DB_HOST'] ?? $_ENV['WORDPRESS_DB_HOST'] ?? null);

/** MySQLi connection timeout in seconds */
define('WP_DB_CONNECT_TIMEOUT_SECONDS', 3);

// Variable substition.
$_host    = DB_HOST;
$dsn      = 'mysql:host=' . $_host . ';dbname=' . DB_NAME;
$user     = DB_USER;
$password = DB_PASSWORD;
$database = DB_NAME;

// Create Connection.
$mysqli = new mysqli();
if (! $mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, WP_DB_CONNECT_TIMEOUT_SECONDS)) {
    throw new RuntimeException('mysqli set option failed.');
}

$mysqli->real_connect($_host, $user, $password, $database);

// If Connection Error.
if ($mysqli->connect_errno) {
    throw new RuntimeException('mysqli connection error: ' . $mysqli->connect_errno . ' ' . $mysqli->connect_error);
}

$sql = 'SELECT 1';
// $sql = 'show databases';  // For debug.

$result = $mysqli->query($sql);
if (false !== $result) {
    $row     = $result->fetch_assoc();
    $dbcheck = $row['1'];

    if ('1' !== $dbcheck) {
        throw new RuntimeException('Invalid value returned from `SELECT 1`');
    } else {
        http_response_code(200);
    }
}

// Close Connection.
$mysqli->close();
