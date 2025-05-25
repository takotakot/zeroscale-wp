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

/*---------------------------------------------------------------------------
 * Feed にサムネイル画像追加
 * source: http://matthewman.net/2012/10/09/wordpress-rss-custom-elements/
 *---------------------------------------------------------------------------*/
// add the namespace to the RSS opening element
add_action( 'rss2_ns', function() {
	echo 'xmlns:media="http://search.yahoo.com/mrss/"';
});

// add the requisite tag where a thumbnail exists
add_action( 'rss2_item', function() {
	global $post;
	if( has_post_thumbnail( $post->ID ) === true ) {
		$thumb_ID = get_post_thumbnail_id( $post->ID );
		$details = wp_get_attachment_image_src( $thumb_ID, 'thumbnail' );
		if( is_array( $details ) ) {
			echo '<media:thumbnail url="' . $details[0] . '" width="' . $details[1] . '" height="' . $details[2] . '" />';
		}
	}
});
