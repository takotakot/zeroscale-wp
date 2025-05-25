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
<ul id="intersection-observer">
<li>
<p><?php echo sprintf( __( '* This feature uses %s.', 'luxeritas' ), '<a href="https://developer.mozilla.org/docs/Web/API/Intersection_Observer_API" target="_blank" rel="noopener noreferrer">Intersection Observer API</a>' ); ?></p>
<p class="checkbox">
<input type="checkbox" value="" name="lazyload_thumbs"<?php thk_value_check( 'lazyload_thumbs', 'checkbox' ); ?> />
<?php echo __( 'Enable Lazy Load for various thumbnail images', 'luxeritas' ); ?>
</p>
<p class="checkbox">
<input type="checkbox" value="" name="lazyload_contents"<?php thk_value_check( 'lazyload_contents', 'checkbox' ); ?> />
<?php echo __( 'Enable Lazy Load for post contents', 'luxeritas' ); ?>
</p>
<p class="checkbox">
<input type="checkbox" value="" name="lazyload_sidebar"<?php thk_value_check( 'lazyload_sidebar', 'checkbox' ); ?> />
<?php echo __( 'Enable Lazy Load for sidebar', 'luxeritas' ); ?>
</p>
<p class="m25-b f09em"><span class="bg-gray"><?php echo __( '* The scroll follow sidebar may become strange movement.', 'luxeritas' ); ?></span></p>
<p class="checkbox">
<input type="checkbox" value="" name="lazyload_footer"<?php thk_value_check( 'lazyload_footer', 'checkbox' ); ?> />
<?php echo __( 'Enable Lazy Load for footer', 'luxeritas' ); ?>
</p>
<p class="checkbox">
<input type="checkbox" value="" name="lazyload_avatar"<?php thk_value_check( 'lazyload_avatar', 'checkbox' ); ?> />
<?php echo __( 'Enable Lazy Load for Gravatar', 'luxeritas' ); ?>
</p>

<?php
if( isset( $luxe['fucking_jetpack'] ) ) {
?>
<p class="checkbox" id="disable_jetpack_lazyload_style">
<input type="checkbox" value="" name="disable_jetpack_lazyload"<?php thk_value_check( 'disable_jetpack_lazyload', 'checkbox' ); ?> />
<?php echo __( 'Disable Jetpack&apos;s Lazy Load', 'luxeritas' ); ?>
</p>
<?php
}
?>

</li>

<li>
<p class="control-title"><?php echo __( 'Option', 'luxeritas' ); ?></p>
<p class="checkbox">
<input type="checkbox" value="" name="lazyload_noscript"<?php thk_value_check( 'lazyload_noscript', 'checkbox' ); ?> />
<?php echo __( 'Display images even if Javascript is disabled', 'luxeritas' ); ?>
</p>
</li>

<li>
<p class="control-title"><?php echo __( 'Effect', 'luxeritas' ); ?></p>
<p class="radio">
<input type="radio" value="fadeIn" name="lazyload_effect"<?php thk_value_check( 'lazyload_effect', 'radio', 'fadeIn' ); ?> />
<?php echo __( 'Fade-in', 'luxeritas' ); ?>
</p>
<p class="radio">
<input type="radio" value="show" name="lazyload_effect"<?php thk_value_check( 'lazyload_effect', 'radio', 'show' ); ?> />
<?php echo __( 'Show (No effect)', 'luxeritas' ); ?>
</p>
</li>

</ul>
