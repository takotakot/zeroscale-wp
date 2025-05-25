<?php
/**
 * Category tree for Luxeritas WordPress Theme
 *
 * @copyright Copyright (C) 2019 Maiden Web Factory.
 * @author LunaNuko
 * @link https://thk.kanzae.net/
 */

/**
 * Template Name: Custom global nav
 *
 * @package WordPress
 */
__( 'Custom global nav', 'luxeritas' );
__( 'Custom global nav', 'luxech' );

global $luxe, $wp_scripts;

unset(
	$luxe['amp_enable'],
	$luxe['rss_feed_enable'],
	$luxe['atom_feed_enable'],
	$luxe['author_visible']
);

$wp_scripts->registered = array();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=yes" />
<meta name="robots" content="noindex, nofollow, noarchive, noimageindex" />
<?php
$remove = 'remove_action';

$remove( 'wp_head', 'index_rel_link' );
$remove( 'wp_head', 'rsd_link' );
$remove( 'wp_head', 'feed_links', 2 );
$remove( 'wp_head', 'feed_links_extra', 3 );
$remove( 'wp_head', 'wp_generator' );
$remove( 'wp_head', 'wlwmanifest_link' );
$remove( 'wp_head', 'start_post_rel_link', 10, 0 );
$remove( 'wp_head', 'parent_post_rel_link', 10, 0 );
$remove( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );
$remove( 'wp_head', 'rel_canonical' );
$remove( 'wp_head', 'wp_shortlink_wp_head' );

remove_filter( 'wp_head', 'thk_insert_description', 5 );
remove_filter( 'wp_head', 'thk_insert_ogp', 6 );

ob_start();
wp_head();
$head = ob_get_clean();

$head = str_replace( "\t", '', $head );
$head = str_replace( "important;\n", 'important;', $head );
$head = preg_replace( '#<noscript>.+?</noscript>\s*#', '', $head );
$head = preg_replace( '#<link[^>]+?preload[^>]+?>\s*#', '', $head );

echo $head;
?>
<style>.wrapper_menu{margin: auto;width: 100%}</style>
</head>
<body>
<div class="container">
<div class="wrapper_menu post">
<?php
remove_filter( 'thk_content', 'thk_intersection_observer_replace_all', 99 );
echo apply_filters( 'thk_content', '' );
?>
</div>
</div>
</body>
</html>
