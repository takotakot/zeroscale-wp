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

?>
<ul>
<li>
<p class="label-title"><?php echo 'Site key'; ?></p>
<input type="text" value="<?php thk_value_check( 'recaptcha_site_key', 'text' ); ?>" name="recaptcha_site_key" />
</li>
<li>
<p class="label-title"><?php echo 'Secret key'; ?></p>
<input type="password" value="<?php thk_value_check( 'recaptcha_secret_key', 'text' ); ?>" name="recaptcha_secret_key" />
<p class="f09em"><?php echo __( '* To use Google reCAPCHA, you will need Site Key and Secrect Key. Please go to <a href="https://www.google.com/recaptcha" target="_blank" rel="noopener noreferrer">Google reCAPTCHA</a> page and get yours.', 'luxeritas' ); ?></p>
<p class="f09em m25-b"><?php echo __( '* It might take some time till you are abel to use the feature after getting your keys.', 'luxeritas' ); ?></p>
</li>
<li>
<p class="control-title"><?php printf( __( 'Setting of %s', 'luxeritas' ), 'v3 ' ); ?></p>
<p class="checkbox">
<input type="checkbox" value="" name="recaptcha_login_user_disable"<?php thk_value_check( 'recaptcha_login_user_disable', 'checkbox' ); ?> />
<?php echo __( 'Disable login users', 'luxeritas' ); ?>
</p>
<p class="label-title"><?php echo __( 'Reliable score threshold ( Google recommended value: 0.5 )', 'luxeritas' ); ?></p>
<p style="margin:10px 0 20px 0">cf. <a href="https://developers.google.com/recaptcha/docs/v3" target="_blank" rel="noopener noreferrer">reCAPTCHA v3 guide</a></p>
<table style="margin-bottom:25px">
<tbody>
<tr>
<td style="vertical-align:middle;padding:0 5px 4px 0;color:red;font-weight:bold">High risk</td>
<td style="vertical-align:middle"><input type="range" id="score_range" style="width:120px" max="1" min="0" step="0.1" value="<?php thk_value_check( 'recaptcha_v3_score', 'number' ); ?>" name="recaptcha_v3_score" /></td>
<td style="vertical-align:middle;padding:0 10px 4px 5px;color:green;font-weight:bold">Low risk</td>
<td style="vertical-align:middle"><input type="number" id="score_number" style="max-width:60px" max="1" min="0" step="0.1" value="<?php thk_value_check( 'recaptcha_v3_score', 'number' ); ?>" /></td>
</tr>
</tbody>
</table>
<script>
!function() {
	var e = document.getElementById("score_range")
	,   t = document.getElementById("score_number")
	,   v = function(e, t) {
		return function(o){
			t.value = e.value;
		}
	},  w = function(e, t) {
		return function(o){
			e.value = t.value;
		}
	};
	e.addEventListener("input", v(e, t));
	t.addEventListener("input", w(e, t));
}();
</script>
<p class="label-title"><?php echo __( 'Position of reCAPTCHA badge and Page Top button', 'luxeritas' ); ?></p>
<select name="recaptcha_v3_ptop">
<option value="none"<?php thk_value_check( 'recaptcha_v3_ptop', 'select', 'none' ); ?>><?php echo __( 'Do nothing', 'luxeritas' ); ?></option>
<option value="link"<?php thk_value_check( 'recaptcha_v3_ptop', 'select', 'link' ); ?>><?php echo __( 'Display a link to Privacy &amp; Terms above the comment submission button', 'luxeritas' ); ?></option>
<option value="left"<?php thk_value_check( 'recaptcha_v3_ptop', 'select', 'left' ); ?>><?php echo __( 'Slide the Page Top button to the left', 'luxeritas' ); ?></option>
<option value="top"<?php thk_value_check( 'recaptcha_v3_ptop', 'select', 'top' ); ?>><?php echo __( 'Slide the Page Top button up', 'luxeritas' ); ?></option>
</select>
<p class="f09em m25-b"><span class="bg-gray"><?php echo __( '* Hiding Privacy &amp; Terms violates the terms.', 'luxeritas' ); ?></span></p>
</li>
<li>
<p class="control-title"><?php printf( __( 'Setting of %s', 'luxeritas' ), 'v2 ' ); ?></p>
<p class="label-title"><?php echo __( 'Theme', 'luxeritas' ); ?></p>
<select name="recaptcha_theme">
<option value="light"<?php thk_value_check( 'recaptcha_theme', 'select', 'light' ); ?>><?php echo __( 'light', 'luxeritas' ); ?></option>
<option value="dark"<?php thk_value_check( 'recaptcha_theme', 'select', 'dark' ); ?>><?php echo __( 'dark', 'luxeritas' ); ?></option>
</select>
</li>
<li>
<p class="label-title"><?php echo __( 'Size', 'luxeritas' ); ?></p>
<select name="recaptcha_size">
<option value="normal"<?php thk_value_check( 'recaptcha_size', 'select', 'normal' ); ?>><?php echo __( 'normal', 'luxeritas' ); ?></option>
<option value="compact"<?php thk_value_check( 'recaptcha_size', 'select', 'compact' ); ?>><?php echo __( 'compact', 'luxeritas' ); ?></option>
</select>
</li>
<li>
<p class="label-title"><?php echo __( 'Type of authentication', 'luxeritas' ); ?></p>
<select name="recaptcha_type">
<option value="image"<?php thk_value_check( 'recaptcha_type', 'select', 'image' ); ?>><?php echo __( 'image', 'luxeritas' ); ?></option>
<option value="audio"<?php thk_value_check( 'recaptcha_type', 'select', 'audio' ); ?>><?php echo __( 'audio', 'luxeritas' ); ?></option>
</select>
</li>
</ul>
