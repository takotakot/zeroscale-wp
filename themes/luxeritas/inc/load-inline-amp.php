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

global $awesome, $sidebars_widgets;

// Amp 用のスタイルとスクリプト挿入
$ampproject  = 'cdn' . '.ampproject' . '.org';

if( isset( $awesome['material']['uri'] ) && isset( $awesome['material']['css'] ) ) {
?>
<link rel="stylesheet" href="<?php echo $awesome['material']['uri'], $awesome['material']['css']; ?>" crossorigin="anonymous" />
<?php
}
if( isset( $awesome['awesome']['uri'] ) && isset( $awesome['awesome']['css'] ) ) {
?>
<link rel="stylesheet" href="<?php echo $awesome['awesome']['uri'], $awesome['awesome']['css']; ?>" crossorigin="anonymous" />
<?php
}
?>
<script async src="https://<?php echo $ampproject; ?>/v0.js"></script>
<?php
$amp_extensions = thk_amp_extensions();

foreach( $amp_extensions as $key => $val ) {
	if( isset( $luxe[$key] ) ) {
?>
<script async custom-element="<?php echo $key; ?>" src="https://<?php echo $ampproject, $val; ?>"></script>
<?php
	}
}
unset( $amp_extensions );
?>
<style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style>
<noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>
<?php
wp_enqueue_style( 'luxe-amp', TDEL . '/style-amp.css', false, array(), 'screen' );
wp_add_inline_style( 'luxe-amp', thk_direct_style( TPATH . DSEP . 'style-amp.min.css' ) );

$load = '';
$css_dir = TPATH . DSEP . 'css' . DSEP;
$styles_dir = TPATH . DSEP . 'styles' . DSEP;

$content = apply_filters( 'thk_content', '' );

// サムネイルの自動挿入
if( $_is['singular'] === true ) {
	require( INC . 'thumb-auto-insert.php' );
}

// ブロックエディタ用インラインスタイル
if( class_exists( 'thk_block_styles' ) === false ) {
	require( INC . 'load-block-styles.php' );
	$block_styles = new thk_block_styles();
	$load .= $block_styles->block_styles();
}

// ブログカード
if( strpos( $content, 'data-blogcard' ) !== false || strpos( $content, 'class="blogcard"' ) !== false ) {
		$load .= thk_fgc( $styles_dir . 'blogcard.css' );
}

// レスポンシブプレビュー
if( isset( $_GET['respond_frame'] ) && $_is['customize_preview'] === false && $_is['edit_posts'] === true ) {
	$load .=  thk_fgc( $css_dir . 'respond.css' );
}

// シンタックスハイライター
if( isset( $luxe['highlighter_css'] ) && $luxe['highlighter_css'] !== 'none' ) {
	if( strpos( $content, '<code class="language-' ) !== false ) {
		$prism_dir = $css_dir . 'prism' . DSEP;
		$load .= thk_fgc( $prism_dir . 'prism-amp-' . $luxe['highlighter_css'] . '.css' );
	}
}

// 新着記事ウィジェット
if( isset( $luxe['thk_recent_posts_wide_thumb'] ) ) {
	$load .= $luxe['thk_recent_posts_wide_thumb'];
}

if( $_is['customize_preview'] === false && $_is['edit_posts'] === true ) {
	$load .= thk_fgc( $css_dir . 'ladmin-amp.css' );
}

$load = trim( thk_simple_css_minify( $load ) );

if( !empty( $load ) ) {
	wp_add_inline_style( 'luxe-amp', $load );
}

// AMP 用子テーマ
if( isset( $luxe['child_css'] ) && TDEL !== SDEL ) {
	wp_enqueue_style( 'luxech-amp', SDEL . '/style-amp.css', false, array(), 'screen' );
	wp_add_inline_style( 'luxech-amp', thk_direct_style( SPATH . DSEP . 'style-amp.min.css' ) );
}

// amp-custom 用カスタムヘッダー (投稿単位の AMP 用追加 CSS)
$ampcustom = get_post_meta( $post->ID, 'amp-custom', true );
if( !empty( $ampcustom ) ) {
	if( TDEL === SDEL ) {
		wp_add_inline_style( 'luxe-amp', $ampcustom );
	}
	else {
		wp_add_inline_style( 'luxech-amp', $ampcustom );
	}
}

unset( $load, $css_dir, $styles_dir );
