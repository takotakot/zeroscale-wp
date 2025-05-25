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
 * 投稿画面のブロックエディタにパターンを追加
 *---------------------------------------------------------------------------*/
call_user_func( function() {
	thk_filesystem_init();
	global $wp_filesystem;

	$pattern_path = SPATH . DSEP . 'block-patterns' . DSEP;
	$mods = get_theme_phrase_mods( 'pattern' );
	$pattern_array = [];

	if( !empty( $mods ) ) ksort( $mods );

	foreach( (array)$mods as $key => $val ) {
		if( strpos( $key, 'bp-' ) === 0 ) {
			$name = ltrim( $key, 'bp-' );
			$pattern_array[$name] = strlen( $name ) . '-' . md5( $name ) . '.txt';
		}
	}
	unset( $mods );

	register_block_pattern_category( 'luxeritas-block-patterns', array( 'label' => 'Luxeritas block patterns' ) );

	$i = 0;

	foreach( (array)$pattern_array as $key => $val ) {
		if( file_exists( $pattern_path . $val ) === true ) {
			$pattern = $wp_filesystem->get_contents( $pattern_path . $val );

			register_block_pattern(
				'luxe-blocks/block-pattern-' . $i,				//ブロックパターン名
				array(
					'title'   => htmlspecialchars_decode( $key ),		//ブロックパターンのタイトル
					'content' => $pattern,					//ブロックの HTML コンテンツ
					'categories' => array( 'luxeritas-block-patterns' ),	//ブロックパターンのカテゴリ
				)
			);
		}
		++$i;
	}
});
