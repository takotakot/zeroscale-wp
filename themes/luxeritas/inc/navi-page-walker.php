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
 * 固定ページの一覧がそのままグローバルナビになってる場合の Walker
 *---------------------------------------------------------------------------*/
if( class_exists('THK_Global_Page_Walker') === false ):
class THK_Global_Page_Walker extends Walker_Page {
	private $_current_item;
	private $_wrap_menu_item;

	public function start_lvl( &$output, $depth = 0, $args = array() ) {
		global $luxe;

		if( isset( $luxe['amp'] ) || !isset( $this->_wrap_menu_item[$this->_current_item->ID] ) ) {
			$output .= '<ul class="sub-menu gu">';
		}
	}

	public function end_lvl( &$output, $depth = 0, $args = array() ) {
		global $luxe;

		if( isset( $luxe['amp'] ) || !isset( $this->_wrap_menu_item[(int)$this->_current_item->post_parent] ) ) {
			$output .= '</ul>';
		}
	}

	public function start_el( &$output, $item, $depth = 0, $args = array(), $current_page = 0 ) {
		global $luxe;

		$this->_current_item = $item;

		$classes = array( 'page_item', 'page-item-' . $item->ID, 'gl' );

		if( isset( $args['pages_with_children'][$item->ID] ) ) {
			$classes[] = 'page_item_has_children';
		}

		if( !empty( $current_page ) ) {
			$_current_page = get_post( $current_page );

			if( $_current_page && in_array( $item->ID, $_current_page->ancestors, true ) ) {
				$classes[] = 'current_page_ancestor';
			}

			if( $item->ID == $current_page ) {
				$classes[] = 'current_page_item';
			}
			elseif( $_current_page && $item->ID === $_current_page->post_parent ) {
				$classes[] = 'current_page_parent';
			}
		}
		elseif( get_option( 'page_for_posts' ) == $item->ID ) {
			$classes[] = 'current_page_parent';
		}

		if( (int)$item->post_parent === 0 ) {
			$page_template = get_post_meta( $item->ID, '_wp_page_template', true );
			if( !empty( $page_template ) && stripos( $page_template, 'pages/wrapper-menu.php' ) !== false ) {
				if( !isset( $this->_wrap_menu_item[$item->ID] ) ) {
					$this->_wrap_menu_item[$item->ID] = $item->ID;
				}
			}
		}
		else {
			if( isset( $this->_wrap_menu_item[$item->post_parent] ) ) {
				$this->_wrap_menu_item[$item->ID] = $item->ID;
			}
		}

		$wrap_menu_page = '';
		$class_names = '';
		$item_output = '';

		if( !empty( $this->_wrap_menu_item[$item->ID]) ) {
			$wrap_menu_page = ' data-child-id="' . $this->_wrap_menu_item[$item->ID] . '-1"';
		}

		if( isset( $luxe['amp'] ) || ( !isset( $luxe['amp'] ) && !isset( $this->_wrap_menu_item[$item->post_parent] ) ) ) {
			if( !empty( $this->_wrap_menu_item[$item->ID] ) && (int)$item->post_parent === 0 ) {
				$classes[] = 'has-wrap-menu';

				if( !isset( $luxe['amp'] ) && in_array( 'page_item_has_children', $classes, true ) ) {
					$classes = array_diff( $classes, ['page_item_has_children'] );
					$classes = array_values( $classes );
				}

				$item_output .= '<a href="#menu-item-'. $this->_wrap_menu_item[$item->ID] . '" class="wrap-menu-href">';

				if( isset( $luxe['amp'] ) ) {
					$item_output .= '<span id="menu-item-'. $this->_wrap_menu_item[$item->ID] . '-1" class="wtg wrap-target"></span>';
				}
			}
			else {
				$attributes = !empty( $item->ID ) ? ' href="' . esc_url( get_permalink( $item->ID ) ) .'"' : '';
				$item_output .= '<a' . $attributes . '>';
			}

			$item_output .= $args['link_before'] . $item->post_title . $args['link_after'] . '</a>';

			if( !isset( $luxe['amp'] ) ) {
				if( !empty( $this->_wrap_menu_item[$item->ID] ) ) {
					$item_output .= '<div id="menu-item-'. $this->_wrap_menu_item[$item->ID] . '-1" class="gnavi-wrap-menu"><div class="gnavi-wrap-container post">' . apply_filters( 'thk_content', get_post( $this->_wrap_menu_item[$item->ID] )->post_content ) . '</div></div>';
				}
			}

			$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item ) );
			$class_names = ' class="'. esc_attr( $class_names ) . '"';

			$output .= '<li id="menu-item-'. $item->ID . '"' . $class_names . $wrap_menu_page . '>';
			$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
		}
	}

	public function end_el( &$output, $item, $depth = 0, $args = array() ) {
		global $luxe;

		if( isset( $luxe['amp'] ) || !isset( $this->_wrap_menu_item[(int)$item->post_parent] ) ) {
			$output .= '</li>';
		}
	}
}
endif;

/*---------------------------------------------------------------------------
 * wp_page_menu の代替関数
 *---------------------------------------------------------------------------*/
if( function_exists('thk_page_menu') === false ):
function thk_page_menu() {
	global $luxe, $_is;

	$show_home = false;
	$class_names = '';

	if( !empty( $luxe['home_text'] ) ) {
		$show_home = $luxe['home_text'];
	}

	$wp_page_menu_args = array(
		'echo'  => false,
		'depth' => 3,
		'menu_class'  => 'gc gnavi-container',
		//'container' => false,
		'link_before' => '<span class="gim gnavi-item">',
		'link_after'  => '</span>',
		'before' => '<ul class="menu gu">',
		'after'  => '</ul>',
		'show_home' => $show_home,
		'walker' => new THK_Global_Page_Walker()
	);

	$ret = str_replace( '<li >', '<li>', wp_page_menu( $wp_page_menu_args ) );

	if( ( $_is['home'] === true && (int)get_option( 'page_on_front' ) === 0 ) || $_is['front_page'] === true ) {
		$class_names = 'current_page_item ';
	}
	$ret = str_replace( '<ul class="menu gu"><li', '<ul class="menu gu"><li class="' . $class_names . 'gl"', wp_page_menu( $wp_page_menu_args ) );

	return $ret;
}
endif;
