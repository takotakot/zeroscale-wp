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

global $luxe, $_is, $awesome;

$visible = array( 'P' => false, 'M' => false, 'C' => false, 'T' => false, 'X' => false );

if( $_is['singular'] === true ) {
	if( isset( $luxe['meta_under'] ) ) {
		if( isset( $luxe['post_date_u_visible'] ) )		$visible['P'] = true;
		if( isset( $luxe['mod_date_u_visible'] ) )		$visible['M'] = true;
		if( isset( $luxe['category_meta_u_visible'] ) )		$visible['C'] = true;
		if( isset( $luxe['tag_meta_u_visible'] ) )		$visible['T'] = true;
		if( isset( $luxe['tax_meta_u_visible'] ) )		$visible['X'] = true;
	}
	else {
		if( isset( $luxe['post_date_visible'] ) )		$visible['P'] = true;
		if( isset( $luxe['mod_date_visible'] ) )		$visible['M'] = true;
		if( isset( $luxe['category_meta_visible'] ) )		$visible['C'] = true;
		if( isset( $luxe['tag_meta_visible'] ) )		$visible['T'] = true;
		if( isset( $luxe['tax_meta_visible'] ) )		$visible['X'] = true;
	}
}
else {
	if( isset( $luxe['meta_under'] ) ) {
		if( isset( $luxe['list_post_date_u_visible'] ) )	$visible['P'] = true;
		if( isset( $luxe['list_mod_date_u_visible'] ) )		$visible['M'] = true;
		if( isset( $luxe['list_category_meta_u_visible'] ) )	$visible['C'] = true;
		if( isset( $luxe['list_tag_meta_u_visible'] ) )		$visible['T'] = true;
		if( isset( $luxe['list_tax_meta_u_visible'] ) )		$visible['X'] = true;
	}
	else {
		if( isset( $luxe['list_post_date_visible'] ) )		$visible['P'] = true;
		if( isset( $luxe['list_mod_date_visible'] ) )		$visible['M'] = true;
		if( isset( $luxe['list_category_meta_visible'] ) )	$visible['C'] = true;
		if( isset( $luxe['list_tag_meta_visible'] ) )		$visible['T'] = true;
		if( isset( $luxe['list_tax_meta_visible'] ) )		$visible['X'] = true;
	}
}

if( $visible['P'] === true || $visible['M'] === true || $visible['C'] === true || $visible['T'] === true || $visible['X'] === true ) {
	$under = ( isset( $luxe['meta_under'] ) ) ? ' meta-u' : '';
	$metatag = '<p class="meta' . $under . '">';
	$mdfdate  = get_the_modified_date('Ymd');
	$postdate = get_the_date('Ymd');
	$published = '';
	$meta = '';

	if( $visible['P'] === true || $visible['M'] === true ) {
		if( $_is['singular'] === true ) {
			$meta .= $awesome['clock'];
			$published = ' published';
		}
		elseif( ( $visible['P'] === false && $visible['M'] === true ) ) {
			$meta .= $awesome['repeat'];
		}
		else {
			$meta .= $awesome['calendar'];
		}

		if( empty( $postdate ) && empty( $mdfdate ) ) {
		}
		elseif( empty( $postdate ) && $visible['M'] === true ) {
			$meta .= sprintf(
				'<span class="date' . $published . '"><time class="entry-date updated" datetime="%1$s" itemprop="dateModified">%2$s</time></span>',
				get_the_modified_date( 'c' ), get_the_modified_date()
			);
		}
		else {
			if( $postdate < $mdfdate ) {
				if( $visible['P'] === true && $visible['M'] === true ) {
					if( $luxe['published'] === 'updated' ) {
						$meta .= sprintf(
							'<span class="date' . $published . '"><meta itemprop="datePublished" content="%1$s" />%2$s</span>' .
							$awesome['repeat'] .
							'<span class="date"><time class="entry-date updated" datetime="%3$s" itemprop="dateModified">%4$s</time></span>',
							get_the_date( 'c' ), get_the_date(), get_the_modified_date( 'c' ), get_the_modified_date()
						);
					}
					else {
						$meta .= sprintf(
							'<span class="date' . $published . '"><time class="entry-date updated" datetime="%1$s" itemprop="datePublished">%2$s</time></span>' .
							$awesome['repeat'] .
							'<span class="date"><meta itemprop="dateModified" content="%3$s">%4$s</span>',
							get_the_date( 'c' ), get_the_date(), get_the_modified_date( 'c' ), get_the_modified_date()
						);
					}
				}
				elseif( $visible['P'] === true ) {
					$meta .= sprintf(
						'<span class="date' . $published . '"><time class="entry-date updated" datetime="%1$s" itemprop="datePublished">%2$s</time></span>',
						get_the_date( 'c' ), get_the_date()
					);
				}
				elseif( $visible['M'] === true ) {
					$meta .= sprintf(
						'<span class="date' . $published . '"><time class="entry-date updated" datetime="%1$s" itemprop="dateModified">%2$s</time></span>',
						get_the_modified_date( 'c' ), get_the_modified_date()
					);
				}
			}
			else {
				if( $_is['singular'] === false && isset( $luxe['meta_under'] ) && isset( $luxe['list_post_date_visible'] ) ) {
					$meta = '';
				}
				else {
					if( $visible['P'] === true || $visible['M'] === true ) {
						$meta .= sprintf(
							'<span class="date' . $published . '"><time class="entry-date updated" datetime="%1$s" itemprop="datePublished">%2$s</time></span>',
							get_the_date( 'c' ), get_the_date()
						);
					}
				}
			}
		}
	}

	if( $_is['page'] === false ) {
		if( $_is['single'] === true ) unset( $luxe['list_meta_max_item'] );

		if( $visible['C'] === true ) {
			$category = '';
			$categories = array();
			$cat_array = get_the_category( $wp_query->post->ID );

			if( isset( $luxe['list_meta_max_item'] ) && is_int( $luxe['list_meta_max_item'] ) ) {
				foreach( (array)$cat_array as $k => $val) {
					if( $k >= $luxe['list_meta_max_item'] ) unset( $cat_array[$k] );
				}
			}

			foreach( (array)$cat_array as $value) {
				if( isset( $value->cat_ID ) && isset( $value->cat_name ) ) {
					$categories[] = '<a href="' . get_category_link( $value->cat_ID ) . '">' . esc_html( $value->cat_name ) . '</a>';
				}
			}

			if( isset( $categories[0] ) ) $categories[0] .= '</span>';

			$category = implode( '<span class="break">,</span>', $categories );

			if( !empty( $category ) ) {
				$meta .= '<span class="category items" itemprop="keywords"><span class="first-item">' . $awesome['folder'] . $category . '</span>';
			}
		}

		if( $visible['T'] === true ) {
			$tag = '';
			$tags = array();
			$tag_array = get_the_tags( $wp_query->post->ID );

			if( isset( $luxe['list_meta_max_item'] ) && is_int( $luxe['list_meta_max_item'] ) ) {
				foreach( (array)$tag_array as $k => $val) {
					if( $k >= $luxe['list_meta_max_item'] ) unset( $tag_array[$k] );
				}
			}

			foreach( (array)$tag_array as $value ) {
				if( isset( $value->term_id ) && isset( $value->name ) ) {
					$tags[] = '<a href="' . get_tag_link( $value->term_id ) . '">' . esc_html( $value->name ) . '</a>';
				}
			}

			if( isset( $tags[0] ) ) $tags[0] .= '</span>';

			$tag = implode( '<span class="break">,</span>', $tags );

			if( !empty( $tag ) ) {
				$meta .= '<span class="tags items" itemprop="keywords"><span class="first-item">' . $awesome['tags'] . $tag . '</span>';
			}
		}

		if( $visible['X'] === true ) {
			$tax = '';
			$taxs = array();
			$tax_names = array();
			$taxonomy_array = array();

			$taxonomies = get_taxonomies();

			foreach( $taxonomies as $taxonomy ) {
				$terms = get_the_terms( $wp_query->post->ID, $taxonomy );
				foreach ( (array)$terms as $tax ) {
					if( isset( $tax->taxonomy ) ) {
						$tax_names[] = $tax->taxonomy;
					}
				}
			}

			foreach( (array)array_unique( $tax_names ) as $value ) {
				$taxonomy_array = array_merge( $taxonomy_array, (array)get_the_terms( $wp_query->post->ID, $value ) );
			}

			if( isset( $luxe['list_meta_max_item'] ) && is_int( $luxe['list_meta_max_item'] ) ) {
				foreach( (array)$taxonomy_array as $k => $val) {
					if( $k >= $luxe['list_meta_max_item'] ) unset( $taxonomy_array[$k] );
				}
			}

			foreach( (array)$taxonomy_array as $value ) {
				if( isset( $value->term_id ) && isset( $value->name ) ) {
					$taxs[] = '<a href="' . get_term_link( $value->term_id ) . '">' . esc_html( $value->name ) . '</a>';
				}
			}

			if( isset( $taxs[0] ) ) $taxs[0] .= '</span>';

			$tax = implode( '<span class="break">,</span>', $taxs );

			if( !empty( $taxs ) ) {
				$meta .= '<span class="taxs items" itemprop="keywords"><span class="first-item">' . $awesome['tag'] . $tax . '</span>';
			}
		}
	}

	if( !empty( $meta ) ) {
		echo $metatag, $meta, '</p>';
	}
}
