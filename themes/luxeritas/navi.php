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
?>
<div id="nav">
<div id="gnavi">
<?php
// AMP 用グローバルナビ
if( isset( $luxe['amp'] ) ) {
?>
<label for="mnav" class="mobile-nav"><?php echo $awesome['menu']; ?><?php echo __(' Menu', 'luxeritas'); ?></label>
<input type="checkbox" id="mnav" class="nav-on" />
<?php
}
// グローバルナビ本体
require( INC. 'navi-menu-walker.php' );
if( has_nav_menu( 'global-nav' ) === false ) {
	require( INC. 'navi-page-walker.php' );
}

if( isset( $luxe['lazyload_type'] ) && $luxe['lazyload_type'] === 'intersection' && isset( $luxe['lazyload_contents'] ) ) {
	remove_filter( 'thk_content', 'thk_intersection_observer_replace_all', 99 );
}

$trims = $luxe['html_compress'] === 'high' ? array( "\r", "\n", "\t" ) : array( "\t" );

echo str_replace( $trims, '',
	wp_nav_menu(
		array(
			'theme_location' => 'global-nav',
			'echo'  => false,
			'depth' => 3,
			'menu_class' => 'menu gu clearfix',
			//'container' => false,
			//'container_id' => '',
			'container_class' => 'gc gnavi-container',
			'link_before' => '<span class="gim gnavi-item">',
			'link_after'  => '</span>',
			'items_wrap'  => '<ul class="%2$s">%3$s</ul>',
			'fallback_cb' => 'thk_page_menu',
			'walker' => new THK_Global_Nav_Walker()
		)
	)
);

if( isset( $luxe['lazyload_type'] ) && $luxe['lazyload_type'] === 'intersection' && isset( $luxe['lazyload_contents'] ) ) {
	add_filter( 'thk_content', 'thk_intersection_observer_replace_all', 99 );
}

if( isset( $luxe['amp'] ) ) {
?>
</div><!--/#gnavi-->
<div class="cboth"></div>
<?php
if( isset( $luxe['global_navi_scroll_progress'] ) ) {
?>
<progress id="gnav-progress" class="luxe-progress"<?php if( isset( $luxe['add_role_attribute'] ) ) echo ' role="progressbar"'; ?> max="100" value="0"></progress>
<?php
}
?>
</div><!--/#nav-->
<?php
	return true;
}

if( $luxe['global_navi_mobile_type'] !== 'luxury' && $luxe['global_navi_mobile_type'] !== 'luxury_head' ) {
?>
<ul class="mobile-nav">
<li class="mob-menu" title="<?php echo __( 'Menu', 'luxeritas' ) ?>"><?php echo $awesome['menu']; ?><?php if( $luxe['global_navi_mobile_type'] === 'global_head' ) echo ' ' . __( 'Menu', 'luxeritas' ); ?></li>
<?php
if( isset( $luxe['pwa_enable'] ) && isset( $luxe['pwa_offline_enable'] ) && isset( $luxe['pwa_install_button'] ) ) {
?>
<li class="mob-install" id="thk_pwa_install" title="<?php echo __( 'Install', 'luxeritas' ) ?>" style="display:none"><?php echo $awesome['install']; ?><?php if( $luxe['global_navi_mobile_type'] === 'global_head' ) echo ' ' . __( 'Install', 'luxeritas'); ?></li>
<?php
}
?>
</ul>
<?php
}
else {
	// 豪華版モバイルメニュー用、前の記事と次の記事

	if(
		( $_is['single'] === true && isset( $luxe['next_prev_nav_visible'] ) ) ||
		( $_is['page'] === true && isset( $luxe['next_prev_nav_page_visible'] ) )
	) {
		$prv = get_adjacent_post( false, '', true );
		$nxt = get_adjacent_post( false, '', false );

		if( !empty( $prv ) ) {
?>
<div id="data-prev" data-prev="<?php echo get_permalink( $prv->ID ); ?>"></div>
<?php
		}
		if( !empty( $nxt ) ) {
?>
<div id="data-next" data-next="<?php echo get_permalink( $nxt->ID ); ?>"></div>
<?php
		}
	}
	elseif( $_is['home'] === true || $_is['archive'] === true || $_is['search'] === true ) {
		$prv = str_replace( array( ' ', '<a', 'href="', '">', '</a>' ), '', (string)get_previous_posts_link( '' ) );
		$nxt = str_replace( array( ' ', '<a', 'href="', '">', '</a>' ), '', (string)get_next_posts_link( '' ) );

		if( !empty( $prv ) && filter_var( $prv, FILTER_VALIDATE_URL ) !== false ) {
?>
<div id="data-prev" data-prev="<?php echo $prv; ?>"></div>
<?php
		}
		if( !empty( $nxt ) && filter_var( $nxt, FILTER_VALIDATE_URL ) !== false ) {
?>
<div id="data-next" data-next="<?php echo $nxt; ?>"></div>
<?php
		}
	}
?>
<ul class="mobile-nav">
<?php
if( $luxe['global_navi_mobile_type'] === 'luxury' ) {
?>
<li class="mob-func"><span><?php echo $awesome['menu']; ?></span></li>
<?php
}
?>
<li class="mob-menu" title="<?php echo __( 'Menu', 'luxeritas' ) ?>"><?php echo $awesome['menu']; ?><p><?php echo __( 'Menu', 'luxeritas' ) ?></p></li>
<?php
if( isset( $luxe['column_style'] ) && $luxe['column_style'] !== '1column' ) {
?>
<li class="mob-side" title="<?php echo __( 'Sidebar', 'luxeritas' ) ?>"><?php echo $awesome['sidebar']; ?><p><?php echo __( 'Sidebar', 'luxeritas' ) ?></p></li>
<?php
}
?>
<li class="mob-prev" title="<?php echo __( ' Prev ', 'luxeritas' ) ?>"><?php echo $awesome['double-arrow-left']; ?><p><?php echo __( ' Prev ', 'luxeritas' ) ?></p></li>
<li class="mob-next" title="<?php echo __( ' Next ', 'luxeritas' ) ?>"><?php echo $awesome['double-arrow-right']; ?><p><?php echo __( ' Next ', 'luxeritas' ) ?></p></li>
<li class="mob-search" title="<?php echo __( 'Search', 'luxeritas' ) ?>"><?php echo $awesome['search']; ?><p><?php echo __( 'Search', 'luxeritas' ) ?></p></li>
<?php
if( isset( $luxe['pwa_enable'] ) && isset( $luxe['pwa_offline_enable'] ) && isset( $luxe['pwa_install_button'] ) ) {
?>
<li class="mob-install" id="thk_pwa_install" title="<?php echo __( 'Install', 'luxeritas' ) ?>" style="display:none"><?php echo $awesome['install']; ?><p><?php echo __( 'Install', 'luxeritas' ) ?></p></li>
<?php
}
?>
</ul>
<?php
}
?>
</div><!--/#gnavi-->
<div class="cboth"></div>
<?php
if( isset( $luxe['global_navi_scroll_progress'] ) ) {
?>
<progress id="gnav-progress" class="luxe-progress"<?php if( isset( $luxe['add_role_attribute'] ) ) echo ' role="progressbar"'; ?> max="100" value="0"></progress>
<?php
}
?>
</div><!--/#nav-->
