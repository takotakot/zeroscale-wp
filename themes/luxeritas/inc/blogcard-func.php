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

$blogcard = new THK_Blogcard();
add_filter( 'thk_content', array( &$blogcard, 'thk_replace_blogcard' ), 536870912, 1 );

class THK_Blogcard {
	private $_cache_dir	= null;
	private $_cache_url	= null;
	private $_filesystem	= null;

	public function __construct() {
		$wp_upload_dir = wp_upload_dir();
		$this->_upload_dir = $wp_upload_dir['basedir'] . DSEP;
		$this->_cache_dir  = $wp_upload_dir['basedir'] . DSEP . 'luxe-blogcard' . DSEP;
		$this->_cache_url  = $wp_upload_dir['baseurl'] . '/luxe-blogcard/';

/*
		foreach( (array)glob( $this->_cache_dir . '*' ) as $val ) {
			if( is_file( $val ) === true ) {
				$wp_filesystem->delete( $val );
			}
		}
*/
	}

	// 投稿の書き換え
	public function thk_replace_blogcard( $content ) {
		global $luxe;

		// URL 直書きをブログカード化する場合の処理
		if( isset( $luxe['blogcard_embedded'] ) ) {
			// 相手サイトが Embed 対応
			if( stripos( $content, 'blockquote' ) !== false && stripos( $content, 'wp-embedded-content' ) !== false ) {
				preg_match_all( '/<blockquote.+?wp-embedded-content.+?href=\"(.+?)\".+?<\/blockquote>/im', $content, $m );

				if( isset( $m[0] ) && isset( $m[1] ) ) {
					foreach( $m[0] as $key => $val ) {
						if( isset( $m[1][$key] ) ) {
							// 内部 URL
							if( stripos( $m[1][$key], THK_HOME_URL ) !== false ) {
								$replace = '<a href="' . $m[1][$key] . '" data-blogcard="1">' . $m[1][$key] . '</a>';
							}
							// 外部 URL
							else {
								$replace = '<a href="' . $m[1][$key] . '" target="_blank" rel="noopener external" data-blogcard="1">' . $m[1][$key] . '</a>';
							}
							// AMP
							if( isset( $luxe['amp'] ) ) $replace = '<p>' . $replace . '</p>';
							// 置換
							$content = str_replace( $val, $replace, $content );
						}
					}
				}
				unset( $m );

				// アイフレーム消す
				$content = preg_replace('/<(amp\-)*i'.'frame.+?wp-embedded-content.+?<\/(amp\-)*i'.'frame>/ism', '', $content );
			}

			// 相手サイトが Embed 非対応
			preg_match_all( '/^(<p>)?(\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|])?(<br ?\/?>)?(<\/p>)?\n/im', $content, $m );

			if( isset( $m[0] ) && isset( $m[2] ) ) {
				foreach( $m[0] as $key => $val ) {
					if( !empty( $m[2][$key] ) ) {
						$content = str_replace( $val, '<p><a href="' . $m[2][$key] . '" target="_blank" rel="noopener external" data-blogcard="1">' . $m[2][$key] . "</a></p>", $content );
					}
				}
			}
			unset( $m );
		}

		if( stripos( $content, 'data-blogcard' ) === false ) return $content;

		$links = $this->thk_get_blogcard_links( $content );

		foreach( array_unique( $links[0] ) as $link ) {
			if( substr_count( $link, '<a ' ) > 1 ) continue;

			$url_items = $this->thk_create_blogcard_cache_path( $link );
			$url     = $url_items[0];	// 整形済み
			$raw_url = $url_items[1];	// 元 URL
			$url_md5 = $url_items[2];	// 認識用 md5

			$url_strimwidth = $url;
			if( mb_strwidth( $url_strimwidth ) > 70 ) {
				$url_strimwidth = mb_strimwidth( $url_strimwidth, 0, 70 );
				if( stripos( $url_strimwidth, '&' ) !== false ) {
					$url_strimwidth = str_replace( substr( $url_strimwidth, strripos( $url_strimwidth, '&' ) ), '', $url_strimwidth );
				}
				$url_strimwidth .= '...';
			}

			$preg_pattern = '/<a [^>]*?href=[\'\"]+?' . preg_quote( $raw_url, '/' ) . '[\'\"]+?[^>]+?data-blogcard[^>]+?>[^<]*?<\/a>/im';

			unset( $cat_obj ); // カテゴリオブジェクト初期化
			$pid = url_to_postid( $url );

			$rpu = rtrim( pdel( $url ), '/' );
			$rpt = rtrim( pdel( THK_HOME_URL ), '/' );

			if( $pid === 0 && stripos( $rpu, $rpt, 0 ) !== false ) {
				// カテゴリの場合
				$cat_obj = get_category_by_path( $url, false );

				// 内部リンクで固定ページのパーマリンクがぶっ壊れてる？場合の例外処理
				if( !isset( $cat_obj ) ) {
					$path = str_replace( THK_HOME_URL, '', $url );
					$page = get_page_by_path( $path );
					if( isset( $page->guid ) ) {
						$pid = url_to_postid( $page->guid );
					}
				}
			}

			if( $pid === 0 && !isset( $cat_obj ) && $rpu !== $rpt ) {
				$cache_file = $this->_cache_dir . $url_md5[0] . DSEP . $url_md5;

				// キャッシュ有効期限のチェックと登録・削除
				$expire = $this->thk_transient_blogcard_cache( $url_md5 );

				if( file_exists( $cache_file ) === false ) {
					if( isset( $luxe['amp'] ) ) {
						// キャッシュがない初回に表示されたのが AMP だった場合は直接取得（このパターンは、ほぼあり得ないけど）
						$this->thk_create_blogcard_shutdown( $content );
					}
					else {
						$luxe['bc_first'] = true;
						$this->thk_regist_global( $url, $url_md5, $link );

						// キャッシュがない初回は Javascript で置換して表示する（footer.php の最後）
						add_action( 'thk_create_blogcard', array( &$this, 'thk_create_blogcard' ), 10, 2 );

						$replace = '<div id="bc_' . $url_md5 . '" class="blogcard"><a href="' . esc_url( $url ) . '" class="blogcard-href nofloatbox"><p>' . __( 'Creating a cache ...', 'luxeritas' ) . '</p><div class="bc-progress"></div><p>' . $url_strimwidth . '</p></a></div>';
						$content = preg_replace( $preg_pattern, $replace, $content );
						continue;
					}
				}
				// キャッシュ期限切れの時は再取得
				if( $expire === true ) {
					if( isset( $luxe['amp'] ) ) {
						// AMP の時は、shutdown 時に再取得
						//add_filter( 'shutdown', function() use( $content ) {
						//	$this->thk_create_blogcard_shutdown( $content );
						//}, 32767 );
					}
					else{
						// AMP でなければ Javascript で置換（置換前は古いキャッシュをそのまま表示）
						$this->thk_regist_global( $url, $url_md5, $link );
						add_action( 'thk_create_blogcard', array( &$this, 'thk_create_blogcard' ), 10, 2 );
					}
				}

				$caches  = $this->thk_get_blogcard_cache( $cache_file, $link, $url_md5 );
				$code    = (int)$caches[0];
				$replace = $caches[1];

				// ステータスコードが「200:OK」ではないキャッシュは再取得してみる（AMP の時は放置）。
				if( $code !== 200 && !isset( $luxe['amp'] ) ) {
					$this->thk_regist_global( $url, $url_md5, $link );

					add_action( 'thk_create_blogcard', array( &$this, 'thk_create_blogcard' ), 10, 2 );

					$content = preg_replace( $preg_pattern, $replace, $content );
					continue;
				}
			}
			else {	//　内部リンクをキャッシュしない場合（トップページ・カテゴリページ・投稿・固定ページ）
				$title   = '';
				$desc    = '';
				$enc_url = '';
				$uri = TURI === SURI ? TURI : SURI;
				$img_url = $uri . '/images/og.png';
				if( get_header_image() === true ){
					$img_url = get_header_image();
				}
				elseif( isset( $luxe['og_img'] ) ) {
					$img_url = $luxe['og_img'];
				}
				$sep = ( $luxe['title_sep'] === 'hyphen' ) ? ' - ' : ' | ';

				// 一覧型トップページの場合
				if( $rpu === $rpt ) {
					if( isset( $luxe['title_top_list'] ) && $luxe['title_top_list'] === 'site' ) {
						$title = get_bloginfo('name');
					}
					else {
						$title = get_bloginfo('name') . $sep . get_bloginfo('description');
					}
					$desc = isset( $luxe['top_description'] ) ? $luxe['top_description'] : THK_DESCRIPTION;
					$enc_url = puny_decode( thk_convert( esc_url( THK_HOME_URL ) ) );
				}
				elseif( isset( $cat_obj->cat_ID ) && isset( $cat_obj->slug ) ) { // カテゴリページ
					$cat_info = get_category( $cat_obj->cat_ID, false );

					if( isset( $luxe['title_other'] ) ) {
						if( $luxe['title_other'] === 'title' ) {
							$cat_info->name;
						}
						else {
							$title = $cat_info->name . $sep . get_bloginfo('name');
						}
					}

					if( !empty( $cat_info->description ) ) {
						// カテゴリの「説明」が入力されてる場合
						$desc = $cat_info->description;
						$desc = thk_remove_characters( $desc );
					}
					else {
						$desc = get_bloginfo('name') . ' | ' . sprintf( __('%s Category', 'luxeritas' ), $cat_info->name );
					}

					$enc_url = puny_decode( thk_convert( esc_url( $url ) ) );
				}
				else {	// 投稿・固定ページ
					$title = get_the_title( $pid );
					$title = mb_strimwidth( $title, 0, 80 );

					//$desc = apply_filters( 'thk_create_description', $pid, 100, false );
					$desc = apply_filters( 'thk_create_description', $pid, 100 );
					$desc = mb_strimwidth( $desc, 0, 100, ' ...' );

					$enc_url = puny_decode( thk_convert( esc_url( $url ) ) );

					// アイキャッチ画像
					$attachment_elements = get_the_post_thumbnail( $pid, 'thumb100' );

					if( stripos( $attachment_elements, '-100x100.' ) === false ) {
						$attachment_elements = get_the_post_thumbnail( $pid, 'thumbnail' );
					}

					if( empty( $attachment_elements ) ) {
						// アイキャッチが無かったら、コンテンツの最初の画像を取得
						$p = get_post( $pid );
						if( stripos( $p->post_content, 'data-src=' ) !== false && stripos( $p->post_content, 'lazy' ) !== false ) {
							$output = preg_match_all( '/<(amp\-)*?img.+data\-src=[\'"]([^\'"]+)[\'"].*>/i', $p->post_content, $matches );
						}
						else {
							$output = preg_match_all( '/<(amp\-)*?img.+src=[\'"]([^\'"]+)[\'"].*>/i', $p->post_content, $matches );
						}
						$img_org = isset( $matches[2][0] ) ? $matches[2][0] : '';

						if( !empty( $img_org ) ) {
							$img_url = wp_get_attachment_thumb_url( thk_get_image_id_from_url( $img_org ) );
							if( $img_url === false ) {
								$img_url = $img_org;
							}
						}
						else {
							$img_url = TURI . '/images/no-img-100x100.png';
						}
					}
					else {
						// アイキャッチがある場合
						if( !isset( $luxe['amp'] ) && stripos( $attachment_elements, 'data-src=' ) !== false && stripos( $attachment_elements, 'lazy' ) !== false ) {
							$img_url = preg_replace( '/.*?<(amp\-)*?img.+data\-src=[\'"]([^\'"]+)[\'"].*/im', '$2', $attachment_elements );
						}
						else {
							$img_url = preg_replace( '/.*?<(amp\-)*?img.+src=[\'"]([^\'"]+)[\'"].*/im', '$2', $attachment_elements );
						}
					}
				}

				if( mb_strwidth( $enc_url ) > 60 ) {
					$enc_url = mb_strimwidth( $enc_url, 0, 60 );
					if( stripos( $enc_url, '&' ) !== false ) {
						$enc_url = str_replace( substr( $enc_url, strripos( $enc_url, '&' ) ), '', $enc_url );
					}
					$enc_url .= '...';
				}

				// アイコン画像
				$theme_url  = SURI;
				$theme_path = SPATH;

				if( SPATH === TPATH ) {
					$theme_url  = TURI;
					$theme_path = TPATH;
				}
				$ico_file = $theme_path . DSEP . 'images' . DSEP . 'fav' . 'icon-min.png';
				$ico_url = $theme_url . '/images/fav' . 'icon-min.png';

				if( file_exists( $ico_file ) === false ) {
					thk_create_icon();

					if( file_exists( $ico_file ) === false ) {
						$ico_url = $theme_url . '/images/fav' . 'icon.ico';
					}
				}

				$target = ( stripos( $link, 'target=' ) !== false && stripos( $link, 'blank' ) !== false ) ? ' target="_blank" rel="noopener external"' : '';

				if( !isset( $luxe['amp'] ) && isset( $luxe['lazyload_type'] ) && $luxe['lazyload_type'] === 'intersection' && isset( $luxe['lazyload_thumbs'] ) ) {
					$replace = '<div id="bc_' . $url_md5 . '" class="blogcard"><a href="' . esc_url( $url ) . '"' . $target . ' class="blogcard-href nofloatbox"><img src="' . $luxe['trans_image'] . '" data-src="' . esc_url( $img_url ) . '" alt="' . $title . '" width="100" height="100" class="blogcard-img lazy" /><p class="blog-card-title">' . $title . '</p><p class="blog-card-desc">' . $desc . '</p><p class="blogcard-link"><img src="' . $ico_url . '" alt="" width="18" height="18" class="blogcard-icon" />&nbsp;' . $enc_url . '</p></a></div>';
				}
				else {
					$replace = '<div id="bc_' . $url_md5 . '" class="blogcard"><a href="' . esc_url( $url ) . '"' . $target . ' class="blogcard-href nofloatbox"><img src="' . esc_url( $img_url ) . '" alt="' . $title . '" width="100" height="100" class="blogcard-img" /><p class="blog-card-title">' . $title . '</p><p class="blog-card-desc">' . $desc . '</p><p class="blogcard-link"><img src="' . $ico_url . '" alt="" width="18" height="18" class="blogcard-icon" />&nbsp;' . $enc_url . '</p></a></div>';
				}
				$content = preg_replace( $preg_pattern, $replace, $content );
				continue;
			}

			// AMP 用タグ置換
			if( isset( $luxe['amp'] ) ) {
				$replace = str_replace( '<img', '<amp-img', $replace  );
				$replace = str_replace( ' />', '></amp-img>', $replace  );
			}

			$content = preg_replace( $preg_pattern, $replace, $content );
		}
		return $content;
	}

	// キャッシュ期限切れの時、shutdown 処理で再取得する add_action 登録用の関数
	public function thk_create_blogcard_shutdown() {
		$content = apply_filters( 'the_content', get_the_content('') );
		$links = $this->thk_get_blogcard_links( $content );

		foreach( array_unique( $links[0] ) as $link ) {
			if( substr_count( $link, '<a ' ) > 1 ) continue;

			$url_items = $this->thk_create_blogcard_cache_path( $link );
			$url     = $url_items[0];	// 整形済み
			$raw_url = $url_items[1];	// 元 URL
			$url_md5 = $url_items[2];	// 認識用 md5

			$this->thk_create_blogcard( $url, $url_md5 );
		}
	}

	// ブログカードのキャッシュ取得
	public function thk_get_blogcard_cache( $cache_file, $link, $url_md5, $no_lazy = false ) {
		global $luxe, $wp_filesystem;

		$cache = '';
		$url = '';
		$enc_url = '';
		$img_file = '';
		$img_url  = '';

		$this->_filesystem();
		$this->_filesystem = new thk_filesystem();

		if( $this->_filesystem->init_filesystem( site_url() ) !== false ) {
			$cache = $wp_filesystem->get_contents( $cache_file );
		}

		$items = explode( "\n", $cache, 6 );

		if( !empty( $items[1] ) ) {
			require_once( INC . 'punycode.php' );

			$url = $items[1];
			$enc_url = puny_decode( thk_convert( esc_url( $items[1] ) ) );
			if( mb_strwidth( $enc_url ) > 60 ) {
				$enc_url = mb_strimwidth( $enc_url, 0, 60 );
				if( stripos( $enc_url, '&' ) !== false ) {
					$enc_url = str_replace( substr( $enc_url, strripos( $enc_url, '&' ) ), '', $enc_url );
				}
				$enc_url .= '...';
			}
		}
		if( !empty( $items[2] ) ) {
			$img_file = $this->_cache_dir . $items[2];
			$img_url  = $this->_cache_url . $items[2];
		}
		$ico_url  = !empty( $items[3] ) ? $this->_cache_url . $items[3] : TURI . '/images/unknown-icon.png';
		$title    = !empty( $items[4] ) ? $items[4] : 'Unknown Title';
		$desc     = !empty( $items[5] ) ? $items[5] : 'Unknown ...';

		$target = ( stripos( $link, 'target=' ) !== false && stripos( $link, 'blank' ) !== false ) ? ' target="_blank" rel="noopener external"' : '';

		if( empty( $items[2] ) || ( file_exists( $img_file ) === true && filesize( $img_file ) <= 0 ) ) {
			$img_url = TURI . '/images/no-img-100x100.png';

			/* screenshot は Google API で取得するけど、今後使えなくなった時には、以下の API 群を使うかも */
			//$img_url = 'http://capture.heartrails.com/100x100?' . $url;
			//$img_url = 'https://s.wordpress.com/mshots/v1/' . rawurlencode( $url ) . '?w=100';
			//$img_url = 'https://api.thumbalizr.com/?url=' . rawurlencode( $url ) . '&width=100';
			//$img_url = 'https://www.googleapis.com/pagespeedonline/v1/runPagespeed?screenshot=true&strategy=mobile&url=' . rawurlencode( $url );

			// no-img の時はキャッシュの再取得を試みる(スクリーンショットすら取れてない場合)
			if( isset( $luxe['amp'] ) ) {
				// AMP の時は、shutdown 時に再取得
				//add_filter( 'shutdown', function() {
				//	$this->thk_create_blogcard_shutdown();
				//}, 32767 );
			}
			else{
				// AMP でなければ jQuery で置換（置換前は古いキャッシュをそのまま表示）
				$this->thk_regist_global( $url, $url_md5, $link );
				add_action( 'thk_create_blogcard', array( &$this, 'thk_create_blogcard' ), 10, 2 );
			}
		}

		if( !isset( $luxe['amp'] ) && isset( $luxe['lazyload_type'] ) && $luxe['lazyload_type'] === 'intersection' && isset( $luxe['lazyload_thumbs'] ) && $no_lazy === false ) {
			$ret = array( $items[0], '<div id="bc_' . $url_md5 . '" class="blogcard"><a href="' . esc_url( $url ) . '"' . $target . ' class="blogcard-href nofloatbox"><img src="' . $luxe['trans_image'] . '" data-src="' . esc_url( $img_url ) . '" alt="' . $title . '" width="100" height="100" class="blogcard-img lazy" /><p class="blog-card-title">' . $title . '</p><p class="blog-card-desc">' . $desc . '</p><p class="blogcard-link"><img src="' . $ico_url . '" alt="" width="18" height="18" class="blogcard-icon" />&nbsp;' . $enc_url . '</p></a></div>' );
		}
		else {
			$ret = array( $items[0], '<div id="bc_' . $url_md5 . '" class="blogcard"><a href="' . esc_url( $url ) . '"' . $target . ' class="blogcard-href nofloatbox"><img src="' . esc_url( $img_url ) . '" alt="' . $title . '" width="100" height="100" class="blogcard-img" /><p class="blog-card-title">' . $title . '</p><p class="blog-card-desc">' . $desc . '</p><p class="blogcard-link"><img src="' . $ico_url . '" alt="" width="18" height="18" class="blogcard-icon" />&nbsp;' . $enc_url . '</p></a></div>' );
		}
		return $ret;
	}

	// キャッシュが無い・ステータスコードが200じゃない場合のグローバル変数登録 (footer.php の最後で jQuery 用として使う)
	public function thk_regist_global( $url, $url_md5, $link ) {
		global $luxe;
		if( !isset( $luxe['bc_url'] ) || ( isset( $luxe['bc_url'] ) && in_array( $url, $luxe['bc_url'], true ) === false ) ) {

			$url = puny_encode( thk_convert( esc_url( $url ) ) );

			//if( filter_var( $url, FILTER_VALIDATE_URL ) !== false ) {
			if( stripos( $url, 'https://' ) === 0 || stripos( $url, 'http://' ) === 0 ) {
				$luxe['bc_url'][] = $url;
				$luxe['bc_md5'][] = $url_md5;
				$luxe['bc_lnk'][] = $link;
			}
		}
	}

	// ブログカードのキャッシュ作成
	public function thk_create_blogcard( $url, $url_md5 ) {
		global $wp_filesystem;

		$this->_filesystem();
		$this->_filesystem = new thk_filesystem();
		if( $this->_filesystem->init_filesystem() === false ) return false;

		$save_dir   = $this->_cache_dir . $url_md5[0] . DSEP;
		$save_file  = $save_dir . $url_md5;
		$code       = 0;	// レスポンスコード
		$title      = '';	// タイトル
		$desc       = '';	// 内容
		$img_file   = '';	// 画像ファイルへの PATH
		$img        = '';	// 画像(バイナリ)
		/* $amazon_img = '';	// Amazon 商品画像(バイナリ) */
		$img_url    = '';	// 画像 URL
		$img_urls   = array();	// 画像として使える候補を格納する配列
		$img_ok     = false;	// 画像として利用できるかどうかの判定(Mime Type が判別できて 100x100 サイズ以上)
		$normal_img = false;	// 画像が正常でリサイズ可能かどうかの判定
		$sshot_flag = false;	// スクリーンショットかどうか

		$this->_mkdir( $save_dir );

		$url = thk_decode( $url );

		// 内部リンク（キャッシュしないようにしたので削除）
		/*
		if( stripos( pdel( $url ), pdel( rtrim( THK_HOME_URL, '/' ) ) ) === 0 ) {
			$code = 200;

			$pid = url_to_postid( $url );

			$title = get_the_title( $pid );
			$title = mb_strimwidth( $title, 0, 80 );

			$desc = apply_filters( 'thk_create_description', $pid );
			$desc = mb_strimwidth( $desc, 0, 100, ' ...' );

			// アイキャッチ画像取得
			$img_meta = wp_get_attachment_metadata( get_post_thumbnail_id( $pid ) );
			if( !isset( $img_meta['file'] ) ) {
				// アイキャッチが無かったら、コンテンツの最初の画像を取得
				$p = get_post( $pid );
				$output = preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $p->post_content, $matches );
				$first_img_url = isset( $matches[1][0] ) ? $matches[1][0] : '';
				$img_meta = array();
				if( !empty( $first_img_url ) ) {
					$img_id = thk_get_image_id_from_url( $first_img_url );
					$img_meta = wp_get_attachment_metadata( $img_id );
				}
			}
			if( isset( $img_meta['file'] ) ) {
				// 画像が存在したら $img に格納
				$img_path = $this->_upload_dir . $img_meta['file'];

				$img = $wp_filesystem->get_contents( $img_path );

				$img_file = $this->_cache_dir . $url_md5[0] . DSEP . $url_md5 . '.png';
				if( $this->_filesystem->file_save( $img_file, $img ) === false ) return false;
				$img_ok = true;
			}
		}
		// 外部リンク
		else {
		*/
			$html = thk_remote_request( $url );

			if( is_array( $html ) === true ) {
				// レスポンスが「200：OK」以外だった場合
				if( file_exists( $save_file ) === true ) return false;

				$code  = $html[0];
				$title = $html[1];
				$desc  = $html[0] . ' : ' . $html[1];
			}
			elseif( $html === false ) {
				// レスポンスそのものが無かった場合 (・Webサーバーが存在しない・通信すらしてない・DNSエラー等)
				if( file_exists( $save_file ) === true ) return false;

				$title = 'No response';
				$desc  = 'There was no response from the server.';
			}
			else {
				// 200 が返ってきた
				$code = 200;

				/* デバッグ用に残しとく
				$_debug = $this->_cache_dir . $url_md5 . '.txt';
				if( $this->_filesystem->file_save( $_debug, $html ) === false ) return false;
				*/

				$html  = thk_convert( $html );
				$title = '';
				$desc  = '';

				// YouTube 動画
				if( stripos( $url, '.youtube.com' ) !== false || stripos( $url, 'youtu.be' ) !== false || stripos( $url, 'y2u.be' ) !== false ) {
					if( stripos( $url, 'watch?v=' ) !== false ) {
						$youtube_uri = explode( 'watch?v=', $url );
						if( isset( $youtube_uri[1] ) ) $youtube_uri = $youtube_uri[1];

						if( isset( $youtube_uri ) ) {
							if( strpos( $youtube_uri, '&' ) !== false ) {
								$youtube_uri = strstr( $youtube_uri, '&', true );
							}
							// 以下どっちか
							$img_urls[] = 'https://img.youtube.com/vi/' . $youtube_uri . '/sddefault.jpg';
							//$img_urls[] = 'https://i.ytimg.com/vi/' . $youtube_uri . '/maxresdefault.jpg';
						}
					}
					$title = preg_replace( '/.*<\s*meta\s*[^>]+?title[^>]+?content=[\'\"]*(.+?)[\'\"\/^>]*>.*/ism', "$1", $html );
					$title = wp_strip_all_tags( $title, true );

					$desc = preg_replace( '/.*<\s*meta\s*[^>]+?description[^>]+?content=[\'\"]*(.+?)[\'\"\/^>]*>.*/ism', "$1", $html );
					$desc = wp_strip_all_tags( $desc, true );
				}

				// 通常処理
				if( empty( $title ) && empty( $desc ) ) {
					$html = preg_replace( '/>\s*</', ">\n<", $html );
					$html = preg_replace( '/<script.*?>.*?<\/script>/ism', '', $html ) ;
					$html = preg_replace( '/<style[^>]*?>\s*[^<]*?\s*<\/style>/ism', '', $html ) ;

					// タイトル取得
					preg_match( '/<\s*title\s*[^>]*>[^<]+?<\/title>/im', $html, $titles );
					$title = isset( $titles[0] ) ? strip_tags( $titles[0] ) : '';
					$title = $this->_remove_spaces( $title );

					// タイトルが Amazon CAPTCHA だった場合は status code 500 と同じ扱いにする
					if( stripos( $title, 'Amazon CAPTCHA' ) !== false ) {
						if( file_exists( $save_file ) === true ) return false;
						$code = 500;
					}

					// 内容取得
					$desc = preg_replace( '/.*<\s*meta\s*[^>]+?description[^>]+?content=[\'\"]*(.+?)[\'\"\/^>]*>.*/ism', "$1", $html );

					$desc = $this->_remove_spaces( $desc );

					if( stripos( $desc, '<' ) !== false || empty( $desc ) ) {
						// description が無い場合は body から取る
						$desc = preg_replace( '/<\/head>.*<\s*body[^>]*?>(.+)/ism', "$1", $html );
						$desc = strip_tags( $desc );
						$desc = $this->_remove_spaces( $desc );
					}
					$desc = trim( $desc, "'\"/><" );
				}

				$title = mb_strimwidth( $title, 0, 80 );
				$desc = mb_strimwidth( $desc, 0, 100, ' ...' );
				if( $desc === ' ...' ) $desc = '';

				$matchs_all = array();
				$matchs = array();

				// 画像取得
				// URL に amazon もしくは amzn が含まれてたら Amazon の商品画像の取得を試みる
				// ASIN や ISBN で画像取得しようとしても、存在しないことが多いので別手法で
				if( stripos( $url, '.amazon.' ) !== false || stripos( $url, '/amzn.' ) !== false ) {
					preg_match( '/data-a-dynamic-image=.+?(http.+?(\.jpg|\.jpe|\.jpeg|\.png|\.gif|\.webp|\.bmp))/im', $html, $matchs_amazon );
					if( isset( $matchs_amazon[1] ) ) {
						$img_urls[] = $matchs_amazon[1];
					}
				}

				preg_match_all( '/<.+?(content|src|href)\s*=\s*[\'\"]*[^=]+?(\.jpg|\.jpe|\.jpeg|\.png|\.gif|\.webp|\.bmp)[^\s^\'^\"]*[\s\'\">]/im', $html, $matchs_all );
				unset( $html );

				if( isset( $matchs_all[0] ) ) {
					$matchs = $matchs_all[0];
					unset( $matchs_all );
				}

				if( isset( $matchs[0] ) ) {
					foreach( $matchs as $key => $value ) {
						if(
							stripos( $value, '.png' )  === false &&
							stripos( $value, '.gif' )  === false &&
							stripos( $value, '.bmp' )  === false &&
							stripos( $value, '.jpg' )  === false &&
							stripos( $value, '.jpe' )  === false &&
							stripos( $value, '.jpeg' ) === false &&
							stripos( $value, '.webp' ) === false
						) {
							unset( $matchs[$key] );
							continue;
						}
						$value = preg_replace( '/\s*=\s*/', '=', $value );
						if(  stripos( $value, 'rel=' ) !== false && stripos( $value, 'icon' ) !== false ) {
							$value = str_replace( 'href', 'icon', $value );
						}
						$matchs[$key] = preg_replace( '/.+?((content|src|href|icon)\s*=\s*[\'\"]*.[^=]+?(\.jpg|\.jpeg|\.png|\.gif|\.webp|\.bmp)[^\s^\'^\"]*)/i', "$1", $value );
					}
				}
				$matchs = array_unique( $matchs );

				if( isset( $matchs[0] ) ) {
					// og:image や twitter:image を優先させるので content 最優先
					foreach( $matchs as $value ) {
						if( stripos( $value, 'content' ) === 0 ) {
							$img_urls[] = strip_tags( $value );
						}
					}
					// content がなければ、icon
					foreach( $matchs as $value ) {
						// icon の場合は、最後に現れたものを取得
						if( stripos( $value, 'icon' ) === 0 ) {
							$img_urls[] = strip_tags( $value );
						}
					}
					// icon もなければ、src
					foreach( $matchs as $value ) {
						// Amazon の global-sprite は無視
						if( stripos( $value, 'sprites/' ) !== false || stripos( $value, 'transparent' ) !== false ) {
							continue;
						}
						if( stripos( $value, 'src' ) === 0 ) {
							$img_urls[] = strip_tags( $value );
						}
					}
					// src もなければ、href
					foreach( $matchs as $value ) {
						if( stripos( $value, 'href' ) === 0 ) {
							$img_urls[] = strip_tags( $value );
						}
					}
				}

				foreach( $img_urls as $val ) {
					$val = preg_replace( '/\s*/', '', $val );
					$val = str_ireplace( array( 'content=', 'src=', 'href=', 'icon=', '"', "'" ), '', $val );

					if( stripos( $val, 'http' ) === 0 || stripos( $val, 'https:' ) === 0 ) {
						$img = thk_remote_request( $val );
					}
					else {
						if( stripos( $val, '//' ) === false || stripos( $val, '//' ) > 0 ) {
							if( stripos( $val, '/' ) === 0 ) {
								$parse = parse_url( $url, PHP_URL_HOST );
								$val = '//' . $parse . $val;
							}
							else {
								$del = substr( $url, strripos( $url, '/' ) + 1, strlen( $url ) );
								$val = rtrim( $url, $del ) . $val;
								$val = str_replace( array( 'http:', 'https:' ), '', $val );
							}
						}
						$img = thk_remote_request( 'https:' . $val );
						if( $img === false ) $img = thk_remote_request( 'http:' . $val );
					}

					if( is_array( $img ) === true || $img === false ) {}
					else {
						$img_file = $this->_cache_dir . $url_md5[0] . DSEP . $url_md5 . '.png';
						if( $this->_filesystem->file_save( $img_file, $img ) === false ) return false;
					}

					$mime = 'png';
					if( is_callable( 'getimagesize' ) === true ) {
						if( file_exists( $img_file ) === true ) $normal_img = getimagesize( $img_file );
						if( isset( $normal_img[0] ) && $normal_img[0] >= 100 && isset( $normal_img[1] ) && $normal_img[1] >= 100 ) {
							$mime = str_replace( 'jpeg', 'jpg', str_replace( 'image/', '', $normal_img['mime'] ) );
							$img_ok = true;
						}
					}
					elseif( is_callable( 'exif_imagetype' ) === true ) {
						$img_ok = exif_imagetype( $img_file );
					}
					else {
						$img_ok = true;
					}

					if( $img_ok !== false ) {
						$img_file_old = $img_file;
						$img_file = str_replace( '.png', '.' . $mime, $img_file_old );
						$wp_filesystem->delete( $img_file_old );
						if( $this->_filesystem->file_save( $img_file, $img ) === false ) return false;

						$img_url = $val;
						break;
					}
				}
			}
		//}

		// 画像として正常かどうかの判別
		if( is_callable( 'getimagesize' ) === true ) {
			if( $img_ok !== false ) {
				$normal_img = true;
			}
			else {
				// 画像が無かったか、もしくは取得できた画像が小さすぎる場合はスクリーンショットを取得
				$wp_filesystem->delete( $img_file );
				$img = $this->thk_get_screenshot( $url );
				if( isset( $img ) ) {
					$img_file = $this->_cache_dir . $url_md5[0] . DSEP . $url_md5 . '.jpg';
					if( $this->_filesystem->file_save( $img_file, $img ) === false ) return false;

					if( file_exists( $img_file ) === true && filesize( $img_file ) > 0 ) {
						if( is_callable( 'exif_imagetype' ) === true ) {
							$normal_img = exif_imagetype( $img_file );
						}
						else {
							$normal_img = getimagesize( $img_file );
						}
					}
					$sshot_flag = true;
				}
			}
		}
		else {
			if( file_exists( $img_file ) === true && filesize( $img_file ) > 0 ) {
				if( is_callable( 'exif_imagetype' ) === true ) {
					$normal_img = exif_imagetype( $img_file );
				}
				else {
					$normal_img = false;
				}
			}
		}

		// 画像リサイズ
		$img_obj = array();
		if( $normal_img !== false ) {
			$image = wp_get_image_editor( $img_file ); // Return an implementation that extends WP_Image_Editor

			if( is_wp_error( $image ) === false ) {
				if( $sshot_flag === true ) {
					$image->resize( 100, 100, array( 'center', 'top' ) );
				}
				else {
					$image->resize( 100, 100, true );
				}
				$img_obj = $image->save( $img_file );
			}
		}

		if( !empty( $img_obj ) ) {
			$img_file = $img_obj['path'];
		}

		// アイコン取得
		//$parse = parse_url( $url, PHP_URL_HOST );
		//$icon = thk_remote_request( 'https://www.google.com/s2/fav' . 'icons?domain=' . $parse );
		$icon = thk_remote_request( 'https://s2.googleusercontent.com/s2/fav' . 'icons?domain_url=' . $url );
		$ico_file = $this->_cache_dir . $url_md5[0]. DSEP . $url_md5 . '-icon.png';

		if( is_array( $icon ) === true || $icon === false ) {
			// 200 以外が返ってきたら unknown-icon.png を使う
			$icon = $wp_filesystem->get_contents( TPATH . '/images/unknown-icon.png' );
		}

		if( $this->_filesystem->file_save( $ico_file, $icon ) === false ) return false;

		$ico_url  = $url_md5[0] . '/' . $url_md5 . '-icon.png';

		// 画像 PATH を URL に置換
		$img_url  = str_replace( str_replace( DSEP, '/', $this->_cache_dir ), '', str_replace( DSEP, '/', $img_file ) );
		if( $normal_img === false || ( file_exists( $img_file ) === true && filesize( $img_file ) <= 0 ) ) {
			$img_url = '';
			if( file_exists( $img_file ) === true ) {
				$wp_filesystem->delete( $img_file );
			}
		}

		// File save
		$save = $code . "\n" . $url . "\n" . $img_url . "\n" . $ico_url . "\n" . $title . "\n" . $desc;
		if( $this->_filesystem->file_save( $save_file, $save ) === false ) return false;
	}

	// ブログカードキャッシュの transient 登録・削除と確認
	public function thk_transient_blogcard_cache( $url_md5 ) {
		global $luxe, $wpdb;

		// 期限切れの transient を全部削除
		$time_now = $_SERVER['REQUEST_TIME'];
		$expired_trans = $wpdb->get_col( "SELECT option_name FROM $wpdb->options where option_name LIKE '%_transient_timeout_%' AND option_value+0 < $time_now" );

		if( !empty( $expired_trans ) ) {
			foreach( (array)$expired_trans as $val ) {
				$site_wide = ( strpos( $val, '_site_transient' ) !== false );
				$trans_name = str_replace( $site_wide ? '_site_transient_timeout_' : '_transient_timeout_', '', $val );

				if( strpos( $trans_name, 'luxe-bc-' ) !== false ) {
					if( $site_wide !== false ) {
						delete_site_transient( $trans_name );
					}
					else {
						delete_transient( $trans_name );
					}
				}
			}
		}

		$transient = 'luxe-bc-' . $url_md5;
		$expire = isset( $luxe['blogcard_cache_expire'] ) && is_int( $luxe['blogcard_cache_expire'] ) ? $luxe['blogcard_cache_expire'] : 2592000;

		// transient が無かったら transient を登録
		if( get_transient( $transient ) === false ) {
			set_transient( $transient, 1, $expire );
			return true;
		}
		return false;
	}

	// コンテンツからブログカード用のリンク配列を取る
	public function thk_get_blogcard_links( $content ) {
		$content = str_replace( '</a><', "</a>\n<", $content );
		preg_match_all( '/<a [^>]*?href=[\'\"]*?([^>]+?)[\s\'\"][^>]*?data-blogcard[^>]+?>[^<]*?<\/a>/im', $content, $links );
		return $links;
	}

	// リンク名から整形済み URL と 元URL と md5 生成
	public function thk_create_blogcard_cache_path( $link ) {
		$raw_url = preg_replace( '/.+?href\s*=[\'\"]*(.+?)[\s\'\"]+.+/', "$1", $link );
		$url = html_entity_decode( thk_decode( $raw_url ) );
		$url_md5 = md5( $url );

		return array( $url, $raw_url, $url_md5 );
	}

	// Google API でスクリーンショットを生成
	public function thk_get_screenshot( $url ) {
		$ret = '';
		if( stripos( $url, '.google.' ) !== false ) {
			// デスクトップのスクリーンショット
			$img_json = thk_remote_request( 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed?screenshot=true&strategy=desktop&url=' . $url );
		}
		else {
			// モバイルのスクリーンショット
			$img_json = thk_remote_request( 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed?screenshot=true&strategy=mobile&url=' . $url );
		}
		if( is_string( $img_json ) === true ) {
			$screenshot = @json_decode( $img_json, true );
			if( isset( $screenshot['lighthouseResult']['audits']['final-screenshot']['details']['data'] ) ) {
				$b64_data = $screenshot['lighthouseResult']['audits']['final-screenshot']['details']['data'];
				$b64_data = str_replace( 'data:image/jpeg;base64,', '', $b64_data );
				/* v2 の場合
				$b64_data = $screenshot['screenshot']['data'];
				$b64_data = str_replace( '_', '/', $b64_data );
				$b64_data = str_replace( '-', '+', $b64_data );
				*/
				$b64_func = 'base' . '64' . '_decode';
				$ret = $b64_func( $b64_data );
			}
		}
		return $ret;
	}

	// 改行やスペースの置換用
	public function _remove_spaces( $str ) {
		$str = str_replace( array( "\n", "\r", "\t" ), ' ', $str );
		$str = preg_replace( '/\s{2,}/', ' ', $str );
		$str = trim( $str );
		return $str;
	}

	public function _filesystem() {
		global $wp_filesystem;
		require_once( INC . 'thk-filesystem.php' );

		if( $this->_mkdir( $this->_cache_dir ) === false ) {
			return false;
		}
		return true;
	}

	// ディレクトリ作成
	public function _mkdir( $dir ) {
		global $wp_filesystem;
		$ret = true;

		$this->_filesystem = new thk_filesystem();
		if( $this->_filesystem->init_filesystem() === false ) return false;

		if( $wp_filesystem->is_dir( $dir ) === false ) {
			/* ディレクトリが存在しなかったら作成 */
			if( wp_mkdir_p( $dir ) === false ) {
				if( $wp_filesystem->mkdir( $dir, FS_CHMOD_DIR ) === false && $wp_filesystem->is_dir( $dir ) === false ) {
					if( defined( 'WP_DEBUG' ) === true && WP_DEBUG == true ) {
						$result = new WP_Error( 'mkdir failed', __( 'Could not create cache directory.', 'luxeritas' ), $dir );
						thk_error_msg( $result );
					}
					$ret = false;
				}
			}
		}

		return $ret;
	}
}
