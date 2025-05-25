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

global $luxe;

?>
<ul>
<li>
<p class="control-title"><?php echo __( 'Select icon fonts to load', 'luxeritas' ); ?></p>
<p class="checkbox">
<input type="checkbox" value="" name="material_load"<?php thk_value_check( 'material_load', 'checkbox' ); ?> />
Material Icons ( Outlined + Filled ) <span style="margin-left:10px">: <a href="https://fonts.google.com/icons" target="_blank" rel="nofollow noopener noreferrer"><?php echo __( 'Icons list', 'luxeritas' ); ?></a> ( Google Fonts )</span>
</p>
<div id="icons1" style="padding:0 0 8px 24px;<?php if( !isset( $luxe['material_load'] ) ) echo 'opacity:.6;pointer-events:none'; ?>">
<p class="checkbox">
<input type="checkbox" value="" name="material_add_rounded"<?php thk_value_check( 'material_add_rounded', 'checkbox' ); ?> />
Rounded
</p>
<p class="checkbox">
<input type="checkbox" value="" name="material_add_sharp"<?php thk_value_check( 'material_add_sharp', 'checkbox' ); ?> />
Sharp
</p>
<p class="checkbox">
<input type="checkbox" value="" name="material_add_two_tone"<?php thk_value_check( 'material_add_two_tone', 'checkbox' ); ?> />
Two tone
</p>
<select name="material_load_async">
<option value="async"<?php thk_value_check( 'material_load_async', 'select', 'async' ); ?>><?php echo __( 'Asynchronous', 'luxeritas' ), ' (', __( 'High rendering speed', 'luxeritas' ), ')'; ?></option>
<option value="sync"<?php thk_value_check( 'material_load_async', 'select', 'sync' ); ?>><?php echo __( 'Synchronism', 'luxeritas' ), ' (', __( 'No delays in icon font', 'luxeritas' ), ')'; ?></option>
</select>
</div>
<p class="checkbox">
<input type="checkbox" value="" name="awesome_load"<?php thk_value_check( 'awesome_load', 'checkbox' ); ?> />
Font Awesome
</p>
<ul id="icons2" style="padding-left:24px;<?php if( !isset( $luxe['awesome_load'] ) ) echo 'opacity:.6;pointer-events:none'; ?>">
<li>
<p class="radio">
<input type="radio" value="6" name="awesome_version"<?php thk_value_check( 'awesome_version', 'radio', 6 ); ?> />
Font Awesome 6 <span style="margin-left:10px">: <a href="https://fontawesome.com/search" target="_blank" rel="nofollow noopener noreferrer"><?php echo __( 'Icons list', 'luxeritas' ); ?></a></span>
</p>
<p class="radio">
<input type="radio" value="5" name="awesome_version"<?php thk_value_check( 'awesome_version', 'radio', 5 ); ?> />
Font Awesome 5 <span style="margin-left:10px">: <a href="https://fontawesome.com/v5/search" target="_blank" rel="nofollow noopener noreferrer"><?php echo __( 'Icons list', 'luxeritas' ); ?></a></span>
</p>
<p class="radio">
<input type="radio" value="4" name="awesome_version"<?php thk_value_check( 'awesome_version', 'radio', 4 ); ?> />
Font Awesome 4 <span style="margin-left:10px">: <a href="https://fontawesome.com/v4.7.0/icons/" target="_blank" rel="nofollow noopener noreferrer"><?php echo __( 'Icons list', 'luxeritas' ); ?></a></span>
</p>
<p>
<select name="awesome_load_async">
<option value="async"<?php thk_value_check( 'awesome_load_async', 'select', 'async' ); ?>><?php echo __( 'Asynchronous', 'luxeritas' ), ' (', __( 'High rendering speed', 'luxeritas' ), ')'; ?></option>
<option value="sync"<?php thk_value_check( 'awesome_load_async', 'select', 'sync' ); ?>><?php echo __( 'Synchronism', 'luxeritas' ), ' (', __( 'No delays in icon font', 'luxeritas' ), ')'; ?></option>
</select>
</p>
</li>
</ul>
</li>
</ul>
