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
 * 外観 -> メニュー でメニュー作ってる場合の Walker
 *---------------------------------------------------------------------------*/
if( class_exists('THK_Global_Nav_Walker') === false ):
class THK_Global_Nav_Walker extends Walker_Nav_Menu {
	private $_current_item;
	private $_wrap_menu_item;

	public function start_lvl( &$output, $depth = 0, $args = null ) {
		global $luxe;

		if( isset( $luxe['amp'] ) || !isset( $this->_wrap_menu_item[$this->_current_item->ID] ) ) {
			$output .= '<ul class="sub-menu gu">';
		}
	}

	public function end_lvl( &$output, $depth = 0, $args = null ) {
		global $luxe;

		if( isset( $luxe['amp'] ) || !isset( $this->_wrap_menu_item[(int)$this->_current_item->menu_item_parent] ) ) {
			$output .= '</ul>';
		}
	}

	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		global $luxe;

		$this->_current_item = $item;

		if( (int)$item->menu_item_parent === 0 ) {
			$page_template = get_post_meta( $item->object_id, '_wp_page_template', true );
			if( !empty( $page_template ) && stripos( $page_template, 'pages/wrapper-menu.php' ) !== false ) {
				if( !isset( $this->_wrap_menu_item[$item->ID] ) ) {
					$this->_wrap_menu_item[$item->ID] = $item->object_id;
				}
			}
		}
		else {
			if( isset( $this->_wrap_menu_item[$item->menu_item_parent] ) ) {
				$this->_wrap_menu_item[$item->ID] = $item->object_id;
			}
		}

		$wrap_menu_page = '';
		$class_names = '';

		$classes = empty( $item->classes ) ? array() : (array)$item->classes;

		foreach( (array)$classes as $key => $val ) {
			// $classes の中で "page-" or "page_" or "post-" or "post_" が含まれて "menu-" の文字列を含まないものは削除 (ページメニュー class との重複回避)
			if(
				stripos( $val, 'menu-' ) === false && 
				(
					stripos( $val, 'page-' ) !== false || stripos( $val, 'page_' ) !== false ||
					stripos( $val, 'post-' ) !== false || stripos( $val, 'post_' ) !== false
				)
			) {
				unset( $classes[$key] );
			}
		}

		if( !empty( $this->_wrap_menu_item[$item->ID]) ) {
			$wrap_menu_page = ' data-child-id="' . $item->ID . '-1"';
		}

		if( isset( $luxe['amp'] ) || ( !isset( $luxe['amp'] ) && !isset( $this->_wrap_menu_item[$item->menu_item_parent] ) ) ) {
			$classes[] = 'menu-item-' . $item->ID;
			$classes[] = 'gl';

			$item_output = $args->before;

			if( !empty( $this->_wrap_menu_item[$item->ID] ) && (int)$item->menu_item_parent === 0 ) {
				$classes[] = 'has-wrap-menu';

				if( !isset( $luxe['amp'] ) && in_array( 'menu-item-has-children', $classes, true ) ) {
					$classes = array_diff( $classes, ['menu-item-has-children'] );
					$classes = array_values( $classes );
				}

				$item_output .= '<a href="#menu-item-'. $item->ID . '-1" class="wrap-menu-href">';

				if( isset( $luxe['amp'] ) ) {
					$item_output .= '<span id="menu-item-'. $item->ID . '-1" class="wtg wrap-target"></span>';
				}
			}
			else {
				$attributes = !empty( $item->url ) ? ' href="' . esc_url( $item->url ) .'"' : '';
				$attributes .= !empty( $item->post_excerpt ) ? ' title="' . $item->post_excerpt .'"' : '';
				$attributes .= !empty( $item->target ) ? ' target="_blank" rel="external noopener noreferrer"' : '';
				if( !empty( $item->xfn ) ) {
					if( strpos( $attributes, ' rel="' ) !== false ) {
						$attributes = str_replace(  'rel="', 'rel="' . $item->xfn . ' ', $attributes );
					}
					else {
						$attributes .= ' rel="' . $item->xfn . '"';
					}
				}
				$item_output .= '<a' . $attributes . '>';
			}

			$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item ) );
			$class_names = ' class="'. esc_attr( $class_names ) . '"';

			$description = !empty( trim( $item->description ) ) ? '<i class="gnavi-desc">' . trim( $item->description ) . '</i>' : '';

			$item_output .= $args->link_before . $item->title . $description . $args->link_after . '</a>';

			if( !isset( $luxe['amp'] ) ) {
				if( !empty( $this->_wrap_menu_item[$item->ID] ) ) {
					$item_output .= '<div id="menu-item-'. $item->ID . '-1" class="gnavi-wrap-menu"><div class="gnavi-wrap-container post">' . apply_filters( 'thk_content', get_post( $this->_wrap_menu_item[$item->ID] )->post_content ) . '</div></div>';
				}
			}

			$item_output .= $args->after;

			$output .= '<li id="menu-item-'. $item->ID . '"' . $class_names . $wrap_menu_page . '>';
			$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
		}
	}

	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		global $luxe;

		if( isset( $luxe['amp'] ) || !isset( $this->_wrap_menu_item[(int)$item->menu_item_parent] ) ) {
			$output .= '</li>';
		}
	}
}
endif;
