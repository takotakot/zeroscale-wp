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
 * サムネイルの自動挿入
 *---------------------------------------------------------------------------*/
$load .= call_user_func_array( function( $load, $post_id, $_is, $luxe ) {
	if(
		(
			( $_is['single'] === true && isset( $luxe['thumb_auto_post'] ) ) ||
			( $_is['page'] === true && $_is['front_page'] === false && isset( $luxe['thumb_auto_page'] ) ) ||
			( $_is['front_page'] === true && isset( $luxe['thumb_auto_front_page'] ) )
		) && isset( $luxe['thumb_auto_insert_position'] )
	) {
		if( strpos( $luxe['thumb_auto_insert_position'], 'back' ) === false ) {
			$margin = 'margin: 20px 0 40px;';	// 記事上、タイトル下の場合の margin
			$max_height = '';

			if( $luxe['thumb_auto_insert_position'] === 'top' ) {
				$margin = 'margin: 0 0 40px;';	// タイトル上の場合の margin
			}

			if( isset( $luxe['thumb_auto_insert_limit_height'] ) && (int)$luxe['thumb_auto_insert_limit_height'] !== 0 ) {
				$max_height = 'max-height:' . $luxe['thumb_auto_insert_limit_height'] . 'px;';
			}

			// タイトル上下、記事上サムネイル
			$load .= <<<INSERT
#post-thumbnail { overflow: hidden; {$max_height} {$margin} text-align: center; border-radius: 4px }
INSERT;

			// 横幅いっぱい
			if( isset( $luxe['thumb_auto_insert_width'] ) && $luxe['thumb_auto_insert_width'] === 'wide' ) {
				$load .= <<<INSERT
#post-thumbnail img { width:100%; }
INSERT;
			}
		}
		elseif( strpos( $luxe['thumb_auto_insert_position'], 'back' ) === 0 ) {
			// タイトル背景にサムネイル
			if( has_post_thumbnail() === true ) {
				$thumb_src = get_the_post_thumbnail_url( $post_id, 'full' );
				$sizes = thk_get_image_size( $thumb_src );

				$entry_title = $_is['front_page'] === true ? '#front-page-title' : '.entry-title';

				// 画像サイズが取れなかった場合 ( getimagesize が無い、getimagesize が失敗したなど )
				if( !isset( $sizes ) ) {
					$post_thumbnail = get_the_post_thumbnail( $post_id, 'full' );
					preg_match( '/width=[\"|\']([0-9]+?)[\"|\'].+?height=[\"|\']([0-9]+?)[\"|\']/', $post_thumbnail, $matchs, PREG_UNMATCHED_AS_NULL );

					$sizes[0] = isset( $matchs[1] ) ? $matchs[1] : 65535;
					$sizes[1] = isset( $matchs[2] ) ? $matchs[2] : 65535;
				}

				if( $luxe['thumb_auto_insert_position'] === 'back-black-1' || $luxe['thumb_auto_insert_position'] === 'back-black-2') {
					$color = '#fff';
					$bg = '#333';
					if( $luxe['thumb_auto_insert_position'] === 'back-white-1' ) {
						$rgba = '0,0,0,.536';	// 黒文字(#333) + 白色オーバーレイ + Filter でギリギリ合格のコントラストになる RGBA
					}
					else {
						$rgba = '0,0,0,.22';	// 黒文字(#333) + 白色オーバーレイ + Filterでギリギリ合格のコントラストになる RGBA
					}
				}
				else {
					$color = '#333';
					$bg = '#eee';
					if( $luxe['thumb_auto_insert_position'] === 'back-white-1' ) {
						$rgba = '255,255,255,.606';	// 黒文字(#333) + 白色オーバーレイ + Filter でギリギリ合格のコントラストになる RGBA
					}
					else {
						$rgba = '255,255,255,.44';	// 黒文字(#333) + 白色オーバーレイ + Filter でギリギリ合格のコントラストになる RGBA
					}
				}

				if( isset( $luxe['thumb_auto_insert_width'] ) && $luxe['thumb_auto_insert_width'] === 'wide' ) {
					$load .= <<<INSERT
#post-thumbnail img { width:100%; }
INSERT;
				}

				if( $luxe['thumb_auto_insert_position'] === 'back-black-1' || $luxe['thumb_auto_insert_position'] === 'back-white-1' ) {
					$load .= <<<INSERT
#article-header {
	position: relative;
	overflow: hidden;
	text-align: center;
	border-radius: 4px;
	background: {$bg};
}
#article-header figure::before {
	position: absolute;
	content: "";
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	background: rgba({$rgba});
}
{$entry_title} {
	position: absolute;
	top: 0;
	margin: 0;
	padding: 0 20px;
	text-align: left;
}
{$entry_title} span {
	position: relative;
	display: block;
	top: 20px;
	color: {$color};
	text-shadow: {$bg} 2px 2px 9px, {$bg} -2px -2px 9px;
	filter: drop-shadow(2px 2px 6px {$bg});
}
INSERT;
				}
				else {
					$max_height = '';
					$padding_bottom = '';
					$background_size = '100%';

					if( isset( $luxe['thumb_auto_insert_limit_height'] ) && (int)$luxe['thumb_auto_insert_limit_height'] !== 0 && $sizes[1] >= $luxe['thumb_auto_insert_limit_height'] ) {
						$max_height = 'height:' . $luxe['thumb_auto_insert_limit_height'] . 'px;';
						$padding_bottom = '';
					}
					else {
						if( isset( $luxe['thumb_auto_insert_width'] ) && $luxe['thumb_auto_insert_width'] === 'orign' ) {
							$padding_bottom = 'padding-bottom:' . $sizes[1] . 'px;';
							$background_size = 'auto';
						}
						else {
							$padding_bottom = 'padding-bottom: calc(' . $sizes[1] . '/' . $sizes[0] . '*100%);';
						}
					}

					$load .= <<<INSERT
#article-header {
	position: relative;
	overflow: hidden;
	text-align: center;
	border-radius: 4px;
	background: {$bg};
}
#article-header figure {
}
{$entry_title} {
	width: 100%;
	position: absolute;
	top: 12px;
	text-align: left;
}
{$entry_title} span {
	position: absolute;
	display: block;
	margin: auto;
	right: 0;
	left: 0;
	width: 96%;
	padding: 12px;
	color: {$color};
	text-shadow: {$bg} 2px 2px 9px, {$bg} -2px -2px 9px;
	filter: drop-shadow(2px 2px 6px {$bg});
	background: rgba({$rgba});
	border-radius: 4px;
}
INSERT;
				}
			}
		}

		$load .= '.post-thumbnail-caption { font-size: 13px; }';
		if( isset( $luxe['thumb_auto_insert_position'] ) ) {
			if( $luxe['thumb_auto_insert_position'] === 'back-white-1' || $luxe['thumb_auto_insert_position'] === 'back-white-2' ) {
				$load .= '.post-thumbnail-caption { color: #000; }';
			}
			elseif( $luxe['thumb_auto_insert_position'] === 'back-black-1' || $luxe['thumb_auto_insert_position'] === 'back-black-2' ) {
				$load .= '.post-thumbnail-caption { color: #fff; }';
			}
		}
	}

	return $load;
}, array( &$load, &$post->ID, &$_is, &$luxe ) );
