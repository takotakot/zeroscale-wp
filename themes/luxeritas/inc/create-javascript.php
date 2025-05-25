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

class create_Javascript {
	private $_tdel   = null;
	private $_js_dir = null;
	private $_depend = array();

	public function __construct() {
		$this->_tdel   = pdel( get_template_directory_uri() );
		$this->_js_dir = TPATH . DSEP . 'js' . DSEP;

		// Javascript の依存チェック用配列
		$this->_depend = array(
			'stickykit'=> $this->_js_dir . 'jquery.sticky-kit.min.js',
			'autosize' => $this->_js_dir . 'autosize.min.js',
		);
		foreach( $this->_depend as $key => $val ) {
			if( file_exists( $val ) === false ) unset( $this->_depend[$key] );
		}
	}

	/*
	------------------------------------
	UTF-8文字列をUnicodeエスケープする。ただし英数字と記号はエスケープしない。
	source: https://iizukaw.hatenadiary.org/entries/2009/04/22
	------------------------------------ */
	private function unicode_decode( $str ) {
		return preg_replace_callback( "/((?:[^\x09\x0A\x0D\x20-\x7E]{3})+)/", array( $this, 'decode_callback' ), $str );
	}

	private function decode_callback( $matches ) {
		$escaped = '';
		$char = mb_convert_encoding( $matches[1], 'UTF-16', 'UTF-8' );
		for( $i = 0, $l = strlen($char); $i < $l; $i += 2 ) {
			$escaped .=  "\u" . sprintf( "%02x%02x", ord( $char[$i] ), ord( $char[$i+1] ) );
		}
		return $escaped;
	}

	/*
	------------------------------------
	Unicodeエスケープされた文字列をUTF-8文字列に戻す
	source: https://iizukaw.hatenadiary.org/entries/2009/04/22
	------------------------------------ */
	private function unicode_encode( $str ) {
		return preg_replace_callback( "/\\\\u([0-9a-zA-Z]{4})/", array( $this, 'encode_callback' ), $str );
	}

	private function encode_callback( $matches ) {
		return mb_convert_encoding( pack( "H*", $matches[1] ), "UTF-8", "UTF-16" );
	}

	/*
	------------------------------------
	 非同期 CSS の読み込み
	------------------------------------ */
	public function create_css_load_script( $url, $media = null ) {
		global $luxe;
		$ret = '';

		if( isset( $luxe['material_load'] ) ) {
			if( isset( $luxe['material_load_async'] ) && $luxe['material_load_async'] === 'async' ) {
				global $awesome;

				if( isset( $awesome['material'] ) ) {
					$ret .= <<< SCRIPT
!function(d){
	var n = d.createElement('link');
	n.async = !0;
	n.defer = !0;
	n.media = "all";
	n.rel  = "stylesheet";
	n.href = "{$awesome['material']['uri']}{$awesome['material']['css']}";
	if( d.getElementsByTagName('head')[0] !== null ) {
		d.getElementsByTagName('head')[0].appendChild( n );
	}
}(document);

SCRIPT;
				}
			}
		}

		if( isset( $luxe['awesome_load'] ) ) {
			if( isset( $luxe['awesome_load_async'] ) && $luxe['awesome_load_async'] === 'async' && isset( $luxe['awesome_type'] ) && $luxe['awesome_type'] === 'css' ) {
				global $awesome;

				if( isset( $awesome['awesome'] ) ) {
					$ret .= <<< SCRIPT
!function(d){
	var n = d.createElement('link');
	n.async = !0;
	n.defer = !0;
	n.media = "all";
	n.rel  = "stylesheet";
	n.href = "{$awesome['awesome']['uri']}{$awesome['awesome']['css']}";
	if( d.getElementsByTagName('head')[0] !== null ) {
		d.getElementsByTagName('head')[0].appendChild( n );
	}
}(document);

SCRIPT;
				}
			}
		}

		if( file_exists( TPATH . DSEP . 'style.async.min.css' ) === false || ( file_exists( TPATH . DSEP . 'style.async.min.css' ) === true && filesize( TPATH . DSEP . 'style.async.min.css' ) <= 0 ) ) {
			return $ret;
		}
		else {
			$style_async_v = filemtime( TPATH . DSEP . 'style.async.min.css' );
		}

		$ret .= <<< SCRIPT
!function(d){
	var n = d.createElement('link');
	n.async = !0;
	n.defer = !0;

SCRIPT;
		if( $media !== null ) $ret .= "n.media = " . $media . "';";

		$ret .= <<< SCRIPT
	n.rel  = "stylesheet";
	n.href = "{$url}?v={$style_async_v}";
	if( d.getElementsByTagName('head')[0] !== null ) {
		d.getElementsByTagName('head')[0].appendChild( n );
	}

SCRIPT;
/*
		if( isset( $luxe['wp_block_library_load'] ) && $luxe['wp_block_library_load'] === 'async' ) {
			// WP 5.0 未満
			if( version_compare( $GLOBALS['wp_version'], '5.0', '<' ) === true ) {
				// Gutenberg プラグインが入ってる場合
				$block_library_style = WP_PLUGIN_URL . 'gutenberg/build/block-library/style.css';
				$block_library_theme = WP_PLUGIN_URL . 'gutenberg/build/block-library/theme.css';
			}
			// WP 5.0 以上
			else {
				$block_library_style = includes_url() . 'css/dist/block-library/style.min.css';
				$block_library_theme = includes_url() . 'css/dist/block-library/theme.min.css';
			}
			$ret .= <<< SCRIPT
	n = d.createElement('link');
	n.async = !0;
	n.defer = !0;
	n.rel  = "stylesheet";
	n.href = "{$block_library_style}";
	if( d.getElementsByTagName('head')[0] !== null ) {
		d.getElementsByTagName('head')[0].appendChild( n );
	}

	n = d.createElement('link');
	n.async = !0;
	n.defer = !0;
	n.rel  = "stylesheet";
	n.href = "{$block_library_theme}";
	if( d.getElementsByTagName('head')[0] !== null ) {
		d.getElementsByTagName('head')[0].appendChild( n );
	}

SCRIPT;
		}
*/

		if( isset( $luxe['web_font_async'] ) ) {
			$font_alphabet = Web_Font::$alphabet[$luxe['font_alphabet']][1];
			if( isset( $font_alphabet ) ) {
				$ret .= <<< SCRIPT
	n = d.createElement('link');
	n.async = !0;
	n.defer = !0;

SCRIPT;
				$ret .= <<< SCRIPT
	n.rel  = "stylesheet";
	n.href = "{$font_alphabet}";
	if( d.getElementsByTagName('head')[0] !== null ) {
		d.getElementsByTagName('head')[0].appendChild( n );
	}

SCRIPT;
			}

			$font_japanese = Web_Font::$japanese[$luxe['font_japanese']][1];
			if( isset( $font_japanese ) ) {
				$ret .= <<< SCRIPT
	n = d.createElement('link');
	n.async = !0;
	n.defer = !0;

SCRIPT;
				$ret .= <<< SCRIPT
	n.rel  = "stylesheet";
	n.href = "{$font_japanese}";
	if( d.getElementsByTagName('head')[0] !== null ) {
		d.getElementsByTagName('head')[0].appendChild( n );
	}

SCRIPT;
			}
		}
		$ret .= <<< SCRIPT
}(document);

SCRIPT;
		return $ret;
	}

	/*
	------------------------------------
	 Copy ボタン
	------------------------------------ */
	public function create_copy_button_script() {
		global $luxe;

		$ret = '';

		if( isset( $luxe['copy_button_target'] ) && $luxe['copy_button_target'] === 'url' ) {
			$copy_msg    = $this->unicode_decode( __( 'You copied URL.', 'luxeritas' ) );
			$copy_target = 'true';
		}
		else {
			$copy_msg    = $this->unicode_decode( __( 'You copied this post title and URL.', 'luxeritas' ) );
			$copy_target = 'false';
		}

		$ret .= <<< SCRIPT
var luxeFadeOut = function(o, l) {
	o.style.opacity = 1;
	var w = window
	,   a = performance.now();
	w.requestAnimationFrame( function e(t) {
		var n = (t - a) / l;
		o.style.opacity = Math.max(1 - n, 0), n < 1 ? w.requestAnimationFrame(e) : (o.style.opacity = "", o.style.display = "none")
	})
}, luxeUrlCopy = function(e) {
	var v, l = document
	,   n = l.getElementById("cp-page-" + e)
	,   i = l.getElementById("cp-button-" + e)
	,   o = n.getAttribute("data-luxe-permalink")
	,   a = l.getElementById("sns-" + e).childNodes[1].childNodes[1]
	,   t = {$copy_target}
	,   s = l.getElementsByTagName("h1")
	,   v = s[0].innerText
	,   p = l.createElement("input")
	,   d = '<a style="color:#ff0;background:#000"';

	if( "" == v || null == v ) {
		v = s[0].nextElementSibling;
		v = v.getAttribute("content");
		if( "" == v || null == v ) {
			v = l.getElementsByTagName("title");
		}
	}

	p.id = "cp-input-area";
	p.type = "text";
	p.style.position = "absolute";
	p.style.top = 0;
	p.style.zIndex = -10;
	p.value = !0 === t ? o : v + " | " + o, l.body.appendChild(p);

	var r = l.createElement("li")
	,   c = r.style
	,   m = l.getElementById("cp-input-area");

	c.flex = "1 1 100%";
	c.minWidth = "100%";
	c.lineHeight = "23px";
	c.height = "35px";
	c.maxHeight = "35px";
	c.marginTop = n.classList.contains("sns-count-true") && !0 === a.classList.contains("snsfb") && e !== "mobile" ? "-18px" : "4px";
	c.marginBottom = "-40px";
	c.padding = "6px";
	c.textAlign = "center";
	c.fontSize = "13px";
	c.color = "#fff";
	c.background = "#000";
	c.borderRadius = "4px";
	c.cursor = "auto";
	r.innerHTML = "{$copy_msg}";
	r.id = "cp-page-tops" === n.id ? "cp-msg-tops" : "cp-page-bottoms" === n.id ? "cp-msg-bottoms" : "cp-msg-mobile";
	luxeFadeOut(r, 3000);

	try {
		m.select(), l.execCommand("copy")
	} catch (a) {
		var g = "This browser was old and could not be copied.";
		r.innerHTML = g;
		c.color = "#fff";
		c.background = "red";
		useZeroClipBoard = !0;
		console.log(g)
	}
	var u = i.innerHTML;
	u = u.split("<a").join(d), i.innerHTML = u, a.appendChild(r), m.parentNode.removeChild(m), setTimeout(function() {
		var e = i.innerHTML;
		e = -1 != e.indexOf(d) ? e.split(d).join("<a") : e.replace(/style=[\"\']{1}[^\"\']+?[\"\']{1} /gi, "");
		i.innerHTML = e;

		var t = l.getElementById("cp-msg-" + e);
		null !== t && t.parentNode.removeChild(t);
	}, 3000)
};

SCRIPT;
		return $ret;
	}

	/*
	------------------------------------
	 いろいろ
	------------------------------------ */
	public function create_luxe_dom_content_loaded_script() {
		global $luxe, $_is, $awesome;

		$ret = '';
		thk_default_set();

		$side_1_width = isset( $luxe['side_1_width'] ) ? $luxe['side_1_width'] : 366;

		require_once( INC . 'colors.php' );
		$conf = new defConfig();
		$colors_class = new thk_colors();

		$defaults = $conf->default_variables();
		$default_colors = $conf->over_all_default_colors();
		unset( $conf );

		$bg_color = isset( $luxe['body_bg_color'] ) ? $luxe['body_bg_color'] : $default_colors[$luxe['overall_image']]['contbg'];
		$inverse = $colors_class->get_text_color_matches_background( $bg_color );
		$rgb = $colors_class->colorcode_2_rgb( $inverse );
		$brap_rgba = 'background: rgba(' . $rgb['r'] . ',' . $rgb['g'] . ',' . $rgb['b'] . ', .5 )';

		$broken = false;
		$ca = new carray();

		$imp = $ca->thk_hex_imp_style();
		$imp_close = $ca->thk_hex_imp_style_close();

		if(
			stripos( $imp, '!;' ) === false ||
			stripos( $imp_close, '!' ) === false
		) {
			$broken = true;
		}
		else {
			$imp = str_replace( '!;', $imp_close, $imp );
		}

		$luxe_version = "?";
		$curent = wp_get_theme();
		if( TPATH === SPATH ) {
			$luxe_version = $curent->get('Version');
		}
		else {
			$parent = wp_get_theme( $curent->get('Template') );
			$luxe_version = $parent->get('Version');
		}

		$ret .= <<< SCRIPT
/* "passive" が使えるかどうかを検出 */
var luxePassiveSupported = function() {
	function e() {}
	var t = !1;
	try {
		var n = Object.defineProperty({}, "passive", {
			get: function () {
				t = !0;
			},
		});
		window.addEventListener("test", e, n), window.removeEventListener("test", e, n);
	} catch(e) {}
	return t;
}

var luxeDOMContentLoaded = function() {
console.log("Luxeritas " + "{$luxe_version}" + ": loading success");
var w = window
,   d = document
,   luxeGetStyleValue = function(e, t) {
	// Get CSS
	return e && t ? w.getComputedStyle(e).getPropertyValue(t) : null
}
,   luxeShow = function( e ) {
	// Show an element
	if( e !== null && typeof e !== "undefined" ) e.style.display = "block";
}
,   luxeHide = function( e ) {
	// Hide an element
	if( e !== null && typeof e !== "undefined" ) e.style.display = "none";
}
,   luxeWrapListner;

try {  /* page.top */
	!function() {
		// トップに戻るボタンでトップに戻る
		var i = d.getElementById("mobile-buttons")
		,   t = d.getElementById("page-top-m")
		,   l = d.getElementById("page-top")
		,   r = null !== l ? l.style : void 0
		,   c = "scrollingElement" in document ? document.scrollingElement : document.documentElement
		,   v = 0		// スクロール位置
		,   x = -1
		,   e = function() {
			var r = performance.now()
			,   a = c.scrollTop
			,   t = w.requestAnimationFrame( function e(l) {
				var o = 1 - (l - r) / 400;
				0 < o ? ((c.scrollTop = o * a), w.requestAnimationFrame(e)) : ((c.scrollTop = 0), w.cancelAnimationFrame(t));
			});
			return !1;
		}
		,   q = function() {
			var n = d.getElementsByTagName("html");
			void 0 !== n[0] && null !== n[0] && (n[0].style.scrollBehavior = "auto");
			e();
			setTimeout( function() {
				void 0 !== n[0] && null !== n[0] && (n[0].style.scrollBehavior = "");
			}, 1000 );
		};

		null !== t && (t.onclick = function() { q() });
		null !== l && (l.onclick = function() { q() });


		// スクロール監視
		w.addEventListener( "scroll", function() {
			var e = w.pageYOffset;

			// スクロール量が 500px に達したらトップに戻るボタン表示
			if( "undefined" != typeof r ) {
				500 < e ? (r.opacity = ".5", r.visibility = "visible" ) : (r.opacity = "0", r.visibility = "hidden");
			} else {
				console.warn( "Page top style (opcity or visibility ?)" );
			}

			// レイヤーが画面外に出たらレイヤー消す
			if( null !== d.getElementById("ovlay") ) {
				var t, n, l = d.getElementById("layer");
				if( null !== d.querySelector("#layer #nav") ) {
					t = l.offsetTop + l.offsetHeight;
					n = l.offsetTop - w.innerHeight;
					(t < e || e < n) && remove_ovlay()
				}
				else {
					var t = d.getElementById("sform");
					if( null !== t && "block" === t.style.display ) return;
					else {
						l = d.getElementById("toclay");
						null === l && ( l = d.getElementById("snslay") );
						null === l && ( l = d.getElementById("sidebar") );
						if( null !== l ) {
							t = l.offsetTop + l.offsetHeight;
							n = l.offsetTop - w.innerHeight;
							(t < e || e < n) && remove_ovlay()
						}
					}
				}
			}
SCRIPT;

		if( isset( $luxe['mobile_button_scroll_hide'] ) ) {
			$ret .= <<< SCRIPT
			// モバイルボタン
			if( "undefined" != typeof i && null !== i ) {
				var z = v - e;
				0 === z && (z = x), v = e, (x = z) <= 0 || c.scrollTop + w.innerHeight >= c.scrollHeight - 20 ? i.style.cssText = "bottom:-200px!important" : i.removeAttribute("style")
			}
			/*
			if( typeof i !== "undefined" && null !== i ) {
				var z = v - e; // スクロールの上下判定 ( difpos > 0 ) で上、( difpos <= 0 ) で下

				if( z === 0 ) z = x;
				v = e;
				x = z;

				if( z <= 0 || c.scrollTop + w.innerHeight >= c.scrollHeight - 20 ) {
					i.style.cssText = "bottom:-200px!important";
				} else {
					i.removeAttribute("style");
				}
			}
			*/
SCRIPT;
		}
		else {
			$ret .= <<< SCRIPT
			typeof i !== "undefined" && null !== i && (c.scrollTop + w.innerHeight >= c.scrollHeight - 20 ? i.style.cssText = "bottom:-200px!important" : i.removeAttribute("style"))
SCRIPT;
		}

			$ret .= <<< SCRIPT
		}, !1 )
	}();
} catch (e) {
	console.error("page.top.error: " + e.message)
}

SCRIPT;

		if( $_is['customize_preview'] === true ) {
			// カスタマイズプレビューだと get_theme_mod で値を直接取ってこないとダメですた
			$luxe['awesome_load_async'] = get_theme_mod('awesome_load_async');
		}

		/* placeholder にアイコンフォントを直接書くと、Nu Checker で Warning 出るので、Javascript で置換 */
		/* 警告出なくなったので、廃止
		$ret .= <<< SCRIPT
!function() {
	for (var e = d.getElementsByClassName("search-field"), r = 0; e.length > r; ++r) {
		var l = e[r].outerHTML; - 1 != l.indexOf("query-input") && -1 != l.indexOf("placeholder") && (e[r].parentNode.innerHTML = l.replace('placeholder="', 'placeholder=" &#xf002; '))
	}
}();
SCRIPT;
		*/

		/* 以下 グローバルナビ */
		$ret .= <<< SCRIPT
	function remove_ovlay() {
		var a = [
			"sidebar",
			"sform",
			"ovlay",
			"ovlay-style"
		];

		a.forEach( function( val ) {
			var f = d.getElementById(val);
			if( f !== null ) {
				if( val === "sidebar" || val === "sform" ) {
					f.removeAttribute("style");
				} else {
					f.parentNode.removeChild(f);
				}
			}
		}); d.body.removeAttribute('style'), d.documentElement.removeAttribute("style");

		w.removeEventListener("resize", luxeWrapListner, !1);
	}

SCRIPT;

		if( isset( $luxe['global_navi_visible'] ) || isset( $luxe['mobile_sidebar_button'] ) || isset( $luxe['mobile_search_button'] ) || isset( $luxe['mobile_sns_button'] ) ) {
			$nav_min_css_path = TPATH . DSEP . 'styles' . DSEP . 'nav.min.css';
			if( file_exists( $nav_min_css_path ) === true ) {
				ob_start();
				require( $nav_min_css_path );
				$nav_min_css = trim( ob_get_clean() );
			}

			$ret .= <<< SCRIPT
try{ /* global.nav */
	if( window.jQuery ) {
		var $ = jQuery;
		$('#nav li.gl').hover( function() {
			var t = $('>ul', this);
			t.css( 'display', 'table');
			t.css( 'width', t.outerWidth() + 'px');
			t.stop(false,true).css( 'display', 'none');	// hide() だと次の toggle() がアニメにならない。なんで？
			//t.stop().slideDown(300);
			t.stop(true,true).toggle(300);
		}, function() {
			$('>ul', this).stop(true,true).slideUp(250);
			//$('>ul', this).stop().toggle(300);
		});
	}
	else {
		//!function(d){
			var n = d.createElement('style');
			n.innerHTML = '{$nav_min_css}';
			if( d.getElementsByTagName('head')[0] !== null ) {
				d.getElementsByTagName('head')[0].appendChild( n );
			}
		//}(document);
	}

	/*
	else {
		!function() {
			var m, v
			,   u = Math
			,   e = d.querySelectorAll("#nav li");

			for( var t = 0, i = e.length; t < i; ++t ) e[t].addEventListener("mouseenter", function() {
				for( var e = this.childNodes, t = -1; ++t < e.length; ) {
					if( 1 == e[t].nodeType && "ul" == e[t].nodeName.toLowerCase() ) {
						var r, n = performance.now()
						,   a = e[t]
						,   l = a.style;
						l.display = "table";
						var h = a.offsetHeight
						,   s = a.offsetWidth
						,   f = 0;
						l.display = "block", l.overflow = "hidden", l.opacity = 1, l.height = 0, l.width = 0, r = w.requestAnimationFrame(function e(t) {
							var i = (t - n) / 300;
							if( 0 === f && (v = m = r), f = 1, m !== v ) {
								var o = d.querySelectorAll("#nav li ul");
								for (t = 0, i = o.length; t < i; ++t) o[t].removeAttribute("style");
								w.cancelAnimationFrame(r)
							}
							i < 1 ? (l.opacity = u.max(i, 0), l.height = u.min(i * h, h) + "px", l.width = u.min(i * s, s) + "px", w.requestAnimationFrame(e)) : (a.removeAttribute("style"), l.display = "table")
						})
					}
				}
			});
			for( var o = 0, r = e.length; o < r; ++o ) e[o].addEventListener("mouseleave", function() {
				for( var e = this.childNodes, t = -1; ++t < e.length; ) {
					if( 1 == e[t].nodeType && "ul" == e[t].nodeName.toLowerCase() ) {
						var o, r = performance.now()
						,   n = e[t]
						,   a = n.style
						,   l = n.offsetHeight
						,   i = n.offsetWidth
						,   h = 1
						,   s = d.querySelectorAll("#gnavi div > ul > li");
						for( var f = (t = 0, s.length); t < f; ++t ) {
							n.parentNode == s[t] && (h = 0);
						}
						a.height = l + "px", a.width = i + "px", a.display = "block", a.overflow = "hidden", o = w.requestAnimationFrame(function e(t) {
							var i = (t - r) / 250;
							0 === h && (v = o), i < (h = 1) ? (a.height = u.max((1 - i) * l, 0) + "px", w.requestAnimationFrame(e)) : n.removeAttribute("style")
						})
					}
				}
			})
		}();
	}
	*/

SCRIPT;
		// PC 用ラッパーメニュー ( Custom global nav )
		// 作ってはみたものの CSS だけで出来ちゃったので削除
		/*
		if( isset( $luxe['wrap_menu_used'] ) ) {
			$ret .= <<< SCRIPT
	try {
		!(function() {
			if( null !== s ) {
				for( var r = d.getElementById("gnavi"), e = r.querySelectorAll(".has-wrap-menu"), t = 0; t < e.length; t++ ) {
					var i = d.getElementById(e[t].id)
					,   n = i.getAttribute("data-child-id")
					,   l = d.getElementById("menu-item-" + n);

					null !== l && (
						i.addEventListener( "mouseover", function() {
							var t = d.getElementById("menu-item-" + this.getAttribute("data-child-id"));
							null !== t && (t.style.width = r.clientWidth + "px");
						}, !1 ),
						i.addEventListener( "mouseleave", function() {
							var t = d.getElementById("menu-item-" + this.getAttribute("data-child-id"));
							null !== t && (t.removeAttribute("style"));
						}, !1 ),
						l.addEventListener( "mouseleave", function() {
							var t = d.getElementById(this.id);
							null !== t && (t.removeAttribute("style"));
						}, !1 )
					);
				}
			}
		})();
	} catch (e) {
		console.error("wrapper.menu.error: " + e.message);
	};

SCRIPT;
		}
		*/

		$ret .= <<< SCRIPT
} catch (e) {
	console.error("global.nav.error: " + e.message)
}

try{ /* mibile.nav */
	var luxeScrollOff = function( e ){
		e.preventDefault(), e.stopImmediatePropagation()
	}, no_scroll = function() {  // スクロール禁止
		// PC
		var sclev = "onwheel" in d ? "wheel" : "onmousewheel" in d ? "mousewheel" : "DOMMouseScroll";
		d.addEventListener( sclev, luxeScrollOff, !!luxePassiveSupported() && { passive: !1 } );
		// スマホ
		d.addEventListener( "touchmove", luxeScrollOff, !!luxePassiveSupported() && { passive: !1 } );
	}, go_scroll =  function() { // スクロール復活 
		// PC
		var sclev = "onwheel" in d ? "wheel" : "onmousewheel" in d ? "mousewheel" : "DOMMouseScroll";
		d.removeEventListener( sclev, luxeScrollOff, !!luxePassiveSupported() && { passive: !1 } );
		// スマホ
		d.removeEventListener( "touchmove", luxeScrollOff, !!luxePassiveSupported() && { passive: !1 } );
	}

	// モバイルメニュー ( Luxury 版 )
	//var nav = $('#nav')
	var mar
  	,   mof = d.querySelector(".mob-func")
	,   mom = d.querySelectorAll(".mob-menu")
	,   mos = d.querySelectorAll(".mob-side")
	,   sns = d.querySelectorAll(".mob-sns")
	,   toc = d.querySelectorAll(".mob-toc")
	,   srh = d.querySelectorAll(".mob-search")
	,   prv = d.querySelector(".mob-prev")
	,   nxt = d.querySelector(".mob-next")
	//,   men = $('.menu ul')
	//,   mob = d.querySelector(".mobile-nav")
	,   prvid = d.getElementById("data-prev")
	,   nxtid = d.getElementById("data-next")
	,   navid = d.getElementById("nav")
	,   sdbid = d.getElementById("sidebar")
	,   snsid = d.getElementById("sns-tops")
	,   tocid = d.getElementById("toc_container")
	,   pzcss = "position:absolute;z-index:1100;"
	,   mobmn = 'style=\"' + pzcss + 'width:100%;margin-top:-' + d.documentElement.clientHeight + 'px\"><div id=\"nav\" style="top:0">' + ( navid !== null ? navid.innerHTML : '' ) + '</div>'
	,   sdbar
	,   snsbn
	,   tocbn
	,   sform = '>';

	if( sdbid !== null ) {
		sdbar = 'style=\"' + pzcss + 'width:1px;height:' + sdbid.offsetHeight + 'px\">' +
			'<style>#side,div[id*=\"side-\"]{margin:0;padding:0}</style>'
	}

	if( snsid === null || ( snsid !== null && Object.keys(snsid.dataset).length && typeof snsid.dataset !== "undefined" ) ) {
		snsid = d.getElementById("sns-bottoms");
	}
	if( snsid !== null ) {
		snsbn = 'style=\"' + pzcss + 'width:100%\"><div id="sns-mobile" style="margin:auto;padding:50px 10px;width:95%;background:#fff;border-radius:4px">' + snsid.innerHTML.split("tops").join("mobile").split("bottoms").join("mobile") + "</div>"
	}

	if( tocid !== null ) {
		tocbn = 'style=\"' + pzcss + 'width:100%;padding:10px\"><div id="toc_container" style="width:100%">' + tocid.innerHTML + "</div></div>" +
			'<style>#toclay label{display:none}#toclay ul{width:auto!important;height:auto!important}</style>'
	}

SCRIPT;
		if(
			isset( $luxe['global_navi_mobile_type'] ) && isset( $awesome["menu"] ) && isset( $awesome["close"] ) &&
			( $luxe['global_navi_mobile_type'] === 'luxury' || $luxe['global_navi_mobile_type'] === 'global' )
		) {
			$ret .= <<< SCRIPT

	!(function() {
		// モバイル用グローバルナビの一段目の開閉
		var t = d.querySelector(".mobile-nav")
		,   e = w.sessionStorage;

		if( null !== mof ) {
			var k = 44	// 閉じてる時の height
			,   v = 48	// 閉じてる時の width
			,   x = 88;	// 開いてる時の width
			1 != e.getItem(["luxe_mobile_menu_open"]) ? ( e.setItem(["luxe_mobile_menu_open"], [0]), mof.innerHTML = '<span>{$awesome["menu"]}</span>' ) : (t.style.width = x + "px", t.style.maxHeight = "100vh", mof.innerHTML = '<span>{$awesome["close"]}</span>'),
			mof.onclick = function() {
				0 == e.getItem(["luxe_mobile_menu_open"]) ? (t.style.width = x + "px", t.style.maxHeight = "100vh", e.setItem(["luxe_mobile_menu_open"], [1])) : (t.style.width = v + "px", t.style.maxHeight = k + "px", e.setItem(["luxe_mobile_menu_open"], [0]));
				1 != e.getItem(["luxe_mobile_menu_open"]) ? mof.innerHTML = '<span>{$awesome["menu"]}</span>' : mof.innerHTML = '<span>{$awesome["close"]}</span>';
			}
		};

		// モバイル用グローバルナビの位置調節
		var n, r, y
		,   p = 8	// モバイル用グローバルナビの top 位置 ( right の位置は custom-css.php 内に書かれてる CSS )
		,   l = d.getElementsByClassName("band")
		,   i = d.getElementById("wpadminbar")
		,   o = null !== i ? i.offsetHeight : 0;

		null != t && (
			(n = 0),
			t.getBoundingClientRect(),
			0 < l.length && (n = l[0].clientHeight),
			(r = function() {
				var e;
				o = null !== i ? i.offsetHeight : 0;
				0 < l.length && ((e = 0 < (e = l[0].getBoundingClientRect()).bottom && e.top < w.innerHeight), (n = 1 == e ? l[0].clientHeight : 0)), (t.style.top = n + o + p + "px");
			}),
			(y = function() {
				t.style.transition = "width .6s, max-height .6s, top .4s",
				r()
			}),
			r(),
			w.addEventListener("resize", y, !1),
			w.addEventListener("scroll", y, !1)
		);
	})();

SCRIPT;
		}

		$close_icon_escape = isset( $awesome['close'] ) ? str_replace( '"', '\"', $awesome['close'] ) : '';

		$ret .= <<< SCRIPT

	mar = {"mom":[mom,mobmn], "mos":[mos,sdbar], "srh":[srh,sform], "sns":[sns,snsbn,"snslay"], "toc":[toc,tocbn,"toclay"]};

	// モバイルメニューの動き
	for( var i in mar ) {
		if( mar.hasOwnProperty(i) ) {
			Array.prototype.forEach.call( mar[i][0], function(e) {
				if( typeof( e ) !== "undefined" && e !== null ) {
					var a = i;
					e.onclick = function() {
						if( typeof mar[a][2] !== "undefined" ) {
							mobile_menu( a, mar[a][1], mar[a][2] );
						} else {
							mobile_menu( a, mar[a][1] );
						}
					}, e.style.cursor = "pointer";
				}
			});
		}
	}

	if( prv !== null ) {
		if( prvid !== null ) {
			prv.onclick = function(){
				location.href = prvid.getAttribute("data-prev");
			}, prv.style.cursor = "pointer";
		} else {
			prv.style.opacity = ".4", prv.style.cursor = "not-allowed";
		}
	}
	if( nxt !== null ) {
		if( nxtid !== null ) {
			nxt.onclick = function(){
				location.href = nxtid.getAttribute("data-next");
			}, nxt.style.cursor = "pointer";
		} else {
			nxt.style.opacity = ".4", nxt.style.cursor = "not-allowed";
		}
	} function mobile_menu( cpoint, layer, layerName ) {
		if( typeof layerName === "undefined" ) var layerName = "layer";
		if( d.getElementById("bwrap") !== null ) remove_ovlay();

		var scltop = w.pageYOffset;

		/*
		$('body').append(
			'<div id=\"ovlay\">' +
			'<div id=\"bwrap\"></div>' +
			'<div id=\"close\"><i class=\"fas fa-times\"></i></div>' +
			'<div id=\"layer\" ' + layer + '</div>' +
			'</div>' );
		*/

		var ctop = 0;
		if( d.getElementById("wpadminbar") !== null ) {
			ctop = ctop + d.getElementById("wpadminbar").offsetHeight;
		}

		var l = d.createElement("div");
		l.id = "ovlay";
		l.innerHTML =
			'<div id=\"bwrap\" style=\"position:absolute;top:0;width:100%;z-index:1000\"></div>' +
			'<div id=\"close\" style="top:' + ( ctop + 10 ) + 'px">{$close_icon_escape}</div>' +
			'<div id=\"' + layerName + '\" ' + layer.replace('id="gnavi-wrap"', 'id="gnavi-wrap-ovlay"').replace(/id=\"menu-item-/g, 'id="menu-item-wrap-') + '</div>' +
			//'<div id=\"' + layerName + '\" ' + layer + '</div>' +
			'</div>';
		;
		d.body.appendChild( l );

		var s = d.createElement("style");
		s.id = "ovlay-style";
		s.innerText = '#bwrap{height:' + d.body.scrollHeight + 'px;{$brap_rgba};}' + '#' + layerName + '{top:' + ( scltop + ctop ) + 'px;}';

		if( cpoint === "mom") {

SCRIPT;
				if( $luxe['global_navi_open_close'] === 'individual' ) {
					$ret .= <<< SCRIPT
		s.innerText +=
		'#layer li[class*=\"children\"] li a::before{content:\"-\"}' +
		'#layer li[class*=\"children\"] a::before,' +
		'#layer li li[class*=\"children\"] > a::before{content:\"\\\\f0fe\";}' +
		'#layer li li[class*=\"children\"] li a::before{content:\"\\\\0b7\"}' +
		'#layer li[class*=\"has-wrap-menu\"] > a::before{content:\"\\\\f150\";margin-left:-4px};}' /* margin-left:-4px は Fontawesome の位置調整 */

SCRIPT;
				}
				else {
					$ret .= <<< SCRIPT
		s.innerText +=
		'#layer li[class*=\"children\"] a{padding-left:20px;}' +
		'#layer li[class*=\"children\"] ul{display:block}' +
		'#layer li ul > li[class*=\"children\"] > a{padding-left:35px;}'

SCRIPT;
				}

				$ret .= <<< SCRIPT
		;
		}
		d.getElementsByTagName("head")[0].appendChild( s );

		if( cpoint === "mom") {
			luxeShow( d.querySelector("#layer ul.gu") );
			luxeHide( d.querySelector("#layer .mobile-nav") );
		} else if( cpoint === "mos") {
			var top = w.pageYOffset;
			if( d.getElementById("wpadminbar") !== null ) {
				top = top + d.getElementById("wpadminbar").offsetHeight + "px";
			} else {
				top = top + "px";
			}

			var winwh  = d.documentElement.clientWidth
			,   width  = {$side_1_width}
			,   sdbar  = sdbid.style;

			if( width > winwh ) width = winwh - 6;

			sdbar.maxWidth = "98vw";
			sdbar.width    = width + "px";
			sdbar.position = "absolute";
			sdbar.right    =  winwh + "px";
			sdbar.top      = top;
			sdbar.zIndex   = "1100";
			sdbar.overflow = "hidden";
			sdbar.background = "#fff";
			sdbar.padding    = "1px";
			sdbar.border     = "3px solid #ddd";
			sdbar.borderRadius = "5px";
		}

SCRIPT;
			if( $luxe['global_navi_open_close'] === 'individual' ) {
				$ret .= <<< SCRIPT
		luxeHide( d.querySelector("#layer ul ul.gu") );
		var layer = d.querySelectorAll('#layer ul li[class*=\"children\"] > a');
		Array.prototype.forEach.call( layer, function(e) {
			e.addEventListener("click", function(F) {
				var m, g = luxeGetStyleValue
				,   t = this.parentNode
				,   a = t.getAttribute("class").match(/item-[0-9]+/)
				,   n = performance.now()
				,   u = Math;

				for( var e = t.childNodes, i = -1; ++i < e.length; ) {
					if( 1 == e[i].nodeType && "ul" == e[i].nodeName.toLowerCase() ) {
						m = e[i]
					}
				}

				var q = m.style

				if( g(m, "display") === "none" ) {
					q.display = "block";
					var h = m.offsetHeight
					,   s = m.offsetWidth;
					q.opacity = 1, q.height = 0, q.width = 0, w.requestAnimationFrame(function e(t) {
						var i = (t - n) / 300;
						i < 1 ? (q.opacity = u.max(i, 0), q.height = u.min(i * h, h) + "px", q.width = u.min(i * s, s) + "px", w.requestAnimationFrame(e)) : (q.opacity = "", q.width = "", q.height = "", q.display = "block")
					})
				} else {
					var h = m.offsetHeight
					,   s = m.offsetWidth;
					w.requestAnimationFrame(function e(t) {
						var i = 1 - ( (t - n) / 300 );
						i > 0 ? (q.height = u.min(i * h, h) + "px", q.width = u.min(i * s, s) + "px", w.requestAnimationFrame(e)) : m.removeAttribute("style")
					})
				}

				if( d.getElementById(a + "-minus") !== null ) {
					var b = d.getElementById(a + "-minus");
					b.parentNode.removeChild(b);
				} else {
					var l = d.createElement("div");
					l.id = a + "-minus";
					l.innerHTML =
						'<style>' +
						'#layer li[class$=\"' + a + '\"] > a::before,' +
						'#layer li[class*=\"' + a + ' \"] > a::before,' +
						'#layer li li[class$=\"' + a + '\"] > a::before,' +
						'#layer li li[class*=\"' + a + ' \"] > a::before{content:\"\\\\f146\";}' +
						'</style>'
					;
					d.getElementById("ovlay").appendChild( l );
				} if( F.target.href.indexOf("#") == 0 ) {
					F.preventDefault();
				} else {
					F.preventDefault(), F.stopImmediatePropagation();
				}
			}, !1 );
		});

SCRIPT;
			}
			$ret .= <<< SCRIPT
		var s, r = performance.now()
		,   c = d.documentElement.clientWidth;

		if( cpoint === "mom" ) {
			s = d.getElementById("layer");
			if( null !== s ) {
				w.requestAnimationFrame( function e(t) {
					var n = 1 - ( (t - r) / 480 )
					,   a = 1 - s.clientHeight * n;
					s.style.marginTop = a + "px", 0 > n ? s.style.marginTop = 0 : w.requestAnimationFrame(e)
				});
			} else {
				remove_ovlay();
				console.error("Not Found: #layer");
			}
		} else if( cpoint === "mos" ) {
			d.getElementById("primary").style.animation = "none";

SCRIPT;
			if( isset( $luxe['hide_mobile_sidebar'] ) ) {
				$ret .= 's = d.getElementById("sidebar");';
				$ret .= 'luxeShow( s );';
			}

			$ret .= <<< SCRIPT
			w.requestAnimationFrame( function e(t) {
				var n = 1 - ( (t - r) / 500 )
				,   a = c * n;
				sdbid.style.right = a + "px", 0 > n ? sdbid.style.right = "3px" : w.requestAnimationFrame(e)
			});
		} else if( cpoint === "sns" ) {
			s = d.getElementById("snslay");
			if( null !== s ) {
				w.requestAnimationFrame( function e(t) {
					var v = w.innerHeight / 2 - s.offsetHeight / 2
					,   n = (t - r) / 220
					,   a = v * n - s.offsetHeight * 2;
					s.style.marginTop = a + "px", a > v ? s.style.marginTop = v + "px" : w.requestAnimationFrame(e)
				});
			} else {
				remove_ovlay();
				console.error("Not Found: #snslay");
			}
		} else if( cpoint === "toc" ) {
			s = d.getElementById("toclay");
			if( null !== s ) {
/*
				w.requestAnimationFrame( function e(t) {
					var v = w.innerHeight / 2 - s.offsetHeight / 2
					,   n = (t - r) / 220
					,   a = v * n - s.offsetHeight * 2;
					s.style.marginTop = a + "px", a > v ? s.style.marginTop = v + "px" : w.requestAnimationFrame(e)
				});
*/
				w.requestAnimationFrame( function e(t) {
					var n = 1 - ( (t - r) / 360 )
					,   a = 1 - s.clientHeight * n;
					s.style.marginTop = a + "px", 0 > n ? s.style.marginTop = 0 : w.requestAnimationFrame(e)
				});
			} else {
				remove_ovlay();
				console.error("Not Found: #toclay");
			}
		} else if( cpoint === "srh" ) {
			s = d.getElementById("sform");
			if( null !== s ) {
				var h = d.getElementsByTagName("html")
				,   p = typeof Promise !== "undefined" ? new Promise( function(r) {
					setTimeout( function() {
						r();
					}, 100 );
				}) : null;

				if( null !== h[0] ) h[0].style.scrollBehavior = "auto";

				null !== p ? p.then( function() {
					w.scrollTo(0, 0);
					no_scroll();
					d.documentElement.style.overflow = "hidden";
					s.style.top = "-100%";
					luxeShow(s);
					w.requestAnimationFrame( function e(o) {
						var t = (o - r) / 250
						,   l = (ctop + 100) * t;
						s.style.top = l + "px", 1 < t ? s.style.top = ctop + 100 + "px" : w.requestAnimationFrame(e)
					}), setTimeout( function() {
						var e = d.querySelector("#sform .search-field");
						null !== s ? ( e.focus(), e.click() ) : console.error("Not Found: #sform .search-field");
					}, 200 );
				}) : alert( "This feature can't be used in this browser" );
			} else { 
				remove_ovlay();
				console.error("Not Found: #sform");
			}
		}

		var B = d.getElementById("bwrap")
		,   C = d.getElementById("close")
		,   layerClose = function(e) {
			var r = performance.now()
			,   u = 550;
			if( cpoint === "mom") {
				//s = d.getElementById("layer");
				if( null !== s ) {
					w.requestAnimationFrame(function e(t) {
						var n = (t - r) / 480
						,   v = s.scrollHeight
						,   a = 1 - v * n;
						s.style.marginTop = a + "px", 1 < n ? s.style.marginTop = 1 - v + "px" : w.requestAnimationFrame(e)
					});
				}
			} else if( cpoint === "mos") {
				d.documentElement.style.overflowX = "hidden";
				w.requestAnimationFrame(function e(t) {
					var n = (t - r) / 500
					,   a = 1 - n * c;
					sdbid.style.marginRight = a + "px", 1 < n ? (sdbid.style.marginRight = 1 - c + "px", sdbid.style.width = "100%") : w.requestAnimationFrame(e)
				});
			} else if( cpoint === "sns" ) {
				//s = d.getElementById("snslay");
				if( null !== s ) {
					var v = Number( s.style.marginTop.replace("px",""))
					,   l = 0 - s.offsetHeight;

					u = 680;
					w.requestAnimationFrame(function e(t) {
						var n = 1 - (t - r) / 220
						,   a = v * n;

						if( n < 0 ) a = 0 - v * ( 0 - n );
						s.style.marginTop = a + "px", a < l ? s.style.marginTop = l + "px" : w.requestAnimationFrame(e)
					});
				}
			} else if( cpoint === "toc") {
				//s = d.getElementById("layer");
				if( null !== s ) {
					w.requestAnimationFrame(function e(t) {
						var n = (t - r) / 360
						,   v = s.scrollHeight
						,   a = 1 - v * n;
						s.style.marginTop = a + "px", 1 < n ? s.style.marginTop = 1 - v + "px" : w.requestAnimationFrame(e)
					});
				}
			} else if( cpoint === "srh") {
				//s = d.getElementById("sform");
				if( null !== s ) {
					w.requestAnimationFrame(function e(t) {
						var n = (t - r) / 450
						,   a = n * d.documentElement.clientHeight + ctop + 100;
						s.style.top = a + "px", 1 < n ? s.style.top = "100%" : w.requestAnimationFrame(e)
					});
				}
			}

			setTimeout(function() {
				if( cpoint === "srh" ) {
					go_scroll();
					w.scrollTo( 0, scltop );
				}

SCRIPT;
			if( isset( $luxe['hide_mobile_sidebar'] ) ) {
				$ret .= 'if( cpoint === "mos" ) luxeHide( s );';
			}

			$ret .= <<< SCRIPT
				remove_ovlay();
			}, u );
		}
		if( null !== B ) B.onclick = layerClose;
		if( null !== C ) C.onclick = layerClose;
	}

SCRIPT;

			// モバイル用ラッパーメニュー ( Custom global nav )
			if( isset( $luxe['wrap_menu_used'] ) ) {
				$ret .= <<< SCRIPT
	try {
		!function() {
			var e = function() {
				var e = d.getElementById("layer");
				if( null !== e ) {
					for( var u = e.querySelectorAll(".gnavi-wrap-menu"), g = e.querySelectorAll(".has-wrap-menu"), t = (e.querySelectorAll("ul li.gl"), 0); t < g.length; t++ ) {
						var l = d.getElementById(g[t].id)
						,   n = l.getAttribute("data-child-id");

						if( null !== d.getElementById("menu-item-wrap-" + n ) ) {
							l.onclick = function(e) {
								for( var n = d.getElementById("menu-item-wrap-" + this.getAttribute("data-child-id")), t = 0; t < g.length; t++ ) {
									var l, i, s = g[t];
									if( "" !== s.style.paddingBottom && s !== this ) {
										i = parseFloat(s.style.paddingBottom.replace("px", ""));
										if( s.getBoundingClientRect().top + w.pageYOffset < e.pageY && void 0 !== (l = d.getElementsByTagName("html"))[0] && null !== l[0] ) {
											l[0].style.scrollBehavior = "auto", s.style.transition = "all .5s", s.style.paddingBottom = 0, scrollBy(0, 0 - i);
											setTimeout( function() {
												/*void 0 !== l[0] && null !== l[0] && ((l[0].style.scrollBehavior = ""), s.removeAttribute("style"));*/
												void 0 !== l[0] && null !== l[0] && (l[0].style.scrollBehavior = "");
											}, "500" );
										}
										w.removeEventListener("resize", luxeWrapListner, !1);
									}
								}
								if( null !== n ) {
									if( "" == n.style.cssText || -1 != n.style.transition.indexOf("none") ) {
										var m = this;
										(luxeWrapListner = function() {
											for( var e = 0; e < u.length; e++ ) {
												var t = d.getElementById(g[e].id)
												,   l = d.getElementById(u[e].id);
												null !== t && t.removeAttribute("style"), null !== l && l.removeAttribute("style");
											}
											n.style.transform = "scale(1, 1)", m.style.paddingBottom = n.childNodes[0].clientHeight + "px";
											w.addEventListener("resize", luxeWrapListner, !1);
										})();
									} else if( 0 !== e.target.querySelectorAll(".gnavi-wrap-menu").length ) {
										for( r = 0; r < u.length; r++ ) {
											o = d.getElementById(g[r].id), a = d.getElementById(u[r].id);
											null !== o && o.removeAttribute("style"), null !== a && a.removeAttribute("style");
										}
										w.removeEventListener("resize", luxeWrapListner, !1);
									}
								}
							};
						}
					}
				}
			}
			for( var t = 0; t < mom.length; t++ ) mom[t].addEventListener("click", e, !1);
		}();
	} catch (e) {
		console.error("mobile.wrapper.menu.error: " + e.message);
	}

SCRIPT;
			}
			$ret .= <<< SCRIPT
} catch(e) { console.error( 'mobile.nav.error: ' + e.message ); };

SCRIPT;
		}

		$site = array();
		$wt = $ca->thk_id();
		$wt_selecter  = $wt;
		$wta_selecter = "#" . $wt . " a";
		$foot_prefix  = '#wp-';
		$wt_array  = $ca->thk_hex_array();
		$wt_txt  = array();
		$ins_func = $ca->ins_luxe();
		$csstext_array = $ca->csstext_imp();
		$site_array = $ca->thk_site_name();

		$css_txt  = 'cssText';
		$wt_txt[] = THK_COPY;

		if( strlen( $wt ) === 3 ) {
			if( $wt[2] !== 'k' )     $broken = true;
			elseif( $wt[1] !== 'h' ) $broken = true;
			elseif( $wt[0] !== 't' ) $broken = true;
		}
		else {
			$broken = true;
		}

		foreach( $wt_array as $val ) $wt_txt[] = $ca->hex_2_bin( $val );
		if(
			( is_array( $wt_txt ) && count( $wt_txt ) >= 5  ) && (
				stripos( $wt_txt[0], 'http' )  === false ||
				stripos( $wt_txt[1], 'style' ) === false ||
				stripos( $wt_txt[2], 'luxeritas' ) === false
			)
		) $broken = true;

		foreach( $site_array as $val ) $site[] = $ca->hex_2_bin( $val );
		if( is_array( $site ) && count( $site ) >= 4 && stripos( $site[0], 'luxeritas' ) === false ) {
			$broken = true;
		}

		foreach( $csstext_array as $key => $val ) {
			$csstext[] = $ca->hex_2_bin( $val );
			if( stripos( $csstext[$key], '!;' ) === false ) {
				$broken = true;
			}
			else {
				$csstext[$key] = str_replace( '!;', $imp_close, $csstext[$key] );
			}
		}

		$ret .= <<< SCRIPT
try{
	var cint = false
	,   c = thk_get_yuv()
	,   i = '{$csstext[0]}'
	,   b = '{$csstext[1]}'
	,   l = '{$csstext[2]}color:' + c[0] + '{$imp_close}'
	,   s = d.createElement('style');

	!function() {
		var h = w.location.href
		,   x  = d.getElementById("{$wt_selecter}")
		,   a = d.querySelector("{$wta_selecter}")
		,   g = d.getElementById("{$site[2]}")
		,   t = ( g !== null ? g.children : '' )
		,   f = false
		,   k = false
		,   j = 0;

		for( j = 0; j < t.length; j++ ){
			if( t[j].tagName.toLowerCase() !== '{$site[2]}' ) t[j].parentNode.removeChild(t[j]);
		} g = d.getElementsByTagName("{$site[2]}"); t = ( typeof g[0] !== "undefined" ? g[0].children : '' );
		for( j = 0; j < t.length; j++ ){
			if( t[j].id.toLowerCase() !== '{$site[4]}' && t[j].id.toLowerCase() !== '{$site[3]}' && t[j].id.toLowerCase() !== '{$site[2]}-nav' ) t[j].parentNode.removeChild(t[j]);
		} t = d.body.children;
		for( j = 0; j < t.length; j++ ) {
			if( t[j].id.toLowerCase() === '{$site[2]}' ) k = true; continue;
		} if( k === true ) {
			for( j = 0; j < t.length; j++ ) {
				if( !t[j] ) {
					if( t[j].id.toLowerCase() === '{$site[2]}' ) {
						f = true; continue;
					} if( f === true ) {
						if( '#' + t[j].id.toLowerCase() !== '{$foot_prefix}{$site[2]}' ) t[j].parentNode.removeChild(t[j]);
						if( '#' + t[j].id.toLowerCase() === '{$foot_prefix}{$site[2]}' ) break;
					}
				}
			}
		} else {
			for( j = 0; j < t.length; j++ ) {
				if( !t[j] ) {
					if( t[j].className.toLowerCase() === 'container' ) {
						f = true; continue;
					} if( f === true ) {
						if( '#' + t[j].id.toLowerCase() !== '{$foot_prefix}{$site[2]}' ) t[j].parentNode.removeChild(t[j]);
						if( '#' + t[j].id.toLowerCase() === '{$foot_prefix}{$site[2]}' ) break;
					}
				}
			}
		} var id = "{$wt}";
		setInterval( function() {
			var n = luxeGetStyleValue;
			if( document.getElementById(id) !== null ) {
				var luxhtml = document.getElementById(id).innerHTML;
				if( luxhtml.indexOf('{$site[0]}') != -1 && luxhtml.indexOf('{$site[1]}') != -1 ) {
					if( d.getElementById(id).parentNode.getAttribute('id') === '{$site[3]}' ) {
						//x.css({'{$css_txt}': b + l });
						//a.css({'{$css_txt}': i + l });
						x.style.{$css_txt} = b + l;
						a.style.{$css_txt} = i + l;
					} else {
						{$ins_func};
					}
				} else {
					{$ins_func};
				}
			} else {
				{$ins_func};
			}

			var C = d.getElementById("{$site[2]}")
			,   A = d.getElementById("{$site[3]}")
			,   B = d.getElementsByTagName("{$site[2]}");

			if( C === null || B.length <= 0 || n(C, "display") == "none" || n(d.querySelector("{$site[2]}"), "display") == "none" ) {
				{$ins_func};
			}
			null !== C && "undefined" != typeof C && C.removeAttribute("style");
			null !== A && "undefined" != typeof A && A.removeAttribute("style");
			null !== B[0] && void 0 !== B[0] && B[0].removeAttribute("style");
		}, 1000 );
	}(); function {$ins_func} {
		if( cint === false ) {
			var t = '{$wt_txt[1]}'
			,   a = d.createElement('div');
			if( d.getElementById('{$site[3]}') !== null ) {
				var s = d.getElementById('{$site[3]}')
				var r = s.innerHTML;
				t = t.replace('><', '>' + r + '<');
				//d.getElementById('{$site[3]}').remove();
				//$('#{$site[3]}').remove();
				s.parentNode.removeChild(s);
			} a.innerHTML = t + b  + l + '{$wt_txt[2]}{$wt_txt[0]}{$wt_txt[3]}' + i  + l + '{$wt_txt[4]}'; d.body.appendChild( a );
			cint = true;
		}
	} function thk_dummy(){}

	function thk_get_yuv() {
		var yuv = 255
		,   k = null
		,   e = ""
		,   i = "rgba(0, 0, 0, 0)"
		,   h = "transparent"
		,   g = "none"
		,   j = "background-color"
		,   n = luxeGetStyleValue
		,   m = n( d.body, j )
		,   c = n( d.getElementById("{$site[2]}"), j )
		,   a = n( d.getElementById("{$site[3]}"), j )
		,   b = n( d.getElementsByTagName("{$site[2]}")[0], j);

		if (a != i && a != h && a != g) {
			k = a
		} else {
			if (b != i && b != h && b != g) {
				k = b
			} else {
				if (c != i && c != h && c != g) {
					k = c
				} else {
					k = m
				}
			}
		}
		if( k != i && k != h && k != g ) {
			if( "undefined"!=typeof k || null!=typeof k ) {
				e = k.split(",")
			} else {
				e = ["255", "255", "255", "0"];
				console.warn( "Yuv k.split" );
			}
		} else {
			e = ["255", "255", "255", "0"]
		}
		if( e.length >= 3 ) {
			e[0] = e[0].replace(/rgba\(/g, "").replace(/rgb\(/g, "");
			e[1] = e[1].replace(/ /g, "");
			e[2] = e[2].replace(/\)/g, "").replace(/ /g, "");
			yuv = 0.299 * e[0] + 0.587 * e[1] + 0.114 * e[2]
		}
		return yuv >= 128 ? ['black', 'white'] : ['white', 'black']
	};
	s.id = '{$wt}c';

SCRIPT;
/*
			$ret .= <<< SCRIPT
	w.addEventListener( "load", function() {
		function e( e, o ) {
			e && e.id ? console.log( o + "prev", e.id ) : console.log( o + "prev", e ),
			null !== e && e.style && ( e.style.display = "none" );
		} function n() {
			return {id:"dummy",className:"dummy"}
		}

SCRIPT;
		if( isset( $luxe['bootstrap_footer'] ) && $luxe['bootstrap_footer'] === 'in' ) {
			$ret .= <<< SCRIPT
		var i = ( o = d.getElementById("footer") ).previousElementSibling
		,   t = o.nextElementSibling;
		if( "primary" !== i.id ) {
			null === i && ( i = n() ), null === t && ( t = n() ), e( i, "f" );
		} else {
			var o;
			i = ( o = d.getElementById("wp-footer") ).previousElementSibling,
			t = o.nextElementSibling;
			"container" !== i.className && ( null === i && ( i = n() ), e( i, "w" ) )
		}

SCRIPT;
		}
		else {
			$ret .= <<< SCRIPT
		var l = "footer", i = "wp-footer"
		,   o = ( s = d.getElementById(l) ).previousElementSibling
		,   t = s.nextElementSibling;
		if( "container" !== o.className || t.id !== i ) {
			null === o && ( o = n() ), null === t && ( t = n() ), e( o, "f" );
		} else {
			var s;
			o = ( s = d.getElementById(i) ).previousElementSibling, t = s.nextElementSibling;
			o.id !== l && ( null === o && ( o = n() ), e( o, "w" ) )
		}

SCRIPT;
		}

		$ret .= <<< SCRIPT
	}, !1 );

SCRIPT;
*/
		if( $_is['customize_preview'] === false ) {
			$ret .= <<< SCRIPT
	s.innerText = '{$imp}color:' + c[0] + '{$imp_close}}';
	document.getElementsByTagName('head')[0].appendChild( s );
	setInterval( function() {
		if( document.getElementById(s.id) === null ) {
			document.getElementsByTagName('head')[0].appendChild( s );
		}
	}, 1000 );
SCRIPT;
		}

		$ret .= <<< SCRIPT
} catch(e) {
	console.error( 'html.body.error: ' + e.message );
	//var c = [], n = d.body; n.parentNode.removeChild( n );
}
};

!function(d) {
	"readyState" in d || (d.readyState = "loading", d.addEventListener("DOMContentLoaded", function e() {
		d.readyState = "interactive", this.removeEventListener("DOMContentLoaded", e, !1)
	}, !1 ), d.addEventListener("load", function e() {
		d.readyState = "complete", this.removeEventListener("load", e, !1)
	}, !1 ))
}(document);

var luxeDOMContentLoadedCheck = function(e) {
	var d = document;
	"loading" !== d.readyState && "uninitialized" !== d.readyState && typeof luxeDOMContentLoaded == "function" ? (console.log("readyState: " + d.readyState), luxeDOMContentLoaded()) : window.setTimeout(function() {
		luxeDOMContentLoadedCheck(e)
	}, 100 )
};

luxeDOMContentLoadedCheck();

SCRIPT;
		if( $broken !== false ) {
			if( $_is['admin'] === true ) {
				return false;
			}
			else {
				wp_die( __( 'This theme is broken.', 'luxeritas' ) );
			}
		}
		if( $_is['customize_preview'] === true ) {
			return str_replace( $ins_func . ';', '', $ret );
		}

		return $ret;
	}

	public function create_luxe_various_script() {
		global $luxe, $_is;


		$ret = '';
		//$home = THK_HOME_URL;

		$ret .= <<< SCRIPT
try {
	var jQeryCheck2 = function(e) {
		window.jQuery ? e(jQuery, window, document) : window.setTimeout(function() {
			jQeryCheck2(e)
		}, 100)
	};
	jQeryCheck2( function($, w, d) {
$( function(){

var luxeListenScroll = function(e) {
	// スクロールイベント登録
	/*
	w.addEventListener( "scroll", e, luxePassiveSupported() ? { passive: true } : false );
	w.addEventListener( "touchmove", e, luxePassiveSupported() ? { passive: true } : false );
	*/
    	w.addEventListener( "scroll", e, !!luxePassiveSupported() && { passive: !1 } );
	w.addEventListener( "touchmove", e, !!luxePassiveSupported() && { passive: !1 } );
}, luxeDetachScroll = function(e) {
	// スクロールイベント解除
	/*
	w.removeEventListener( "scroll", e, luxePassiveSupported() ? { passive: true } : false );
	w.removeEventListener( "touchmove", e, luxePassiveSupported() ? { passive: true } : false );
	*/
    	w.removeEventListener( "scroll", e, !!luxePassiveSupported() && { passive: !1 } );
	w.removeEventListener( "touchmove", e, !!luxePassiveSupported() && { passive: !1 } );
}, luxeGetStyleValue = function(e, t) {
	// Get CSS
	return e && t ? w.getComputedStyle(e).getPropertyValue(t) : null
}, luxeRemoveClass = function(t, s) {
	if( t.classList.contains(s) == true ) t.classList.remove(s);
/*
	for (var e = s.trim().split(/\s+/), i = (t.getAttribute("class") || "").trim().split(/\s+/), l = i.length; l--;) ~e.indexOf(i[l]) && i.splice(l, 1);
	t.setAttribute("class", i.join(" "))
*/
}, luxeAddClass = function(t, s) {
	if( t.classList.contains(s) == false ) t.classList.add(s);
/*
	for (var e, i = s.trim().split(/\s+/), l = (t.getAttribute("class") || "").trim(), r = l.split(/\s+/), a = i.length; a--;) ~r.indexOf(i[a]) && i.splice(a, 1);
	e = i.join(" "), l.length ? e.length ? t.setAttribute("class", l + " " + e) : t.setAttribute("class", l) : t.setAttribute("class", e)
*/
};

SCRIPT;

		$ret .= <<< SCRIPT
var offset;
try{ /* offset.set */
	var offsetCalc = function() {
		offset = 0;
		if( typeof skeepid !== "undefined" && null !== skeepid ) {
			if( luxeGetStyleValue( skeepid, "max-width" ) !== "32767px" ) {

SCRIPT;
		/* スムーススクロール と 追従スクロール の offset 値 */
		if( isset( $this->_depend['stickykit'] ) ) {
			if( isset( $luxe['head_band_visible'] ) && isset( $luxe['head_band_fixed'] ) ) {
				// 帯メニュー固定時の高さをプラス
				$ret .= "offset += $('.band').height();";
			}

			if( isset( $luxe['global_navi_sticky'] ) && $luxe['global_navi_sticky'] !== 'none' && $luxe['global_navi_sticky'] !== 'smart' ) {
				// グローバルナビ固定時の高さをプラス
				$ret .= "if( d.getElementById('nav') !== null ) {";
				$ret .= "offset += d.getElementById('nav').offsetHeight;";
				$ret .= "}";
			}
		}
		// アドミンバーの高さをプラス
		$ret .= <<< SCRIPT
				if( d.getElementById('wpadminbar') !== null ) {
					offset += d.getElementById('wpadminbar').offsetHeight;
				}
			}
		}
	}
} catch(e) { console.error( 'offset.set.error: ' + e.message ); }

SCRIPT;

		/* スムーススクロール ( LazyLoad 対応 ) */
		if( !isset( $luxe['smooth_scroll_off'] ) && isset( $luxe['jquery_load'] ) && $luxe['jquery_load'] !== 'none' ) {
			$ret .= <<< SCRIPT
Array.prototype.forEach.call( d.querySelectorAll("a[href^='#']"), function(e) {
	e.addEventListener( "click", function(t) {
		var o = this.getAttribute("href").trim()
		,   b = d.getElementById( o.slice(1) );

		if( o === "#" ) return;

		if( 0 < (a = $(o)).length ) {
			t.preventDefault();
			$("html, body").animate(
				{
					scrollTop: a.offset().top
				}, {
					duration: 600,
					step: function(t, o) {
						var e = a.offset().top;
						o.end !== e && (o.end = e);
					},
				}
			);

		/* jQuery 使わないバージョン ( IE 未対応 )
		if( "#" !== o && null !== b ) {
			t.preventDefault();

			w.requestAnimationFrame( function t(o) {
				var e = b.getBoundingClientRect().top + w.scrollY;
				w.scrollTo({ top: e, behavior: "smooth" });

			        setTimeout( function() {
					var a = b.getBoundingClientRect().top + w.scrollY;
					w.scrollTo({ top: a, behavior: "auto" });
					a !== e && w.requestAnimationFrame(t);
			        }, "600");
			});
			*/

			w.history && w.history.pushState && history.pushState("", d.title, o);

SCRIPT;

		if( !isset( $luxe['smooth_scroll_hash'] ) ) $ret .= 'location.hash = "";';

		$ret .= <<< SCRIPT
		}
	});
});

SCRIPT;
			if( !isset( $luxe['smooth_scroll_hash'] ) ) {
			/* ブラウザの履歴からアンカーリンクの # 消す */
			$ret .= <<< SCRIPT
w.history.replaceState(null, "", location.pathname + location.search);

SCRIPT;
			}
		}

$ret .= "try{\n"; /* stick.watch */

		/* 追従スクロール */
		if( isset( $this->_depend['stickykit'] ) ) {
			$stick_init_y = 0;
			$stick_init_y = isset( $luxe['global_navi_scroll_up_sticky'] ) ? 0 : 'offset';

			$ret .= <<< SCRIPT
	var stkwch  = false
	,   navid   = d.getElementById("nav")
	,   mob     = d.querySelector(".mobile-nav")
	,   skeep   = $("#side-scroll")
	,   skeepid = d.getElementById("side-scroll")
	,   sHeight = 0;

	offsetCalc();

	function stick_primary( top ) {
		if( skeep.css('max-width') !== '32767px' ) {

SCRIPT;
			// CSS の親要素に transeform が使われてると position: fixed が効かなくなってスクロール追従サイドバーが動かくなくなるので、その対策
			if( isset( $luxe['opening_anime'] ) && ( $luxe['opening_anime'] === 'stretch_sideways' || $luxe['opening_anime'] === 'stretch_vertically' ) ) {
				$ret .= 'd.getElementById("primary").style.animation="none"';
			}

			$ret .= <<< SCRIPT
			//skeep.stick_in_parent({parent:'#primary',offset_top:top,spacer:0,inner_scrolling:0,recalc_every:1});
			skeep.stick_in_parent({parent:'#primary',offset_top:top,spacer:0,inner_scrolling:0});
		}
	} stick_primary( {$stick_init_y} );

	// 非同期系のブログパーツがあった場合に追従領域がフッターを突き抜けてしまうのでその予防策
	// ＆ 追従領域がコンテンツより下にあった場合にフッターまでスクロールできない現象の対策
	function stick_watch() {
		var i		// setInterval
		,   s		// 現在の #side の高さ
		,   j = 0;	// インターバルのカウンター

		if( d.getElementById("sidebar") !== null ) {
			i = setInterval( function() {
				if( luxeGetStyleValue( skeepid, "max-width") !== "32767px" ) {
					if( d.getElementById("side") !== null ) {
						if( typeof d.getElementById("side").children[0] !== "undefined" ) {
							// #side aside の高さ（こっち優先）
							s = d.getElementById('side').children[0].offsetHeight
						} else {
							// #side の高さ
							s = d.getElementById("side").offsetHeight;
						}
					}

					if( s >= sHeight ) {
						sHeight = s;
						d.getElementById("sidebar").style.minHeight=s + "px";
						stick_primary( {$stick_init_y} );
						//skeep.trigger('sticky_kit:recalc');
					}

					++j;
					if( j >= 100 ) {
						clearInterval( i ); // PC 表示の時に30秒間だけ監視( 300ms * 100 )
					}
				}
			}, 300);
		}
	}

	if( luxeGetStyleValue( skeepid, "max-width" ) !== '32767px' ) {
		stick_watch();
	} var S = false	// リサイズイベント負荷軽減用
	, skprsz = ( "resize", function() {
		if( S === false ) {
			requestAnimationFrame( function() {
				S = false;
				if( d.getElementById('sidebar') !== null ) {
					sHeight = 0;
					if( skeepid !== null ) skeepid.style.minHeight = "";
					if( luxeGetStyleValue( skeepid, "max-width" ) !== "32767px" ) {
						stick_watch();
					} else {
						skeep.trigger("sticky_kit:detach");
					}
				}
			});
			S = true;
		}
	});

	// リサイズイベント登録
	w.addEventListener( "resize", skprsz, !1 );

SCRIPT;
		}

		/* グローバルナビTOP固定 */
		if(
			isset( $this->_depend['stickykit'] ) && isset( $luxe['global_navi_visible'] ) &&
			isset( $luxe['global_navi_sticky'] ) && $luxe['global_navi_sticky'] !== 'none'
		) {
			$nav_sticky = <<< NAV_STICKY
	top = 0;
	if( bar !== null ) {
		top += bar.offsetHeight;
	}

NAV_STICKY;
			//if( isset( $luxe['head_band_visible'] ) && isset( $luxe['head_band_fixed'] ) ) {
				// 帯メニュー固定時の高さをプラス
				//$nav_sticky .= "top += $('.band').height();";
			//}

			$nav_sticky .= <<< NAV_STICKY
	thk_unpin( navid );
	mnav = luxeGetStyleValue(mob, "display");
	e = d.getElementById("nav");
	r = ( e !== null ? e.getBoundingClientRect() : '' );
	y = w.pageYOffset;
	hidfgt = r.top + y, null !== bar && ( hidfgt -= bar.offsetHeight ); // #nav のY座標 (この位置からナビ固定)
	/*
	hidfgt = r.top + y;	// #nav のY座標 (この位置からナビ固定)
	if( d.getElementById('wpadminbar') !== null ) hidfgt -= d.getElementById('wpadminbar').offsetHeight;
	*/
	navhid = top - ( e !== null ? e.offsetHeight : 0 ) - 1; // グローバルナビの高さ分マイナス(リサイズイベント用)
	T = false;

NAV_STICKY;

			// グローバルナビを上スクロールの時だけ固定する場合
			if( isset( $luxe['global_navi_scroll_up_sticky'] ) ) {

				$gnav_progress_height = 0;
				if( isset( $luxe['global_navi_scroll_progress'] ) && isset( $luxe['gnavi_border_bottom_width'] ) ) {
					$gnav_progress_height = (int)$luxe['gnavi_border_bottom_width'] + 1;
				}

				$nav_sticky .= <<< NAV_STICKY
	if( skeepid !== null ) skeepid.style.transition = "all .5s ease-in-out";
	hidfgb = hidfgt + ( e !== null ? e.offsetHeight : 0 );	// 上スクロールの時だけ固定する場合は、#nav の bottom 部分を Y座標にする

	if( y > hidfgb ) {
		skeep.trigger("sticky_kit:detach");
		stick_primary( top );
		thk_pin( e, navhid, "" );
		//luxeAddClass(e, "pinf");	// pin first の略。最初の一発目の position:fixed 挿入時に上スクロール判定されるために不自然な動きになるのを防ぐ
	}

	stkeve = ("scroll", function(E) {
		if( T === false ) {
			requestAnimationFrame( function() {
				T = false;

				//E.preventDefault();
				//E.stopPropagation();
				//E.stopImmediatePropagation();
				p = d.querySelector(".pin")
				y = w.pageYOffset;

				var difpos = nowpos - y; // スクロールの上下判定 ( difpos > 0 ) で上、( difpos <= 0 ) で下
				if( difpos === 0 ) difpos = opos;
				nowpos = y;
				opos = difpos;

				navhid = top - e.offsetHeight - 1 + {$gnav_progress_height}; // ナビの高さ分マイナス(スクロールイベント用)

				if( y <= hidfgb && difpos <= 0 ) {
					thk_unpin( e );
				} else if( y <= hidfgt && difpos > 0 ) {
					thk_unpin( e );
				} else if( p == null && y > hidfgb ) {
					skeep.trigger("sticky_kit:detach");
					stick_primary( top );
					thk_pin( e, navhid, "" );
					//luxeAddClass(e, "pinf");
				} else if( p != null ) {
					var sdscrl = d.getElementById("side-scroll")
					,   sdstop = sdscrl != null ? v( sdscrl, "top") : void 0;

					if( difpos > 5 ) { // スクロールアップ時にナビ表示
						if( v(e, "top") !== top + "px" ) {
							//if( d.querySelector(".pinf") != null ) luxeRemoveClass(e, "pinf");
							thk_pin( e, top );
							// 追従スクロールの高さ調整
							if( sdscrl != null ) {
								if( sdstop === top + "px" && v( skeepid, "max-width" ) !== "32767px" ) {
									if( skeepid !== null ) skeepid.style.top = offset + "px";
									//skeep.trigger("sticky_kit:recalc");
								}
							}
						}
					} else if( difpos < -5 ) { // スクロールダウンでナビを画面上に隠す
						if( v(e, "top") !== navhid + "px" ) { // !== navhid だとカクッとなるので条件厳しく
							thk_pin( e, navhid );
							// 追従スクロールの高さ調整
							if( sdscrl != null ) {
								if( sdstop !==  top + "px" && v( skeepid, "max-width" ) !== "32767px" ) {
									if( skeepid !== null ) skeepid.style.top = 0;
									//skeep.trigger("sticky_kit:recalc");
								}
							}
						}
					}
				} else if( p == null && y <= hidfgt ) {
					if( v(e, "top") !== navhid + "px" ) {
						if( v( skeepid, "max-width" ) !== "32767px" ) {
							if( skeepid !== null ) skeepid.style.top = 0;
							//skeep.trigger("sticky_kit:recalc");
						}
						thk_pin( e, navhid );
					}
				}
			});
			T = true;
		}
	});

NAV_STICKY;
			}
			// グローバルナビを常に固定する場合
			else {
				$nav_sticky .= <<< NAV_STICKY
	if( y > hidfgt ) {
		thk_pin( e, top, '' );
	}

	//T = false;
	stkeve = ( "scroll", function(){
	//	if( T === false ) {
			//requestAnimationFrame( function() {
			//	T = false;
				//p = $('.pin')[0];
				p = d.querySelector(".pin")

				if( w.pageYOffset <= hidfgt ) {
					thk_unpin( e );
				} else if( p === null ) {
					thk_pin( e, top, '' );
				}
			//});
			//T = true;
	//	}
	});

NAV_STICKY;
			}

			$block_if_else = "
				{$nav_sticky};
				if( mnav !== 'none' ) {
					luxeListenScroll( stkeve );
				} else {
					thk_unpin( e );
					luxeDetachScroll( stkeve );
				}
			\n";

			$stick = '';

			if( $luxe['global_navi_sticky'] === 'smart' ) {
				$stick .= $block_if_else;
			} elseif( $luxe['global_navi_sticky'] === 'pc' ) {
				$stick .= str_replace( "!== 'none'", "=== 'none'", $block_if_else );
			} else {
				$stick .= $nav_sticky . 'luxeListenScroll( stkeve );';
			}

			$nav_width = isset( $luxe['bootstrap_header'] ) && $luxe['bootstrap_header'] === 'in' ? 'd.getElementById("primary").clientWidth + "px"' : '"100%"';

			$ret .= <<< SCRIPT
	var e, r, p, y, T
	,   top = 0
	,   navhid = 0
	,   mnav
	,   hidfgt
	,   hidfgb
	//,   resz = false	// リサイズイベントかどうかの判別
	,   nowpos = 0		// スクロール位置
	,   opos = -1
	/* ,   stktim = null	// スクロールイベント負荷軽減用 */
	/* ,   stkint = 200	// インターバル(PC では少し速く:100、モバイルでは少し遅く:200) */
	,   stkeve
	,   bar = d.getElementById('wpadminbar')
	,   v = luxeGetStyleValue;

	function thk_nav_stick() {
		{$stick}
	}

	thk_nav_stick();

	var R = false;
	w.addEventListener( "resize", function() {
		if( R === false ) {
			requestAnimationFrame( function() {
				R = false;
				//resz = true;
				//thk_nav_stick();
				//e.style.width = "";
				e.style.width = {$nav_width}
				offsetCalc();
				//resz = false;
			});
			R = true;
		}
	}, !1 );

	function thk_pin( o, sp, trs, cls ) {
		var s = o.style;
		//if( typeof trs === "undefined" || trs === null || trs === "" ) var trs = "all .5s ease-in-out";
		if( typeof trs === "undefined" ) var trs = "all .5s ease-in-out";
		if( typeof cls === "undefined" ) var cls = "pin";

		s.transition = trs,
SCRIPT;

		if( isset( $luxe['head_band_visible'] ) && isset( $luxe['head_band_fixed'] ) ) {
			if( isset($luxe['bootstrap_header']) && $luxe['bootstrap_header'] === 'out' ) {
				$ret .= 'd.getElementById("head-band") !== null && (s.top = sp + d.getElementById("head-band").offsetHeight + "px"),';
			}
			else {
				$ret .= 'd.getElementById("head-band-in") !== null && (s.top = sp + d.getElementById("head-band-in").offsetHeight + "px"),';
			}
		}
		else {
			$ret .= 's.top = sp + "px",';
		}

		$ret .= <<< SCRIPT
		s.position = "fixed",
		//s.width = o.clientWidth + 'px'
		s.width = {$nav_width}

		luxeAddClass( o, cls );
		//$('body').css('marginTop', d.getElementById('nav').offsetHeight + 'px');
		d.body.style.marginTop = o.offsetHeight + "px";
	} function thk_unpin( o ) {
		/* o.css({ 'transition': '', 'top': sp + '', 'position': '' }); */
		o.removeAttribute("style");
		luxeRemoveClass( o, "pin" );
		d.body.removeAttribute("style");
		//$('body').removeAttr('style');
	}

SCRIPT;
		}

$ret .= "} catch(e) { console.error( 'stick.watch.error: ' + e.message ); }\n";

		if( $luxe['gallery_type'] === 'tosrus' && $_is['customize_preview'] === false ) {
			/* Tosrus */
			$ret .= <<< SCRIPT
try{ /* tosrus */
	$("a[data-rel^=tosrus]").tosrus({
		caption : {
			add : true,
			attributes : ["data-title","title", "alt", "rel"]
		},
		pagination : {
			add : true,
		},
		infinite : true,
		wrapper : {
			onClick: "close"
		}
	});
} catch(e) { console.error( 'tosrus.error: ' + e.message ); }

SCRIPT;
		}

		if( $luxe['gallery_type'] === 'lightcase' && $_is['customize_preview'] === false ) {
			/* Lightcase */
			$ret .= "try{\n"; /* lightcase */
			$ret .= "$('a[data-rel^=lightcase]').lightcase();\n";
			$ret .= "} catch(e) { console.error( 'lightcase.error: ' + e.message ); }\n";
		}

		if( $luxe['gallery_type'] === 'fluidbox' && $_is['customize_preview'] === false ) {
			/* Fluidbox */
			$ret .= "try{\n"; /* fluidbox */
			$ret .= "$(function () {;\n";
			$ret .= "$('.post a[data-fluidbox]').fluidbox();;\n";
			$ret .= "});;\n";
			$ret .= "} catch(e) { console.error( 'fluidbox.error: ' + e.message ); }\n";
		}

		if( isset( $luxe['head_band_visible'] ) && isset( $luxe['head_band_search'] ) ) {
			/* 帯メニュー検索ボックスのサイズと色の変更 */
			$ret .= <<< SCRIPT
try { /* head.band.search */
	var subm = d.querySelector("#head-search button[type=submit]")
	,   text = d.querySelector("#head-search input[type=text]")
	,   menu = d.querySelector(".band-menu ul");
	"block" != luxeGetStyleValue(text, "display") && (text.onclick = function() {
		subm.style.color = "#888",
		menu.style.right = "210px",
		text.style.width = "200px",
		text.style.color = "#000",
		text.style.backgroundColor = "rgba(255, 255, 255, 1.0)",
		text.setAttribute("placeholder", "")
	}, text.onblur = function() {
		subm.removeAttribute("style"),
		menu.removeAttribute("style"),
		text.removeAttribute("style"),
		text.setAttribute("placeholder", "Search ...")
	})
} catch (e) {
	console.error("head.band.search.error: " + e.message)
}
SCRIPT;
		}

		/* グローバルナビのスクロールプログレスバー */
		if( isset( $luxe['global_navi_scroll_progress'] ) ) {
			if( isset( $luxe['global_navi_progress_rate'] ) && $luxe['global_navi_progress_rate'] === 'post' ) {
				/* 投稿で100% */
				$ret .= <<< SCRIPT
var LuxeProgressBar = function () {
	var e, t, l, n, u
	,   r = window
	,   i = document
	,   s = i.getElementById("core");
	null === s && (s = i.getElementById("list")),
		null === s && (s = i.getElementById("main")),
		null !== s &&
			((e = i.documentElement.clientHeight),
			(t = r.scrollY || r.pageYOffset),
			(l = s.clientHeight),
			(n = (t / (s.offsetTop + l - e)) * 100),
			null !== (u = i.getElementById("gnav-progress")) && (n < 100 && 0 < n ? u.setAttribute("value", n) : 100 <= n ? u.setAttribute("value", 100) : u.setAttribute("value", 0)));
};
SCRIPT;
			}
			else {
				/* ページ全体で100% */
				$ret .= <<< SCRIPT
var LuxeProgressBar = function () {
	var e = d.documentElement.clientHeight
	,   n = ((w.scrollY || w.pageYOffset) / (d.body.clientHeight - e)) * 100
	,   u = d.getElementById("gnav-progress");
	null !== u && (n < 100 && 0 < n ? u.setAttribute("value", n) : 100 <= n ? u.setAttribute("value", 100) : u.setAttribute("value", 0));
};
SCRIPT;
			}

			/* スクロールプログレスバーのリスナー */
			$ret .= <<< SCRIPT
w.addEventListener("scroll", LuxeProgressBar, !1), w.addEventListener("resize", LuxeProgressBar, !1);
//w.onscroll = LuxeProgressBar, w.onresize = LuxeProgressBar;

SCRIPT;
		}

		if( isset( $luxe['autocomplete'] ) ) {
			/* 検索ボックスのオートコンプリート (Google Autocomplete) */
			$ret .= <<< SCRIPT
		!function() {
			try{ /* autocomplete */
			$('.search-field, .head-search-field').autocomplete({
				source: function(request, response){
					$.ajax({
						type: "GET",
						url: "//www.google.com/complete/search",
						data: {
							hl: 'ja',
							ie: 'utf_8',
							oe: 'utf_8',
							client: 'firefox', // For XML: use toolbar, For JSON: use firefox, For JSONP: use firefox
							q: request.term
						},
						dataType: "jsonp",
					}).then( function(d) {
						response(d[1]);
					});
				},
				delay: 300
			});
			} catch(e) { console.error( 'autocomplete.error: ' + e.message ); }
		}();

SCRIPT;
		}

		if( isset( $this->_depend['autosize'] ) ) {
			/* コメント欄の textarea 自動伸縮 */
			$ret .= "try{\n"; /* comment.autosize */
			$ret .= "autosize($('textarea#comment'));\n";
			$ret .= "} catch(e) { console.error( 'comment.autosize.error: ' + e.message ); }\n";
		}

		$ret .= <<< SCRIPT
});
});
} catch (e) {
	console.error("jquery.check2.error: " + e.message)
};

/* IE8以下、Firefox2 以下で getElementsByClassName 使えない時用 */
/*
if (typeof(document.getElementsByClassName) == "undefined") {
	document.getElementsByClassName = function(o) {
		var q = new Array(),
			p = document;
		if (p.all) {
			var m = p.all
		} else {
			var m = p.getElementsByTagName("*")
		}
		for (var l = j = 0, k = m.length; l < k; l++) {
			var n = m[l].className.split(/\s/);
			for (var i = n.length - 1; i >= 0; i--) {
				if (n[i] === o) {
					q[j] = m[l];
					j++;
					break
				}
			}
		}
		return q
	}
};
*/

SCRIPT;
		return $ret;
	}

	/*
	------------------------------------
	 SNS のカウント数読み込み
	------------------------------------ */
	public function create_sns_count_script() {
		global $luxe;
		$ret = '';
		$ajaxurl = admin_url( 'admin-ajax.php' );

		$ret .= <<< SCRIPT
var luxeGetSnsCount = function(h, i, j, k, f) {
	var g = h.location.search
	,   q = document.querySelectorAll(j);

	if( window.jQuery && q.length > 0 ) {
		jQuery.ajax({
			type: "GET",
			url: "{$ajaxurl}",
			data: {
				action: "thk_sns_real",
				sns: i,
				url: f
			},
			dataType: "text",
			async: true,
			cache: false,
			timeout: 30000
		}).then( function(b) {
			if( isFinite(b) && b !== "" ) {
				for( r = 0; r < q.length; r++ ) {
					q[r].textContent = String(b).replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,")
				}
			} else {
				if( typeof(b) === "string" && b !== "" ) {
					var a = b.slice(0, 11);
					for( r=0; r<q.length; r++ ) q[r].textContent = a;
				} else {
					for( r=0; r<q.length; r++ ) q[r].textContent = "!";
				}
			}
		}, function() {
			q.textContent = "!"
		})
	} else {
		if( q != null ) q.textContent = "?";
	}
};
!function(b, d) {
	b.addEventListener("load", function c() {
		var m = d.getElementsByClassName("sns-count-true")
		,   g = d.getElementsByClassName("sns-cache-true")
		,   r = d.getElementsByClassName("feed-count-true");

		if( m.length > 0 || g.length > 0 ) {
			var k = g.length > 0 ? g[0] : m[0]
			,   f = k.getAttribute("data-luxe-permalink")
			,   e = k.getAttribute("data-incomplete")
			,   l = e != null ? e.split(",") : "";

			if( g.length > 0 && window.jQuery ) {
				var s = jQuery.ajax({
					type: "POST",
					url: "{$ajaxurl}",
					data: {
						action: "thk_sns_cache",
						url: f
					},
					dataType: "text",
					async: true,
					cache: false
				})
				,   h = setInterval( function() {
					if( s && s.readyState > 0 ) {
						s.abort();
						clearInterval(h)
					}
				}, 500 )
			}

			var q = {
				t: ".pinit-count",
				h: ".hatena-count",
				p: ".pocket-count",
				f: ".facebook-count"
			};

			Object.keys(q).forEach( function(i) {
				var j = this[i];
				if( g.length < 1 || l.indexOf(i) >= 0 ) {
					luxeGetSnsCount(b, i, j, m, f)
				}
			}, q )
		}
		if( r.length > 0 && d.getElementsByClassName("feed-cache-true").length < 1 ) {
			luxeGetSnsCount(b, "r", ".feedly-count", r, f)
		}
		this.removeEventListener("load", c, !1);
	}, !1 );
}(window, document);

SCRIPT;
		return $ret;
	}
}
