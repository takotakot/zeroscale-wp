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
 * カウント数のキャッシュ取得
 * require で変数を継承するので関数化しない
 *---------------------------------------------------------------------------*/
global $_is;

$incomplete = '';

if( $_is['preview'] === true || $_is['customize_preview'] === true ) {
	$id_cnt = thk_get_sns_count_cache( false );
}
else {
	$id_cnt = thk_get_sns_count_cache( true );
}

if( $_is['front_page'] === true ) {
	$permalink = THK_HOME_URL;
}
else {
	$permalink = get_permalink();
}

foreach( (array)$id_cnt as $key => $val ) {
	if( ctype_digit( $val ) === false ) {
		$incomplete .= ',' . $key;
	}
	else {
		$id_cnt[$key] = apply_filters( 'thk_sns_count', $val, $key, $permalink );
	}
}
$incomplete = ltrim( $incomplete, ',' );

if( $_is['preview'] === true || $_is['customize_preview'] === true ) {
	$feed_cnt = thk_get_feedly_count_cache( false );
}
else {
	$feed_cnt = thk_get_feedly_count_cache( true );
}

if( ctype_digit( $feed_cnt ) === true ) {
	$feed_cnt = apply_filters( 'thk_sns_count', $feed_cnt, 'r', $permalink );
}
