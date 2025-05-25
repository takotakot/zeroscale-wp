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

if( function_exists( 'luxe_unicode_decode' ) === false ):
function luxe_unicode_decode( $str ) {
	return preg_replace_callback( "/((?:[^\x09\x0A\x0D\x20-\x7E]{3})+)/", 'decode_callback', $str );
}
endif;

if( function_exists( 'decode_callback' ) === false ):
function decode_callback( $matches ) {
	$escaped = '';
	$char = mb_convert_encoding( $matches[1], 'UTF-16', 'UTF-8' );
	for( $i = 0, $l = strlen($char); $i < $l; $i += 2 ) {
		$escaped .=  "\u" . sprintf( "%02x%02x", ord( $char[$i] ), ord( $char[$i+1] ) );
	}
	return $escaped;
}
endif;

if( function_exists( 'create_ladmin_script' ) === false ):
function create_ladmin_script() {
	$scrollbooster = TDEL . '/js/scrollbooster.min.js?v=' . $_SERVER['REQUEST_TIME'];
	$css = TDEL . '/css/ladmin.css?v=' . $_SERVER['REQUEST_TIME'];
	$home_URL = THK_HOME_URL;
	$logout_msg = luxe_unicode_decode( __( 'Log out ?', 'luxeritas' ) );

	$ret = <<< SCRIPT
!function(e){
	if (window != window.parent) {
		var t = e.getElementById("ladmin")
		,   n = {
			prev: e.getElementById("data-prev"),
			next: e.getElementById("data-next")
		}
		,   o = e.querySelectorAll('a:not([href^="{$home_URL}"])')
		,   r = e.querySelectorAll('[href^="{$home_URL}"]')
		,   a = e.querySelectorAll('[href^="{$home_URL}wp-admin/"]')
		,   l = "respond_frame=1";

		function c(e) {
			e.preventDefault()
		}

		function cc(e) {
			e.style.pointerEvents = "none", e.style.cssText += "cursor: not-allowed!important"
		}

		null !== t && (t.style.display = "none");
		Array.prototype.forEach.call(o, function(e) {
			cc(e)
		});
		Array.prototype.forEach.call(a, function(e) {
			cc(e)
		});
		Array.prototype.forEach.call(r, function(e) {
			-1 != e.href.indexOf("?") ? e.href += "&" + l : e.href += "?" + l
			if( -1 != e.href.indexOf("action=") ) cc(e)
		});
		Object.keys(n).forEach(function(e) {
			var t = this[e];
			null !== t && (-1 != t.dataset[e].indexOf("?") ? t.dataset[e] += "&" + l : t.dataset[e] += "?" + l)
		}, n);

		var s = 0
		,   i = "scrollingElement" in e ? e.scrollingElement : e.body;

		i.addEventListener("mousedown", function() {
			s = 0
		}, !1);
		i.addEventListener("mousemove", function() {
			s = 1
		}, !1);

		d = function() {
			new ScrollBooster({
				viewport: e.body,
				//mode: "y",
				//friction: "0.85",
				friction: "0",
				bounce: !0,
				textSelection: !1,
				onUpdate: function(e) {
					i.scrollTop -= e.dragOffsetPosition.y;
					i.scrollLeft -= e.dragOffsetPosition.x;
					1 === s ? i.addEventListener("click", c, !1) : i.removeEventListener("click", c, !1);
				}
			})
		}, p = e.createElement("script"), f = e.getElementsByTagName("script")[0], p.async = 1, p.onload = p.onreadystatechange = function(e, t) {
			!t && p.readyState && !/loaded|complete/.test(p.readyState) || (p.onload = p.onreadystatechange = null, p = void 0, t || d())
		}, p.src = "{$scrollbooster}", f.parentNode.insertBefore(p, f)
	}
	else {
		var n = e.createElement('link');
		n.async = !0;
		n.defer = !0;
		n.rel  = 'stylesheet';
		n.href = '{$css}';
		if( e.getElementsByTagName('head')[0] !== null ) {
			e.getElementsByTagName('head')[0].appendChild( n );
		}

		var a = e.querySelectorAll('[href*="wp-login.php?action=logout"]');
		Array.prototype.forEach.call(a, function(e) {
			e.onclick = function() {
				if( !window.confirm("{$logout_msg}") ) {
					return false;
				}
			}
		});

		var o = e.getElementById("ladmin-o")
		,   c = e.getElementById("ladmin-c")
		,   v = e.getElementById("ladmin-v")
		,   b = e.getElementById("ladmin-b");
		if( null !== o && null !== c ) {
			o.onclick = function() {
				b.style.left = "10px";
				v.style.left = "-600px";
			}
			c.onclick = function() {
				b.style.left = "-600px";
				v.style.left = "0";
			}
		}

		if( -1 != e.referrer.indexOf("respond_preview=1") && null !== o && null !== c ) {
			b.style.left = "10px";
			v.style.left = "-600px";
		}
	}
	var d, p, f
}(document);

window.addEventListener("load", function c() {
	document.getElementById("ladmin").removeAttribute("style");
	this.removeEventListener("load", c, !1 );
}, !1 );

SCRIPT;
	return $ret;
}
endif;
