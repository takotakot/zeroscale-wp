<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', $_SERVER['WORDPRESS_DB_NAME'] ?? $_ENV['WORDPRESS_DB_NAME'] ?? null );

/** Database username */
define( 'DB_USER', $_SERVER['WORDPRESS_DB_USER'] ?? $_ENV['WORDPRESS_DB_USER'] ?? null );

/** Database password */
define( 'DB_PASSWORD', $_SERVER['WORDPRESS_DB_PASSWORD'] ?? $_ENV['WORDPRESS_DB_PASSWORD'] ?? null );

/** Database hostname */
define( 'DB_HOST', $_SERVER['WORDPRESS_DB_HOST'] ?? $_ENV['WORDPRESS_DB_HOST'] ?? null );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
$keys_string = $_SERVER['WORDPRESS_AUTH_KEYS'] ?? $_ENV['WORDPRESS_AUTH_KEYS'] ?? '';
$keys        = array();
for ( $i = 0; $i < 8; $i++ ) {
    $start = $i * 64;
    if ( strlen( $keys_string ) >= $start + 64 ) {
        $keys[$i] = substr( $keys_string, $start, 64 );
    } else {
		// If the keys_string is shorter than expected, generate a new key.
        $keys[$i] = hash( 'sha256', $keys_string . $i );
    }
}
define( 'AUTH_KEY',         $keys[0] );
define( 'SECURE_AUTH_KEY',  $keys[1] );
define( 'LOGGED_IN_KEY',    $keys[2] );
define( 'NONCE_KEY',        $keys[3] );
define( 'AUTH_SALT',        $keys[4] );
define( 'SECURE_AUTH_SALT', $keys[5] );
define( 'LOGGED_IN_SALT',   $keys[6] );
define( 'NONCE_SALT',       $keys[7] );
/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', strtolower( $_SERVER['WORDPRESS_DEBUG'] ?? $_ENV['WORDPRESS_DEBUG'] ?? '' ) !== '' );

/* Add any custom values between this line and the "stop editing" line. */

define( 'FS_METHOD', 'direct' );

// If we're behind a proxy server and using HTTPS, we need to alert WordPress of that fact
// see also https://wordpress.org/support/article/administration-over-ssl/#using-a-reverse-proxy
if ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && strpos( $_SERVER['HTTP_X_FORWARDED_PROTO'], 'https' ) !== false ) {
	$_SERVER['HTTPS'] = 'on';
}
// (we include this by default because reverse proxying is extremely common in container environments)

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
