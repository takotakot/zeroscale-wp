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

require_once( INC . 'thumbnail-images.php' );
thk_custom_image_sizes::custom_image_sizes();

if( class_exists( 'carray' ) === false ) {
	require( INC . 'carray.php' );
}

/*---------------------------------------------------------------------------
 * admin init
 *---------------------------------------------------------------------------*/
add_action( 'admin_init', function() {
	thk_default_set();
}, 9 );

/*---------------------------------------------------------------------------
 * admin_head
 *---------------------------------------------------------------------------*/
add_action( 'admin_head', function() {
	if( _is_block_editor() === false || ( isset( $_GET['page'] ) && stripos( $_GET['page'], 'luxe_' ) === 0 ) ) {
		$admin_inline_styles = '';

		/* jquery-ui の CSS */
		$admin_inline_styles .= thk_add_admin_inline_css( TPATH . '/css/jquery-ui.min.css' );
		echo '<style>', thk_simple_css_minify( $admin_inline_styles ), '</style>';
	}
}, 99 );

/*---------------------------------------------------------------------------
 * admin_print_scripts / admin_print_styles
 *---------------------------------------------------------------------------*/
/* jquery-ui script */
add_action( 'admin_print_scripts', function() {
	if( _is_block_editor() === false || ( isset( $_GET['page'] ) && stripos( $_GET['page'], 'luxe_' ) === 0 ) ) {
		wp_enqueue_script( 'thk-jquery-ui-script', TURI . '/js/jquery-ui.min.js', array( 'jquery' ), false, false );
	}
}, 99 );

if( current_user_can( 'edit_published_posts' ) === true ) {
	// イメージセレクター用 Javascript
	add_action( 'admin_print_scripts', function() {
		wp_enqueue_media();
		wp_enqueue_script( 'thk-imgselector-script', get_template_directory_uri() . '/js/thk-imgselector.js', array( 'media-views' ), false );
		wp_localize_script( 'media-views', '_thkImageViewsL10n', array( 'setImage' => __( 'Set image', 'luxeritas' ) ) );

	});

	// イメージセレクター用 CSS
	add_action( 'admin_print_styles', function() {
		wp_register_style( 'thk-imgselector-style', get_template_directory_uri() . '/css/thk-imgselector.css', false, false );
	        wp_enqueue_style( 'thk-imgselector-style' );
	});

	// メディア画面で ALT 属性を自動設定
	/*
	if( isset( $luxe['media_alt_auto_input'] ) ) {
		add_filter( 'wp_prepare_attachment_for_js', function( $args, $attachment, $meta ) {
			if( empty( $args['alt'] ) )
				$args['alt'] = $args['title'];
			return $args;
		}, 10, 3 );
	}
	*/
}

/*---------------------------------------------------------------------------
 * 管理画面で使う CSS の読み込み
 *---------------------------------------------------------------------------*/
if( function_exists( 'thk_add_admin_inline_css' ) === false ):
function thk_add_admin_inline_css( $css_path ) {
	if( file_exists( $css_path ) === true ) {
		ob_start();
		require( $css_path );
		$css = ob_get_clean();
		return $css;
	}
	return '';
}
endif;

/*---------------------------------------------------------------------------
 * ブロックエディタかどうかを判別する関数
 *---------------------------------------------------------------------------*/
if( function_exists( '_is_block_editor' ) === false ):
function _is_block_editor() {
	global $post;

	if( apply_filters( 'replace_editor', false, $post ) !== true ) {
		if( function_exists( 'use_block_editor_for_post' ) === true ) {
			if( use_block_editor_for_post( $post ) ) {
				return true;
			}
			else {
				return false;
			}
		}
	}
	else {
		return false;
	}
}
endif;

/*---------------------------------------------------------------------------
 * wp_dropdown_pages の投稿ページ版
 *---------------------------------------------------------------------------*/
if( function_exists( 'thk_dropdown_posts' ) === false ):
function thk_dropdown_posts( $args = '' ) {
	$defaults = array(
		'selected'              => false,
		'pagination'            => false,
		'post_type'           	=> 'post',
		'posts_per_page'        => - 1,
		'post_status'           => 'publish',
		'cache_results'         => true,
		'cache_post_meta_cache' => true,
		'echo'                  => 1,
		'select_name'           => 'post_id',
		'id'                    => '',
		'class'                 => '',
		'show'                  => 'post_title',
		'show_callback'         => null,
		'show_option_all'       => null,
		'show_option_none'      => null,
		'option_none_value'     => '',
		'multi'                 => false,
		'value_field'           => 'ID',
		'order'                 => 'ASC',
		'orderby'               => 'post_title',
	);

	$r = wp_parse_args( $args, $defaults );

	$posts  = get_posts( $r );
	$output = '';

	$show = $r['show'];

	if( !empty( $posts ) ) {
		$name = esc_attr( $r['select_name'] );

		if( $r['multi'] && ! $r['id'] ) {
			$id = '';
		}
		else {
			$id = $r['id'] ? " id='" . esc_attr( $r['id'] ) . "'" : " id='$name'";
		}

		$output = "<select name='{$name}'{$id} class='" . esc_attr( $r['class'] ) . "'>";

		if( $r['show_option_all'] ) {
			$output .= "<option value='0'>{$r['show_option_all']}</option>";
		}

		if( $r['show_option_none'] ) {
			$_selected = selected( $r['show_option_none'], $r['selected'], FALSE );
			$output .= "<option value='" . esc_attr( $r['option_none_value'] ) . "'$_selected>{$r['show_option_none']}</option>";
		}

		foreach( (array)$posts as $post ) {
			$post->ID  = (int) $post->ID;
			$_selected = selected( $post->ID, $r['selected'], FALSE );

			$value   = !isset( $r['value_field'] ) || ! isset( $post->{$r['value_field']} ) ? $post->ID : $post->{$r['value_field']};
			$display = !empty( $post->$show ) ? $post->$show : sprintf( '#%d (no title)', $post->ID );

			if( $r['show_callback'] ) $display = call_user_func( $r['show_callback'], $display, $post->ID );

			$output .= "<option value='{$value}'{$_selected}>" . esc_html( $display ) . "</option>";
		}
		$output .= "</select>";
	}

	$ret = apply_filters( 'thk_dropdown_posts', $output, $r, $posts );

	if( $r['echo'] ) echo $ret;

	return $ret;
}
endif;

/*---------------------------------------------------------------------------
 * 投稿画面のボタン配列
 *---------------------------------------------------------------------------*/
// TinyMCE ボタン 1段目
if( function_exists( 'thk_mce_buttons_1' ) === false ):
function thk_mce_buttons_1() {
	return array(
		'formatselect'		=> '<div class="cover" title="' . __( 'Paragraph', 'luxeritas' ) . '"><input type="text" class="drop-down" value="' . __( 'Paragraph', 'luxeritas' ) . '" /></div>',
		'thk-phrase-button'	=> '<i class="mce-ico mce-i-thk-phrase-button" title="' . __( 'HTML pattern', 'luxeritas' ) . '"></i>',
		'thk-shortcode-button'	=> '<i class="mce-ico mce-i-thk-shortcode-button" title="' . __( 'Shortcode', 'luxeritas' ) . '"></i>',
		'thk-blogcard-button'	=> '<i class="mce-ico mce-i-thk-blogcard-button" title="' . __( 'Blog Card', 'luxeritas' ) . '"></i>',
		'visualblocks'		=> '<i class="mce-ico mce-i-visualblocks" title="' . __( 'Show blocks', 'luxeritas' ) . '"></i>',
		'bold'			=> '<i class="mce-ico mce-i-bold" title="' . __( 'Bold', 'luxeritas' ) . '"></i>',
		'italic'		=> '<i class="mce-ico mce-i-italic" title="' . __( 'Italic', 'luxeritas' ) . '"></i>',
		'bullist'		=> '<i class="mce-ico mce-i-bullist" title="' . __( 'Bulleted list', 'luxeritas' ) . '"></i>',
		'numlist'		=> '<i class="mce-ico mce-i-numlist" title="' . __( 'Numbered list', 'luxeritas' ) . '"></i>',
		'blockquote'		=> '<i class="mce-ico mce-i-blockquote" title="' . __( 'Blockquote', 'luxeritas' ) . '"></i>',
		'alignleft'		=> '<i class="mce-ico mce-i-alignleft" title="' . __( 'Align left', 'luxeritas' ) . '"></i>',
		'aligncenter'		=> '<i class="mce-ico mce-i-aligncenter title="' . __( 'Align center', 'luxeritas' ) . '""></i>',
		'alignright'		=> '<i class="mce-ico mce-i-alignright" title="' . __( 'Align right', 'luxeritas' ) . '"></i>',
		'link'			=> '<i class="mce-ico mce-i-link" title="' . __( 'Insert/edit link', 'luxeritas' ) . '"></i>',
		'unlink'		=> '<i class="mce-ico mce-i-unlink" title="' . __( 'Remove link', 'luxeritas' ) . '"></i>',
		'table'			=> '<i class="mce-ico mce-i-table" title="' . __( 'Table', 'luxeritas' ) . '"></i>',
		'thk_emoji'		=> '<i class="mce-ico mce-i-thk_emoji" title="' . __( 'Emoji', 'luxeritas' ) . ' by Luxeritas"></i>',
		'wp_more'		=> '<i class="mce-ico mce-i-wp_more" title="' . __( 'Read more...', 'luxeritas' ) . '"></i>',
		'wp_page'		=> '<i class="mce-ico mce-i-wp_page" title="' . __( 'Page break', 'luxeritas' ) . '"></i>',
		//'spellchecker'	=> true,	// 元の配列には存在するけど表示はされない (TinyMCE のプラグインが入ってない。日本語チェックできないからイラネ)
		/* 以下、常に表示させるので配列から削除 */
		//'kitchensink'		=> true,	// 2段目を表示・非表示するやつ
		//'wp_adv'		=> true,	// kitchen sink のエイリアス (ツールチップの名前が違う)
		//'dfw'			=> true,	// distraction free writing mode (集中執筆モード)
	);
}
endif;

// TinyMCE ボタン 2段目
if( function_exists( 'thk_mce_buttons_2' ) === false ):
function thk_mce_buttons_2() {
	return array(
		'fontsizeselect'	=> '<div class="cover" title="' . __( 'Font size', 'luxeritas' ) . '"><input type="text" class="drop-down" value="' . __( 'Font size', 'luxeritas' ) . '" /></div>',
		'fontselect'		=> '<div class="cover" title="' . __( 'Font family ', 'luxeritas' ) . '"><input type="text" class="drop-down" value="' . __( 'Font family ', 'luxeritas' ) . '" /></div>',
		'strikethrough'		=> '<i class="mce-ico mce-i-strikethrough" title="' . __( 'Strikethrough', 'luxeritas' ) . '"></i>',
		'underline'		=> '<i class="mce-ico mce-i-underline" title="' . __( 'Underline', 'luxeritas' ) . '"></i>',
		'hr'			=> '<i class="mce-ico mce-i-hr" title="' . __( 'Horizontal line', 'luxeritas' ) . '"></i>',
		'forecolor'		=> '<i class="mce-ico mce-i-forecolor" title="' . __( 'Text color', 'luxeritas' ) . '"></i>',
		'backcolor'		=> '<i class="mce-ico mce-i-backcolor" title="' . __( 'Background color', 'luxeritas' ) . '"></i>',
		'pastetext'		=> '<i class="mce-ico mce-i-pastetext" title="' . __( 'Paste as text', 'luxeritas' ) . '"></i>',
		'removeformat'		=> '<i class="mce-ico mce-i-removeformat" title="' . __( 'Clear formatting', 'luxeritas' ) . '"></i>',
		'charmap'		=> '<i class="mce-ico mce-i-charmap" title="' . __( 'Special character', 'luxeritas' ) . '"></i>',
		'outdent'		=> '<i class="mce-ico mce-i-outdent" title="' . __( 'Decrease indent', 'luxeritas' ) . '"></i>',
		'indent'		=> '<i class="mce-ico mce-i-indent" title="' . __( 'Increase indent', 'luxeritas' ) . '"></i>',
		'undo'			=> '<i class="mce-ico mce-i-undo" title="' . __( 'Undo', 'luxeritas' ) . '"></i>',
		'redo'			=> '<i class="mce-ico mce-i-redo" title="' . __( 'Redo', 'luxeritas' ) . '"></i>',
		'searchreplace'		=> '<i class="mce-ico mce-i-searchreplace" title="' . __( 'Find and replace', 'luxeritas' ) . '"v></i>',
		'wp_help'		=> '<i class="mce-ico mce-i-wp_help" title="help" title="' . __( 'Keyboard Shortcuts', 'luxeritas' ) . '"></i>',
		'thk-mce-settings-button' => '<i class="mce-ico mce-i-thk-mce-settings-button" title="Luxeritas Visual Editor Settings"></i>',
	);
}
endif;

// TinyMCE ボタン 未使用（ごみ箱）
if( function_exists( 'thk_mce_buttons_d' ) === false ):
function thk_mce_buttons_d() {
	return array(
		'toc'			=> '<i class="mce-ico mce-i-toc" title="' . __( 'Table of Contents', 'luxeritas' ) . '"></i>',
		'copy'			=> '<i class="mce-ico mce-i-copy" title="' . __( 'Copy', 'luxeritas' ) . '"></i>',
		'paste'			=> '<i class="mce-ico mce-i-paste" title="' . __( 'Paste', 'luxeritas' ) . '"></i>',
		'cut'			=> '<i class="mce-ico mce-i-cut" title="' . __( 'Cut', 'luxeritas' ) . '"></i>',
		'nonbreaking'		=> '<i class="mce-ico mce-i-nonbreaking" title="' . __( 'Nonbreaking space', 'luxeritas' ) . '"></i>',
		'anchor'		=> '<i class="mce-ico mce-i-anchor" title="' . __( 'Anchor', 'luxeritas' ) . '"></i>',
		'insertdatetime'	=> '<i class="mce-ico mce-i-insertdatetime" title="' . __( 'Insert date/time', 'luxeritas' ) . '"></i>',
		'ltr'			=> '<i class="mce-ico mce-i-ltr" title="' . __( 'Left to right', 'luxeritas' ) . '"></i>',
		'rtl'			=> '<i class="mce-ico mce-i-rtl" title="' . __( 'Right to left', 'luxeritas' ) . '"></i>',
		'wp_code'		=> '<i class="mce-ico mce-i-code" title="' . __( 'Source code', 'luxeritas' ) . '"></i>',
		'alignjustify'		=> '<i class="mce-ico mce-i-alignjustify" title="' . __( 'Justify', 'luxeritas' ) . '"></i>',
		'visualchars'		=> '<i class="mce-ico mce-i-visualchars" title="' . __( 'Show invisible characters', 'luxeritas' ) . '"></i>',
		'superscript'		=> '<i class="mce-ico mce-i-superscript" title="' . __( 'Superscript', 'luxeritas' ) . '"></i>',
		'subscript'		=> '<i class="mce-ico mce-i-subscript" title="' . __( 'Subscript', 'luxeritas' ) . '"></i>',
		'image'			=> '<i class="mce-ico mce-i-image" title="' . __( 'Insert/edit image', 'luxeritas' ) . '"></i>',
		'media'			=> '<i class="mce-ico mce-i-media" title="' . __( 'Insert/edit video', 'luxeritas' ) . '"></i>',
		'print'			=> '<i class="mce-ico mce-i-print" title="' . __( 'Print', 'luxeritas' ) . '"></i>',
		'fullscreen'		=> '<i class="mce-ico mce-i-fullscreen" title="' . __( 'Fullscreen', 'luxeritas' ) . '"></i>',
	);
}
endif;

// テキスト入力ボタン
if( function_exists( 'thk_txt_buttons_1' ) === false ):
function thk_txt_buttons_1() {
	$ret = array();
	$ids = array(
		'thk-b'			=> 'b',
		'em'			=> 'i',
		'thk-strong'		=> 'strong',
		'link'			=> 'link',
		'thk-div'		=> 'div',
		'thk-span'		=> 'span',
		'block'			=> 'b-quote',
		'thk-h2'		=> 'h2',
		'thk-h3'		=> 'h3',
		'thk-h4'		=> 'h4',
		'thk-hr'		=> 'hr',
		'del'			=> 'del',
		'ins'			=> 'ins',
		'img'			=> 'img',
		'ul'			=> 'ul',
		'ol'			=> 'ol',
		'li'			=> 'li',
		'thk-pre'		=> 'pre',
		'code'			=> 'code',
		'thk-next'		=> 'nextpage',
		'more'			=> 'more',
		'thk-phrase'		=> __( 'HTML patterns', 'luxeritas' ),
		'thk-shortcode'		=> __( 'Shortcode', 'luxeritas' ),
		'thk-blogcard'		=> __( 'Blog Card', 'luxeritas' ),
		'close'			=> __( 'close tags', 'luxeritas' ),
	);
	foreach( $ids as $key => $val ) {
		$ret[$key] = '<div class="cover"><input type="button" id="qt_content_' . $key . '" class="ed_button button button-small" value="' . $val . '" /></div>';
	}
	return $ret;
}
endif;
