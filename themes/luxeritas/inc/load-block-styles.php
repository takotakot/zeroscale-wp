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

if( class_exists( 'thk_block_styles' ) === false ):
class thk_block_styles {
	private $_styles  = array( 'styles' => '' );
	private $_list_view_content = false;
	private $_aos_once = false;

	public function __construct() {
	}

	public function block_styles( $list_view_content = false ) {
		global $_is, $luxe, $post, $widget_concat;

		// 再利用ブロックウィジェットのブロックスタイル
		if( $_is['singular'] === false && $list_view_content === true ) {
			// アーカイブが全文表示の場合
			$ret = '';
			if( have_posts() === true ) {
				$ret = $this->create_block_styles( $widget_concat );
				while( have_posts() === true ) {
					the_post();
					// 投稿コンテンツ内に再利用ブロックが使われてる場合のブロックスタイル
					if ( has_blocks( $post->post_content ) && strpos( $post->post_content, '<!-- wp:block {"ref":' ) !== false ) {
						$blocks = do_blocks( $post->post_content );
						$ret = $this->create_block_styles( $blocks );
					}
					else {
						$ret = $this->create_block_styles( $post->post_content );
					}
				}
			}
			return $ret;
		}
		elseif( $_is['singular'] === true ) {
			// 投稿コンテンツ内に再利用ブロックが使われてる場合のブロックスタイル
			if ( has_blocks( $post->post_content ) && strpos( $post->post_content, '<!-- wp:block {"ref":' ) !== false ) {
				$blocks = do_blocks( $post->post_content );
				return $this->create_block_styles( $widget_concat . $blocks );
			}
			// 投稿コンテンツ内のブロックスタイル
			return $this->create_block_styles( $widget_concat . $post->post_content );
		}
		else {
			return $this->create_block_styles( $widget_concat );
		}
	}

	private function create_block_styles( $contents ) {
		global $luxe, $awesome;

		$css_dir = TPATH . DSEP . 'css' . DSEP;
		$styles_dir = TPATH . DSEP . 'styles' . DSEP;

		if( strpos( $contents, 'luxe-blocks' ) !== false ) {
			$this->_styles['styles'] .= "[class*=luxe-blocks] p {margin:1em 0}";
		}

		// color & background-color
		if( strpos( $contents, 'has-text-color' ) !== false || strpos( $contents, 'has-background' ) !== false ) {
			$color_palette = thk_get_block_editor_color_palette();

			foreach( $color_palette as $key => $val ) {
				if( strpos( $contents, 'has-' . $val['slug'] . '-color' ) !== false ) {
					$this->_styles['styles'] .= '.has-' . $val['slug'] . '-color{color:' . $val['color'] . '}';
				}
				if( strpos( $contents, 'has-' . $val['slug'] . '-background-color' ) !== false ) {
					$this->_styles['styles'] .= '.has-' . $val['slug'] . '-background-color{background-color:' . $val['color'] . '}';
				}
			}
		}

		// font-size
		for( $i = 10; 56 >= $i; ++$i ) {
			if( !isset( $this->_styles['has-' . $i . '-px-font-size'] ) ) {
				if( strpos( $contents, 'has-' . $i . '-px-font-size' ) !== false ) {
					$this->_styles['styles'] .= '.has-' . $i . '-px-font-size{font-size:' . $i . 'px}';
					$this->_styles['has-' . $i . '-px-font-size'] = true;
				}
			}
		}
		if( !isset( $this->_styles['has-small-font-size'] ) ) {
			if( strpos( $contents, 'has-small-font-size' ) !== false ) {
				$this->_styles['styles'] .= '.has-small-font-size{font-size:13px}';
				$this->_styles['has-small-font-size']  = true;
			}
		}
		if( !isset( $this->_styles['has-medium-font-size'] ) ) {
			if( strpos( $contents, 'has-medium-font-size' ) !== false ) {
				$this->_styles['styles'] .= '.has-medium-font-size{font-size:20px}';
				$this->_styles['has-medium-font-size'] = true;
			}
		}
		if( !isset( $this->_styles['has-large-font-size'] ) ) {
			if( strpos( $contents, 'has-large-font-size' ) !== false ) {
				$this->_styles['styles'] .= '.has-large-font-size{font-size:36px}';
				$this->_styles['has-large-font-size']  = true;
			}
		}
		if( !isset( $this->_styles['has-huge-font-size'] ) ) {
			if( strpos( $contents, 'has-huge-font-size' ) !== false ) {
				$this->_styles['styles'] .= '.has-huge-font-size{font-size:48px}';
				$this->_styles['has-huge-font-size']   = true;
			}
		}

		$luxe_blocks = [
			'<div class="wp-block-luxe-blocks-vertical"'		=> 'vertical.css',		// 縦書き
			'<span class="wp-block-luxe-blocks-topic-icon"'		=> 'topic.css',			// トピック
			'<div class="wp-block-luxe-blocks-notification-icon"'	=> 'notification.css',		// お知らせ（Notification）
			'<div class="wp-block-luxe-blocks-accordion"'		=> 'accordion.css',		// アコーディオン
			'<div class="wp-block-luxe-blocks-profile"'		=> 'profile.css',		// 紹介文（Profile）
			' luxe-overlay-'					=> 'block-overlay.css',		// オーバーレイ
		];

		if( isset( $luxe['amp'] ) || ( isset( $luxe['wp_block_library_load'] ) && $luxe['wp_block_library_load'] === 'none' ) ) {
			$luxe_blocks += [
				'<span class="luxe-hilight-'		=> 'inline-hilight.css',	// 蛍光ペン
				'<span class="luxe-dot-hilight-'	=> 'inline-hilight-dot.css',	// 蛍光ペン（ドット型）
			];

			if( isset( $luxe['amp'] ) ) {
				$luxe_blocks += [
					'class="wp-block-gallery '	=> 'wp-block-gallery-amp.css',	// ブロックエディタのギャラリー
				];
			}
		}

		foreach( $luxe_blocks as $key => $css ) {
			$global_key = 'blocks-' . str_replace( '.css', '', $css );
			if( !isset( $this->_styles[$global_key] ) ) {
				if( strpos( $contents, $key ) !== false ) {
					$this->_styles['styles'] .= thk_fgc( $styles_dir . $css );
					$this->_styles[$global_key] = true;
				}
			}
		}

		// 吹き出し
		if( strpos( $contents, '<div class="wp-block-luxe-blocks-balloon"' ) !== false ) {
			// 全共通
			$balloon_dir = $css_dir . 'balloon' . DSEP;
			if( !isset( $this->_styles['balloon-common'] ) ) {
				$this->_styles['styles'] .= thk_fgc( $balloon_dir . 'common.css' );
				$this->_styles['balloon-common'] = true;
			}
			// 通常共通
			if( !isset( $this->_styles['balloon-normal-common'] ) ) {
				if(
					strpos( $contents, '<div class="luxe-bl-lmain"' ) !== false ||
					strpos( $contents, '<div class="luxe-bl-rmain"' ) !== false
				) {
					$this->_styles['styles'] .= thk_fgc( $balloon_dir . 'normal-common.css' );
					$this->_styles['balloon-normal-common'] = true;
				}
			}
			// 左通常
			if( !isset( $this->_styles['balloon-normal-left'] ) ) {
				if( strpos( $contents, '<div class="luxe-bl-lbf"' ) !== false ) {
					$this->_styles['styles'] .= thk_fgc( $balloon_dir . 'normal-left.css' );
					$this->_styles['balloon-normal-left'] = true;
				}
			}
			// 右通常
			if( !isset( $this->_styles['balloon-normal-right'] ) ) {
				if( strpos( $contents, '<div class="luxe-bl-rbf"' ) !== false ) {
					$this->_styles['styles'] .= thk_fgc( $balloon_dir . 'normal-right.css' );
					$this->_styles['balloon-normal-right'] = true;
				}
			}
			// 考え共通
			if( !isset( $this->_styles['balloon-thought-common'] ) ) {
				if(
					strpos( $contents, '<div class="luxe-bl-ltk"' ) !== false ||
					strpos( $contents, '<div class="luxe-bl-rtk"' ) !== false
				) {
					$this->_styles['styles'] .= thk_fgc( $balloon_dir . 'thought-common.css' );
					$this->_styles['balloon-thought-common'] = true;
				}
			}
			// 左考え
			if( !isset( $this->_styles['balloon-thought-left'] ) ) {
				if( strpos( $contents, '<div class="luxe-bl-tk-lbf"' ) !== false ) {
					$this->_styles['styles'] .= thk_fgc( $balloon_dir . 'thought-left.css' );
					$this->_styles['balloon-thought-left'] = true;
				}
			}
			// 右考え
			if( !isset( $this->_styles['balloon-thought-right'] ) ) {
				if( strpos( $contents, '<div class="luxe-bl-tk-rbf"' ) !== false ) {
					$this->_styles['styles'] .= thk_fgc( $balloon_dir . 'thought-right.css' );
					$this->_styles['balloon-thought-right'] = true;
				}
			}
		}

		// アニメーションブロック (AOS スクリプト)
		if( !isset( $luxe['amp'] ) && strpos( $contents, 'wp-block-luxe-blocks-aos' ) !== false ) {
			if( $this->_aos_once === false ) {
				wp_enqueue_script( 'luxe-aos-script', TURI . '/js/aos/aos.js?v=2.3.4', array() );

				$aos =	'!function(e){var a=e.createElement("link");a.defer=!0,a.media="all",a.rel="stylesheet",a.href="' . TDEL . '/css/aos/aos.css",null!==e.getElementsByTagName("head")[0]&&e.getElementsByTagName("head")[0].appendChild(a)}(document);'
				.	'document.addEventListener("DOMContentLoaded",function(){"function"==typeof AOS.init?AOS.init():console.error("AOS not found")});';

				wp_add_inline_script( 'luxe-aos-script', $aos );

				// 一回だけ登録
				$this->_aos_once = true;
			}
		}

		// スクロールブロック
		if( strpos( $contents, 'wp-block-luxe-blocks-scroll-block' ) !== false ) {
			if( !isset( $this->_styles['scroll-block'] ) ) {
				if( isset( $luxe['amp'] ) ) {
					$this->_styles['styles'] .= thk_fgc( $styles_dir . 'scroll-block-amp.css' );
				}
				else {
					$this->_styles['styles'] .= thk_fgc( $styles_dir . 'scroll-block.css' );
				}
				$this->_styles['scroll-block'] = true;
			}

			// スクロールブロックで高さ・背景色・背景画像のスタイル指定があるもの
			if( strpos( $contents, 'luxe-scroll-block-css:' ) !== false ) {
				global $post;

				preg_match_all( '/<div[^>]+?class="[^>]+?luxe-scroll-block-css:[^>]+?>.*?<(figure|pre)[^>]+?>/ism', $contents, $scroll_block_css_array );

				foreach( array_unique( $scroll_block_css_array[0] ) as $scroll_block_css ) {
					$id_css = preg_replace( '/<div[^>]+?id="([^"]+?)"[^>]+?class="[^>]+?luxe-scroll-block-css\:([0-9a-f]+)[^>]+?>.*?<(figure|pre)[^>]+?>/ism', '$1#$2# $3', $scroll_block_css );

					if( stripos( $id_css, '#' ) !== false ) {
						$id_css_array = explode( '#', $id_css );
						if( count( $id_css_array ) >= 3 ) {
							$css = '#' . $id_css_array[0] . $id_css_array[2] . hex2bin( $id_css_array[1] );
							$this->_styles['styles'] .= $css;
							// 不要になった class 削除その1
							$post->post_content = str_replace( $id_css_array[1], '', $post->post_content );
						}
					}
				}
				// 不要になった class 削除その2
				$post->post_content = str_replace( ' luxe-scroll-block-css:', '', $post->post_content );
			}

			/* ドラッグスクロール用のスクリプトは thk_the_content() 内で wp_enqueue_script するようにした
			if( stripos( $contents, 'dragscroll-on' ) !== false ) {
				wp_enqueue_script( 'luxe-dragscroll', TURI . '/js/dragscroll.min.js', array(), false );
			}
			*/
		}

		return $this->_styles['styles'];
	}
}
endif;
