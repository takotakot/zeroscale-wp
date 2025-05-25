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

if( $luxe['bootstrap_footer'] !== 'in' ) {
?>
</div><!--/.container-->
<?php
}
?>
<div id="footer" itemscope itemtype="https://schema.org/WPFooter"<?php if( isset( $luxe['add_role_attribute'] ) ) echo ' role="contentinfo"'; ?>>
<footer>
<?php
$footer_nav = wp_nav_menu(
	array(
		'theme_location' => 'footer-nav',
		'depth' => '1',
		'container' => 'nav',
		'container_class' => 'foot-nav',
		'container_aria_label' => '',
		'link_before' => '<span>',
		'link_after' => '</span>',
		'echo' => false,
		'fallback_cb' => '',
		'items_wrap' => '<ul class="%2$s clearfix">%3$s</ul>',
	)
);

if( !empty( $footer_nav ) && isset( $luxe['foot_nav_position'] ) && $luxe['foot_nav_position'] === 'above' ) {
?><div id="footer-nav">
<?php
	$trims = $luxe['html_compress'] === 'high' ? array( "\r", "\n", "\t" ) : array( "\t" );
	echo str_replace( $trims, '', $footer_nav );
?></div><!--/#footer-nav-->
<?php
}

if( !isset( $luxe['amp'] ) ) {
	$_is_footer_left = false;
	$_is_footer_right = false;
	$_is_footer_center = false;

	if( function_exists('dynamic_sidebar') === true ){
		$_is_footer_left = is_active_sidebar('footer-left');
		$_is_footer_right = is_active_sidebar('footer-right');
		$_is_footer_center = is_active_sidebar('footer-center');
	}

	if( $_is_footer_left === true || $_is_footer_right === true || $_is_footer_center === true ) {
?>
<div id="foot-in">
<?php
	// Footer Widget Area
	if( ( $_is['mobile'] === true && !isset( $luxe['hide_mobile_footer'] ) ) || $_is['mobile'] === false ) {
		if( $luxe['foot_widget'] !== 0 ) {
			$fwl = 'col-4 col-xs-4';
			$fwc = 'col-4 col-xs-4';
			$fwr = 'col-4 col-xs-4';
			if( $luxe['foot_widget'] === 1 ) {
				$fwc = 'col-12 col-xs-12';
			}
			elseif( $luxe['foot_widget'] === 2 ) {
				$fwl = 'col-6 col-xs-6';
				$fwr = 'col-6 col-xs-6';
			}
?>
<aside class="row">
<?php
				if( $luxe['foot_widget'] !== 1 ) {
?><div class="<?php echo $fwl; ?>"><?php
					if( $_is_footer_left === true ) dynamic_sidebar( 'footer-left' );
?></div><?php
				}
				if( $luxe['foot_widget'] !== 2 ) {
?><div class="<?php echo $fwc; ?>"><?php
					if( $_is_footer_center === true ) dynamic_sidebar( 'footer-center' );
?></div><?php
				}
				if( $luxe['foot_widget'] !== 1 ) {
?><div class="<?php echo $fwr; ?>"><?php
					if( $_is_footer_right === true ) dynamic_sidebar( 'footer-right' );
?></div><?php
				}
?>
</aside>
<div class="clearfix"></div>
<?php
			}
		}
?>
</div><!--/#foot-in-->
<?php
	}
}

if( !empty( $footer_nav ) && isset( $luxe['foot_nav_position'] ) && $luxe['foot_nav_position'] === 'below' ) {
?><div id="footer-nav">
<?php
	$trims = $luxe['html_compress'] === 'high' ? array( "\r", "\n", "\t" ) : array( "\t" );
	echo str_replace( $trims, '', $footer_nav );
?></div><!--/#footer-nav-->
<?php
}
?>
<div id="copyright">
<?php echo isset( $luxe['copyright'] ) ? $luxe['copyright'] : ''; ?>
<p id="thk" class="copy">WordPress Luxeritas Theme is provided by &quot;<a href="<?php echo THK_COPY; ?>" target="_blank" rel="nofollow noopener">Thought is free</a>&quot;.</p>
</div><!--/#copy-->
</footer>
</div><!--/#footer-->
<?php
if( $luxe['bootstrap_footer'] === 'in' ) {
?>
</div><!--/.container-->
<?php
}
?>
<div id="wp-footer">
<?php
if( !isset( $luxe['amp'] ) ) {
	if( isset( $luxe['mobile_buttons'] ) ) get_template_part( 'mobile-buttons' );
?>
<div id="page-top"><?php echo $awesome['page-top'], isset( $luxe['page_top_text'] ) ? '<span class="ptop"> ' . $luxe['page_top_text'] . '</span>' : ''; ?></div>
<?php
	if( ( isset( $luxe['global_navi_mobile_type'] ) && ( $luxe['global_navi_mobile_type'] === 'luxury' || $luxe['global_navi_mobile_type'] === 'luxury_head' ) ) || isset( $luxe['mobile_search_button'] ) ) {
?><aside><div id="sform" itemscope itemtype="https://schema.org/WebSite"><meta itemprop="url" content="<?php echo THK_HOME_URL; ?>" /><form itemprop="potentialAction" itemscope itemtype="https://schema.org/SearchAction" method="get" class="search-form" action="<?php echo THK_HOME_URL; ?>"><meta itemprop="target" content="<?php echo THK_HOME_URL; ?>?s={s}"/><div><input itemprop="query-input" type="search" class="search-field mobile-search" name="s" placeholder=" &#xf002; Search for ..." required /></div><input type="submit" class="search-submit" value="Search" /></form></div></aside><?php
	}

	if( $_is['customize_preview'] === true ) {
		require_once( INC . 'create-javascript.php' );
		$jscript = new create_Javascript();
		$luxe['awesome_load_async'] = 'none';
		$luxe['wrap_menu_used'] = true;

		if( isset( $luxe['jquery_load'] ) && $luxe['jquery_load'] !== 'none' ) {
			$files = array(
				'jquery.sticky-kit.min.js',
				'autosize.min.js',
			);
			foreach( $files as $val ) echo '<script src="', TDEL, '/js/', $val, '"></script>';
		}

		echo	'<script>'
		,	$jscript->create_luxe_dom_content_loaded_script()
		,	$jscript->create_luxe_various_script()
		,	'</script>';
	}

	// 子 luxech.js もしくは luxech.min.js
	if( !isset( $luxe['child_script'] ) ) {
		if( $luxe['child_js_compress'] === 'none' ) {
			if( file_exists( SPATH . DSEP . 'luxech.js' ) ) {
?><script src="<?php echo SDEL; ?>/luxech.js?v=<?php echo $_SERVER['REQUEST_TIME'] ?>" defer></script><?php
			}
		}
		elseif( $luxe['child_js_compress'] === 'comp' ) {
			if( file_exists( SPATH . DSEP . 'luxech.min.js' ) && filesize( SPATH . DSEP . 'luxech.min.js' ) > 0 ) {
?><script src="<?php echo SDEL; ?>/luxech.min.js?v=<?php echo $_SERVER['REQUEST_TIME'] ?>" defer></script><?php
			}
		}
	}
}

// アクセス解析追加用( Bodyタグ最下部に設定されてる場合 )
if( isset( $luxe['analytics_position'] ) && $luxe['analytics_position'] === 'bottom' ) {
	require_once( INC . 'analytics.php' );
	$analytics = new thk_analytics();
	echo $analytics->analytics( 'add-analytics.php' );
}

if( isset( $luxe['amp'] ) ) {
	// AMP HTML ( body )
	if( isset( $luxe['amp_body_position'] ) && $luxe['amp_body_position'] === 'bottom' ) {
		get_template_part( 'add-amp-body' );
	}
}
else {
	get_template_part( 'add-footer' ); // ユーザーフッター追加用

	// SNS ボタンが非表示に設定されてるけど SNS カウントキャッシュを取得する場合
	if( isset( $luxe['sns_count_cache_enable'] ) && isset( $luxe['sns_count_cache_force'] ) ) {
		if(
			( $_is['home'] && !isset( $luxe['sns_toppage_view'] ) ) ||
			( $_is['singular'] && !isset( $luxe['sns_tops_enable'] ) && !isset( $luxe['sns_bottoms_enable'] ) )

		) {
			require( INC . 'sns-cache-get.php' );
			echo '<div class="sns-cache-true feed-cache-true" '
			,    'data-incomplete="" data-luxe-permalink="' . $permalink . '"></div>';
		}
	}

	// Service Worker
	if( $_is['preview'] === false && $_is['customize_preview'] === false ) {
		if( isset( $luxe['pwa_enable'] ) && $_is['ssl'] === true ) {
			$sw_script = THK_HOME_PATH . 'luxe-serviceworker.js';
			$sw_regist_script = array( TPATH . DSEP . 'js' . DSEP . 'luxe-serviceworker-regist.js', TDEL . '/js/luxe-serviceworker-regist.js' );
			if( ( isset( $luxe['pwa_dynamic_files'] ) || file_exists( $sw_script ) === true ) && file_exists( $sw_regist_script[0] ) === true ) {
?><script src="<?php echo $sw_regist_script[1]; ?>?v=<?php echo $_SERVER['REQUEST_TIME'] ?>" async defer></script><?php
			}
		}
	}

	if( $_is['singular'] === true || $_is['home'] === true ) {
		if( $luxe['sns_tops_type'] === 'normal' || $luxe['sns_bottoms_type'] === 'normal' ) {
			// Facebook normal button
			if( isset( $luxe['facebook_share_tops_button'] ) || isset( $luxe['facebook_share_bottoms_button'] )  ) {
				if( class_exists( 'thk_locale' ) === false ) require( INC . 'locale.php' );
				$thk_locale = new thk_locale();
?>
<div id="fb-root"></div>
<script async defer crossorigin="anonymous" src="https://connect.facebook.net/<?php echo $thk_locale->thk_locale_wp_2_ogp( get_locale() ) ?>/sdk.js#xfbml=1&version=v8.0&autoLogAppEvents=1&appId="></script>
<?php
			}
			// LinkedIn normal button
			if( isset( $luxe['linkedin_share_tops_button'] ) || isset( $luxe['linkedin_share_bottoms_button'] ) ) {
?>
<script src="//platform.linkedin.com/in.js" type="text/javascript"></script>
<?php
			}
		}

		// Pinterest button
		if(
			( ( $_is['singular'] === true && ( isset( $luxe['sns_tops_enable'] ) || isset( $luxe['sns_bottoms_enable'] ) ) ) || ( $_is['home'] === true && isset( $luxe['sns_toppage_view'] ) ) ) &&
			( isset( $luxe['pinit_share_tops_button'] ) || isset( $luxe['pinit_share_bottoms_button'] ) )
		) {
			if( $_is['home'] === true || ( $_is['singular'] === true && !isset( $luxe['pinit_hover_button'] ) ) ) {
?>
<script async defer src="//assets.pinterest.com/js/pinit.js"></script>
<?php
			}
			else {
?>
<script async defer data-pin-hover="true" src="//assets.pinterest.com/js/pinit.js"></script>
<?php
			}
		}
		elseif( $_is['singular'] === true && isset( $luxe['pinit_hover_button'] ) ) {
?>
<script async defer data-pin-hover="true" src="//assets.pinterest.com/js/pinit.js"></script>
<?php
		}
	}

	if( isset( $luxe['gallery_type'] ) && $luxe['gallery_type'] === 'highslide' ) {
?>
<script>try{var hsCheck=function(o){window.hs?o(window.hs):window.setTimeout(function(){hsCheck(o)},100)};hsCheck(function(){hs.registerOverlay({thumbnailId:null,overlayId:"controlbar",position:"top right",hideOnMouseOut:!0})})}catch(o){console.error("hs.check.error: "+o.message)}</script>
<div id="controlbar" class="highslide-overlay"><button class="highslide-prev" title="Previous (left arrow key)" onclick="return hs.previous(this)"></button><button class="highslide-next" title="Next (right arrow key)" onclick="return hs.next(this)"></button><button class="highslide-close" title="Close" onclick="return hs.close(this)"></button></div>
<?php
	}

	wp_footer();
}

/* ログインしてる人がいる時 */
if( $_is['user_logged_in'] === true ) {
	/* WordPress の管理バーが見えてる場合のヘッダー上部の帯メニュー位置調整用スクリプト */
	if( is_admin_bar_showing() === true && !isset( $luxe['amp'] ) ) {
?><script src="<?php echo TDEL . '/js/wp-adminbar-position.js'; ?>" async defer></script><?php
	}

	/* Luxeritas 管理バーの表示 */
	if( $_is['customize_preview'] === false && $_is['edit_posts'] === true && !isset( $luxe['hide_luxe_adminbar'] ) && file_exists( TPATH . DSEP . 'js' . DSEP . 'ladmin.min.js' ) === true  && file_exists( TPATH . DSEP . 'css' . DSEP . 'ladmin.css') ) {
		if( isset( $_GET['respond_frame'] ) ) {
?><script><?php echo thk_fgc( TPATH . DSEP . 'js' . DSEP . 'ladmin.min.js' ); ?></script><?php
		}
		else {
			require( INC . 'ladmin.php' );
		}
	}

	/* カスタマイズプレビュー用の特殊スクリプト( iframe に勝手に style 挿入された場合に style を消す) */
	if( $_is['customize_preview'] === true ) {
?><script>!function(){var t=window.parent.document.getElementsByTagName("iframe");setInterval(function(){Array.prototype.forEach.call(t,function(t){t.removeAttribute("style")})},1)}();</script><?php
	}
}
require( INC . 'json-ld.php' );
echo apply_filters( 'thk_json_ld', '' ); // load json-ld

if( !isset( $luxe['amp'] ) ) {
	/* ブログカードのキャッシュ作成（最初の一回だけ） */
	if( isset( $luxe['bc_url'] ) && isset( $luxe['bc_md5'] ) && isset( $luxe['bc_lnk'] ) ) {
		if( isset( $luxe['bc_first'] ) ) {
?><style>.bc-progress{width:100%;height:1pc;margin-bottom:1pc;background:#3798d9;background-image:linear-gradient(-45deg,hsla(0,0%,100%,.125) 25%,transparent 25%,transparent 50%,hsla(0,0%,100%,.125) 50%,hsla(0,0%,100%,.125) 75%,transparent 75%,transparent);background-size:34px 34px;transition:none 0 ease 0;animation:a 2s linear infinite}@keyframes a{to{background-position:34px 34px}}</style><?php
		}

		if( !isset( $luxe['jquery_load'] ) || ( isset( $luxe['jquery_load'] ) && $luxe['jquery_load'] === 'none' ) ) {
			// jQuery が無効化されてて ajax が使えない場合
			$blogcard = new THK_Blogcard();

			$wp_upload_dir = wp_upload_dir();
			$cache_dir = $wp_upload_dir['basedir'] . DSEP . 'luxe-blogcard' . DSEP;
			foreach( $luxe['bc_url'] as $i => $val ) {
				$url_md5 = $luxe['bc_md5'][$i];
				$cache_file = $cache_dir . $url_md5[0] . DSEP . $url_md5;
				thk_flash();
				do_action( 'thk_create_blogcard', $val, $url_md5 );
				$caches = $blogcard->thk_get_blogcard_cache( $cache_file, $luxe['bc_lnk'][$i], $url_md5 );

				if( isset( $caches[1] ) ) {
?><script>try{!function(){var e=document.getElementById("bc_<?php echo $url_md5; ?>");null!==e&&(e.outerHTML="<?php echo $caches[1]; ?>")}()}catch(e){console.error("bc_<?php echo $url_md5; ?>.error: "+e.message)}</script><?php
				}
				unset( $luxe['bc_url'][$i], $luxe['bc_md5'][$i], $luxe['bc_lnk'][$i] );
			}
		}
		else {
			// ajax が使える場合
			$ajaxurl = admin_url( 'admin-ajax.php' );
			$blogcard_cache_nonce = wp_create_nonce( 'blogcard_cache' );

			foreach( (array)$luxe['bc_url'] as $i => $val ) {
?><script>try{var bcjck=function(c){window.jQuery?c(jQuery):window.setTimeout(function(){bcjck(c)},100)};bcjck(function(c){var e=jQuery.ajax({type:"POST",url:"<?php echo $ajaxurl; ?>",data:{action:"thk_blogcard_cache",bc_url:'<?php echo $val; ?>',bc_md5:'<?php echo $luxe["bc_md5"][$i]; ?>',bc_lnk:'<?php echo $luxe["bc_lnk"][$i]; ?>',blogcard_cache_nonce:"<?php echo $blogcard_cache_nonce; ?>"},dataType:"json",async:!0,cache:!1,timeout:3e5}).then(function(e){try{null!==(c=document.getElementById("bc_"+e[0]))&&(c.outerHTML=e[1]),console.log("Create blogcard cache. Success: bc_<?php echo $luxe['bc_md5'][$i]; ?>")}catch(c){console.error("bc_"+e[0]+".error: "+c.message)}var c},function(){console.log("Create blogcard cache. Failed: bc_<?php echo $luxe['bc_md5'][$i]; ?>"),e.abort()})})}catch(c){}</script><?php
				unset( $luxe['bc_url'][$i], $luxe['bc_md5'][$i], $luxe['bc_lnk'][$i] );
			}
		}
	}
}
?>
</div><!--/#wp-footer-->
</body>
</html>
