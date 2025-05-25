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
 * 記事投稿(編集)画面に更新レベルのボックス追加
 *---------------------------------------------------------------------------*/

/* ボックス追加 */
add_action( 'add_meta_boxes', function() {
	add_meta_box( 'update_level', __( 'Update method', 'luxeritas' ), 'post_update_level_box', null, 'side', 'default' );
}, 10, 2 );
/*
add_action( 'admin_menu', function() {
	add_meta_box( 'update_level', __( 'Update method', 'luxeritas' ), 'post_update_level_box', 'post', 'side', 'default' );
	add_meta_box( 'update_level', __( 'Update method', 'luxeritas' ), 'post_update_level_box', 'page', 'side', 'default' );
});
*/

/* スタイル追加 */
add_action( 'admin_print_styles', function() {
	wp_register_style( 'thk_post_update_style', get_template_directory_uri() . '/css/post-update.css?v=' . $_SERVER['REQUEST_TIME'], array(), 'all' );
        wp_enqueue_style( 'thk_post_update_style' );
});

/* メインフォーム */
if( function_exists( 'post_update_level_box' ) === false ):
function post_update_level_box() {
	global $post;
?>
<div style="padding-top: 5px; overflow: hidden;">
<label class="thk-block-label" style="padding:5px 0"><input class="thk-block-input" name="update_level" type="radio" value="high" checked="checked" /><?php echo  __( 'Normal update', 'luxeritas' ); ?></label>
<label class="thk-block-label" style="padding: 5px 0"><input class="thk-block-input" name="update_level" type="radio" value="low" /><?php echo  __( 'Quick fix (No change to modify date)', 'luxeritas' ); ?></label>
<label class="thk-block-label" style="padding: 5px 0"><input class="thk-block-input" name="update_level" type="radio" value="del" /><?php echo  __( 'Erase modify date (Publish date and modify date becomes the same)', 'luxeritas' ); ?></label>
<label class="thk-block-label" style="padding: 5px 0; margin-bottom: 10px"><input id="update_level_edit" class="thk-block-input" name="update_level" type="radio" value="edit" /><?php echo  __( 'Set the modify date manually', 'luxeritas' ); ?></label>
<?php
	if( get_the_modified_date( 'c' ) ) {
		$stamp = __( 'Modified on:', 'luxeritas' ) . ' <span style="font-weight:bold">' . get_the_modified_date( __( 'M j, Y @ H:i', 'luxeritas') ) . '</span>';
	}
	else {
		$stamp = __( 'Modified on:', 'luxeritas' ) . ' <span style="font-weight:bold">' . __( 'Not updated', 'luxeritas' ) .'</span>';
	}
	$date = date_i18n( get_option('date_format') . ' @ ' . get_option('time_format'), strtotime( $post->post_modified ) );
?>
<span class="modtime"><?php printf( $stamp, $date ); ?></span>
<div id="timestamp_mod_div" onkeydown="document.getElementById('update_level_edit').checked=true" onclick="document.getElementById('update_level_edit').checked=true">
<?php thk_time_mod_form(); ?>
</div>
</div>
<?php
}
endif;

/* 更新日時変更の入力フォーム */
if( function_exists( 'thk_time_mod_form' ) === false ):
function thk_time_mod_form() {
	global $wp_locale, $post;

	$tab_index = 0;
	$tab_index_attribute = '';
	if ( (int) $tab_index > 0 ) {
		$tab_index_attribute = ' tabindex="' . $tab_index . '"';
	}

	$jj_mod = mysql2date( 'd', $post->post_modified, false );
	$mm_mod = mysql2date( 'm', $post->post_modified, false );
	$aa_mod = mysql2date( 'Y', $post->post_modified, false );
	$hh_mod = mysql2date( 'H', $post->post_modified, false );
	$mn_mod = mysql2date( 'i', $post->post_modified, false );
	$ss_mod = mysql2date( 's', $post->post_modified, false );

	$year = '<label for="aa_mod" class="screen-reader-text">' . __( 'Year', 'luxeritas' ) .
		'</label><input type="text" id="aa_mod" name="aa_mod" value="' .
		$aa_mod . '" size="4" maxlength="4"' . $tab_index_attribute . ' autocomplete="off" />' . __( 'Year', 'luxeritas' );

	$month = '<label for="mm_mod" class="screen-reader-text">' . __( 'Month', 'luxeritas' ) .
		'</label><select id="mm_mod" name="mm_mod"' . $tab_index_attribute . ' style="min-width:40px;min-height:28px">' . "\n";
	for( $i = 1; $i < 13; $i = $i +1 ) {
		$monthnum = zeroise($i, 2);
		$month .= "\t\t\t" . '<option value="' . $monthnum . '" ' . selected( $monthnum, $mm_mod, false ) . '>';
		//$month .= $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) );
		$month .= $monthnum;
		$month .= "</option>\n";
	}
	$month .= '</select>' . __( 'Month', 'luxeritas' );

	$day = '<label for="jj_mod" class="screen-reader-text">' . __( 'Day', 'luxeritas' ) .
		'</label><input type="text" id="jj_mod" name="jj_mod" value="' .
		$jj_mod . '" size="2" maxlength="2"' . $tab_index_attribute . ' autocomplete="off" />' . __( 'Day', 'luxeritas' );
	$hour = '<label for="hh_mod" class="screen-reader-text">' . __( 'Hour', 'luxeritas' ) .
		'</label><input type="text" id="hh_mod" name="hh_mod" value="' . $hh_mod .
		'" size="2" maxlength="2"' . $tab_index_attribute . ' autocomplete="off" />';
	$minute = '<label for="mn_mod" class="screen-reader-text">' . __( 'Minute', 'luxeritas' ) .
		'</label><input type="text" id="mn_mod" name="mn_mod" value="' . $mn_mod .
		'" size="2" maxlength="2"' . $tab_index_attribute . ' autocomplete="off" />';

	printf( '%1$s %2$s %3$s @ %4$s : %5$s', $year, $month, $day, $hour, $minute );
	echo	'<input type="hidden" id="ss_mod" name="ss_mod" value="' . $ss_mod . '" />';

	echo	'<input type="hidden" id="jj_mod_h" name="jj_mod_h" value="' . $jj_mod . '" />'
	,	'<input type="hidden" id="mm_mod_h" name="mm_mod_h" value="' . $mm_mod . '" />'
	,	'<input type="hidden" id="aa_mod_h" name="aa_mod_h" value="' . $aa_mod . '" />'
	,	'<input type="hidden" id="hh_mod_h" name="hh_mod_h" value="' . $hh_mod . '" />'
	,	'<input type="hidden" id="mn_mod_h" name="mn_mod_h" value="' . $mn_mod . '" />'
	,	'<input type="hidden" id="ss_mod_h" name="ss_mod_h" value="' . $ss_mod . '" />';
}
endif;

if( function_exists( 'thk_create_update_date' ) === false ):
function thk_create_update_date( $data, $post_aa_mod, $post_mm_mod, $post_jj_mod, $post_hh_mod, $post_mn_mod, $post_ss_mod ) {
	$aa_mod = $post_aa_mod <= 0 ? date('Y') : $post_aa_mod;
	$mm_mod = $post_mm_mod <= 0 ? date('n') : $post_mm_mod;
	$jj_mod = $post_jj_mod > 31 ? 31 : $post_jj_mod;
	$jj_mod = $jj_mod <= 0 ? date('j') : $jj_mod;
	$hh_mod = $post_hh_mod > 23 ? $post_hh_mod -24 : $post_hh_mod;
	$mn_mod = $post_mn_mod > 59 ? $post_mn_mod -60 : $post_mn_mod;
	$ss_mod = $post_ss_mod > 59 ? $post_ss_mod -60 : $post_ss_mod;
	$modified_date = sprintf( '%04d-%02d-%02d %02d:%02d:%02d', $aa_mod, $mm_mod, $jj_mod, $hh_mod, $mn_mod, $ss_mod );
	if ( ! wp_checkdate( $mm_mod, $jj_mod, $aa_mod, $modified_date ) ) {
		unset( $data['post_modified'] );
		unset( $data['post_modified_gmt'] );
		return $data;
	}
	$data['post_modified'] = $modified_date;
	$data['post_modified_gmt'] = get_gmt_from_date( $modified_date );

	return $data;
}
endif;

/* 「修正のみ」は更新しない。それ以外は、それぞれの更新日時に変更する */
add_filter( 'wp_insert_post_data', function( $data, $postarr ) {
	$mydata = isset( $_POST['update_level'] ) ? $_POST['update_level'] : null;

	if( $mydata === 'low' ) {
		/*
		unset( $data['post_modified'] );
		unset( $data['post_modified_gmt'] );
		*/
		$data = thk_create_update_date( $data, $_POST['aa_mod_h'], $_POST['mm_mod_h'], $_POST['jj_mod_h'], $_POST['hh_mod_h'], $_POST['mn_mod_h'], $_POST['ss_mod_h'] );
	}
	elseif( $mydata === 'edit' ) {
		$data = thk_create_update_date( $data, $_POST['aa_mod'], $_POST['mm_mod'], $_POST['jj_mod'], $_POST['hh_mod'], $_POST['mn_mod'], $_POST['ss_mod'] );
	}
	elseif( $mydata === 'del' ) {
		$data['post_modified'] = $data['post_date'];
		$data['post_modified_gmt'] = $data['post_date_gmt'];
	}
	return $data;
}, 10, 2 );

/*---------------------------------------------------------------------------
 * nofollow、noindex、noarchive のボックス追加
 *---------------------------------------------------------------------------*/

/* ボックス追加 */
add_action( 'admin_menu', function() {
	add_meta_box( 'thk_robots', __( 'Search Engine Visibility', 'luxeritas' ), 'thk_robots_box', 'post', 'side', 'default' );
	add_meta_box( 'thk_robots', __( 'Search Engine Visibility', 'luxeritas' ), 'thk_robots_box', 'page', 'side', 'default' );
}, 10, 2 );

if( function_exists( 'thk_robots_box' ) === false ):
function thk_robots_box() {
	global $post;

	$disabled = '';

	$page_template = get_post_meta( $post->ID, '_wp_page_template', true );
	if( !empty( $page_template ) && stripos( $page_template, 'pages/wrapper-menu.php' ) !== false ) {
		/* カスタムグローバルメニューの場合は全部チェック( noindex, nofollow, noarchive, noimageindex ) */
		$thk_noindex = 'enable';
		$thk_nofollow = 'enable';
		$thk_noarchive = 'enable';
		$thk_noimageindex = 'enable';

		$disabled = ' disabled';
		$opacity  = ' style="opacity:.7"';
	}
	else {
		/* 既に値がある場合 */
		$thk_robots = get_post_meta( $post->ID, 'thk_robots', true );
		$thk_robots = explode( ',', $thk_robots );

		$thk_noindex = isset( $thk_robots[0] ) && $thk_robots[0] == 1 ? 'enable' : 'disable';
		$thk_nofollow = isset( $thk_robots[1] ) && $thk_robots[1] == 1 ? 'enable' : 'disable';
		$thk_noarchive = isset( $thk_robots[2] ) && $thk_robots[2] == 1 ? 'enable' : 'disable';
		$thk_noimageindex = isset( $thk_robots[3] ) && $thk_robots[3] == 1 ? 'enable' : 'disable';
	}
?>
<div<?php echo $opacity; ?>>
<p class="meta-options">
<label class="selectit thk-block-label">
<input id="thk-noindex" class="thk-block-input" type="checkbox" name="thk_noindex" value="<?php echo $thk_noindex; ?>"<?php echo $thk_noindex === 'enable' ? ' checked' : ''; echo $disabled; ?> />
Noindex<br /><?php echo __( 'Noindex tells search engines not to index.', 'luxeritas' ); ?>
</label>
</p>
<p class="meta-options">
<label class="selectit thk-block-label">
<input id="thk-nofollow" class="thk-block-input" type="checkbox" name="thk_nofollow" value="<?php echo $thk_nofollow; ?>"<?php echo $thk_nofollow === 'enable' ? ' checked' : ''; echo $disabled; ?> />
Nofollow<br /><?php echo __( 'Nofollow tells search engines not to follow the links.', 'luxeritas' ); ?>
</label>
</p>
<p class="meta-options">
<label class="selectit thk-block-label">
<input id="thk-noarchive" class="thk-block-input" type="checkbox" name="thk_noarchive" value="<?php echo $thk_noarchive; ?>"<?php echo $thk_noarchive === 'enable' ? ' checked' : ''; echo $disabled; ?> />
Noarchive<br /><?php echo __( 'Noarchive tells search engines not to store a cache.', 'luxeritas' ); ?>
</label>
</p>
<p class="meta-options">
<label class="selectit thk-block-label">
<input id="thk-noimageindex" class="thk-block-input" type="checkbox" name="thk_noimageindex" value="<?php echo $thk_noimageindex; ?>"<?php echo $thk_noimageindex === 'enable' ? ' checked' : ''; echo $disabled; ?> />
Noimageindex<br /><?php echo __( 'Noimageindex tells search engines not to index images on this page.', 'luxeritas' ); ?>
</label>
</p>
</div>
<?php
}
endif;

/*---------------------------------------------------------------------------
 * jQuery 非同期解除ボックス追加
 *---------------------------------------------------------------------------*/
/* ボックス追加 */
if( get_theme_mod( 'jquery_defer', false ) === true ) {
	add_action( 'admin_menu', function() {
		add_meta_box( 'thk_disable_async_jquery', __( 'Disable jQuery Asynchronous', 'luxeritas' ), 'thk_thk_disable_async_jquery_box', 'post', 'side', 'default' );
		add_meta_box( 'thk_disable_async_jquery', __( 'Disable jQuery Asynchronous', 'luxeritas' ), 'thk_thk_disable_async_jquery_box', 'page', 'side', 'default' );
	});
}

/* 投稿画面に表示するフォーム */
if( function_exists( 'thk_thk_disable_async_jquery_box' ) === false ):
function thk_thk_disable_async_jquery_box() {
	global $post;
	/* 既に値がある場合 */
	$thk_disable_async_jquery = get_post_meta( $post->ID, 'thk_disable_async_jquery', true );
	$thk_disable_async_jquery = $thk_disable_async_jquery === 'disable' ? 'disable' : 'enable';
?>
<div id="thk-disable-async-jquery-form">
<p class="meta-options">
<label class="selectit thk-block-label">
<input id="thk-disable-async-jquery" class="thk-block-input" type="checkbox" name="thk_disable_async_jquery" value="<?php echo $thk_disable_async_jquery; ?>"<?php echo $thk_disable_async_jquery === 'disable' ? ' checked' : ''; ?> />
<?php echo __( 'Disable jQuery Asynchronous on this page', 'luxeritas' ); ?>
</label>
</p>
</div>
<?php
}
endif;


/*---------------------------------------------------------------------------
 * AMP 解除のボックス追加
 *---------------------------------------------------------------------------*/
/* ボックス追加 */
if( get_theme_mod( 'amp_enable', false ) === true ) {
	add_action( 'admin_menu', function() {
		add_meta_box( 'thk_post_amp', __( 'Disable AMP', 'luxeritas' ), 'thk_post_amp_box', 'post', 'side', 'default' );
		add_meta_box( 'thk_post_amp', __( 'Disable AMP', 'luxeritas' ), 'thk_post_amp_box', 'page', 'side', 'default' );
	});
}

/* 投稿画面に表示するフォーム */
if( function_exists( 'thk_post_amp_box' ) === false ):
function thk_post_amp_box() {
	global $post;
	/* 既に値がある場合 */
	$thk_amp = get_post_meta( $post->ID, 'thk_amp', true );
	$thk_amp = $thk_amp === 'disable' ? 'disable' : 'enable';
?>
<div id="thk-post-amp-form">
<p class="meta-options">
<label class="selectit thk-block-label">
<input id="thk-amp" class="thk-block-input" type="checkbox" name="thk_amp" value="<?php echo $thk_amp; ?>"<?php echo $thk_amp === 'disable' ? ' checked' : ''; ?> />
<?php echo __( 'Disable AMP on this page', 'luxeritas' ); ?>
</label>
</p>
</div>
<?php
}
endif;

/*---------------------------------------------------------------------------
 * 目次を非表示にするボックス追加
 *---------------------------------------------------------------------------*/
/* ボックス追加 */
if( get_theme_mod( 'toc_auto_insert', false ) === true ) {
	add_action( 'admin_menu', function() {
		add_meta_box( 'thk_post_toc', __( 'Hide TOC', 'luxeritas' ), 'thk_post_toc_box', 'post', 'side', 'default' );
		add_meta_box( 'thk_post_toc', __( 'Hide TOC', 'luxeritas' ), 'thk_post_toc_box', 'page', 'side', 'default' );
	});
}

/* 投稿画面に表示するフォーム */
if( function_exists( 'thk_post_toc_box' ) === false ):
function thk_post_toc_box() {
	global $post;
	/* 既に値がある場合 */
	$thk_toc = get_post_meta( $post->ID, 'thk_hide_toc', true );
	$thk_toc = $thk_toc === 'disable' ? 'disable' : 'enable';
?>
<div id="thk-post-toc-form">
<p class="meta-options">
<label class="selectit thk-block-label">
<input id="thk-hide-toc" class="thk-block-input" type="checkbox" name="thk_hide_toc" value="<?php echo $thk_toc; ?>"<?php echo $thk_toc === 'disable' ? ' checked' : ''; ?> />
<?php echo __( 'Hide TOC on this page', 'luxeritas' ); ?>
</label>
</p>
</div>
<?php
}
endif;

/*---------------------------------------------------------------------------
 * save_post ( カスタムフィールドの値をDBに書き込む )
 *---------------------------------------------------------------------------*/
add_action( 'save_post', function( $post_id ) {

	$slugs = ['thk_disable_async_jquery', 'thk_amp', 'thk_hide_toc'];

	foreach( $slugs as $value ) {
		$meta_data = isset( $_POST[$value] ) ? $_POST[$value] : null;
		$meta_data = $meta_data ? 'disable' : null;
		if( !empty( $meta_data ) ) {
			/* 保存 */
			add_post_meta( $post_id, $value, $meta_data, true ) ;
		}
		else {
	        	/* 削除 */
	        	delete_post_meta( $post_id, $value ) ;
		}
	}

	// nofollow、noindex、noarchive 追加
	$thk_robots = '';
	$thk_robots .= isset( $_POST['thk_noindex'] ) ? '1,' : '0,';
	$thk_robots .= isset( $_POST['thk_nofollow'] ) ? '1,' : '0,';
	$thk_robots .= isset( $_POST['thk_noarchive'] ) ? '1,' : '0,';
	$thk_robots .= isset( $_POST['thk_noimageindex'] ) ? '1,' : '0,';

	$thk_robots = rtrim( $thk_robots, ',' );

	$thk_robots_org = get_post_meta( $post_id, 'thk_robots' );

	if( empty( $thk_robots_org ) && !empty( $thk_robots ) && stripos( $thk_robots, '1' ) !== false ) {
		/* 保存 */
		add_post_meta( $post_id, 'thk_robots', $thk_robots, true ) ;
	}
	elseif( !empty( $thk_robots ) && stripos( $thk_robots, '1' ) !== false ) {
		/* 更新 */
		update_post_meta( $post_id, 'thk_robots', $thk_robots );
	}
	else {
        	/* 削除 */
        	delete_post_meta( $post_id, 'thk_robots' ) ;
	}
});
