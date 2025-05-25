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

require_once( INC . 'thk-filesystem.php' );

/*---------------------------------------------------------------------------
 * Optimize
 *---------------------------------------------------------------------------*/
class thk_optimize {
	private $_thk_files	= null;
	private $_filesystem	= null;
	private $_js_dir	= null;
	private $_css_dir	= null;
	private $_tmpl_dir	= null;
	private $_patterns	= array();
	private $_wrap_menu_used	= false;

	public function __construct() {
		require_once( INC . 'files.php' );
		require_once( INC . 'create-javascript.php' );

		$this->_js_dir   = TPATH . DSEP . 'js' . DSEP;
		$this->_css_dir  = TPATH . DSEP . 'css' . DSEP;
		$this->_tmpl_dir = TPATH . DSEP . 'styles' . DSEP;

		$this->_thk_files  = new thk_files();
		$this->_patterns = get_pattern_list( 'shortcode', false, true );

		// filesystem initialization
		$this->_filesystem = new thk_filesystem();
		if( $this->_filesystem->init_filesystem() === false ) return false;

		// Determine if Custom global nav is used
		if( thk_wrap_menu_used() === true ) $this->_wrap_menu_used = true;
	}

	/*
	 * CSS Optimize initialization
	 */
	public function css_optimize_init() {
		global $luxe;

		$files = $this->_thk_files->styles();

		if( !isset( $luxe['wrap_menu_used'] ) && $this->_wrap_menu_used === true ) {
			$luxe['wrap_menu_used'] = true;
		}

		// get overall image
		if( get_theme_admin_mod( 'all_clear', false ) === false ) {
			$files['style_thk'] = TPATH . DSEP . get_overall_image();
		}

		// file exists check
		foreach( $files as $key => $val ) {
			if( file_exists( $val ) === false ) unset( $files[$key] );
		}

		// determining the conditions
		$mode_unset_array = [
			'luxe-mode',
			'bootstrap',
			'bootstrap4',
			'bootstrap5'
		];
		$luxe_mode_select = $luxe['luxe_mode_select'];

		if( empty( $luxe['luxe_mode_select'] ) || $luxe['luxe_mode_select'] === 'luxeritas' ) {
			$luxe_mode_select = 'luxe-mode';
			unset( $files['bootstrap-clear'] );
		}

		foreach( $mode_unset_array as $val ) {
			if( $val !== $luxe_mode_select ) {
				unset( $files[$val] );
			}
		}
		unset( $mode_unset_array, $luxe_mode_select );

		// Material icons
		if( !isset( $luxe['material_load'] ) ) {
			unset( $files['material-icons'] );
		}

		// Grid layout
		if( !isset( $luxe['grid_enable'] ) ) {
			unset( $files['grid'] );
		}

		// Custom global nav ( Wrapper menu )
		if( $this->_wrap_menu_used === false ) {
			unset( $files['nav-wrap-menu'] );
		}

		// sns buttons
		if( isset( $luxe['sns_tops_enable'] ) || isset( $luxe['sns_bottoms_enable'] ) ) {
			if( $luxe['sns_tops_type'] !== 'color' && $luxe['sns_tops_type'] !== 'white' && $luxe['sns_bottoms_type'] !== 'color' && $luxe['sns_bottoms_type'] !== 'white' ) {
				unset( $files['sns'] );
			}
			if( $luxe['sns_tops_type'] !== 'flatc' && $luxe['sns_tops_type'] !== 'flatw' && $luxe['sns_bottoms_type'] !== 'flatc' && $luxe['sns_bottoms_type'] !== 'flatw' ) {
				unset( $files['sns-flat'] );
			}
			if( $luxe['sns_tops_type'] !== 'iconc' && $luxe['sns_tops_type'] !== 'iconw' && $luxe['sns_bottoms_type'] !== 'iconc' && $luxe['sns_bottoms_type'] !== 'iconw' ) {
				unset( $files['sns-icon'] );
			}
			if( $luxe['sns_tops_type'] !== 'normal' && $luxe['sns_bottoms_type'] !== 'normal' ) {
				unset( $files['sns-normal'] );
			}
		}

		if( !isset( $luxe['toc_auto_insert'] ) )	unset( $files['toc'] );
		if( !isset( $luxe['toc_css'] ) )		unset( $files['toc'] );
		if( !isset( $luxe['blogcard_enable'] ) )	unset( $files['blogcard'] );
		if( !isset( $luxe['css_search'] ) )		unset( $files['search'] );
		if( !isset( $luxe['css_archive'] ) )		unset( $files['archive'] );
		if( !isset( $luxe['css_archive_drop'] ) )	unset( $files['archive-drop'] );
		if( !isset( $luxe['css_calendar'] ) )		unset( $files['calendar'] );
		if( !isset( $luxe['css_tagcloud'] ) )		unset( $files['tagcloud'] );
		if( !isset( $luxe['css_new_post'] ) )		unset( $files['new-post'] );
		if( !isset( $luxe['css_rcomments'] ) )		unset( $files['rcomments'] );
		if( !isset( $luxe['css_adsense'] ) )		unset( $files['adsense'] );
		if( !isset( $luxe['css_follow_button'] ) )	unset( $files['follow-button'] );
		if( !isset( $luxe['css_rss_feedly'] ) )		unset( $files['rss-feedly'] );
		if( !isset( $luxe['css_qr_code'] ) )		unset( $files['qr-code'] );
		if( !isset( $luxe['css_pwa_install_box'] ) )	unset( $files['pwa-install-box'] );

		if( !isset( $luxe['pwa_enable'] ) || !isset( $luxe['pwa_offline_enable'] ) || !isset( $luxe['pwa_install_widget'] ) ) {
			unset( $files['pwa-install-box'] );
		}

		if( !isset( $luxe['global_navi_visible'] ) || !isset( $luxe['global_navi_mobile_type'] ) ) {
			unset(
				$files['mobile-common'],
				$files['mobile-menu']
			);
			if( !isset( $luxe['mobile_search_button'] ) ) {
				unset( $files['mobile-luxury'] );
			}
		}
		elseif( isset( $luxe['global_navi_mobile_type'] ) && ( $luxe['global_navi_mobile_type'] === 'luxury' || $luxe['global_navi_mobile_type'] === 'luxury_head' ) ) {
			unset( $files['mobile-menu'] );
		}
		else {
			if( !isset( $luxe['mobile_search_button'] ) ) {
				unset( $files['mobile-luxury'] );
			}
		}

		if( !isset( $luxe['head_band_search'] ) ) {
			unset( $files['head-search'] );
		}

		// balloon
		if( !isset( $luxe['balloon_enable'] ) ) {
			unset( $files['balloon'] );
		}

		return array_filter( $files, 'strlen' );
	}

	/*
	 * CSS Optimize
	 */
	public function css_optimize( $files = array(), $name = 'style.min.css', $dir_replace_flag = false ) {
		global $luxe, $wp_filesystem;

		$contents = array();

		$style_min = TPATH . DSEP . $name;

		// get stylesheet file content
		$replaces = $this->_thk_files->dir_replace();

		foreach( $files as $key => $val ) {
			if( isset( $replaces[$key] ) && $dir_replace_flag === true ) {
				if( $replaces[$key] === true ) {
					$contents[$key] = str_replace( '../', './', $wp_filesystem->get_contents( $val ) );
				}
				else {
					$contents[$key] = str_replace( '../', $replaces[$key], $wp_filesystem->get_contents( $val ) );
				}
			}
			else {
				$contents[$key] = $wp_filesystem->get_contents( $val );
			}
		}

		if( isset( $contents['material-icons'] ) ) {
			$material_classes = 'body .material-icons, body .material-icons-outlined';

			$material_replaced = $material_classes;

			if( isset( $luxe['material_add_rounded'] ) ) {
				$material_replaced .= ',body .material-icons-rounded';
			}
			if( isset( $luxe['material_add_sharp'] ) ) {
				$material_replaced .= ',body .material-icons-sharp';
			}
			if( isset( $luxe['material_add_two_tone'] ) ) {
				$material_replaced .= ',body .material-icons-two-tone';
			}

			if( $material_classes !== $material_replaced ) {
				$contents['material-icons'] = str_replace( $material_classes, $material_replaced, $contents['material-icons'] );
			}
		}

		// design file css
		$contents['design_file'] = thk_read_design_style( 'style.css' );
		if( empty( $contents['design_file'] ) ) unset( $contents['design_file'] );

		// get luxe customizer css
		if( get_theme_admin_mod( 'all_clear', false ) === false ) {
			$files['style_thk'] = TPATH . DSEP . get_overall_image();

			// 管理画面でのカスタマイズ内容
			$contents['customize'] = trim( str_replace( array( '<style>', '</style>' ), '', thk_custom_css() ) );
			if( $contents['customize'] === '/*! luxe customizer css */' ) {
				$contents['customize'] = '';
			}
			else {
				$contents['customize'] = str_replace( '/*! luxe customizer css */' . "\n", '/*! luxe customizer css */', $contents['customize'] );
			}
		}

		// balloon css replace
		if( isset( $contents['balloon'] ) ) {
			$contents['balloon'] = $this->ballon_css_replace( $contents['balloon'] );
		}

		// css bind
		$save = '';
		foreach( $contents as $value ) {
			$save .= $value . "\n";
		}

		// css compression and save
		if( isset( $luxe['parent_css_uncompress'] ) ) {
			if( $this->_filesystem->file_save( $style_min, $save ) === false ) return false;
		}
		else {
			if( $this->_filesystem->file_save( $style_min, thk_cssmin( $save, true ) ) === false ) return false;
		}

		return true;
	}

	/*
	 * Asynchronous CSS Optimize initialization
	 */
	public function css_async_optimize_init() {
		global $luxe;

		$files = $this->_thk_files->styles_async();

		// file exists check
		foreach( $files as $key => $val ) {
			if( file_exists( $val ) === false ) unset( $files[$key] );
		}

		// Block library
		if( $luxe['wp_block_library_load'] !== 'async' ) {
			unset( $files['block-library-style'], $files['block-library-theme'] );
		}

		// spotlight
		if( $luxe['gallery_type'] !== 'spotlight' ) {
			unset( $files['spotlight'] );
		}
		// strip
		if( $luxe['gallery_type'] !== 'strip' ) {
			unset( $files['strip'] );
		}
		// tosrus
		if( $luxe['gallery_type'] !== 'tosrus' ) {
			unset( $files['tosrus'] );
		}
		// lightcase
		if( $luxe['gallery_type'] !== 'lightcase' ) {
			unset( $files['lightcase'] );
		}
		// highslide
		if( $luxe['gallery_type'] !== 'highslide' ) {
			unset( $files['highslide'] );
		}
		// floatbox
		if( $luxe['gallery_type'] !== 'floatbox' ) {
			unset( $files['floatbox'] );
		}
		// fluidbox
		if( $luxe['gallery_type'] !== 'fluidbox' ) {
			unset( $files['fluidbox'] );
		}

		// Google Autocomplete
		if( !isset( $luxe['autocomplete'] ) ) {
			unset( $files['autocomplete'] );
		}

		return array_filter( $files, 'strlen' );
	}

	/*
	 * Asynchronous CSS Optimize
	 */
	public function css_async_optimize( $files = array(), $dir_replace_flag = false ) {
		global $luxe, $wp_filesystem;
		$contents = array();

		$style_min = TPATH . DSEP . 'style.async.min.css';

		// get stylesheet file content
		$replaces = $this->_thk_files->dir_replace();

		foreach( $files as $key => $val ) {
			if( isset( $replaces[$key] ) && $dir_replace_flag === true ) {
				if( $replaces[$key] === true ) {
					$contents[$key] = str_replace( '../', './', $wp_filesystem->get_contents( $val ) );
				}
				else {
					$contents[$key] = str_replace( '../', $replaces[$key], $wp_filesystem->get_contents( $val ) );
				}
			}
			else {
				$contents[$key] = $wp_filesystem->get_contents( $val );
			}
		}

		// css bind
		$save = '';
		foreach( $contents as $value ) {
			$save .= $value . "\n";
		}

		// css compression and save
		if( isset( $luxe['parent_css_uncompress'] ) ) {
			if( $this->_filesystem->file_save( $style_min, $save ) === false ) return false;
		}
		else {
			if( $this->_filesystem->file_save( $style_min, thk_cssmin( $save ) ) === false ) return false;
		}

		return true;
	}

	/*
	 * Amp CSS Optimize initialization
	 */
	public function css_amp_optimize_init() {
		global $luxe, $wp_filesystem;

		if( !isset( $luxe['wrap_menu_used'] ) && $this->_wrap_menu_used === true ) {
			$luxe['wrap_menu_used'] = true;
		}

		$files = $this->_thk_files->styles_amp();

		// get overall image
		if( get_theme_admin_mod( 'all_clear', false ) === false  ) {
			$files['style_amp'] = TPATH . DSEP . str_replace( '.css', '-amp.css', get_overall_image() );
		}

		// file exists check
		foreach( $files as $key => $val ) {
			if( file_exists( $val ) === false ) unset( $files[$key] );
		}

		if( isset( $luxe['sns_tops_enable'] ) || isset( $luxe['sns_bottoms_enable'] ) ) {
			if( $luxe['sns_tops_type'] === 'normal' ) $luxe['sns_tops_type'] = 'color';
			if( $luxe['sns_bottoms_type'] === 'normal' ) $luxe['sns_bottoms_type'] = 'color';

			if( $luxe['sns_tops_type'] !== 'color' && $luxe['sns_tops_type'] !== 'white' && $luxe['sns_bottoms_type'] !== 'color' && $luxe['sns_bottoms_type'] !== 'white' ) {
				unset( $files['sns'] );
			}
			if( $luxe['sns_tops_type'] !== 'flatc' && $luxe['sns_tops_type'] !== 'flatw' && $luxe['sns_bottoms_type'] !== 'flatc' && $luxe['sns_bottoms_type'] !== 'flatw' ) {
				unset( $files['sns-flat'] );
			}
			if( $luxe['sns_tops_type'] !== 'iconc' && $luxe['sns_tops_type'] !== 'iconw' && $luxe['sns_bottoms_type'] !== 'iconc' && $luxe['sns_bottoms_type'] !== 'iconw' ) {
				unset( $files['sns-icon'] );
			}
		}

		if( !isset( $luxe['toc_auto_insert'] ) )	unset( $files['toc'] );
		if( !isset( $luxe['toc_amp'] ) )		unset( $files['toc'] );
		if( !isset( $luxe['amp_css_archive'] ) )	unset( $files['archive'] );
		if( !isset( $luxe['amp_css_archive_drop'] ) )	unset( $files['archive-drop'] );
		if( !isset( $luxe['amp_css_calendar'] ) )	unset( $files['calendar'] );
		if( !isset( $luxe['amp_css_tagcloud'] ) )	unset( $files['tagcloud'] );
		if( !isset( $luxe['amp_css_new_post'] ) )	unset( $files['new-post'] );
		if( !isset( $luxe['amp_css_rcomments'] ) )	unset( $files['rcomments'] );
		if( !isset( $luxe['amp_css_adsense'] ) )	unset( $files['adsense'] );
		if( !isset( $luxe['amp_css_follow_button'] ) )	unset( $files['follow-button'] );
		if( !isset( $luxe['amp_css_rss_feedly'] ) )	unset( $files['rss-feedly'] );
		if( !isset( $luxe['amp_css_qr_code'] ) )	unset( $files['qr-code'] );

		if( !isset( $luxe['head_band_search'] ) )	unset( $files['head-search'] );

		// Custom global nav ( Wrapper menu )
		if( $this->_wrap_menu_used === false ) {
			unset( $files['nav-wrap-menu'] );
		}

		if( !isset( $luxe['balloon_enable'] ) ) {
			unset( $files['balloon'] );
		}

		// prism
/*
		foreach( $files as $key => $val ) {
			if( strpos( $key, 'prism-amp-' ) !== false ) {
				if( 'prism-amp-' . $luxe['highlighter_css'] !== $key ) {
					unset( $files[$key] );
				}
			}
		}

		if( !isset( $luxe['highlighter_enable'] ) ) {
			unset( $files['prism-amp-' . $luxe['highlighter_css']] );
		}
*/
		return array_filter( $files, 'strlen' );
	}

	/*
	 * Amp CSS Optimize
	 */
	public function css_amp_optimize( $files = array(), $dir_replace_flag = false ) {
		global $luxe, $wp_filesystem;
		$contents = array();

		foreach( $files as $key => $val ) {
			if( $dir_replace_flag === true ) {
				$contents[$key] = str_replace( '../', './', $wp_filesystem->get_contents( $val ) );
				$contents[$key] = str_replace( '!important', '', $contents[$key] );
			}
		}

		// balloon css replace
		if( isset( $contents['balloon'] ) ) {
			$contents['balloon'] = $this->ballon_css_replace( $contents['balloon'] );
		}

		return $contents;
	}

	/*
	 * Javascript Optimize
	 */
	public function javascript_optimize() {
		global $luxe, $wp_filesystem;

		$contents = array();
		$save = '';

		$luxe_min = $this->_js_dir . 'luxe.min.js';

		if( !isset( $luxe['wrap_menu_used'] ) && $this->_wrap_menu_used === true ) {
			$luxe['wrap_menu_used'] = true;
		}

		$jscript = new create_Javascript();

		/*
		 * Asynchronous Javascript Optimize
		 */

		$files = $this->_thk_files->scripts_async();

		// file exists check 1
		foreach( $files as $key => $val ) {
			if( $val === true ) continue;
			if( file_exists( $val ) === false ) unset( $files[$key] );
		}

		$files = array_filter( $files, 'strlen' );

		// get Asynchronous javascript file content
		$jscript = new create_Javascript();
		$tdel = pdel( get_template_directory_uri() );
		$sdel = pdel( get_stylesheet_directory_uri() );

		foreach( $files as $key => $val ) {
			if( $key === 'async' ) {
				$contents[$key] = $jscript->create_css_load_script( $tdel . '/style.async.min.css' );
				continue;
			}

			$contents[$key] = $wp_filesystem->get_contents( $val );
		}

		// SNS Count script
		if(
			( isset( $luxe['sns_count_cache_enable'] ) && isset( $luxe['sns_count_cache_force'] ) ) ||
			( isset( $luxe['sns_tops_enable'] ) && isset( $luxe['sns_tops_count'] ) ) ||
			( isset( $luxe['sns_bottoms_enable'] ) && isset( $luxe['sns_bottoms_count'] ) ) ||
			( isset( $luxe['sns_toppage_view'] ) && isset( $luxe['sns_bottoms_count'] ) )
		){
			$contents[$key] .= $jscript->create_sns_count_script();
		}

		// Url copy script
		if( isset( $luxe['copypage_tops_button'] ) || isset( $luxe['copypage_bottoms_button'] ) ) {
			$contents['copy_url'] = $jscript->create_copy_button_script();
		}

		// javascript bind 1
		foreach( $contents as $value ) {
			$save .= $value . "\n";
		}

		/*
		 * DOMContentLoaded Javascript Optimize
		 */
		$contents = array();

		// file exists check 2
		$files = $this->_thk_files->scripts_dom_content_loaded();
		foreach( $files as $key => $val ) {
			if( $val !== null ) {
				if( file_exists( $val ) === true ) {
					$contents[$key] = $wp_filesystem->get_contents( $val );
				}
			}

			// luxe script
			if( $key === 'luxe' ) {
				$various = $jscript->create_luxe_dom_content_loaded_script();
				if( stripos( $various, 'insert' . '_luxe' ) === false || stripos( $various, 'thk' . '_get' . '_yuv' ) === false ) {
					wp_die();
				}
				if( !empty( $contents[$key] ) ) {
					$contents[$key] .= $various;
				}
				else {
					$contents[$key] = $various;
				}
			}
		}

		// javascript bind 2
		foreach( $contents as $value ) {
			$save .= $value . "\n";
		}

		/*
		 * jQuery depend Javascript Optimize
		 */
		$contents = array();

		if( $luxe['jquery_load'] !== 'none' ) {
			// file exists check 2
			$files = $this->_thk_files->scripts_jquery_depend();
			foreach( $files as $key => $val ) {
				if( $val === true ) continue;
				if( file_exists( $val ) === false ) unset( $files[$key] );
			}

/*
			// prism
			if( !isset( $luxe['highlighter_enable'] ) ) {
				unset( $files['prism'] );
			}
*/
			// smoothScroll
/*
			if( isset( $luxe['lazyload_thumbs'] ) || isset( $luxe['lazyload_contents'] ) || isset( $luxe['lazyload_sidebar'] ) || isset( $luxe['lazyload_footer'] ) ) {
				unset( $files['sscroll'] );
			}
*/
			// strip
			if( $luxe['gallery_type'] !== 'strip' ) {
				unset( $files['strip'] );
			}
			// tosrus
			if( $luxe['gallery_type'] !== 'tosrus' ) {
				unset( $files['tosrus'] );
			}
			// lightcase
			if( $luxe['gallery_type'] !== 'lightcase' ) {
				unset( $files['lightcase'] );
			}
			// fluidbox
			if( $luxe['gallery_type'] !== 'fluidbox' ) {
				unset( $files['fluidbox'] );
				unset( $files['throttle'] );
			}

			// Google Autocomplete
			if( !isset( $luxe['autocomplete'] ) ) {
				unset( $files['autocomplete'] );
			}

			$files = array_filter( $files, 'strlen' );

			// get javascript file content
			foreach( $files as $key => $val ) {
				if( $val !== null ) {
					if( file_exists( $val ) === true ) {
						$contents[$key] = $wp_filesystem->get_contents( $val );
					}
				}

				// luxe script
				if( $key === 'luxe' ) {
					$various = $jscript->create_luxe_various_script();
					if( !empty( $contents[$key] ) ) {
						$contents[$key] .= $various;
					}
					else {
						$contents[$key] = $various;
					}
				}
			}

			// javascript bind 2
			if( $luxe['jquery_load'] !== 'luxeritas' ) {
				// jQuery が読み込まれてなかったら、実行遅らせる
				// 参考 URL： http://jsfiddle.net/ocfzf3bb/2/
				$save .= <<< JQUERY_CHECK
try {
	var jQeryCheck1 = function(e) {
		window.jQuery ? e(jQuery) : window.setTimeout(function() {
			jQeryCheck1(e)
		}, 100)
	};
	jQeryCheck1( function($) {

JQUERY_CHECK;
			}
			else {
				$save .= <<< JQUERY_CHECK
(function() {

JQUERY_CHECK;
			}

			foreach( $contents as $key => $value ) {
				if( $key !== 'luxe' ) {
					$save .= $value . "\n";
				}
			}

			if( $luxe['jquery_load'] !== 'luxeritas' ) {
				$save .=  <<< JQUERY_CHECK
	});
	} catch (e) {
		console.error("jquery.check1.error: " + e.message)
	};

JQUERY_CHECK;
			}
			else {
				$save .= '})();';
			}

			$save .= $contents['luxe'] . "\n";
		}

		if( $this->_filesystem->file_save( $luxe_min, thk_jsmin( $save ) ) === false ) return false;

		return true;
	}

	/*
	 * Asynchronous Javascript Optimize
	 */
/*
	public function js_async_optimize() {
		global $luxe, $wp_filesystem;
		$contents = array();

		$luxe_async = $this->_js_dir . 'luxe.async.min.js';

		$files = $this->_thk_files->scripts_async();

		// file exists check
		foreach( $files as $key => $val ) {
			if( $val === true ) continue;
			if( file_exists( $val ) === false ) unset( $files[$key] );
		}

		$files = array_filter( $files, 'strlen' );

		// get Asynchronous javascript file content
		$jscript = new create_Javascript();
		$tdel = pdel( get_template_directory_uri() );
		$sdel = pdel( get_stylesheet_directory_uri() );

		foreach( $files as $key => $val ) {
			if( $key === 'async' ) {
				$contents[$key] = $jscript->create_css_load_script( $tdel . '/style.async.min.css' );
				continue;
			}

			$contents[$key] = $wp_filesystem->get_contents( $val );
		}

		// SNS Count script
		if(
			( isset( $luxe['sns_count_cache_enable'] ) && isset( $luxe['sns_count_cache_force'] ) ) ||
			( isset( $luxe['sns_tops_enable'] ) && isset( $luxe['sns_tops_count'] ) ) ||
			( isset( $luxe['sns_bottoms_enable'] ) && isset( $luxe['sns_bottoms_count'] ) ) ||
			( isset( $luxe['sns_toppage_view'] ) && isset( $luxe['sns_bottoms_count'] ) )
		){
			$contents[$key] .= $jscript->create_sns_count_script();
		}

		// Url copy script
		if( isset( $luxe['copypage_tops_button'] ) || isset( $luxe['copypage_bottoms_button'] ) ) {
			$contents['copy_url'] = $jscript->create_copy_button_script();
		}

		// Asynchronous javascript bind
		$save = '';
		foreach( $contents as $value ) {
			$save .= $value . "\n";
		}

		if( $this->_filesystem->file_save( $luxe_async, thk_jsmin( $save ) ) === false ) return false;

		return true;
	}
*/


	/*
	 * Search highlight script Optimize
	 */
	public function js_search_highlight() {
		global $wp_filesystem;

		$contents = array();
		$files = $this->_thk_files->scripts_search_highlight();

		$luxe_search_highlight = $this->_js_dir . 'thk-highlight.min.js';

		foreach( $files as $key => $val ) {
			$contents[$key] = $wp_filesystem->get_contents( $val );
		}

		$save = '';

		// jQuery が読み込まれてなかったら、実行遅らせる
		// 参考 URL： http://jsfiddle.net/ocfzf3bb/2/
		$save .= <<< JQUERY_CHECK
var searchJcheck = function(callback) {
	if( window.jQuery ) {
		callback(jQuery);
	} else {
		window.setTimeout( function() {
			searchJcheck(callback);
		}, 100 );
	}
};
searchJcheck( function($) {

JQUERY_CHECK;

		foreach( $contents as $value ) {
			$save .= $value . "\n";
		}

		$save .= '});';

		if( $this->_filesystem->file_save( $luxe_search_highlight, thk_jsmin( $save ) ) === false ) return false;

		return true;
	}

	/*
	 * jQuery and bootstrap Optimize
	 */
	public function jquery_optimize() {
		global $luxe, $wp_filesystem;
		/*
		$bind = array(
			$this->_js_dir . 'jquery.luxe.min.js',
			$this->_js_dir . 'jquery.luxe-migrate.min.js'
		);
		*/
		$jquery_save = $this->_js_dir . 'jquery.luxe.min.js';

		if( get_theme_admin_mod( 'all_clear', false ) === true || $luxe['jquery_load'] !== 'luxeritas' ) {
			if( $wp_filesystem->delete( $jquery_save ) === false ) {
				$this->_filesystem->file_save( $jquery_save, null );
			}
			return true;
		}

		$contents = array();

		/* $jquery_save = $bind[0]; */
		$files = $this->_thk_files->jquery();

		// file exists check
		foreach( $files as $key => $val ) {
			if( file_exists( $val ) === false ) unset( $files[$key] );
		}

		// get script files
		if( file_exists( $files['jquery'] ) === true ) {
			// jquery
			$contents['jquery'] = $wp_filesystem->get_contents( $files['jquery'] );
			// luxe.async.min.js
			//$luxe_async = $this->_js_dir . 'luxe.async.min.js';
			// luxe.min.js
			$luxe_min = $this->_js_dir . 'luxe.min.js';

			$contents['migrate'] = '';

			if( isset( $luxe['jquery_migrate_load'] ) ) {
				if( file_exists( $files['migrate'] ) === true ) {
					// jquery-migrate
					$contents['migrate'] .= $wp_filesystem->get_contents( $files['migrate'] );
					/*
					$jquery_save = $bind[1];
					$del_file = $bind[0];
					*/
				}
				/*
				else {
					$del_file = $bind[1];
				}
				*/
			}
			/*
			else {
				$del_file = $bind[1];
			}
			*/

			/*
			if( file_exists( $luxe_async ) === true ) {
				// luxe.async.min.js
				$contents['migrate'] .= $wp_filesystem->get_contents( $luxe_async );
			}
			*/

			if( file_exists( $luxe_min ) === true ) {
				// luxe.min.js
				$contents['migrate'] .= $wp_filesystem->get_contents( $luxe_min );
			}
			/*
			if( $wp_filesystem->delete( $del_file ) === false ) {
				$this->_filesystem->file_save( $del_file, null );
			}
			*/
		}

		// javascript compression and save
		$save = '';
		foreach( $contents as $value ) {
			$save .= $value . "\n";
		}
		if( $this->_filesystem->file_save( $jquery_save, thk_jsmin( $save ) ) === false ) return false;

		return true;
	}

	protected function ballon_css_replace( $contents ) {
		require_once( INC . 'balloon-css-replace.php' );
		return $contents;
	}
}
