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
 * ヘッダー、サイドバー、その他の書き換え
 *---------------------------------------------------------------------------*/
// head
add_filter( 'thk_head', function() use( &$luxe ) {
	global $_is;

	if( $_is['feed'] === true ) return;

	$remove = 'remove_action';

	// WP 5.7 のサイトヘルスチェックで引っかかるので、rsd_link と wlwmanifest_link は functions.php に移動。
	// ( サイトヘルスチェックが動く前に remove_action しないと has_action が true になっちゃうのが原因 )
	// 参考： wp-includes/https-detection.php: wp_is_local_html_output()

	$remove( 'wp_head', 'index_rel_link' );
	//$remove( 'wp_head', 'rsd_link' );
	$remove( 'wp_head', 'feed_links', 2 );
	$remove( 'wp_head', 'feed_links_extra', 3 );
	$remove( 'wp_head', 'wp_generator' );
	//$remove( 'wp_head', 'wlwmanifest_link' );
	$remove( 'wp_head', 'start_post_rel_link', 10, 0 );
	$remove( 'wp_head', 'parent_post_rel_link', 10, 0 );
	$remove( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );

	// canonical と shortlink は SEO で重要なので meta tag の上位に表示されるように、いったん消す
	$remove( 'wp_head', 'rel_canonical' );
	$remove( 'wp_head', 'wp_shortlink_wp_head' );

	// 絵文字スクリプト (非同期 の Javascript を優先させるため、wp_head の後に挿入するのでいったん消して再挿入)
	$remove( 'wp_head', 'print_emoji_detection_script', 7 );

	if( version_compare( $GLOBALS['wp_version'], '6.4', '<' ) === true ) {
		$remove( 'wp_print_styles', 'print_emoji_styles', 10 );
	}
	else {
		if( isset( $luxe['thk_emoji_disable'] ) || isset( $luxe['amp'] ) ) {
			$remove( 'wp_print_styles', 'print_emoji_styles', 10 );
		}
	}

	if( !isset( $luxe['thk_emoji_disable'] ) && !isset( $luxe['amp'] ) ) {
		add_action( 'wp_head', 'print_emoji_detection_script', 109 );
		add_action( 'wp_head', function() {
			ob_start();
			if( version_compare( $GLOBALS['wp_version'], '6.4', '<' ) === true ) {
				// WP 6.4 未満
				print_emoji_styles();
			}
			else {
				// WP 6.4 以上
				wp_enqueue_emoji_styles();
			}
			$emoji = ob_get_clean();
			echo thk_simple_css_minify( $emoji ), "\n";
		}, 110 );
	}

	// embed
	if( isset( $luxe['amp'] ) || isset( $luxe['thk_embed_disable'] ) || isset( $luxe['blogcard_embedded'] ) ) {
		$remove( 'wp_head', 'rest_output_link_wp_head' );
		$remove( 'wp_head', 'wp_oembed_add_discovery_links' );
		$remove( 'wp_head', 'wp_oembed_add_host_js' );
		$remove( 'parse_query', 'wp_oembed_parse_query' );
		$remove( 'rest_api_init', 'wp_oembed_register_route' );
		remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );
		remove_filter( 'pre_oembed_result', 'wp_filter_pre_oembed_result', 10 );
		add_filter( 'embed_oembed_discover', '__return_false' );
	}
	else {
		// WP 5.7 以降用 ( Embed が ON なら functions.php で remove した oEmbed のリンクを復活させる)
		add_action( 'wp_head', 'rest_output_link_wp_head' );
	}

	apply_filters( 'thk_rel', '' );

	ob_start();
	wp_head();
	$head = ob_get_clean();

	$head = str_replace(
		array(
			' type="text/javascript"',
			" type='text/javascript'",
			' type="text/css"',
			" type='text/css'"
		)
	, '', $head );

	$head = str_replace( 'as="style"', 'as="style" type="text/css"', $head );

	if( isset( $luxe['amp'] ) ) {
		$head = str_replace( "'", '"', $head );
		$head = str_replace( 'id="luxe-amp-inline-css"', 'amp-custom', $head );
		$head = str_replace( "</style>\n<style id=\"luxech-amp-inline-css\">\n", '', $head );
	}

	// 特殊なパターンのインラインスタイル挿入
	$_is_admin_bar = is_admin_bar_showing();
	$inline_style = '';

	// reCAPTCHA v3 が設定されてる場合 ( Page Top ボタンの位置をずらす)
	if( isset( $luxe['captcha_enable'] ) && $luxe['captcha_enable'] === 'recaptcha-v3' && isset( $luxe['recaptcha_v3_ptop'] ) ) {
		if( $luxe['recaptcha_v3_ptop'] === 'left' ) {
			$inline_style .= '#page-top{right:76px}';
		}
		elseif( $luxe['recaptcha_v3_ptop'] === 'top' ) {
			$inline_style .= '#page-top{right:0;bottom:80px}';
		}
	}

	if(
		( $_is['mobile']=== true && isset( $luxe['hide_mobile_sidebar'] ) ) ||
		( $_is['mobile']=== true && isset( $luxe['hide_mobile_footer'] ) ) ||
		$_is_admin_bar === true
	) {
		// モバイル・スマホでサイドバーが非表示に設定されてる場合
		// Chrome の iPad Pro エミュ（幅：1024px）だとサイドバー非表示にならんので削除
		// ていうか iPad Pro のサイズではサイドバー表示するようにした
		/*
		if( $_is['mobile']=== true && isset( $luxe['hide_mobile_sidebar'] ) ) {
			$inline_style .= 'body #main{flex:none;float:none;max-width:100%;}';
		}
		*/

		// モバイル・スマホでフッターが非表示に設定されてる場合
		if( $_is['mobile']=== true && isset( $luxe['hide_mobile_footer'] ) ) {
			$inline_style .= 'body #foot-in{padding:0}';
		}

		// WordPress の管理バーが見えてる場合 600px 以下でも固定表示させる
		if( $_is_admin_bar === true ) {
			$inline_style .= '#wpadminbar{position:fixed!important}' . "\n";
		}
	}
	if( !empty( $inline_style ) ) {
		$inline_style = thk_simple_css_minify( $inline_style );

		$start = strripos( $head, '</style>' );
		if( isset( $luxe['amp'] ) || $start !== false ) {
			$head = substr_replace( $head, $inline_style . '</style>', $start, 8 );
		}
		else {
			$head .= '<style>' . $inline_style . '</style>' . "\n";
		}
	}

	// 圧縮
	if( $luxe['html_compress'] !== 'none' ) {
		$head = preg_replace( '/\n\s+</', "\n".'<', $head );
		$head = str_replace( "\t", '', $head );
		$head = thk_html_format( $head );
	}

	return $head;
}, 9, 1 );

// header
/*
add_filter( 'thk_header', function() {
	ob_start();
	get_header();
	return ob_get_clean();
}, 9, 1 );
*/

// sidebar
add_filter( 'thk_sidebar', function( $col = null ) use( &$luxe ) {
	global $_is;

	if( $_is['feed'] === true ) return;

	ob_start();
	if( empty( $col ) ) {
		get_sidebar();
	}
	else {
		// 3カラム用サイドバー
		get_sidebar('left');
	}
	$side = ob_get_clean();

	if( isset( $luxe['amp'] ) ) {
		$side = thk_amp_not_allowed_tag_replace( $side );
	}

	if( $luxe['html_compress'] !== 'none' ) {
		$side = thk_html_format( $side );
	}

	return $side;
}, 9, 1 );

// footer
add_filter( 'thk_footer', function() use( &$luxe ) {
	global $_is;

	if( $_is['feed'] === true ) return;

	ob_start();
	get_footer();
	$foot = ob_get_clean();

	$foot = str_replace( array(
			" type='text/javascript'",
			' type="text/javascript"',
			" type='text/css'",
			' type="text/css"'
		), '', $foot
	);

	if( stripos( $foot, "<!--/#wp-footer-->\n</body>" ) === false ) {
		$foot = preg_replace( '/<\!--\/#footer-->.+?<\/body>/ism', "<!--/#footer-->\n</body>", $foot );
	}

	if( $luxe['html_compress'] !== 'none' ) {
		$foot = str_replace( "\t", ' ', $foot );
		$cnt = 1;
		while( $cnt !== 0 ) {
			$foot = str_replace( '  ', ' ', $foot, $cnt );
		}
		$foot = str_replace( "\n ", "\n", $foot );
		$foot = thk_html_format( $foot );
	}

	return $foot;
}, 9, 1 );

/*---------------------------------------------------------------------------
 * thk_prefetch
 *---------------------------------------------------------------------------*/
add_filter( 'thk_prefetch', function( $ret ) use( &$luxe ) {
	global $awesome;

	// Font Awesome
	if( isset( $awesome['awesome']['cdn'] ) ) {
		$ret .= "<link rel='dns-prefetch' href='//" . $awesome['awesome']['cdn'] . "' />\n";
	}

	// Material Icons
	if( isset( $awesome['material']['cdn'] ) ) {
		$ret .= "<link rel='preconnect' href='//" . $awesome['material']['cdn'] . "' crossorigin />\n";
	}

	// Web Font
	if(
		( isset( $luxe['font_alphabet'] ) && isset( Web_Font::$webfont[$luxe['font_alphabet']] ) ) ||
		( isset( $luxe['font_japanese'] ) && isset( Web_Font::$webfont[$luxe['font_japanese']] ) )
	) {
		$ret .= "<link rel='preconnect' href='//fonts.gstatic.com' crossorigin />\n";
	}

	return $ret;
}, 9, 1 );

/*---------------------------------------------------------------------------
 * header タグ内の一番下
 *---------------------------------------------------------------------------*/
add_filter( 'thk_header_under', function( $ret ) {
	return $ret;
}, 9, 1 );

/*---------------------------------------------------------------------------
 * remove SVG and global styles for the WP duotone filter.
 *---------------------------------------------------------------------------*/
if( isset( $luxe['wp_disable_duotone'] ) ) {
	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
	//remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );
	remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );
	remove_filter( 'render_block', 'wp_render_duotone_support' );
	//remove_filter( 'render_block', 'wp_restore_group_inner_container' );
	//remove_filter( 'render_block', 'wp_render_layout_support_flag' );
}

/*---------------------------------------------------------------------------
 * 投稿・固定・404 ページの H1 (フロントページは H2)
 *---------------------------------------------------------------------------*/
add_filter( 'thk_h_tag', function( $deps = 1, $title = '', $itemprop = '', $id = '', $cls = '', $span = false ) {
	if( empty( $title ) )		$title = get_the_title();
	if( !empty( $id ) )		$id = 'id="' . $id . '" ';
	if( !empty( $cls ) )		$cls = 'class="' . $cls . '" ';
	if( !empty( $itemprop ) )	$itemprop = 'itemprop="' . $itemprop . '"';

	if( $span !== false ) {
		return '<h' . $deps . ' ' . $id . $cls . $itemprop . '><span>' . $title . '</span></h' . $deps . '>';
	}

	return '<h' . $deps . ' ' . $id . $cls . $itemprop . '>' . $title . '</h' . $deps . '>';
}, 9, 6 );

/*---------------------------------------------------------------------------
 * サムネイルの自動挿入
 *---------------------------------------------------------------------------*/
add_filter( 'thk_thumb_auto_insert', function( $post_id, $thumb = 'full', $figure_class ='', $img_class = 'post_thumbnail' ) {
	$ret = '';

	if( isset( $post_id ) && has_post_thumbnail() === true ) {
		if( !empty( $figure_class ) ) {
			$figure_class = ' class="' . $figure_class . '"';
		}
		$ret = '<figure id="post-thumbnail"' . $figure_class . '>' . get_the_post_thumbnail( $post_id, $thumb, array( 'itemprop' => 'image', 'class' => $img_class ) );

		$figcaption = get_post( get_post_thumbnail_id() )->post_excerpt;

		if( !empty( $figcaption ) ) {
			$ret .= '<figcaption class="post-thumbnail-caption">' . $figcaption . '</figcaption>';
		}
		$ret .= '</figure>';
	}

	return $ret;
}, 9, 4 );

/*---------------------------------------------------------------------------
 * 関連記事
 *---------------------------------------------------------------------------*/
add_filter( 'thk_related', function() {
	ob_start();
	get_template_part( 'related' );
	$related = ob_get_clean();

	return $related;
}, 9, 1 );

/*---------------------------------------------------------------------------
 * WordPress 4.7.0 以降で追加された「追加 CSS」機能
 *---------------------------------------------------------------------------*/
add_filter( 'wp_get_custom_css', function( $css ) {
	$css = trim( $css );

	if( empty( $css ) ) return false;

	if( $css[0] === '/' && $css[1] === '*' ) {
		if( substr_count( $css, '/*' ) <= 1 && substr( $css, -2 ) === '*/' ) {
			return false;
		}
	}

	/*
	if( class_exists('CSSminMinifier') === false ) {
		require( INC . 'cssmin.php' );
	}
	$minify = new CSSminMinifier();
	if( method_exists( $minify, 'run' ) === true ) {
		return $minify->run( $css );
	}
	*/

	/* 動的処理になるので、本気を出さない簡易圧縮 (本気圧縮するなら上記のコメント外す) */
	return thk_simple_css_minify( $css );
}, 99 );

/*---------------------------------------------------------------------------
 * ヘッダーナビ
 *---------------------------------------------------------------------------*/
add_filter( 'thk_head_nav', function() {
	ob_start();
	get_template_part('navi');
	$nav = ob_get_clean();
	return $nav;
}, 9, 1 );

/*---------------------------------------------------------------------------
 * 投稿本文
 *---------------------------------------------------------------------------*/
add_filter( 'thk_content', function( $content = null ) {
	global $luxe;
	if( empty( $content ) )	$content = get_the_content('');
	$content = apply_filters( 'the_content', $content );

	if( $luxe['html_compress'] === 'low' ) {
		$content = preg_replace( '/>\s+?</im', ">\n<", $content );
	}
	elseif( $luxe['html_compress'] === 'high' ) {
		$content = preg_replace( '/>\s+?</im', "><", $content );
	}

	return $content;
}, 9, 1 );

/*---------------------------------------------------------------------------
 * コメント
 *---------------------------------------------------------------------------*/
add_filter( 'get_comment_text', function( $comment_content, $comment, $args = array() ) {
	return thk_admin_comments( $comment_content, $comment );
}, 10, 3 );

/*---------------------------------------------------------------------------
 * avatar
 *---------------------------------------------------------------------------*/
add_filter( 'get_avatar', function( $content ) {
	global $luxe;

	return $content;
}, 99 );

/*---------------------------------------------------------------------------
 * the content
 *---------------------------------------------------------------------------*/
add_filter( 'the_content', function( $content = null ) {
	if( empty( $content ) )	{
		$content = get_the_content('');
	}
	return $content;
}, 9 );

/*---------------------------------------------------------------------------
 * カスタム HTML ウィジェット
 *---------------------------------------------------------------------------*/
add_filter('widget_text', 'do_shortcode');

/*---------------------------------------------------------------------------
 * メニューの説明文で HTML 使えるようにする
 *---------------------------------------------------------------------------*/
remove_filter('nav_menu_description', 'strip_tags');
add_filter( 'wp_setup_nav_menu_item', 'thk_nav_menu_description_usable_html' );

/*---------------------------------------------------------------------------
 * 改行するタイプの抜粋
 *---------------------------------------------------------------------------*/
add_filter( 'thk_excerpt', function( $length = 120, $content = null ) use( &$luxe ) {
	global $post, $more;
	$more = true;	// more タグ無視で指定した文字数まで出力( more で切る場合は false に)

	if( is_int( $length ) === false ) {
		$length = 120;
	}

	if( empty( $content ) )	{
		if( has_excerpt() === true && isset( $luxe['excerpt_priority'] )  ) {
			//$content = apply_filters( 'the_excerpt', get_the_excerpt('') );
			$content = apply_filters( 'the_excerpt', $post->post_excerpt );
		}
		else {
			add_filter( 'thk_ex_content', 'wptexturize' );
			add_filter( 'thk_ex_content', 'convert_smilies', 20 );
			add_filter( 'thk_ex_content', 'wpautop' );
			add_filter( 'thk_ex_content', 'shortcode_unautop' );
			//$content = apply_filters( 'thk_ex_content', get_the_content('') );
			$content = apply_filters( 'thk_ex_content', $post->post_content );
		}
	}

	// 改行・タブ削除
	$content = preg_replace( '/\t|\r|\n/', '', trim( $content ) );
	// 連続スペースを1つに
	$content = preg_replace('/\s{2,}/', ' ', $content);

	// </li>タグを<br>に変換（後から<br />に再度変換する）
	$content = str_replace( '</li>', '<br>', $content );

	// <p><br>タグは残して、他のタグを削除
	$content = str_replace(
		array(
			'>広告<',
			'>スポンサーリンク<',
			'>スポンサードリンク<',
			'>Sponsored Links<',
			'>Advertisements<'
		), '><', $content
	);
	if( stripos( $content, '<script' ) !== false || stripos( $content, '<style' ) !== false ) {
		/*$content = preg_replace( '/<script.*?>.*?<\/script>/ism', '', $content );*/
		$content = preg_replace( '/<(script|style)[^>]*?>.*?<\/\\1>/ism', '', $content );
	}
	$content = strip_tags( $content, '<p><br>' );

	// ショートコード削除
	$content = strip_shortcodes( $content );
	if( strpos( $content, '[' ) !== false ) {
		$content = preg_replace( '/\[.+?\]/i', '', $content); // shortcodes の登録タイミングによって strip_shortcodes では取り切れないので
	}

	// URL 削除
	$content = thk_remove_url( $content );

	$content = str_replace( array( '/ ', ' /' ), '/', $content );
	$content = str_replace( array( ' >', '> ' ), '>', $content );
	$content = str_replace( array( '< ', ' <' ), '<', $content );

	// <p> についてる class や style 等を削除
	$content = preg_replace( '/<p[^>]+?>/', '<p>', $content );

	// <img ～>などを<p>で囲ってた場合、<p></p> の形で残るので削除
	$content = str_replace( '<p></p>', '', $content );

	$content = mb_substr( $content, 0, $length );	//文字列を指定した長さで切り取る

	// <p><br>タグの途中で文字列が切れた場合、中途半端に残ったタグを < が出てくるまで後ろから1文字づつ削除
	while( strrpos( $content, '<' ) > strrpos( $content, '>' ) ) {
		$content = substr( $content, 0, -1 );
	}
	// 最後が<br>だったら削除
	$content = str_replace( '<br/>', '<br />', $content );
	$content = str_replace( '<br>', '<br />', $content );

	// <br> が連続してる場合は一個にする
	$cnt = 1;
	while( $cnt !== 0 ) {
		$content = str_replace( '<br /><br />', '<br />', $content, $cnt );
	}

	$content = str_replace( '<p></p>', '', $content );
	$content = str_replace( '<p><br />', '<p>', $content );
	$content = str_replace( '<br /></p>', '</p>', $content );
	if( substr( $content, -6 ) === '<br />' ) {
		$content = substr( $content, 0, -6 );
	}

	// まれに、</p></p><p> こーいう形になるのを修正
	//$content = str_replace( '</p></p><p>', '</p><p>', $content );
	$content = str_replace( '</p></p>', '</p>', $content );

	// 記事の一番最初に空段落（<p></p>）があると冒頭に </p> が残るので削除
	if( stripos( $content, '</p>' ) === 0 ) {
		$content = substr_replace( $content, '', 0, 4 );
	}

	// 三点リーダー付ける
	$three_point = $length > 0 && mb_strlen( $content ) >= $length ? ' ...' : '';

	if( substr( $content, -4 ) === '</p>' ) {
		$content = substr( $content, 0, -4 );
		$content .= $three_point . '</p>';
	}
	else {
		$content .= $three_point;
	}

	// <p>タグの終了タグが無くなってた場合は終了タグを補完
	/*
	if( strripos( $content, '<p>' ) > strripos( $content, '</p>' ) ) {
		$content .= '</p>';
	}
	*/
	// 上記だと不完全なことがあるので以下の処理に変更
	if( stripos( $content, '<p>' ) !== false ) {
		while( substr_count( $content, '<p>' ) > substr_count( $content, '</p>' ) ) {
			$content .= '</p>';
		}
	}

	return $content;
}, 9, 1 );

/*---------------------------------------------------------------------------
 * 改行しないタイプの抜粋
 * wp_excerpt() だとゴミが混じるので改良版
 *---------------------------------------------------------------------------*/
add_filter( 'thk_excerpt_no_break', function( $length, $content = null ) use( &$luxe ) {
	global $post;

	if( is_int( $length ) === false ) {
		$length = 70;
	}
	if( has_excerpt() === true && isset( $luxe['excerpt_priority'] )  ) {
		//$content = apply_filters( 'the_excerpt', get_the_excerpt('') );
		$content = $post->post_excerpt;
	}
	else {
		//$content = apply_filters( 'the_content', get_the_content('') );
		//$content = get_the_content('');
		$content = $post->post_content;
	}
	$content = str_replace(
		array(
			'>広告<',
			'>スポンサーリンク<',
			'>スポンサードリンク<',
			'>Sponsored Links<',
			'>Advertisements<'
		), '><', $content
	);
	//$content = wp_strip_all_tags( $content );
	if( stripos( $content, '<script' ) !== false || stripos( $content, '<style' ) !== false ) {
		$content = preg_replace( '/<(script|style)[^>]*?>.*?<\/\\1>/ism', '', $content );
	}
	$content = strip_tags( $content );

	// ショートコード削除
	$content = strip_shortcodes( $content );
	if( strpos( $content, '[' ) !== false ) {
		$content = preg_replace( '/\[.+?\]/i', '', $content); // shortcodes の登録タイミングによって strip_shortcodes では取り切れないので
	}

	// URL 削除
	$content = thk_remove_url( $content );

	$three_point = $length > 0 && mb_strlen( $content ) >= $length ? ' ...' : '';

	return wp_html_excerpt( $content, $length, $three_point );
}, 9, 2 );

/*---------------------------------------------------------------------------
 * ページネーション (bootstrap version)
 *---------------------------------------------------------------------------*/
add_filter( 'thk_pagination', function( $flag = null ) {
	global $luxe, $_is, $wp_query, $paged;

	$paged = (int)$wp_query->get( 'paged' );

	if( $_is['home'] === true && isset( $luxe['items_home'] ) && isset( $luxe['items_home_num'] ) ) {
		$posts_per_page = $luxe['items_home_num'];
	}
	elseif( $_is['category'] === true && isset( $luxe['items_category'] ) && isset( $luxe['items_category_num'] ) ) {
		$posts_per_page = $luxe['items_category_num'];
	}
	elseif( $_is['archive'] === true && $_is['category'] === false && isset( $luxe['items_archive'] ) && isset( $luxe['items_archive_num'] ) ) {
		$posts_per_page = $luxe['items_archive_num'];
	}
	else {
		$posts_per_page = get_option('posts_per_page');
	}

	if( ( !$paged || $paged < 2 ) && $wp_query->found_posts <= $posts_per_page ) {
		return false;
	}
	elseif( $flag !== null ) {
		return true;
	}

	if( empty( $paged ) ) $paged = 1;

	$pages = (int)$wp_query->max_num_pages;

	if( isset( $luxe['grid_first'] ) && $luxe['grid_first'] > 0 ) {
		// グリッドの通常表示部分は１ページに表示する件数に含めない
		$pages = ceil( ( $wp_query->found_posts - ( $posts_per_page + $luxe['grid_first'] ) ) / $posts_per_page ) + 1;
	}

	if( empty( $pages ) ) $pages = 1;

	$range = 3; //左右に表示する件数
	$showitems = ( $range * 2 ) + 1;	// アイテム数 (current 1件、左右3件、計7件表示)

	if( $_is['mobile'] === true ) {		// モバイル
		if( $paged === 1 ) $range += 1;			// 1ページ目は右に + 1件
		elseif( $paged === 2 ) $range += 1;		// 2ページ目は右に + 1件
		elseif( $paged === 3 ) $range += 1;		// 3ページ目は右に + 1件
		elseif( $paged === $pages ) $range += 1;	// 最終ページは左に + 1件
		elseif( $paged === $pages - 1 ) $range += 1;	// 後ろから2ページ目は左に + 1件
		elseif( $paged === $pages - 2 ) $range += 1;	// 後ろから3ページ目は左に + 1件
	}
	else {					// PC
		if( $paged === 1 ) $range += 3;			// 1ページ目は右に + 3件
		elseif( $paged === 2 ) $range += 2;		// 2ページ目は右に + 2件
		elseif( $paged === 3 ) $range += 1;		// 3ページ目は右に + 1件
		elseif( $paged === $pages ) $range += 3;	// 最終ページは左に + 3件
		elseif( $paged === $pages - 1 ) $range += 2;	// 後ろから2ページ目は左に + 2件
		elseif( $paged === $pages - 2 ) $range += 1;	// 後ろから3ページ目は左に + 1件
	}

	$html = '';

	if( $pages !== 1 ) {
		$html .= '<div id="paging">' . "\n";
		$html .= '<nav>' . "\n";
		$html .= '<ul class="pagination">' . "\n";

		if( $paged > 1 ) {
			$html .= '<li><a href="' . get_pagenum_link( 1 ) . '"><i>&laquo;</i></a></li>' . "\n";
			$html .= '<li><a href="' . get_pagenum_link( $paged - 1 ) . '"><i>&lsaquo;</i></a></li>' . "\n";
		}
		else {
			$html .= '<li class="not-allow"><span><i>&laquo;</i></span></li>' . "\n";
			$html .= '<li class="not-allow"><span><i>&lsaquo;</i></span></li>' . "\n";
		}

		$paginate = array();
		for( $i = 1, $j = 1; $i <= $pages; ++$i ) {
			if( $pages !== 1 &&( !( $i >= $paged + $range + 1 || $i <= $paged - $range - 1 ) || $pages <= $showitems ) ) {
				if( $paged == $i ) {
					$paginate[] = '<li class="active"><span class="current">' . $i . '</span></li>';
				}
				else {
					$paginate[] = '<li><a href="' . get_pagenum_link( $i ) . '" class="inactive">' . $i . '</a></li>';
				}
				//++$j;
			}
		}

		$item_max = count( $paginate );
		foreach( $paginate as $key => $val ) {
			if(
				( $item_max >= $showitems && ( $key < 1 || $key >= $item_max - 1 ) ) ||
				( $item_max >= $showitems - 1 && $pages >= $item_max / 2 && $key < 1 ) ||
				( $item_max >= $showitems - 1 && $pages <= $item_max / 2 && $key >= $item_max - 1 )
			) {
				$html .= str_replace( '<li>', '<li class="bothends">', $val );
			}
			else {
				$html .= $val;
			}
		}

		if( $paged < $pages ) {
			$html .= '<li><a href="' . get_pagenum_link( $paged + 1 ) . '"><i>&rsaquo;</i></a></li>' . "\n";
			$html .= '<li><a href="' . get_pagenum_link( $pages ) . '"><i>&raquo;</i></a></li>' . "\n";
		}
		else {
			$html .= '<li class="not-allow"><span><i>&rsaquo;</i></span></li>' . "\n";
			$html .= '<li class="not-allow"><span><i>&raquo;</i></span></li>' . "\n";
		}
		$html .= '</ul>' . "\n";
		$html .= '</nav>' . "\n";
		$html .= '</div>' . "\n";
	}

	return $html;
}, 9, 1 );

/*---------------------------------------------------------------------------
 * 投稿・固定ページのページネーション (bootstrap version)
 *---------------------------------------------------------------------------*/
add_filter( 'thk_link_pages', function() {
	$wp_link_pages = wp_link_pages( array(
		'before'           => '',
		'after'            => '',
		'link_before'      => '',
		'link_after'       => '',
		'next_or_number'   => 'number',
		'separator'        => '|',
		'nextpagelink'     => '',
		'previouspagelink' => '',
		'pagelink'         => '%',
		'echo'             => 0
	) );

	$html = '';

	if( !empty( $wp_link_pages ) ) {
		$html .= '<div id="paging"><nav><ul class="pagination">';

		$link_pages = explode( '|', $wp_link_pages );

		$range = 3;				// 左右に表示する件数
		$showitems = ( $range * 2 ) + 1;	// アイテム数 (current 1件、左右3件、計7件表示)
		$paged = ( get_query_var('page') ) ? (int)get_query_var('page') : 1;	// 今いるページ番号
		$max_page = count( $link_pages );	// 最終ページ番号

		// 0から始まる添字を1から始まるように、添字振り直し(ついでに、前後空白削除)
		foreach( $link_pages as $key => $val) {
			$link_pages[$key + 1] = trim( $val );
		}
		unset( $link_pages[0] );

		if( $paged === 1 ) $range += 3;			// 1ページ目は右に + 3件
		elseif( $paged === 2 ) $range += 2;		// 2ページ目は右に + 2件
		elseif( $paged === 3 ) $range += 1;		// 3ページ目は右に + 1件
		elseif( $paged === $max_page ) $range += 3;	// 最終ページは左に + 3件
		elseif( $paged === $max_page - 1 ) $range += 2;	// 後ろから2ページ目は左に + 2件
		elseif( $paged === $max_page - 2 ) $range += 1;	// 後ろから3ページ目は左に + 1件

		$paginate = array();
		foreach( $link_pages as $key => $val ) {
			if( $max_page !== 1 &&( !( $key >= $paged + $range + 1 || $key <= $paged - $range - 1 ) || $max_page <= $showitems ) ) {
				$paginate[$key] = $val;
			}
		}

		$prv_page = $paged - 1;	// 前のページ番号
		$nxt_page = $paged + 1;	// 次のページ番号

		if( $paged > 1 ) {
			$html .= '<li>' . str_replace( '>' . 1 . '<', '><i>&laquo;</i><', $link_pages[1] ) . '</li>' . "\n";
			$html .= '<li>' . str_replace( '>' . $prv_page . '<', '><i>&lsaquo;</i><', $link_pages[$prv_page] ) . '</li>' . "\n";
		}
		else {
			$html .= '<li class="not-allow"><span><i>&laquo;</i></span></li>' . "\n";
			$html .= '<li class="not-allow"><span><i>&lsaquo;</i></span></li>' . "\n";
		}

		foreach( $paginate as $key => $val) {
			$bothends = '';
			if(
				( $max_page >= $showitems && ( $key < 1 || $key >= $max_page - 1 ) ) ||
				( $max_page >= $showitems - 1 && $paged >= $max_page / 2 && $key < 1 ) ||
				( $max_page >= $showitems - 1 && $paged <= $max_page / 2 && $key >= $max_page - 1 )
			) {
				$bothends = ' class="bothends"';
			}

			if( is_numeric( $val ) === false ) {
				$html .= '<li'. $bothends . '>' . $val . '</li>';
			}
			else {
				$html .= '<li class="active"><span class="current">' . $val . '</span></li>';
			}
		}

		if( $paged < $max_page ) {
			$html .= '<li>' . str_replace( '>' . $nxt_page . '<', '><i class="bold">&rsaquo;</i><', $link_pages[$nxt_page] ) . '</li>' . "\n";
			$html .= '<li>' . str_replace( '>' . $max_page . '<', '><i class="bold">&raquo;</i><', $link_pages[$max_page] ) . '</li>' . "\n";
		}
		else {
			$html .= '<li class="not-allow"><span><i>&rsaquo;</i></span></li>' . "\n";
			$html .= '<li class="not-allow"><span><i>&raquo;</i></span></li>' . "\n";
		}

		$html .= '</ul></nav></div>';

		return $html;
	}
}, 9, 1 );

/*---------------------------------------------------------------------------
 * コメント欄表示
 *---------------------------------------------------------------------------*/
add_filter( 'thk_comments', function() use( &$luxe ) {
	ob_start();
	comments_template();
	$comments = ob_get_clean();
	$comments = preg_replace( '/\n+\s*</', "\n".'<', $comments );
	$comments = preg_replace( '/>\s+\n/', '>'."\n", $comments );
	$comments = preg_replace( '/>\\s+?</', '><', $comments );

	if( isset( $luxe['amp'] ) ) {
		$comments = thk_amp_not_allowed_tag_replace( $comments );
	}

	return $comments;
}, 9, 1 );

/*---------------------------------------------------------------------------
 * SNS カウント数表示
 * id:	f = Facebook
 *	t = Pinterest
 *	l = LinkedIn
 *	h = Hatena
 *	p = Pocket
 *	r = Feedly
 *---------------------------------------------------------------------------*/
add_filter( 'thk_sns_count', function( $cnt = 0, $id = null, $url = null ) {
	if( ctype_digit( $cnt ) === true ) return number_format( $cnt );
	return $cnt;
}, 11, 3 );

/*---------------------------------------------------------------------------
 * オリジナルディスクリプション生成
 *---------------------------------------------------------------------------*/
if( function_exists('thk_remove_characters') === false ):
function thk_remove_characters( $content = '', $length = 100 ) {
	$ret = '';
	if( !empty( $content ) ) {
		$summary = strip_tags( $content );
		$summary = strip_shortcodes( $summary );
		if( strpos( $summary, '[' ) !== false ) {
			$summary = preg_replace( '/\[([^<>&\/\[\]\x00-\x20=]++)\]/', '', $summary); // shortcodes の登録タイミングによって strip_shortcodes では取り切れないので
		}
		$summary = thk_remove_url( $summary );
		$summary = preg_replace( '/\s+/', ' ', $summary );
		$summary = esc_html( mb_substr( $summary, 0, $length ) ); // 抜粋文字数
		$summary .= mb_strlen( $content ) >= $length ? '...' : '';
		$ret = trim( $summary );
	}
	return $ret;
}
endif;

add_filter( 'thk_create_description', function( $pid = null, $len = 100, $front_page_rewrite = true ) use( &$luxe ) {
	global $_is;

	$p = '';

	if( empty( $pid ) ) {
		global $post;
		$p = $post;
	}
	else {
		$p = get_post( $pid );
		if( $front_page_rewrite === true ) {
			$_is['singular'] = true;
			$_is['front_page'] = false;
		}
	}

	$desc = '';

	if( $_is['front_page'] === true || $_is['home'] === true ) { // フロントページの時
		$desc = isset( $luxe['top_description'] ) ? $luxe['top_description'] : THK_DESCRIPTION;
		$paged = ( get_query_var('paged') ) ? (int)get_query_var('paged') : 1;
		if( $paged > 1 ) {
			$desc = __( 'Pages', 'luxeritas' ) . ' ' . $paged . ' | ' . $desc;
		}
	}
	elseif( $_is['singular'] === true ) { // 個別記事と固定ページ
		$content = get_post_meta( $p->ID, 'change-description', true ); // カスタムフィールドがある場合

		if( empty( $content ) ) { // 抜粋がある場合
			$content = str_replace( array( "\r", "\n", "\t" ), '', $p->post_excerpt );

			// ページ分割してる場合は、2ページ目以降に No 付ける
			if( stripos( $p->post_content, '<!--nextpage-->' ) !== 0 ) {
				$paged = ( get_query_var('page') ) ? (int)get_query_var('page') : 1;

				if( $paged > 1 ) {
					$content .= ' | ' . 'NO:' . $paged;
				}
			}
		}

		if( empty( $content ) ) { // 抜粋がない場合
			// ページ分割してる場合は「そのページで表示されてる本文」の先頭 100 文字
			if( stripos( $p->post_content, '<!--nextpage-->' ) !== 0 ) {
				$contents = explode('<!--nextpage-->', $p->post_content );
				$paged = ( get_query_var('page') ) ? get_query_var('page') : 1;
				$content = trim( $contents[$paged - 1] );
			}
			else {
				$content = $p->post_content;
			}
		}
		// 抜粋もしくは、記事の最初の100文字を description にする
		$desc = thk_remove_characters( $content, $len );
	}
	elseif( $_is['category'] === true ) { // カテゴリページ
		$cat_info = get_category( get_query_var( 'cat' ), false );
		if( !empty( $cat_info->description ) ) {
			// カテゴリの「説明」が入力されてる場合
			$desc = $cat_info->description;
			$desc = thk_remove_characters( $desc );
		}
		else {
			$desc = get_bloginfo('name') . ' | ' . sprintf( __('%s Category', 'luxeritas' ), single_cat_title( '', false ) );
		}
		$paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
		if( $paged > 1 ) {
			$desc .= ' | ' . 'NO:' . $paged;
		}
	}
	elseif( $_is['tag'] === true ) { // タグページ
		$tag_info = get_term( get_query_var( 'tag_id' ), false );
		if( !empty( $tag_info->description ) ) {
			// カテゴリの「説明」が入力されてる場合
			$desc = $tag_info->description;
			$desc = thk_remove_characters( $desc );
		}
		else {
			$desc = get_bloginfo('name') . ' | ' . sprintf( __('%s Tag', 'luxeritas' ), single_tag_title( '', false ) );
		}
		$paged = ( get_query_var('paged') ) ? (int)get_query_var('paged') : 1;
		if( $paged > 1 ) {
			$desc .= ' | ' . 'NO:' . $paged;
		}
	}
	else { // その他のページ ( description の重複を避けるため NO を付ける)
		$id = get_the_ID();
		$desc = get_bloginfo('name') . ' | ' . THK_DESCRIPTION;
		if( !empty( $id) ) $desc .= ' | ' . 'NO:' . $id;
	}

	return $desc;
}, 9, 3 ); 
