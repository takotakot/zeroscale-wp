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
 * 検索機能拡張
 *---------------------------------------------------------------------------*/
if( function_exists( 'thk_search_extend' ) === false ):
function thk_search_extend() {
	global $luxe;

	thk_default_set();

	add_action( 'posts_request', function( $query ) {
		global $luxe, $wpdb;

		if( stripos( $query, 'SELECT ' ) === 0 && stripos( $query, ' DISTINCT' ) === false ) {
			// 検索文字列に「SELECT」という文言が入ってるとアカンので str_replace でなく substr_replace 使う
			$query = substr_replace( $query, 'SELECT DISTINCT ', 0, 7 );
		}
		$query = thk_search_query_replace( $query );

		$colmuns = array(
			$wpdb->posts . '.post_title',
			$wpdb->posts . '.post_content',
			$wpdb->posts . '.post_excerpt',
			$wpdb->comments . '.comment_author',
			$wpdb->comments . '.comment_content',
		);

		$charset = ( $wpdb->charset === 'utf8mb4' ) ? 'utf8mb4' : 'utf8';

		if( $wpdb->charset === 'utf8mb4' || $wpdb->charset === 'utf8') {
			if( $luxe['search_match_method'] === 'unicode' ) {
				if( $wpdb->collate !== $charset . '_unicode_ci' ) {
					foreach( $colmuns as $val ) {
						$query = str_replace( $val, "convert(" . $val . " using " . $charset . ") COLLATE " . $charset . "_unicode_ci", $query );
					}
				}
				$query = mb_convert_kana( $query, 'rnKV', 'UTF-8' );
			}
			elseif( $luxe['search_match_method'] === 'general' && $wpdb->collate !== $charset . '_general_ci' ) {
				foreach( $colmuns as $val ) {
					$query = str_replace( $val, "convert(" . $val . " using " . $charset . ") COLLATE " . $charset . "_general_ci", $query );
				}
			}
			else {
				$query = mb_convert_kana( $query, 'rnKV', 'UTF-8' );
			}
		}

		// ラッパーメニュー用の固定ページを含まないようにする
		if( stripos( $query, 'pages/wrapper-menu.php' ) === false ) {
			if( thk_wrap_menu_used() === true ) {
				// 最初の一個目の WHERE 文だけ置換したいので str_replace でなく preg_replace 使う
				$query = preg_replace( '/ WHERE /i', ' WHERE ' . $wpdb->posts . '.ID NOT IN (SELECT DISTINCT ' . $wpdb->postmeta . '.post_id FROM ' . $wpdb->postmeta . ' WHERE ' . $wpdb->postmeta . ".meta_value = 'pages/wrapper-menu.php' AND " . $wpdb->postmeta . '.post_id IS NOT NULL ) AND ', $query, 1 );
			}
		}
		return $query;
	});

	if( isset( $luxe['comment_search'] ) ) {
		add_action( 'posts_join', function( $query ) {
			global $wpdb;

			if( stripos( $query, ' LEFT JOIN ' . $wpdb->comments ) === false ) {
				$query .= " LEFT JOIN " . $wpdb->comments . " ON (comment_post_ID = ID AND comment_approved = '1') ";
			}
			return $query;
		} );

		add_action( 'posts_search', function( $query ) {
			global $wp_query, $wpdb;

			$query = thk_search_query_replace( $query );

			$phrase = str_replace( array( "\t", '　' ), ' ', $wp_query->query_vars['s'] );

			$keywords = explode( ' ', $phrase );
			$keywords = array_filter( $keywords, 'strlen' );

			$wild_card = thk_get_query_wild_card( $query );

			foreach( $keywords as $val ) {
				$query = str_replace(
					$wpdb->posts . ".post_content LIKE '" . $wild_card . esc_sql( $val ) . $wild_card . "')",

					$wpdb->posts . ".post_content LIKE '" . $wild_card . esc_sql( $val ) . $wild_card . "')"
					. "OR (" . $wpdb->comments . ".comment_content LIKE '" . $wild_card . esc_sql( $val ) . $wild_card . "')"
					. "OR (" . $wpdb->comments . ".comment_author LIKE '"  . $wild_card . esc_sql( $val ) . $wild_card . "')"
				, $query );
			}

			return $query;
		});
	}
}
endif;

/*---------------------------------------------------------------------------
 * ワイルドカードの置換
 *---------------------------------------------------------------------------*/
if( function_exists( 'thk_search_query_replace' ) === false ):
function thk_search_query_replace( $query ) {
	$wild_card = thk_get_query_wild_card( $query );

	$query = preg_replace( '/\{([0-9a-z]{16,}?)\}/', $wild_card, $query );

	return $query;
}
endif;

/*---------------------------------------------------------------------------
 * % ではないワイルドカードが使われてる場合にワイルドカードの文字列を返す
 *---------------------------------------------------------------------------*/
if( function_exists( 'thk_get_query_wild_card' ) === false ):
function thk_get_query_wild_card( $query ) {
	global $thk_search_query_wild_card;

	if( isset( $thk_search_query_wild_card ) ) {
		return $thk_search_query_wild_card;
	}
	else {
		preg_match( '/\{([0-9a-z]{16,}?)\}/', $query, $match );
		if( !isset( $match[0] ) ) {
			$thk_search_query_wild_card = '%';
		}
		else {
			$thk_search_query_wild_card = $match[0];
		}
	}

	// SQL 文を確認する際のデバッグ用
	// $thk_search_query_wild_card = '%';

	return $thk_search_query_wild_card;
}
endif;

/*---------------------------------------------------------------------------
 * 検索結果のハイライト用インラインスタイル
 *---------------------------------------------------------------------------*/
if( function_exists( 'thk_search_highlight_inline_style' ) === false ):
function thk_search_highlight_inline_style() {
	global $luxe;
	$style = '.highlight{';
	if( isset( $luxe['highlight_bold'] ) ) $style .= 'font-weight: bold;';
	if( isset( $luxe['highlight_oblique'] ) ) $style .= 'font-style: oblique;';
	if( isset( $luxe['highlight_bg'] ) && isset( $luxe['highlight_bg_color'] ) ) $style .= 'background:' . $luxe['highlight_bg_color'] . ';';
	if( isset( $luxe['highlight_radius'] ) && isset( $luxe['highlight_radius'] ) ) $style .= 'border-radius:' . $luxe['highlight_radius'] . 'px;';
	$style .= '}';

	return $style;
}
endif;


/*---------------------------------------------------------------------------
 * 検索用の抜粋
 *---------------------------------------------------------------------------*/
if( function_exists( 'thk_search_excerpt' ) === false ):
function thk_search_excerpt( $content = null ) {
	global $luxe, $post, $s;

	$f_len = 0;	// Full length
	$h_len = 0;	// Half length

	if( isset( $luxe['search_extract_length'] ) && is_numeric( $luxe['search_extract_length'] ) ) {
		$f_len = $luxe['search_extract_length'];
		$h_len = ceil( $f_len / 2 );
	}

	if( empty( $content ) )	{
		global $more;
		$more = 1;
		$content = apply_filters( 'the_content', get_the_content('') );
	}

	if( isset( $luxe['comment_search'] ) ) {
		$comments_query = new WP_Comment_Query;
		$comments = $comments_query->query( array( 'post_id' => $post->ID ) );
		foreach ( (array)$comments as $comment ) {
			if( (string)$comment->comment_approved === '1' ) {
				$content .= $comment->comment_content;
			}
		}
	}

	$content = preg_replace( '/<script.*<\/script>/', '', $content ) ;
	$content = strip_tags( $content );
	$content = strip_shortcodes( $content );
	$content = preg_replace( '/((?:https?|ftp|ed2k):\/\/[-_.!~*\'()a-zA-Z0-9%?@&=+,#$:;\/]+)/', '', $content );
	$content = mb_convert_kana( $content, 'rnKV', 'UTF-8' );

	$word = str_replace( array( "\t", '　' ), ' ', mb_convert_kana( trim( $s ), 'rnKV', 'UTF-8' ) );
	if( mb_stripos( $word, ' ' ) !== false ) {
		//$word = mb_substr( $word, 0, mb_stripos( $word, ' ' ) );
		$words = explode( ' ', $word );
		$word = $words[0];
	}
	$w_len = floor( mb_strlen( $word ) / 2 );	// Keyword length

	$hira = mb_convert_kana( $content, 'cH', 'UTF-8' );
	$kata = mb_convert_kana( $hira, 'CK', 'UTF-8' );

	$before_array = array(
		@mb_stripos( $hira, mb_convert_kana( $word, 'cH', 'UTF-8' ) ),
		@mb_stripos( $kata, mb_convert_kana( $word, 'CK', 'UTF-8' ) ),
		@mb_stripos( $content, $word )
	);
	rsort( $before_array );

	$before = isset( $before_array[0] ) ? $before_array[0] - $h_len : 0;
	if( $before < 0 ) $before = 0;

	$tmp = mb_substr( $content, $before, $f_len + $w_len );

	if( mb_strlen( $tmp ) + $w_len < $f_len ) {
		$content = mb_substr( $content, - $f_len );
	}
	else {
		$content = $tmp . ' ...';
	}

	if( $before > 0 ) $content = '... ' . $content;

	return $content;
}
endif;
