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

global $luxe, $_is;

?>
<ul>
<li>
<p class="m25-b">
<?php
if( $_is['ssl'] === false ) {
echo '<span class="dashicons dashicons-no" style="color:red;width:24px;font-size:26px;margin:-1px 8px 0 -4px"></span>' . __( 'Your website is not served over SSL', 'luxeritas' );
}
else {
echo '<span class="dashicons dashicons-yes" style="color:green;width:24px;font-size:26px;margin:-1px 8px 0 -4px"></span>' . __( 'Your website is served over SSL', 'luxeritas' );
}
?>
</p>
<p class="control-title"><?php printf( __( 'Setting of %s', 'luxeritas' ), 'PWA ' ); ?></p>
<p class="checkbox">
<input type="checkbox" value="" name="pwa_enable"<?php thk_value_check( 'pwa_enable', 'checkbox' ); if( $_is['ssl'] === false ) echo ' readonly style="opacity:.6;pointer-events:none"'; ?> />
<?php printf( __( 'Enable %s', 'luxeritas' ), 'PWA ( Progressive Web Apps )' . ' ' ); ?>
</p>
<?php /* <p class="f09em m25-b"><?php echo __( '* When checked, users will be able to appear and operate your site like web application on mobile device.', 'luxeritas'); ?></p> */ ?>
</li>
<?php
/*
<li>
<p class="checkbox">
<input type="checkbox" value="" name="pwa_mobile"<?php thk_value_check( 'pwa_mobile', 'checkbox' ); if( !isset( $luxe['pwa_enable'] ) ) echo ' readonly'; ?> />
<?php echo __( 'Enable PWA only for access from mobile device', 'luxeritas' ); ?>
</p>
<p class="m25-b"><span class="f09em bold bg-gray"><?php echo __( '* If cache plugin is installed, it may not behavior properly.', 'luxeritas'); ?></span></p>
</li>
*/
?>
<li>
<p class="control-title"><?php echo __( 'Offline page', 'luxeritas' ); ?></p>
<p id="pwa1" class="checkbox"<?php if( !isset( $luxe['pwa_enable'] ) ) echo ' style="opacity:.6;pointer-events:none"';?>>
<input type="checkbox" value="" name="pwa_offline_enable"<?php thk_value_check( 'pwa_offline_enable', 'checkbox' ); ?> />
<?php echo __( 'Allow browsing offline by caching', 'luxeritas' ); ?>
</p>
</li>
<li id="pwa2"<?php if( !isset( $luxe['pwa_enable'] ) || !isset( $luxe['pwa_offline_enable'] ) ) echo ' style="opacity:.6;pointer-events:none"';?>>
<?php
$wp_dropdown_pages = wp_dropdown_pages( array( 
	'name' => 'pwa_offline_page', 
	'echo' => false, 
	//'show_option_none' => __( 'Top Page', 'luxeritas' ), 
	'option_none_value' => '0', 
	'selected' =>  isset( $luxe['pwa_offline_page'] ) ? $luxe['pwa_offline_page'] : '',
));
if( !empty( $wp_dropdown_pages ) ) {
	echo $wp_dropdown_pages;
}
else {
	echo '<input type="text" value="', __( 'No pages available to select.', 'luxeritas' ), '" disabled />';
}
?>
<p class="f09em"><?php echo __( '* Offline page is displayed when the device is offline and the requested page is not already cached.', 'luxeritas'); ?></p>
<?php /* <p class="f09em m25-b"><?php echo __( '* Please check <code class="normal-family">&quot;Include theme&apos;s CSS in HTML&quot;</code> in CSS setting to display page with CSS applied in cache.', 'luxeritas'); ?></p> */?>
</li>

<li id="pwa3"<?php if( !isset( $luxe['pwa_enable'] ) || !isset( $luxe['pwa_offline_enable'] ) ) echo ' style="opacity:.6;pointer-events:none"';?>>
<p class="control-title"><?php echo __( 'Install button', 'luxeritas' ); ?></p>
<p style="margin:10px 0 15px 0">
<input type="checkbox" value="" name="pwa_install_button"<?php thk_value_check( 'pwa_install_button', 'checkbox' ); ?> />
<?php echo __( 'Display application install button on mobile global nav.', 'luxeritas' ); ?>
</p>

<p style="margin:10px 0 25px 0">
<div id="pwa4"<?php if( !isset( $luxe['pwa_enable'] ) || !isset( $luxe['pwa_offline_enable'] ) ) echo ' style="opacity:.6;pointer-events:none"';?>>
<input type="checkbox" value="" name="pwa_install_widget"<?php thk_value_check( 'pwa_install_widget', 'checkbox' ); if( !isset( $luxe['pwa_enable'] ) ) echo ' readonly'; ?> />
<?php echo __( 'Arrange the install button using widget.', 'luxeritas' ); ?>
</div>
</p>
<?php
/*
<p class="f09em"><?php echo __( '* In order to display installation button, display mode must be &quot;minimal-ui&quot; or &quot;standalone&quot;.', 'luxeritas' ); ?></p>
<p class="f09em m25-b"><?php echo __( '* Installation button is automatically displayed under certain conditions such as user&apos;s visit frequency. ( The condition depends on browser specs )', 'luxeritas' ); ?></p>
*/
?>
</li>
</ul>
<div style="display:none">
<script>
jQuery(document).ready(function($) {
	var e = $('input[name="pwa_enable"]')
	,   o = $('input[name="pwa_offline_enable"]')
	,   i = $('input[name="pwa_install_button"]')
	,   l = $('input[name="pwa_install_widget"]')
	,   p1= $('#pwa1')
	,   p2= $('#pwa2')
	,   p3= $('#pwa3')
	,   p4= $('#pwa4');

	e.on('change', function() {
		if( e.prop('checked') === true ) {
			var a = [p1];
			for( var z = 0; z < a.length; ++z ) {
				a[z].css('opacity', ''); a[z].css('pointerEvents', '');
			}
			if( o.prop('checked') === true ) {
				var a = [p2,p3,p4];
				for( var z = 0; z < a.length; ++z ) {
					a[z].css('opacity', ''); a[z].css('pointerEvents', '');
				}
			}
		}
		else {
			var a = [p1,p2,p3,p4];
			for( var z = 0; z < a.length; ++z ) {
				a[z].css('opacity', '.6'); a[z].css('pointerEvents', 'none');
			}
		}
	});
	o.on('change', function() {
		if( o.prop('checked') === true ) {
			var a = [p2,p3];
			for( var z = 0; z < a.length; ++z ) {
				a[z].css('opacity', ''); a[z].css('pointerEvents', '');
			}
			if( i.prop('checked') === true ) {
				var a = [p4];
				for( var z = 0; z < a.length; ++z ) {
					a[z].css('opacity', ''); a[z].css('pointerEvents', '');
				}
			}
		}
		else {
			var a = [p2,p3,p4];
			for( var z = 0; z < a.length; ++z ) {
				a[z].css('opacity', '.6'); a[z].css('pointerEvents', 'none');
			}
		}
	});
	i.on('change', function() {
		if( i.prop('checked') === true ) {
			var a = [p4];
			for( var z = 0; z < a.length; ++z ) {
				a[z].css('opacity', ''); a[z].css('pointerEvents', '');
			}
		}
		else {
			var a = [p4];
			for( var z = 0; z < a.length; ++z ) {
				a[z].css('opacity', '.6'); a[z].css('pointerEvents', 'none');
			}
		}
	});
});
</script>
</div>
