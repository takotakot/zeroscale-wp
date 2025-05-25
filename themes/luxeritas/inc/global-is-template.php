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
 * global
 *---------------------------------------------------------------------------*/
global $_is;

$_is['preview']		= is_preview();
$_is['front_page']	= is_front_page();
$_is['home']		= is_home();
$_is['singular']	= is_singular();
$_is['archive']		= is_archive();
//$_is['search']	= is_search();
$_is['feed']		= is_feed();
$_is['404']		= is_404();

if( $_is['singular'] === true ) {
	$_is['single']		= is_single();
	$_is['page']		= is_page();
	$_is['attachment']	= is_attachment();
	$_is['comments_open']	= comments_open();
}
elseif( $_is['archive'] === true ) {
	$_is['category']	= is_category();
	$_is['tag']		= is_tag();
	if( $_is['category'] === false && $_is['tag'] === false ) {
		$_is['tax']		= is_tax();
		$_is['day']		= is_day();
		$_is['month']		= is_month();
		$_is['year']		= is_year();
		$_is['author']		= is_author();
		$_is['post_type_archive'] = is_post_type_archive();
	}
}
