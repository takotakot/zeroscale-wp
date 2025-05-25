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

global $luxe, $_is, $awesome, $post, $cat;

?>
<div itemprop="breadcrumb">
<ol id="breadcrumb">
<?php
	if( $_is['front_page'] === true ) {
?>
<li><?php echo $awesome['home']; ?><?php echo $luxe['home_text']; ?><i class="arrow">&gt;</i></li>
<?php
	}
	elseif( $_is['page'] === true ) {
		$i = 2;
		$parents = array_reverse( get_post_ancestors( $post->ID ) );
?>
<li><?php echo $awesome['home']; ?><a href="<?php echo THK_HOME_URL; ?>"><?php echo $luxe['home_text']; ?></a><i class="arrow">&gt;</i></li><?php
		if( empty( $parents ) ) {
?>
<li><?php echo $awesome['file']; ?><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
<?php
		}
		else {
			foreach ( $parents as $p_id ){
?>
<li><?php echo $awesome['folder']; ?><a href="<?php echo get_page_link( $p_id );?>"><?php echo get_page( $p_id )->post_title; ?></a><i class="arrow">&gt;</i></li>
<?php
				++$i;
			}
?>
<li><?php echo $awesome['file']; ?><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
<?php
		}
	}
	elseif( $_is['attachment'] === true ) {
?>
<li><?php echo $awesome['home']; ?><a href="<?php echo THK_HOME_URL; ?>"><?php echo $luxe['home_text']; ?></a><i class="arrow">&gt;</i></li><?php
?><li><?php echo $awesome['file']; ?><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
<?php
	}
	elseif( $_is['single'] === true || $_is['category'] === true ) {
		$cat_obj = $_is['single'] === true ? get_the_category() : array( get_category( $cat ) );

		if( empty( $cat_obj ) ) {
			$post_type = get_post_type();
			$post_type_obj = get_post_type_object( $post_type );

			if( !empty( $post_type_obj->label ) ) {
				$archive_link = get_post_type_archive_link( $post_type );
				$label = $post_type_obj->label;
			}
		}

		if( is_wp_error( $cat_obj ) === false && !empty( $cat_obj ) ) {
			$html = '';
			$html_array = array();
			$pars = !empty( $cat_obj ) && isset( $cat_obj[0]->parent ) ? $pars = get_category( $cat_obj[0]->parent ) : '';
?>
<li><?php echo $awesome['home']; ?><a href="<?php echo THK_HOME_URL; ?>"><?php echo $luxe['home_text']; ?></a><i class="arrow">&gt;</i></li><?php
			if( isset( $post->post_type ) && $post->post_type !== 'post' ) {
				if( get_post_type_archive_link( $post->post_type ) !== false ) {
					$post_type_obj = get_post_type_object( $post->post_type );
?>
<li><?php echo $awesome['folder']; ?><a href="<?php echo get_post_type_archive_link( $post->post_type ); ?>"><?php echo $post_type_obj->label; ?></a><?php if( !empty( $pars ) ) { ?><i class="arrow">&gt;</i><?php } ?></li><?php
				}
			}

			if( !empty( $pars ) ) {
				while( $pars && !is_wp_error( $pars ) && $pars->cat_ID != 0 ) {
					$html_array[] = '<li>' . $awesome['folder'] . '<a href="' . get_category_link($pars->cat_ID) . '">' . $pars->name . '</a><i class="arrow">&gt;</i></li>';
					$pars = get_category( $pars->parent );
				}
				if( !empty( $html_array ) ) $html_array = array_reverse( $html_array );

				foreach( (array)$html_array as $val ) {
					$html .= $val;
				}

				$title = !empty( $cat_obj ) ? $cat_obj[0]->name : '';
				if( $_is['category'] === true ) {
					$title = '<h1>' . $cat_obj[0]->name . '</h1>';
				}
				echo $html
				,	'<li>' . $awesome['folder-open'] . '<a href="'
				,	get_category_link($cat_obj[0]->cat_ID), '">', $title, '</a></li>';
			}
		}
		elseif( isset( $label ) ) {
?>
<li><?php echo $awesome['home']; ?><a href="<?php echo THK_HOME_URL; ?>"><?php echo $luxe['home_text']; ?></a><i class="arrow">&gt;</i></li><?php
			if( !empty( $archive_link ) ) {
?>
<li><?php echo $awesome['folder']; ?><a href="<?php echo $archive_link; ?>"><?php echo $label; ?></a><?php if( !empty( $pars ) ) { ?><i class="arrow">&gt;</i><?php } ?></li><?php
			}
		}
		else {
?>
<li><?php echo $awesome['home']; ?><a href="<?php echo THK_HOME_URL; ?>"><?php echo $luxe['home_text']; ?></a><i class="arrow">&gt;</i></li><?php

		}
	}
	elseif(
		$_is['tag'] === true	||
		$_is['tax'] === true	||
		$_is['day'] === true	||
		$_is['month'] === true	||
		$_is['year'] === true	||
		$_is['author'] === true	||
		$_is['search'] === true	||
		$_is['post_type_archive'] === true
	) {
?>
<li><?php echo $awesome['home']; ?><a href="<?php echo THK_HOME_URL; ?>"><?php echo $luxe['home_text']; ?></a><i class="arrow">&gt;</i></li><?php
?><li><?php echo $awesome['folder']; ?><h1><?php
		if( $_is['tag'] === true ) {
			single_tag_title();
		}
		elseif( $_is['tax'] === true ) {
			single_term_title();
		}
		elseif( $_is['day'] === true ) {
			 echo get_the_date( __( 'F j, Y', 'luxeritas' ) );
		}
		elseif( $_is['month'] === true ) {
			echo get_the_date( __( 'F Y', 'luxeritas' ) );
		}
		elseif( $_is['year'] === true ) {
			echo get_the_date( __( 'Y', 'luxeritas' ) );
		}
		elseif( $_is['author'] === true ) {
			echo esc_html(get_queried_object()->display_name);
		}
		elseif( $_is['search'] === true ) {
			echo sprintf( __( 'Search results of [%s]', 'luxeritas' ), esc_html( $s ) );
		}
		elseif( $_is['post_type_archive'] === true ) {
			echo post_type_archive_title( '', false );
		}
?></h1></li>
<?php
	}
	else {
?><li><?php echo $awesome['home']; ?><a href="<?php echo THK_HOME_URL; ?>"><?php echo $luxe['home_text']; ?></a><i class="arrow">&gt;</i></li>
<?php
		if( $_is['404'] === true ) {
?><li><?php echo $awesome['file']; ?><?php echo __( 'Page not found', 'luxeritas' ); ?></li>
<?php
		}
	}
?>
</ol><!--/breadcrumb-->
</div>
