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
<ul style="margin-bottom:-15px">
<li>
<input type="checkbox" value="" name="block_editor_off"<?php thk_value_check( 'block_editor_off', 'checkbox' ); ?> />
<?php echo __( 'Disable Block Editor', 'luxeritas' ); ?>
<p class="f09em" style="margin-bottom:30px"><?php echo __( '* Disable block editor and revert to the old one.', 'luxeritas' ); ?></p>
</li>
</ul>
