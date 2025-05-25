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

require_once( INC . 'post-meta-boxes.php');
require_once( INC . 'post-side-boxes.php' );

/*---------------------------------------------------------------------------
 * admin init
 *---------------------------------------------------------------------------*/
add_action( 'admin_init', function() {
	global $luxe, $_is;

	$path = TPATH !== SPATH ? SPATH : TPATH;

	// ブロックエディタ ・ TinyMCE 共通
	if( $_is['edit_posts'] === true ) {
		// エディタ CSS のタイムスタンプチェック
		if( file_exists( $path . DSEP . 'editor-style.min.css' ) === false || ( file_exists( $path . DSEP . 'editor-style.css' ) === true && file_exists( $path . DSEP . 'editor-style.min.css' ) === true ) ) {
			$etime = filemtime( $path . DSEP . 'editor-style.css' );
			if( file_exists( $path . DSEP . 'editor-style.min.css' ) === false || $etime !== filemtime( $path . DSEP . 'editor-style.min.css' ) ) {
				global $wp_filesystem;
				require_once( INC . 'compress.php' );
				thk_create_editor_style();
				$filesystem = new thk_filesystem();
				$filesystem->init_filesystem();
				$wp_filesystem->touch( $path . DSEP . 'editor-style.min.css', $etime );
			}
		}
	}

	// クラシックブロック用にブロックエディタと旧エディタ両方で読み込むけど、いずれ旧エディタのみにする予定
	require( INC . 'tinymce-before-init.php' );

	// ブロックエディタを無効化する設定になってた場合
	if( isset( $luxe['block_editor_off'] ) ) {
		add_filter( 'use_block_editor_for_post', '__return_false' );
	}
}, 9 );

/*---------------------------------------------------------------------------
 * block categories
 *---------------------------------------------------------------------------*/
// WP 5.8 以上
$hook = 'block_categories_all';
// WP 5.8 未満
if( version_compare( $GLOBALS['wp_version'], '5.8', '<' ) === true ) {
	$hook = 'block_categories';
}

add_filter( $hook, function( $categories, $post ) {
	return array_merge( $categories,
		[
			[
				'slug' => 'luxe-blocks',
				'title' => 'Luxeritas Blocks',
				//'icon'  => 'layout',
			]
		]
	);
}, 10, 2 );

/*---------------------------------------------------------------------------
 * enqueue block editor assets
 *---------------------------------------------------------------------------*/
add_action( 'enqueue_block_editor_assets', function() {
	global $luxe;

	$uri = TDEL !== SDEL ? SDEL : TDEL;

	wp_enqueue_style( 'editor-style', $uri . '/editor-style.min.css?v=' . $_SERVER['REQUEST_TIME'], [ 'wp-edit-blocks' ] );
	wp_enqueue_style( 'editor-style-gutenberg', TDEL . '/editor-style-gutenberg.min.css?v=' . $_SERVER['REQUEST_TIME'], [ 'wp-edit-blocks' ] );

	if( !isset( $luxe['block_debug'] ) ):
	if( !isset( $luxe['luxe_block_toolbar_off'] ) && !isset( $luxe['luxe_blocks_off'] ) ) {
		wp_enqueue_style( 'luxe-blocks-style', TDEL . '/styles/luxe-blocks-style.min.css?v=' . $_SERVER['REQUEST_TIME'], [ 'wp-edit-blocks'] );
		wp_enqueue_style( 'luxe-blocks-editor-style', TDEL . '/css/luxe-blocks-editor-style.css?v=' . $_SERVER['REQUEST_TIME'], [ 'wp-edit-blocks'] );

		$theme = THEME;
		if( TPATH !== SPATH ) {
			$curent = wp_get_theme();
			$theme = wp_get_theme( $curent->get('Template') )->get('Name');
		}

		$handles = array();
		if( !isset( $luxe['luxe_block_toolbar_off'] ) ) $handles[] = 'luxe-block-toolbar';
		if( !isset( $luxe['luxe_blocks_off'] ) ) $handles[] = 'luxe-blocks';

		foreach( $handles as $handle ) {
			wp_enqueue_script(
				$handle,
				TDEL . '/js/' . $handle . '.js?v=' . $_SERVER['REQUEST_TIME'],
				array( 'wp-blocks', 'wp-components', 'wp-element', 'wp-rich-text', 'wp-i18n', 'wp-editor', 'wp-compose', 'wp-autop' ),
				false
			);

			if( function_exists( 'wp_set_script_translations' ) === true ) {
				wp_set_script_translations( $handle, 'luxeritas', get_template_directory() . '/languages/admin' );
			}

			wp_localize_script( $handle, 'themeName', array( strtolower( $theme ) ) );
		}

		if( !isset( $luxe['luxe_block_toolbar_off'] ) ) {
			/* 登録されてるショートコードの一覧を blocks.js に渡す */
			$registed = get_pattern_list( 'shortcode', true, false );
			foreach( (array)$registed as $key => $value ) {
				$dec = htmlspecialchars_decode($key);
				if( !isset( $registed[$dec] ) ) {
					unset( $registed[$key] );
				}
				$registed[$dec] = @json_decode( $value, true );
			}
			foreach( (array)$registed as $key => $value ) {
				foreach( (array)$value as $k => $val ) {
					if( $k === 'label' ) {
						$registed[$key]['label'] = mb_strimwidth( $val, 0, 25 );
					}
				}
			}

			/* Luxeritas メニューのショートコード登録ではなく、functions.php 等で定義されてるショートコードがあったら、それもリストに加える */
			global $shortcode_tags;

			$start = false;

			foreach( (array)$shortcode_tags as $key => $val ) {
				if( $start === true && !isset( $registed[$key] ) ) {
					$registed[$key] = ['label'=>$key,'php'=>false,'close'=>false,'hide'=>false,'active'=>false];
				}
				elseif( $val === '__return_false' ) {
					$start = true;
				}
			}

			/* ショートコードが全く登録されてない場合 */
			if( empty( $registed ) ) {
				$registed = ['empty'=>
					['label'=>__('There is no active shortcode','luxeritas'),'php'=>false,'close'=>false,'hide'=>false,'active'=>false]
				];
			}

			asort( $registed );
			wp_localize_script( 'luxe-block-toolbar', 'luxeShortcodeList', $registed );

			/* 定型文の一覧を blocks.js に渡す */
			$registed = get_pattern_list( 'phrase', true, false );
			foreach( (array)$registed as $key => $value ) {
				$dec = htmlspecialchars_decode($key);
				if( !isset( $registed[$dec] ) ) {
					unset( $registed[$key] );
				}
				$registed[$dec] = @json_decode( $value, true );
			}
			foreach( (array)$registed as $key => $value ) {
				$registed[$key]['file'] = strlen( $key ) . '-' . md5( $key );
				foreach( (array)$value as $k => $val ) {
					if( $k === 'label' ) {
						$registed[$key]['label'] = mb_strimwidth( $val, 0, 25 );
					}
				}
			}

			if( empty( $registed ) ) {
				$registed = ['empty'=>
					['label'=>'','close'=>false,'file'=>'']
				];
			}

			asort( $registed );
			wp_localize_script( 'luxe-block-toolbar', 'luxePhraseList', $registed );

			/* Nonce */
			wp_localize_script( 'luxe-block-toolbar', 'luxePhraseNonce', array( wp_create_nonce( 'phrase_popup' ) ) );

			/* 親テーマの URL を block.js に渡す(絵文字の表を表示する際に必要) */
			wp_localize_script( 'luxe-block-toolbar', 'luxeThemeURL', array( get_template_directory_uri() ) );

			/* 絵文字の一覧を block.js に渡す*/
			ob_start();
			require( TPATH . DSEP . 'json' . DSEP . 'emoji-twitter.json' );
			$emoji_list = ob_get_clean();
			wp_localize_script( 'luxe-block-toolbar', 'luxeEmojiList', array( trim( $emoji_list ) ) );
		}

		if( !isset( $luxe['luxe_blocks_off'] ) ) {
			/* シンタックスハイライターの一覧を blocks.js に渡す */
			$highlighter = thk_syntax_highlighter_list();
			asort( $highlighter, SORT_NATURAL | SORT_FLAG_CASE );
			wp_localize_script( 'luxe-block-toolbar', 'luxeHighlighterList', $highlighter );

			/* アニメーションの一覧を blocks.js に渡す */
			$animated = [
				'fade',
				'fade-up',
				'fade-down',
				'fade-left',
				'fade-right',
				'fade-up-right',
				'fade-up-left',
				'fade-down-right',
				'fade-down-left',
				'flip-up',
				'flip-down',
				'flip-left',
				'flip-right',
				'slide-up',
				'slide-down',
				'slide-left',
				'slide-right',
				'zoom-in',
				'zoom-in-up',
				'zoom-in-down',
				'zoom-in-left',
				'zoom-in-right',
				'zoom-out',
				'zoom-out-up',
				'zoom-out-down',
				'zoom-out-left',
				'zoom-out-right',
			];
			wp_localize_script( 'luxe-blocks', 'luxeAnimeList', $animated );

			/* ロケール情報を blocks.js に渡す */
			$get_locale = get_locale();
			wp_localize_script( 'luxe-blocks', 'luxeLocale', array( $get_locale ) );
		}
	}
	endif;

	add_action( 'admin_footer', function() {
?>
<script>
window.addEventListener('DOMContentLoaded', function(){
	[].forEach.call(document.querySelectorAll("div.editor-styles-wrapper"), function( e ){ e.classList.add('post') });
});
</script>
<?php
	});
}, 11 );

/*---------------------------------------------------------------------------
 * admin head
 *---------------------------------------------------------------------------*/
add_action( 'admin_head', function() {
	global $luxe;

	// 以下３つの require はクラシックブロック用にブロックエディタと旧エディタ両方で読み込むけど、いずれ旧エディタのみにする予定
	require( INC . 'thk-post-style.php' );			// TinyMCE 用のスタイル
	require( INC . 'phrase-post.php' );			// 定型文の挿入ボタン
	require( INC . 'shortcode-post.php' );			// ショートコードの挿入ボタン
	if( isset( $luxe['blogcard_enable'] ) ) {
		require( INC . 'blogcard-post-func.php' );	// ブログカードの挿入ボタン
	}

	// 旧エディタを使用してる場合のみ
	if( _is_block_editor() === false ) {
		// 投稿画面のボタン挿入(クイックタグ)
		$teditor_buttons_d = get_theme_admin_mod( 'teditor_buttons_d' );
		if( !empty( $teditor_buttons_d ) ) {
			$luxe['teditor_buttons_d'] = $teditor_buttons_d;
		}

		require( INC . 'quicktags.php' );
	}
}, 100 );

/*---------------------------------------------------------------------------
 * タブの入力ができるようにする
 *---------------------------------------------------------------------------*/
//add_action( 'admin_footer', function() {
add_action( 'admin_print_footer_scripts', function() {
	//旧エディタを使用してる場合のみ
	if( _is_block_editor() === false ) {
?>
<script>
var textareas = document.getElementsByTagName('textarea');
var count = textareas.length;
for( var i = 0; i < count; i++ ) {
	textareas[i].onkeydown = function(e){
		if( e.keyCode === 9 || e.which === 9 ) {
			e.preventDefault();
			var s = this.selectionStart;
			this.value = this.value.substring( 0, this.selectionStart ) + "\t" + this.value.substring( this.selectionEnd );
			this.selectionEnd = s + 1;
		}
	}
}
</script>
<?php
	}
}, 99 );

/*---------------------------------------------------------------------------
 * ブロックエディタのカラーパレットとフォトサイズ
 *---------------------------------------------------------------------------*/
add_action( 'after_setup_theme', function() {
	global $luxe;

	/* カラーパレット */
	$color_palette = thk_get_block_editor_color_palette();
	add_theme_support( 'editor-color-palette', array_values( $color_palette ) );

	/* フォントサイズ */
	$normal_font_size = isset( $luxe['font_size_post'] ) ? (int)$luxe['font_size_post'] : 16;
	$font_sizes = array();

	for( $i = 10; 56 >= $i; ++$i ) {
		if( $i === 13 ) {
			$font_sizes[] = array(
				'name'		=> $i . 'px ( ' . __( 'Small', 'luxeritas' ) . ' )',
				'shortName'	=> $i . 'px',
				'size'		=> $i,
				'slug'		=> 'small',
			);
		}
		elseif( $i === 20 ) {
			$font_sizes[] = array(
				'name'		=> $i . 'px ( ' . __( 'Medium', 'luxeritas' ) . ' )',
				'shortName'	=> $i . 'px',
				'size'		=> $i,
				'slug'		=> 'medium',
			);
		}
		elseif( $i === 36 ) {
			$font_sizes[] = array(
				'name'		=> $i . 'px ( ' . __( 'Large', 'luxeritas' ) . ' )',
				'shortName'	=> $i . 'px',
				'size'		=> $i,
				'slug'		=> 'large',
			);
		}
		elseif( $i === 48 ) {
			$font_sizes[] = array(
				'name'		=> $i . 'px ( ' . __( 'Huge', 'luxeritas' ) . ' )',
				'shortName'	=> $i . 'px',
				'size'		=> $i,
				'slug'		=> 'huge',
			);
		}
		elseif( $i === $normal_font_size ) {
			$font_sizes[] = array(
				'name'		=> $i . 'px ( ' . __( 'Normal', 'luxeritas' ) . ' )',
				'shortName'	=> $i . 'px',
				'size'		=> $i,
				'slug'		=> 'normal',
			);
		}
		else {
			$font_sizes[] = array(
				'name'		=> $i . 'px',
				'shortName'	=> $i . 'px',
				'size'		=> $i,
				'slug'		=> $i . 'px',
			);
		}
	}
	add_theme_support( 'editor-font-sizes', $font_sizes );
});
