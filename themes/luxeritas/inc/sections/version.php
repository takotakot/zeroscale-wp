<?php
/**
 * Luxeritas WordPress Theme - free/libre wordpress platform
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * @copyright Copyright (C) 2015 Thought is free.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPL v2 or later
 * @author LunaNuko
 * @link https://thk.kanzae.net/
 * @translators rakeem( http://rakeem.jp/ )
 */

$curent = wp_get_theme();

if( TPATH !== SPATH ) $parent = wp_get_theme( $curent->get('Template') );

$uri = 'thk.' . 'kanzae.' . 'net';
$authoruri = 'https://' . $uri . '/';
$themeuri  = 'https://' . $uri . '/wp/';
?>
<fieldset>
<div class="luxe-info">
<div class="screenshots">
<?php
if( TPATH !== SPATH && file_exists( SPATH . '/screenshot.jpg' ) === true ) {
?>
<div class="screenshot"><img src="<?php echo SURI . '/screenshot.jpg'; ?>" alt="" /></div>
<?php
}
elseif( TPATH !== SPATH && file_exists( SPATH . '/screenshot.png' ) === true ) {
?>
<div class="screenshot"><img src="<?php echo SURI . '/screenshot.png'; ?>" alt="" /></div>
<?php
}
elseif( TPATH === SPATH && file_exists( TPATH . '/screenshot.jpg' ) === true ) {
?>
<div class="screenshot"><img src="<?php echo TURI . '/screenshot.jpg'; ?>" alt="" /></div>
<?php
}
elseif( TPATH === SPATH && file_exists( TPATH . '/screenshot.png' ) === true ) {
?>
<div class="screenshot"><img src="<?php echo TURI . '/screenshot.png'; ?>" alt="" /></div>
<?php
}
else {
?>
<div class="screenshot blank"></div>
<?php
}
?>
</div>

<div class="info">
<span class="current-label"><?php echo __( 'Current Theme', 'luxeritas' ); ?></span>
<legend><h2 class="name"><?php echo $curent->get('Name'); ?><span class="version"><?php printf( __( 'Version: %s', 'luxeritas' ), $curent->get('Version') ); ?></span></h2>

<?php if( TPATH !== SPATH ) { ?>
<p class="parent-theme"><?php printf( __( 'This is a child theme of %s.', 'luxeritas' ), '<strong>' . $parent->get('Name') . '</strong>' ); ?></p>

<span class="current-label"><?php echo __( 'Parent Theme', 'luxeritas' ); ?></span>
<legend><h2 class="name"><?php echo $parent->get('Name'); ?><span class="version"><?php printf( __( 'Version: %s', 'luxeritas' ), $parent->get('Version') ); ?></span></h2>
<?php } ?>

<h3 class="author">
<?php printf( __( 'By %s', 'luxeritas' ), '<a href="' . $authoruri . '" target="_blank" rel="noopener">' . $curent->get('Author') . '</a>' ); ?>
</h3>
<!--
<p class="description"><?php echo $curent->get('Description'); ?></p>
<p class="tags"><span><?php echo __( 'Tags', 'luxeritas' ); ?></span>: <?php foreach( $curent->get('Tags') as $val ) echo $val . ', '; ?></p>
-->
<p class="tags">Author URL: <?php echo '<a href="' . $authoruri . '" target="_blank" rel="noopener">' .$authoruri . '</a>'; ?></p>
<p style="color:#82878c">Theme URL: <?php echo '<a href="' . $themeuri . '" target="_blank" rel="noopener">' . $themeuri . '</a>'; ?></p>
</div>
</div>

<div style="clear:both;padding-bottom:30px"></div>

<?php
if( current_user_can( 'edit_theme_options' ) === true ) {
	$iniget = 'ini' . '_get';
?>

<style>
.table-responsive {
	display: flex;
	flex-wrap: wrap;
}
.table-flex {
	min-width: 47%;
	margin: 0 10px;
}
@media (max-width: 1199px) {
	.table-flex {
		min-width: 100%;
	}
}
.wp-list-table {
	width: 100%;
	margin: 0 10px 10px 10px;
	margin-left: 0;
}
.wp-list-table th, .wp-list-table td {
	padding: 12px 10px;
}
.wp-list-table th {
	background: #f7fcfe;
	border-left: 4px solid #009fd0;
}
.col1 {
	width: 30%;
}
.alert {
	margin-right: 6px;
	font-size: 1.2em;
}
</style>

<div class="table-responsive">

<div class="table-flex">

<table class="wp-list-table widefat striped">
<colgroup class="col1"></colgroup>
<colgroup class="col2"></colgroup>
<thead>
<tr><th colspan="2">Server information</th></tr>
</thead>
<tbody>
<tr>
<td>Operating System</td>
<td><?php echo php_uname( 's' ); ?>&nbsp;<?php echo php_uname( 'r' ); ?> / <?php echo php_uname( 'm' ); ?></td>
</tr>
<tr>
<td>Hostname</td>
<td><?php echo php_uname( 'n' ); ?></td>
</tr>
<tr>
<td>IP / Port</td>
<td><?php echo $_SERVER['SERVER_ADDR']; ?>:<?php echo $_SERVER['SERVER_PORT']; ?></td>
</tr>
<tr>
<td>Server protocol</td>
<td><?php echo $_SERVER['SERVER_PROTOCOL']; ?></td>
</tr>
<tr>
<td>Domain name</td>
<td><?php echo $_SERVER['HTTP_HOST'];; ?></td>
</tr>
<tr>
<td>Server software</td>
<td><?php echo $_SERVER['SERVER_SOFTWARE']; ?></td>
</tr>
<tr>
<td>PHP version</td>
<td><?php echo phpversion(); ?></td>
</tr>
<tr>
<td>CGI version</td>
<td><?php echo $_SERVER['GATEWAY_INTERFACE']; ?></td>
</tr>
<tr>
<td>SSL</td>
<td><?php echo is_ssl() === true ? 'Enabled' : '<span class="alert">&#x26a0;</span> Disabled'; ?></td>
</tr>
<tr>
<td>allow_url_fopen</td>
<td><?php echo $iniget('allow_url_fopen') ? 'Enabled' : 'Disabled'; ?></td>
</tr>
<tr>
<td>Timezone ( default )</td>
<td><?php echo date_default_timezone_get(); ?></td>
</tr>
<tr>
<td>Timezone ( php.ini )</td>
<td><?php echo $iniget('date.timezone'); ?></td>
</tr>
<tr>
<td>Memory limit</td>
<td><?php echo MEMORY_LIMIT_INI; echo wp_convert_hr_to_bytes( MEMORY_LIMIT_INI ) < MIN_MEM_INT ? ' -&gt; ' . MIN_MEM : ''; ?></td>
</tr>
<tr>
<td>Post max size</td>
<td><?php echo $iniget('post_max_size'); ?></td>
</tr>
<tr>
<td>Upload max file size</td>
<td><?php echo $iniget('upload_max_filesize'); ?></td>
</tr>
</tbody>
</table>

<table class="wp-list-table widefat striped">
<colgroup class="col1"></colgroup>
<colgroup class="col2"></colgroup>
<thead>
<tr><th colspan="2">Database information</th></tr>
</thead>
<tbody>
<tr>
<td>MySQL version</td>
<td><?php
//global $wp_db_version; echo $wp_db_version;
$con = mysqli_connect( DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );
$mysql_inf = mysqli_get_server_info( $con );
echo $mysql_inf; ?></td>
</tr>
<tr>
<td>DB hostname</td>
<td><?php echo DB_HOST; ?></td>
</tr>
<tr>
<td>DB name</td>
<td><?php echo DB_NAME; ?></td>
</tr>
<tr>
<td>DB username</td>
<td><?php echo DB_USER; ?></td>
</tr>
<tr>
<td>DB charset</td>
<td><?php
echo strtoupper( DB_CHARSET );
global $wpdb;
$mysql_name = stripos( $mysql_inf, 'MariaDB' ) !== false ? 'MariaDB' : 'MySQL';
echo ' ( ' . $mysql_name . ' charset :&nbsp;&nbsp;' . $wpdb->charset . ' )';
?></td>
</tr>
<tr>
<td>DB collation</td>
<td><?php if( defined( 'DB_COLLATE' ) === true ) echo DB_COLLATE; ?></td>
</tr>
</tbody>
</table>
</div>

<div class="table-flex">
<table class="wp-list-table widefat striped">
<colgroup class="col1"></colgroup>
<colgroup class="col2"></colgroup>
<thead>
<tr><th colspan="2">WordPress information</th></tr>
</thead>
<tbody>
<tr>
<td>WordPress version</td>
<td><?php echo $wp_version; ?></td>
</tr>
<tr>
<td>Path</td>
<td><?php echo ABSPATH; ?></td>
</tr>
<tr>
<td>Locale</td>
<td><?php echo get_locale(); ?></td>
</tr>
<tr>
<td>Timezone</td>
<td><?php echo get_option( 'timezone_string' ); ?></td>
</tr>
<tr>
<td>Memory limit</td>
<td><?php echo WP_MEMORY_LIMIT; echo wp_convert_hr_to_bytes( WP_MEMORY_LIMIT ) < MIN_MEM_INT ? ' -&gt; ' . MIN_MEM : ''; ?></td>
</tr>
<tr>
<td>Max Memory limit</td>
<td><?php echo WP_MAX_MEMORY_LIMIT; ?></td>
</tr>
<tr>
<td>Multisite</td>
<td><?php echo is_multisite() === true ? 'Enabled' : 'Disabled'; ?></td>
</tr>
<tr>
<td>Debug mode</td>
<td><?php echo defined( 'WP_DEBUG' ) === true && WP_DEBUG == 1 ? 'Enabled' : 'Disabled'; ?></td>
</tr>
<tr>
<td>Display debug info</td>
<td><?php
if( defined( 'WP_DEBUG' ) === true && WP_DEBUG == 1 ) {
	if( defined( 'WP_DEBUG_DISPLAY' ) === false || ( defined( 'WP_DEBUG_DISPLAY' ) === true && WP_DEBUG_DISPLAY == 1 ) ) {
		echo '<span class="alert">&#x26a0;</span> Enabled';
	}
	else {
		echo 'Disabled';
	}
}
else {
	echo 'Disabled';
}
?></td>
</tr>
<tr>
<td>WP cache</td>
<td><?php echo defined( 'WP_CACHE' ) === true && WP_CACHE == 1 ? 'Enabled' : 'Disabled'; ?></td>
</tr>
<tr>
<td>Disallow file edit</td>
<td><?php echo defined( 'DISALLOW_FILE_EDIT' ) === true && DISALLOW_FILE_EDIT == 1 ? '<span class="alert">&#x26a0;</span> Enabled' : 'Disabled'; ?></td>
</tr>
<tr>
<?php
$all_plugins = get_plugins();
$active_plugins = [];
$inactive_plugins = [];

foreach( $all_plugins as $key => $val ) {
	if( is_plugin_active( $key ) === false ) {
		$inactive_plugins[] = $val['Name'];
	}
	else {
		$active_plugins[] = $val['Name'];
	}
}
?>
<td>Active plugins</td>
<td><?php
echo '<ul>';
foreach ( $active_plugins as $key => $val ) {
	echo '<li>', $val, '</li>';
}
echo '</ul>';
?>
</td>
</tr>
<tr>
<td>Inactive plugins</td>
<td><?php
echo '<ul>';
foreach ( $inactive_plugins as $key => $val ) {
	echo '<li>', $val, '</li>';
}
echo '</ul>';
?></td>
</tr>
</tbody>
</table>
</div>

</div>

<?php
}
