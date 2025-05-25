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
 * ajax - after_setup_theme
 *---------------------------------------------------------------------------*/
// 一括処理系
if( isset( $_POST['luxe_reget_sns'] ) || isset( $_POST['luxe_regen_thumb'] ) ) {
	require( INC . 'luxe-batch.php' );

	// SNS カウントキャッシュ一括取得処理
	if( isset( $_POST['luxe_reget_sns'] ) ) {
		if( check_ajax_referer( 'luxe_reget_sns', 'luxe_nonce' ) ) {
			luxe_batch::luxe_reget_sns();
		}
	}
	// サムネイル一括再構築処理
	if( isset( $_POST['luxe_regen_thumb'] ) ) {
		if( check_ajax_referer( 'luxe_regen_thumb', 'luxe_nonce' ) ) {
			luxe_batch::luxe_regen_thumb();
		}
	}
}

// wp version check
if( isset( $_POST['luxe_wp_version_memory_nonce'] ) ) {
	// nonce チェック
	if( wp_verify_nonce( $_POST['luxe_wp_version_memory_nonce'], 'luxe_wp_version_memory' ) ) {
		add_action( 'wp_ajax_luxe_wp_version_memory_notice', function() {
			set_theme_admin_mod( 'wp_version_memory', $GLOBALS['wp_version'] );
			exit;
		});
	}
}

// ブロックパターン ( ブロックパターン編集の際に中身を取得する用 )
if( isset( $_POST['bp_popup_nonce'] ) ) {
	// nonce チェック
	if( wp_verify_nonce( $_POST['bp_popup_nonce'], 'pattern_popup' ) ) {
		add_action( 'wp_ajax_thk_pattern_regist', function() {
			$name = trim( esc_attr( stripslashes( $_POST['name'] ) ) );
			$file_name = strlen( $name ) . '-' . md5( $name );
			$code_file = SPATH . DSEP . 'block-patterns' . DSEP . $file_name . '.txt';
			thk_filesystem_init();
			global $wp_filesystem;
			echo $wp_filesystem->get_contents( $code_file );
			exit;
		});
	}
}

// 定型文 ( TinyMCE の挿入ポップアップと定型文編集の際に中身を取得する用 )
if( isset( $_POST['fp_popup_nonce'] ) ) {
	// nonce チェック
	if( wp_verify_nonce( $_POST['fp_popup_nonce'], 'phrase_popup' ) ) {
		add_action( 'wp_ajax_thk_phrase_regist', function() {
			$name = trim( esc_attr( stripslashes( $_POST['name'] ) ) );
			$file_name = strlen( $name ) . '-' . md5( $name );
			$code_file = SPATH . DSEP . 'phrases' . DSEP . $file_name . '.txt';
			thk_filesystem_init();
			global $wp_filesystem;
			echo $wp_filesystem->get_contents( $code_file );
			exit;
		});
	}
	
}

// ショートコード ( TinyMCE の挿入ポップアップとショートコード編集の際に中身を取得する用 )
if( isset( $_POST['sc_popup_nonce'] ) ) {
	// nonce チェック
	if( wp_verify_nonce( $_POST['sc_popup_nonce'], 'shortcode_popup' ) ) {
		add_action( 'wp_ajax_thk_shortcode_regist', function() {
			$name = trim( esc_attr( stripslashes( $_POST['name'] ) ) );
			$code_file = SPATH . DSEP . 'shortcodes' . DSEP . $name . '.inc';
			thk_filesystem_init();
			global $wp_filesystem;
			$codes = $wp_filesystem->get_contents_array( $code_file );
			$len = count( $codes );
			unset( $codes[0],  $codes[1], $codes[$len-1], $codes[$len-2] );
			foreach( (array)$codes as $val ) echo $val;
			exit;
		});
	}
}

// TinyMCE set
if( isset( $_POST['mce_popup_nonce'] ) ) {
	if( wp_verify_nonce( $_POST['mce_popup_nonce'], 'mce_popup' ) ) {
		add_action( 'wp_ajax_thk_mce_settings', function() {
			if( isset( $_POST['mce_menubar'] ) ) {
				if( $_POST['mce_menubar'] === 'true' ) {
					$_POST['mce_menubar'] = true;
				}
				else {
					unset( $_POST['mce_menubar'] );
				}
			}
			thk_customize_result_set( 'mce_color', 'text', 'admin' );
			thk_customize_result_set( 'mce_bg_color', 'text', 'admin' );
			thk_customize_result_set( 'mce_max_width', 'number', 'admin' );
			thk_customize_result_set( 'mce_enter_key', 'radio', 'admin' );
			thk_customize_result_set( 'mce_menubar', 'checkbox', 'admin' );
		});
	}
}

// Editor button settings
if( isset( $_POST['editor_settings_nonce'] ) ) {
	if( wp_verify_nonce( $_POST['editor_settings_nonce'], 'settings_nonce' ) ) {
		/*
		 * Visual Editor
		 */
		add_action( 'wp_ajax_v_editor_settings', function() {
			/*** ビジュアルエディタのボタン 1段目 ***/
			$buttons_1 = array();
			$buttons_default_1 = array();

			// POST で受け取った配列
			foreach( $_POST['buttons_1'] as $val ) {
				$buttons_1[$val] = true;
			}

			// デフォルトの配列
			$v_buttons_default_1 = thk_mce_buttons_1();
			foreach( $v_buttons_default_1 as $key => $val ) {
				$buttons_default_1[$key] = true;
			}

			// デフォルト配列と異なってる時だけ DB に保存 ( DB の無駄を出さないため)
			if( $buttons_1 !== $buttons_default_1 ) {
				set_theme_admin_mod( 'veditor_buttons_1', $buttons_1 );
			}
			else {
				remove_theme_admin_mod( 'veditor_buttons_1' );
			}

			/*** ビジュアルエディタのボタン 2段目 ***/
			$buttons_2 = array();
			$buttons_default_2 = array();

			// POST で受け取った配列
			foreach( $_POST['buttons_2'] as $val ) {
				$buttons_2[$val] = true;
			}

			// デフォルトの配列
			$v_buttons_default_2 = thk_mce_buttons_2();
			foreach( $v_buttons_default_2 as $key => $val ) {
				$buttons_default_2[$key] = true;
			}

			// デフォルト配列と異なってる時だけ DB に保存 ( DB の無駄を出さないため)
			if( $buttons_2 !== $buttons_default_2 ) {
				set_theme_admin_mod( 'veditor_buttons_2', $buttons_2 );
			}
			else {
				remove_theme_admin_mod( 'veditor_buttons_2' );
			}
		});
		add_action( 'wp_ajax_v_editor_restore', function() {
			remove_theme_admin_mod( 'veditor_buttons_1' );
			remove_theme_admin_mod( 'veditor_buttons_2' );
		});

		/*
		 * Text Editor
		 */
		add_action( 'wp_ajax_t_editor_settings', function() {
			$buttons_d = array();
			foreach( $_POST['buttons_d'] as $val ) {
				$buttons_d[$val] = true;
			}
			if( !empty( $buttons_d ) ) {
				set_theme_admin_mod( 'teditor_buttons_d', $buttons_d );
			}
			else {
				remove_theme_admin_mod( 'teditor_buttons_d' );
			}
		});
		add_action( 'wp_ajax_t_editor_restore', function() {
			remove_theme_admin_mod( 'teditor_buttons_d' );
		});
	}
}
