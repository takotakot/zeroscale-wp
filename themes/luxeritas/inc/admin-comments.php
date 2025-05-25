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
 * 管理者のみ閲覧できるコメント（管理画面用）
 *---------------------------------------------------------------------------*/
/* ボックス追加 */
add_action( 'admin_init', function() {
	add_meta_box( 'luxe_admin_comments', __( 'Show only admins.', 'luxeritas' ), function() {
		global $comment_id;
		$secret = get_comment_meta( $comment_id, 'luxe_admin_comments', true );
		$checked = checked( $secret, 1, false );
		$comment_field = '<label for="luxe_admin_comments"><input name="luxe_admin_comments" id="luxe_admin_comments" type="checkbox" value="1"' . $checked . '></label>';
		echo $comment_field;
	}, 'comment', 'normal' );
});

/* コメント保存 */
add_filter( 'comment_save_pre', function( $comment_content ) {
	global $comment_id;
	thk_admin_comment_post( $comment_id );
	return $comment_content;
});
add_filter( 'comment_post', 'thk_admin_comment_post' );

/* コメント編集画面のフィールド */
add_filter( 'comment_form_field_comment', function( $comment_field ) {
	$comment_field .= '<label for="'.'luxe_admin_comments'.'"><input name="luxe_admin_comments" id="luxe_admin_comments" type="checkbox" value="1" >' . __( 'Only administrators can see this comment.', 'luxeritas' ) . '</label>';
	return $comment_field;
});

/* コメントの表示 */
add_filter( 'get_comment_text', function( $comment_content, $comment, $args = array() ) {
	return thk_admin_comments( $comment_content, $comment );
}, 10, 3 );
