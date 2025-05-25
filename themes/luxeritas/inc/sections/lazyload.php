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
<p class="radio">
<input type="radio" value="native" name="lazyload_type"<?php thk_value_check( 'lazyload_type', 'radio', 'native' ); ?> />
<?php echo 'Native Lazyload ( WP 5.5 or later )'; ?>
</p>
<p class="radio">
<input type="radio" value="intersection" name="lazyload_type"<?php thk_value_check( 'lazyload_type', 'radio', 'intersection' ); ?> />
<?php echo 'Intersection Observer'; ?>
</p>
<p class="radio">
<input type="radio" value="none" name="lazyload_type"<?php thk_value_check( 'lazyload_type', 'radio', 'none' ); ?> />
<?php echo __( 'None', 'luxeritas' ); ?>
</p>
</li>
</ul>

<div style="display:none">
<script>
jQuery(document).ready(function($) {
	var e = function() {
		var s = $("#intersection-observer")
		,   v = $('input[name="lazyload_type"]:checked').val()
		if( "intersection" != v ) {
			s.css("opacity", ".6"), s.css("pointer-events", "none");
		} else {
			s.removeAttr("style");
		}
	};
	e();

	$('input[name="lazyload_type"]').on("click", function() {
		e();
	});
});
</script>
</div>
