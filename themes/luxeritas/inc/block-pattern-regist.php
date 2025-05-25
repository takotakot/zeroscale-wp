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
 * thk_pattern_regist
 *---------------------------------------------------------------------------*/
if( function_exists( 'thk_pattern_regist' ) === false ):
function thk_pattern_regist( $regist_name, $code_text, $new_or_edit = 'new' ) {
	$filesystem = thk_filesystem_init();
	global $wp_filesystem;

	$ret = false;
	$patterns_dir = SPATH . DSEP . 'block-patterns' . DSEP;
	$regist_file = strlen( $regist_name ) . '-' . md5( $regist_name );

	// 登録済みのラベルかをチェック
	if( $ret !== true && $new_or_edit === 'new' ) {
		$registed = get_theme_phrase_mods();

		if( isset( $registed['bp-' . $regist_name] ) ) {
			add_settings_error( 'luxe-custom', $_POST['option_page'], sprintf( '[' . $regist_name . '] ' . __( 'This %s has already been registered.', 'luxeritas' ), __( 'label', 'luxeritas' ) ), 'error' );
			$ret = true;
		}
		unset( $registed );
	}

	if( $ret !== true ) {
		if( ( !empty( $code_text ) && $code_text != 1 ) ) {
			if( $wp_filesystem->is_dir( $patterns_dir ) === false ) {
				// ディレクトリが存在しなかったら作成
				if( wp_mkdir_p( $patterns_dir ) === false ) {
					if( $wp_filesystem->mkdir( $patterns_dir, FS_CHMOD_DIR ) === false && $wp_filesystem->is_dir( $patterns_dir ) === false ) {
						add_settings_error( 'luxe-custom', $_POST['option_page'], __( 'Could not create directory.', 'luxeritas' ), 'error' );
						$ret = true;
					}
				}
			}
			if( wp_is_writable( $patterns_dir ) === true ) {
				$code_text = preg_replace( "/\r\n|\r|\n/", "\n", $code_text );

				if( $filesystem->file_save( $patterns_dir . $regist_file . '.txt', $code_text ) === false ) {
					// ファイル保存失敗
					add_settings_error( 'luxe-custom', $_POST['option_page'], __( 'Error saving file.', 'luxeritas' ), 'error' );
					$ret = true;
				}
			}
			else {
				// ディレクトリの書き込み権限がない
				add_settings_error( 'luxe-custom', $_POST['option_page'],
					__( 'You do not have permission to create and save files.', 'luxeritas' ) .
					__( 'Please check the owner and permissions of the following file or directory.', 'luxeritas' ) . '<br />' . $patterns_dir . $regist_file . '.txt'
				, 'error' );
				$ret = true;
			}
		}
		else {
			if( file_exists( $patterns_dir . $regist_file . '.txt' ) === true ) {
				$wp_filesystem->delete( $patterns_dir . $regist_file . '.txt' );
			}
		}

		if( $ret === false ) {
			if( set_theme_phrase_mod( 'bp-' . $regist_name, true ) === false ) {
				$ret = true;
			}
		}
	}
	return $ret;
}
endif;

/*---------------------------------------------------------------------------
 * conditional execution
 *---------------------------------------------------------------------------*/
$_POST = stripslashes_deep( $_POST );

thk_filesystem_init();
global $wp_filesystem;

$patterns_dir = SPATH . DSEP . 'block-patterns' . DSEP;
$phrases_dir  = SPATH . DSEP . 'phrases' . DSEP;

if( isset( $_FILES['add-file-pattern']['name'] ) && isset( $_FILES['add-file-pattern']['tmp_name'] ) ) {
	/*** インポート ***/
	$json_file = $_FILES['add-file-pattern']['tmp_name'];
	$json = $wp_filesystem->get_contents( $json_file );
	$a = (array)@json_decode( $json );

	if( !empty( $a ) ) {
		foreach( $a as $key => $val ) {
			$regist_name = $key;
			$code_text   = unserialize( $val );
			break;
		}
		$err = thk_pattern_regist( $regist_name, $code_text );
	}
}
elseif( isset( $_POST['code_save'] ) && isset( $_POST['code_save_item'] ) ) {
	/*** エクスポート ***/
	$save = trim( esc_attr( stripslashes( $_POST['code_save_item'] ) ) );
	$save_file = strlen( $save ) . '-' . md5( $save );

	$contents = '';

	if( file_exists( $patterns_dir . $save_file . '.txt' ) === true ) {
		$contents = $wp_filesystem->get_contents( $patterns_dir . $save_file . '.txt' );
	}

	$json = json_encode( array( $save => serialize( preg_replace( "/\r\n|\r|\n/", "\n", $contents ) ) ) );

	$file = str_replace( array( '.', ',', '"', '/', '\\', '[', ']', ':', ';', '|', '=' ), '', $_POST['code_save_item'] ) . '.json';
	@ob_start();
	@header( 'Content-Description: File Transfer' );
	@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
	@header( 'Content-Disposition: attachment; filename=' . $file );
	echo $json;
	@ob_end_flush();
	exit;
}
elseif( isset( $_POST['code_delete'] ) && isset( $_POST['code_delete_item'] ) ) {
	/*** 削除 ***/
	$del = trim( esc_attr( stripslashes( $_POST['code_delete_item'] ) ) );
	$file_del = strlen( $del ) . '-' . md5( $del );

	remove_theme_phrase_mod( 'bp-' . $del );

	if( file_exists( $patterns_dir . $file_del . '.txt' ) === true ) {
		$wp_filesystem->delete( $patterns_dir . $file_del . '.txt' );
	}
}
elseif( isset( $_POST['to_reusable_blocks'] ) && isset( $_POST['to_reusable_blocks_item'] ) ) {
	/*** 再利用ブロックに追加 ***/
	$regist_name = trim( esc_attr( stripslashes( $_POST['to_reusable_blocks_item'] ) ) );
	$file_name = strlen( $regist_name ) . '-' . md5( $regist_name );
	$code_text = '';

	if( file_exists( $patterns_dir . $file_name . '.txt' ) === true ) {
		$code_text = $wp_filesystem->get_contents( $patterns_dir . $file_name . '.txt' );
	}

	$post = array(
		'post_title'	=> $regist_name,
		'post_content'	=> $code_text,
		'post_type'	=> 'wp_block',
		'post_status'	=> 'publish',
		'comment_status'=> 'closed',
		'ping_status'	=> 'closed',
	);

	wp_insert_post( $post );
}
elseif( isset( $_POST['post_id'] ) ) {
	/*** 再利用ブロックから作成 ***/
	$get_post = get_post( $_POST['post_id'] );

	$regist_name = isset( $get_post->post_title ) ? trim( esc_attr( $get_post->post_title ) ) : '';
	$code_text = isset( $get_post->post_content ) ? $get_post->post_content : '';

	$err = thk_pattern_regist( $regist_name, $code_text, 'new' );
}
elseif( isset( $_POST['html_pattern_label'] ) ) {
	/*** HTML 定型文から作成 ***/
	$regist_name = trim( esc_attr( $_POST['html_pattern_label'] ) );
	$file_name = strlen( $regist_name ) . '-' . md5( $regist_name );
	$code_text = '';

	if( file_exists( $phrases_dir . $file_name . '.txt' ) === true ) {
		$code_text = $wp_filesystem->get_contents( $phrases_dir . $file_name . '.txt' );
		$code_text = str_replace( "\n<!--" . $file_name . "-->\n", '', $code_text );
	}

	$err = thk_pattern_regist( $regist_name, $code_text, 'new' );
}
else {
	/*** 新規追加 ***/
	if( isset( $_POST['code_name'] ) ) {
		$regist_name = trim( esc_attr( $_POST['code_name'] ) );
		$code_text = isset( $_POST['code_text'] ) ? $_POST['code_text'] : '';
		$new_or_edit = isset( $_POST['code_new'] ) && $_POST['code_new'] == 1 ? 'new' : 'edit';

		$err = thk_pattern_regist( $regist_name, $code_text, $new_or_edit );
	}
}
