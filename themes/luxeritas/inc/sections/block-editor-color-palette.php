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

//settings_fields( 'block_color_palette' );

wp_enqueue_style( 'wp-color-picker' );
wp_enqueue_script( 'wp-color-picker' );

?>
<div style="display:none">
<style>button{margin:0!important}</style>
<script>
jQuery(document).ready(function($) {
	$('.thk-color-picker').wpColorPicker();
	$('.wp-color-result').on('click', function() {
		$("#save").prop("disabled", false);
	});
});
</script>
</div>
<ul>
<li>
<table class="balloon-regist-table" style="margin-left:5px">
<colgroup span="2" style="width:120px" />
<tbody>
<tr>
<th style="padding:0 10px;white-space:nowrap"><?php echo __( 'Color', 'luxeritas' ) ?></th>
<th style="padding:0 10px;white-space:nowrap"><?php echo __( 'Name', 'luxeritas' ) ?></th>
<th style="padding:0 10px;white-space:nowrap"><?php echo __( 'Slug', 'luxeritas' ) ?></th>
</tr>
<?php
$i = 0;
$color_palette = thk_block_editor_color_palette();
foreach( $color_palette as $val ) {
	$color_key = 'block_palette_color_' . $i;
	$name_key  = 'block_palette_name_' . $i;
	$slug_key  = 'block_palette_slug_' . $i;
	$color_value = thk_value_check( $color_key, 'text', $val['color'], false );
	$name_value  = thk_value_check( $name_key, 'text', $val['name'], false );
	$slug_value  = thk_value_check( $slug_key, 'text', $val['slug'], false );
?>
<tr>
<td style="vertical-align:top" id="<?php echo $color_key ?>_td"><input class="thk-color-picker" type="text" id="<?php echo $color_key ?>" name="<?php echo $color_key ?>" value="<?php echo $color_value ?>" /></td>
<td style="vertical-align:top"><input style="max-width:120px;min-height:32px" type="text" id="<?php echo $name_key ?>" name="<?php echo $name_key ?>" value="<?php echo $name_value ?>" /></td>
<td style="vertical-align:top"><input style="max-width:120px;min-height:32px" type="text" id="<?php echo $slug_key ?>" name="<?php echo $slug_key ?>" value="<?php echo $slug_value ?>" /></td>
</tr>
<?php
	++$i;
}
?>
<td style="padding:20px 0 0 10px"><?php echo __( 'Color', 'luxeritas' ) ?></td>
<td style="padding:20px 0 0 10px"><?php echo __( 'Name', 'luxeritas' ) ?></td>
<td style="padding:20px 0 0 10px"><?php echo __( 'Slug', 'luxeritas' ) ?></td>
<?php
for( $j = 0; 6 > $j; ++$j ) {
	$color_key = 'block_palette_color_' . $i;
	$name_key  = 'block_palette_name_' . $i;
	$slug_key  = 'block_palette_slug_' . $i;
	$color_value = thk_value_check( $color_key, 'text', $val['color'], false );
	$name_value  = thk_value_check( $name_key, 'text', $val['name'], false );
	$slug_value  = thk_value_check( $slug_key, 'text', $val['slug'], false );
?>
<tr>
<td style="vertical-align:top" id="<?php echo $color_key ?>_td"><input class="thk-color-picker" type="text" id="<?php echo $color_key ?>" name="<?php echo $color_key ?>" value="<?php echo $color_value ?>" /></td>
<td style="vertical-align:top"><input style="max-width:120px;min-height:32px" type="text" id="<?php echo $name_key ?>" name="<?php echo $name_key ?>" value="<?php echo $name_value ?>" /></td>
<td style="vertical-align:top"><input style="max-width:120px;min-height:32px" type="text" id="<?php echo $slug_key ?>" name="<?php echo $slug_key ?>" value="<?php echo $slug_value ?>" /><span style="white-space:nowrap">&nbsp;<?php echo __( '(Addition)', 'luxeritas' ); ?></span></td>
</tr>
<?php
	++$i;
}
?>
<tr><td style="padding-top:20px" colspan="2"><hr style="margin-left:0;max-width:320px" /></td></tr>
<tr><td><button type="button" id="block_color_palette-default" class="button"><?php echo __( 'Initial settings', 'luxeritas' ); ?></button></td></tr>
</tbody>
</table>
</li>
</ul>
<div style="display:none">
<script>
jQuery(document).ready(function(o) {
	o("#block_color_palette-default").on("click", function() {
		var c = "#block_palette_color_"
		,   n = "#block_palette_name_"
		,   d = "_td button"
		,   b = "background-color";
<?php
$i = 0;
foreach( $color_palette as $val ) {
?>
		o(c + "<?php echo $i ?>" + d).css(b, "<?php echo $val['color'] ?>");
		o(c + "<?php echo $i ?>").val("<?php echo $val['color'] ?>");
		o(n + "<?php echo $i ?>").val("<?php echo $val['name'] ?>");
<?php
	++$i;
}
?>
		o("#save").prop("disabled", false);
	});
});
</script>
</div>
