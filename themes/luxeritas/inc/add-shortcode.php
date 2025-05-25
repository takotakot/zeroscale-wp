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

global $post, $widget_concat;

$json = array();
$sc_mods = get_pattern_list( 'shortcode', true, true );

if( !empty( $sc_mods ) ) {
	$contents = '';
	if( isset( $post->post_content ) ) $contents .= $post->post_content;
	if( isset( $widget_concat ) ) $contents .= $widget_concat;

	foreach( (array)$sc_mods as $key => $val ) {
		// 投稿内に該当するショートコードが書かれてなければショートコードそのものを登録しない
		//if( strpos( $post->post_content, '[' . $key ) !== false ) {
		if( strpos( $contents, '[' . $key ) !== false ) {
			// functions.php 等ですでに同名のショートコードが登録済みの場合も登録しない
			if( shortcode_exists( $key ) === false ) {
				$json = array( 'label' => '', 'php' => false, 'close' => false, 'hide' => false, 'active' => false );
				$json = wp_parse_args( @json_decode( $val ), $json );

				if( $json['active'] !== false && $json['hide'] === false ) {
					if( file_exists( SPATH . DSEP . 'shortcodes' . DSEP . $key . '.inc' ) ) {
						require( SPATH . DSEP . 'shortcodes' . DSEP . $key . '.inc' );
					}
				}
				// ショートコード非表示設定の時
				if( $json['hide'] !== false ) {
					add_filter( 'the_content', function( $content = null ) use( $key, $val ) {
						$content = preg_replace( '/\[' . $key . '[^\]]*?\].+?\[\/' . $key . '\]/ism', '', $content );
						$content = preg_replace( '/\[' . $key . '[^\]]*?\]/im', '', $content );
						return $content;
					}, 9 );
				}
			}
		}
	}
	unset( $contents );
}
