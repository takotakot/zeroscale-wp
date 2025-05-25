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

settings_fields( 'phrase_sample' );

$fp_mods = get_pattern_list( 'phrase', false );
?>
<fieldset class="luxe-field">
<legend>
<h2 class="luxe-field-title"><?php echo __( 'HTML pattern', 'luxeritas' ), __( ' sample', 'luxeritas' ); ?></h2>
</legend>
<ul>
<li>
<p class="control-title">Google Adsense</p>
<p class="checkbox">
<?php $phrase_name = 'Google Adsense'; ?>
<input type="checkbox" value="" name="phrase_adsense_sample"<?php echo isset( $fp_mods[$phrase_name] ) ? ' checked disabled' : ''; ?> />
<?php echo $phrase_name; ?> ( <?php echo __( 'Please edit contents after registration.', 'luxeritas' ); ?> )
</p>
</li>
</ul>
</fieldset>
