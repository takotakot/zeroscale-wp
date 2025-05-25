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

/*---------------------------------------------------------------------------
 * ファイル操作
 *---------------------------------------------------------------------------*/
class thk_filesystem {
	/* save */
	public function file_save( $file=THK_STYLE_TMP_CSS, $txt='' ) {
		global $wp_filesystem;

		add_filter( 'request_filesystem_credentials', '__return_true' );

		$this->init_filesystem();
		if( false === $wp_filesystem->put_contents( $file , $txt, FS_CHMOD_FILE ) ) {
			//echo "error saving file!";
			if( is_admin() === true ) {
				if( function_exists( 'add_settings_error' ) === true ) {
					add_settings_error( 'luxe-custom', '', __( 'Error saving file.', 'luxeritas' ) . '<br />' . $file );
				}
			}
			elseif( defined( 'WP_DEBUG' ) === true && WP_DEBUG == true ) {
				$result = new WP_Error( 'error saving file', __( 'Error saving file.', 'luxeritas' ), $file );
				thk_error_msg( $result );
			}
			return false;
		}
		return;
	}

	/* init */
	public function init_filesystem( $url = null ) {
		global $wp_filesystem;
		require_once( ABSPATH . 'wp-admin/includes/file.php' );

		// direct accsess
		$access_type = get_filesystem_method();

		if( $access_type !== 'direct') {
			add_filter( 'filesystem_method', function( $a ) {
				return 'direct';
			});
			if( defined( 'FS_CHMOD_DIR' ) === false ) {
				//define( 'FS_CHMOD_DIR', ( 0755 & ~ umask() ) );
				define( 'FS_CHMOD_DIR', 0777 );
			}
			if( defined( 'FS_CHMOD_FILE' ) === false ) {
				//define( 'FS_CHMOD_FILE', ( 0644 & ~ umask() ) );
				define( 'FS_CHMOD_FILE', 0666 );
			}
		}

		// nonce
		if( $url === null ) {
			$url = wp_nonce_url( 'customize.php?return=' . urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
		}
		$creds = request_filesystem_credentials( $url, '', false, false, null );

		// Writable or Check
		if( $creds === false ) {
			return false;
		}
		// WP_Filesystem_Base init
		if( WP_Filesystem( $creds ) === false ) {
			request_filesystem_credentials( $url, '', true, false, null );
			return false;
		}
		return;
	}
}
