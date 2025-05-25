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

//settings_fields( 'shortcode_sample' );

wp_enqueue_style( 'wp-color-picker' );
wp_enqueue_script( 'wp-color-picker' );

$sc_mods = get_pattern_list( 'shortcode', false, true );
$highlighter_list = thk_syntax_highlighter_list();
$i = 0;
?>
<div style="display:none">
<script>
jQuery(document).ready(function($) {
	$('.thk-color-picker').wpColorPicker();
	$('.wp-color-result').on('click', function() {
		$("#save").prop("disabled", false);
	});
});
</script>
</div>

<fieldset class="luxe-field">
<legend>
<h2 class="luxe-field-title"><?php echo __( 'Speech balloon shortcode', 'luxeritas' ), __( ' for luxeritas only', 'luxeritas' ); ?></h2>
</legend>
<ul>
<li>
<p class="control-title"><?php echo __( 'Speech balloon', 'luxeritas' ); ?></p>
<p class="f09em m10-b"><?php echo __( '* If speech balloon shortcode is registered, CSS for speech balloon will be automatically loaded.', 'luxeritas' ); ?></p>
<p class="checkbox">
<input type="checkbox" value="" name="shortcode_balloon_left_sample"<?php echo isset( $sc_mods['balloon_left'] ) ? ' checked disabled' : ''; ?> />
<?php echo __( 'Speech balloon', 'luxeritas' ) . ' ( ' . __( 'Left', 'luxeritas' ) . ' ) '; ?>
</p>

<table class="balloon-regist-table">
<colgroup span="1" style="width:120px" />
<tbody>
<tr>
<th style="padding-top:10px;vertical-align:top">* <?php echo __( 'Image', 'luxeritas' ); ?> URL : </th>
<td>
<?php
if( isset( $sc_mods['balloon_left'] ) ) {
?>
<input type="text" id="sbl_image_url" name="sbl_image_url" value="" placeholder="<?php echo __( 'Registered', 'luxeritas' ); ?>" readonly />
<?php
}
else {
	$image = TURI . '/images/white-cat.png';
?>
<input type="text" id="sbl_image_url" name="sbl_image_url" value="<?php echo $image; ?>" />
<div style="margin:5px 0 10px 0">
<script>thkImageSelector('sbl_image_url', 'Image');</script>
<input id="sbl_image_url-set" type="button" class="button" value="<?php echo __( 'Select image', 'luxeritas' ); ?>" style="vertical-align:middle" />
( <?php printf( __( 'Width %s recommended', 'luxeritas' ), '60px' ); ?> )
</div>
<?php
}
?>
</td>
</tr>
<tr>
<th>* <?php echo __( 'Caption', 'luxeritas' ); ?> : </th>
<td>
<?php
if( isset( $sc_mods['balloon_left'] ) ) {
?>
<input type="text" id="sbl_caption" name="sbl_caption" value="" placeholder="<?php echo __( 'Registered', 'luxeritas' ); ?>" readonly />
<?php
}
else {
?>
<input type="text" id="sbl_caption" name="sbl_caption" value="<?php echo __( 'Left caption', 'luxeritas' ); ?>" />
<?php
}
?>
</td>
</tr>
</tbody>
</table>

<p>
<input type="checkbox" value="" name="shortcode_balloon_right_sample"<?php echo isset( $sc_mods['balloon_right'] ) ? ' checked disabled' : ''; ?> />
<?php echo __( 'Speech balloon', 'luxeritas' ) . ' ( ' . __( 'Right', 'luxeritas' ) . ' ) '; ?>
</p>

<table class="balloon-regist-table">
<colgroup span="1" style="width:120px" />
<tbody>
<tr>
<th style="padding-top:10px;vertical-align:top">* <?php echo __( 'Image', 'luxeritas' ); ?> URL : </th>
<td>
<?php
if( isset( $sc_mods['balloon_right'] ) ) {
?>
<input type="text" id="sbr_image_url" name="sbr_image_url" value="" placeholder="<?php echo __( 'Registered', 'luxeritas' ); ?>" readonly />
<?php
}
else {
	$image = TURI . '/images/black-cat.png';
?>
<input type="text" id="sbr_image_url" name="sbr_image_url" value="<?php echo $image; ?>" />
<div style="margin:5px 0 10px 0">
<script>thkImageSelector('sbr_image_url', 'Image');</script>
<input id="sbr_image_url-set" type="button" class="button" value="<?php echo __( 'Select image', 'luxeritas' ); ?>" style="vertical-align:middle" />
( <?php printf( __( 'Width %s recommended', 'luxeritas' ), '60px' ); ?> )
</div>
<?php
}
?>
</td>
</tr>
<tr>
<th>* <?php echo __( 'Caption', 'luxeritas' ); ?> : </th>
<td>
<?php
if( isset( $sc_mods['balloon_right'] ) ) {
?>
<input type="text" id="sbr_caption" name="sbr_caption" value="" placeholder="<?php echo __( 'Registered', 'luxeritas' ); ?>" readonly />
<?php
}
else {
?>
<input type="text" id="sbr_caption" name="sbr_caption" value="<?php echo __( 'Right caption', 'luxeritas' ); ?>" />
<?php
}
?>
</td>
</tr>
</tbody>
</table>

<p class="control-title"><?php echo __( 'CSS settings for speech balloon', 'luxeritas' ); ?></p>
<p class="f09em m10-b"><?php echo __( '* This item can be changed even after shortcode registration.', 'luxeritas' ); ?></p>
<?php
require( 'balloon-css.php' );
?>
<div style="display:none">
<script>
jQuery(document).ready(function($) {
	$('input[name="shortcode_balloon_left_sample"],input[name="shortcode_balloon_right_sample"]').on('change', function() {
		if(
			$('input[name="shortcode_balloon_left_sample"]').prop('checked') === true ||
			$('input[name="shortcode_balloon_right_sample"]').prop('checked') === true
		) {
			$('input[name="balloon_enable"]').prop('checked', true);
			$('input[name="balloon_enable"]').prop('disabled', true);
		}
		else {
			$('input[name="balloon_enable"]').prop('disabled', false);
		}
	});
});
</script>
</div>
</li>
</ul>
</fieldset>

