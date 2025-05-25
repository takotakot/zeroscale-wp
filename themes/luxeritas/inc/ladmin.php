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

if( $_is['home'] === true || $_is['front_page'] === true ) {
	$permalink = THK_HOME_URL;
}
else {
	$protocol = is_ssl() ? 'https' : 'http';
	$permalink = $protocol . '://' . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
}

if( stripos( THK_HOME_URL, '?' ) === false  ) {
	$concat = '?';
}
else {
	$concat = '&';
}

if( isset( $luxe['amp_enable'] ) ) {
	$amp_permalink = thk_get_amp_permalink( get_queried_object_id() );
}

if( isset( $luxe['amp'] ) ) {
	global $awesome;

	if( $_is['front_page'] === true ) {
		$enc_url_front_page = rawurlencode( THK_HOME_URL . '?amp=1' );
	}

	$enc_url = rawurlencode( $amp_permalink );
?>
<div id="ladmin">
<ul id="ladmin-b">
<li><a href="<?php echo wp_get_canonical_url() ?>"><?php echo $awesome['circle-left']; ?><?php echo __( 'Origin', 'luxeritas' ) ?></a></li>
<?php
	if( $_is['mobile'] === false ) {
?>
<li><span title="PC" style="color:#ccc"><?php echo $awesome['desktop']; ?></span></li>
<li><a href="<?php echo THK_HOME_URL, $concat ?>respond_preview=1&device=tablet&url=<?php echo isset( $enc_url_front_page ) ? $enc_url_front_page : $enc_url ?>" title="<?php echo __( 'Tablet', 'luxeritas' ) ?>"><?php echo $awesome['tablet']; ?></a></li>
<li><a href="<?php echo THK_HOME_URL, $concat ?>respond_preview=1&device=mobile&url=<?php echo isset( $enc_url_front_page ) ? $enc_url_front_page : $enc_url ?>" title="<?php echo __( 'Mobile', 'luxeritas' ) ?>"><?php echo $awesome['mobile']; ?></a></li>
<?php
	}
?>
<li><a href="https://validator.ampproject.org/#url=<?php echo $enc_url ?>" target="_blank" rel="noopener noreferrer">&#x26A1; <?php echo __( 'Validate', 'luxeritas' ) ?></a></li>
<li><a href="https://cdn.ampproject.org/c/<?php echo stripos( $amp_permalink, 'https:' ) !== false ? 's/' : '', str_replace( array( 'http://', 'https://'), '', $amp_permalink ) ?>" target="_blank" rel="noopener noreferrer">&#x26A1; <?php echo __( 'Cache', 'luxeritas' ) ?></a></li>
</ul>
</div>
<?php
}
else {
	$enc_url = rawurlencode( $permalink );
?>
<div id="ladmin" style="display: none">
<ul id="ladmin-v"><li><span id="ladmin-o" title="<?php echo __( 'Show', 'luxeritas' ) ?>" style="cursor:pointer"><i class="collapse-sidebar-arrow"></i></span></li></ul>
<ul id="ladmin-b">
<li><span id="ladmin-c" title="<?php echo __( 'Hide', 'luxeritas' ) ?>" style="cursor:pointer"><i class="collapse-sidebar-arrow"></i><span class="item_name">&nbsp;<?php echo __( 'Hide', 'luxeritas' ) ?></span></span></li>
<?php
	if( $_is['mobile'] === false ) {
?>
<li class="pc-only"><a href="<?php echo $permalink ?>" title="PC" onclick="return false;" style="color:#aaa"><i class="preview-desktop"></i></a></li>
<li class="pc-only"><a href="<?php echo THK_HOME_URL, $concat ?>respond_preview=1&device=tablet&url=<?php echo $enc_url ?>" title="<?php echo __( 'Tablet', 'luxeritas' ) ?>"><i class="preview-tablet"></i></a></li>
<li class="pc-only"><a href="<?php echo THK_HOME_URL, $concat ?>respond_preview=1&device=mobile&url=<?php echo $enc_url ?>" title="<?php echo __( 'Mobile', 'luxeritas' ) ?>"><i class="preview-mobile"></i></a></li>
<?php
}
?>
<?php
	if( $_is['preview'] === false ) {
		global $post;

		if( $_is['mobile'] === false ) {
?>
<li class="pc-only"><a href="https://validator.schema.org/#url=<?php echo $enc_url ?>" target="_blank" rel="noopener noreferrer" title="<?php echo __( 'Structured data', 'luxeritas' ) ?>"><i class="structured"></i></a></li>
<?php
		}
		if( $_is['singular'] === true && isset( $luxe['amp_enable'] ) ) {
?>
<li><a href="<?php echo $amp_permalink ?>#development=1" title="AMP"> &#x26A1; AMP</a></li>
<?php
		}
?>
<li><a href="<?php echo wp_logout_url( $permalink ); ?>" title="<?php echo __( 'Log out', 'luxeritas' ) ?>"><i class="logout"></i><span>&nbsp;<?php echo __( 'Log out', 'luxeritas' ) ?></span></a></li>
<?php
		if( $_is['singular'] === true ) {
			$post_link = get_edit_post_link();
			if( isset( $post_link ) ) {
?>
<li style="padding:0"></li>
<li><a href="<?php echo get_edit_post_link(); ?>" title="<?php echo __( 'Edit This', 'luxeritas' ) ?>"><i class="edit"></i><span class="item_name2">&nbsp;<?php echo __( 'Edit This', 'luxeritas' ) ?></span></a></li>
<?php
			}
		}
	}
?>
</ul>
</div>
<script><?php echo thk_fgc( TPATH . DSEP . 'js' . DSEP . 'ladmin.min.js' ); ?></script>
<?php
}
