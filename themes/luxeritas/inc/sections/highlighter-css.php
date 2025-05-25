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
<p>
<select name="highlighter_css">
<option value="none"<?php thk_value_check( 'highlighter_css', 'select', 'solarized_light' ); ?>><?php echo __( 'None', 'luxeritas' ); ?></option>
<option value="default"<?php thk_value_check( 'highlighter_css', 'select', 'default' ); ?>>Default</option>
<option value="dark"<?php thk_value_check( 'highlighter_css', 'select', 'dark' ); ?>>Dark</option>
<option value="okaidia"<?php thk_value_check( 'highlighter_css', 'select', 'okaidia' ); ?>>Okaidia</option>
<option value="twilight"<?php thk_value_check( 'highlighter_css', 'select', 'twilight' ); ?>>Twilight</option>
<option value="coy"<?php thk_value_check( 'highlighter_css', 'select', 'coy' ); ?>>Coy</option>
<option value="solarized-light"<?php thk_value_check( 'highlighter_css', 'select', 'solarized-light' ); ?>>Solarized Light</option>
<option value="tomorrow-night"<?php thk_value_check( 'highlighter_css', 'select', 'tomorrow-night' ); ?>>Tomorrow Night</option>
</select>
</p>
</li>
</ul>
