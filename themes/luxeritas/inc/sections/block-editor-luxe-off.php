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
<p class="checkbox">
<input type="checkbox" value="" name="luxe_block_toolbar_off"<?php thk_value_check( 'luxe_block_toolbar_off', 'checkbox' ); ?> />
<?php echo __( 'Turn off Luxeritas block toolbar', 'luxeritas' ); ?>
</p>
</li>
<li>
<p class="checkbox" style="margin-bottom:30px">
<input type="checkbox" value="" name="luxe_blocks_off"<?php thk_value_check( 'luxe_blocks_off', 'checkbox' ); ?> />
<?php echo __( 'Turn off Luxeritas Blocks', 'luxeritas' ); ?>
</p>
</li>
</ul>
