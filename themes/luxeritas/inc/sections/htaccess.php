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

thk_filesystem_init();
global $wp_filesystem;

$file_name = get_locale() === 'ja' ? '/htaccess.txt' : '/htaccess_en.txt';

$htaccess = 'File Not Found' . "\n" . TPATH . $file_name;
if( file_exists( TPATH . $file_name ) ) {
	$htaccess = $wp_filesystem->get_contents( TPATH . $file_name );
	$htaccess = thk_convert( $htaccess );
	$htaccess = esc_html( $htaccess );

	$htaccess = preg_replace( '/(\sHeader always set Strict-Transport-Security.+?upgrade-insecure-requests&quot;\s)/ism', '<span style="color:red;font-weight:bold">$1</span>', $htaccess );
	/*
	if( !isset( $_is['ssl'] ) ) {
		$htaccess = preg_replace( '/\sHeader always set Strict-Transport-Security.+?"upgrade-insecure-requests"\s/ism', '', $htaccess );
	}
	*/
?>
<p><?php echo __( 'For apache web server.', 'luxeritas' ); ?></p>
<p><?php echo __( 'By adding the below lines on your .htaccess, it will enable Gzip compression and browser cache and will boost rendering speed.', 'luxeritas' ); ?></p>
<p><?php echo __( '* <span class="bold">Do not overwrite/replace with your .htaccess, but ADD these lines !</span>', 'luxeritas' ); ?></p>
<p><?php echo __( '* <span class="bold"><span style="color:red">Red letters</span> are for SSL (HTTPS connection). If your site is HTTP, remove this letters.</span>', 'luxeritas' ); ?></p>
<?php
}
?>
<style>
.luxe-field .htaccess {
	overflow: auto;
	background: #fff;
	width: 100%;
	max-width: 720px;
	max-height: 1280px;
	box-shadow: 0 0 0 transparent;
	border-radius: 4px;
	border: 1px solid #8c8f94;
	background-color: #fff;
	color: #2c3338;
	padding: 2px 6px;
	resize: vertical;
	box-sizing: border-box;
	font-family: Consolas,"Courier New",Courier,Monaco,monospace;
	margin: 10px 0;
	font-size: 12px;
	line-height: 1.42857143;
	white-space: pre;
}
</style>
<pre class="htaccess"><?php echo $htaccess; ?></pre>
