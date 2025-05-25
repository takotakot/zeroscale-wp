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

global $luxe, $_is, $awesome;

$span = '<span>';

if( isset( $luxe['mobile_button_icon_text'] ) && $luxe['mobile_button_icon_text'] === 'vertical' ) {
	$span = '<br /><span>';
}

?><div id="mobile-buttons"><ul><?php

// ホームに戻るボタン
if( $_is['home'] === false && $_is['front_page'] === false && isset( $luxe['mobile_home_button'] ) ) {
?><li><a href="<?php echo THK_HOME_URL ?>" title="<?php echo __( 'Home', 'luxeritas' ) ?>"><?php echo $awesome['home'], !isset( $luxe['mobile_button_name_hide'] ) ? $span . __( 'Home', 'luxeritas' ) . '</span>' : ''; ?></a></li><?php
}

// グローバルメニューボタン
if( isset( $luxe['mobile_menu_button'] ) ) {
?><li class="mob-menu" title="<?php echo __( 'Menu', 'luxeritas' ) ?>"><?php echo $awesome['menu'], !isset( $luxe['mobile_button_name_hide'] ) ? $span . __( 'Menu', 'luxeritas' ) . '</span>' : ''; ?></li><?php
}

// SNS ボタン
if( isset( $luxe['mobile_sns_button'] ) ) {
	if(
		( $_is['home'] === true && isset( $luxe['sns_toppage_view'] ) && isset( $luxe['sns_bottoms_type'] ) && $luxe['sns_bottoms_type'] !== 'normal' ) ||
		( $_is['singular'] === true && (
			( isset( $luxe['sns_tops_enable'] ) && isset( $luxe['sns_tops_type'] ) && $luxe['sns_tops_type'] !== 'normal' ) ||
			( isset( $luxe['sns_bottoms_enable'] ) && isset( $luxe['sns_bottoms_type'] ) && $luxe['sns_bottoms_type'] !== 'normal' )
		) )
	) {
?><li class="mob-sns" title="SNS"><?php echo $awesome['share'], !isset( $luxe['mobile_button_name_hide'] ) ? $span . 'SNS</span>' : ''; ?></li><?php
	}
}

// サイドバーボタン
if( isset( $luxe['mobile_sidebar_button'] ) ) {
	if( $luxe['column_style'] !== '1column' ) {
?><li class="mob-side" title="<?php echo __( 'Sidebar', 'luxeritas' ) ?>"><?php echo $awesome['sidebar'], !isset( $luxe['mobile_button_name_hide'] ) ? $span . __( 'Sidebar', 'luxeritas' ) . '</span>' : ''; ?></li><?php
	}
}

// 目次ボタン
if( isset( $luxe['mobile_toc_button'] ) && isset( $luxe['toc_auto_insert'] ) ) {
	if( ( $_is['single'] === true && isset( $luxe['toc_single_enable'] ) ) || ( $_is['page'] === true && isset( $luxe['toc_page_enable'] ) ) ) {
		global $post;
		$toc_array = thk_create_toc( $post->post_content, false );

		if( !empty( $toc_array[1] ) ) {
?><li class="mob-toc" title="<?php echo __( 'TOC', 'luxeritas' ) ?>"><?php echo $awesome['toc'], !isset( $luxe['mobile_button_name_hide'] ) ? $span . __( 'TOC', 'luxeritas' ) . '</span>' : ''; ?></li><?php
		}
	}
}

// コメントボタン
if( isset( $luxe['mobile_comment_button'] ) ) {
	if( $_is['comments_open'] === true && ( ( $_is['single'] === true && isset( $luxe['comment_visible'] ) ) || ( $_is['page'] === true && isset( $luxe['comment_page_visible'] ) ) ) ) {
?><li class="mob-comment"><a href="#respond" title="<?php echo __( 'Comment', 'luxeritas' ) ?>"><?php echo $awesome['comment'], !isset( $luxe['mobile_button_name_hide'] ) ? $span . __( 'Comment', 'luxeritas' ) . '</span>' : ''; ?></a></li><?php
	}
}

// 検索ボタン
if( isset( $luxe['mobile_search_button'] ) ) {
?><li class="mob-search" title="<?php echo __( 'Search', 'luxeritas' ) ?>"><?php echo $awesome['search'], !isset( $luxe['mobile_button_name_hide'] ) ? $span . __( 'Search', 'luxeritas' ) . '</span>' : ''; ?></li><?php
}

// ページ上に戻るボタン
if( isset( $luxe['mobile_pagetop_button'] ) ) {
?><li id="page-top-m" title="<?php echo __( 'Page Top', 'luxeritas' ) ?>"><?php echo $awesome['page-top'], !isset( $luxe['mobile_button_name_hide'] ) ? $span . __( 'Page Top', 'luxeritas' ) . '</span>' : ''; ?></li><?php
}
?></ul></div>
