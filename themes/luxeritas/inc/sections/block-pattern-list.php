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

$bp_mods = get_pattern_list( 'pattern' );
$fp_mods = get_pattern_list( 'phrase' );
?>
<ul style="display:flex;flex-wrap:wrap">
<li style="white-space:nowrap;margin-right:14px">
<form enctype="multipart/form-data" id="add-form" method="post" action="">
<?php
settings_fields( 'pattern' );	// インポートで必要
?>
<input type="button" class="button secondary add-pattern" value="<?php echo __( 'Add New', 'luxeritas' ); ?>" name="" onclick="thkEditBtn(this)" />
<label class="button secondary"><?php echo __( 'Import', 'luxeritas' ); ?>
<input type="file" id="add-file-pattern" name="add-file-pattern" style="display:none" />
</label>
</form>
<p style="margin-bottom:0"><?php echo __( '* If you create a new block pattern, we recommend that you create a block in a reusable block and then add it to the block patterns.', 'luxeritas' ); ?></p>
</li>
</ul>
<ul>
<li style="white-space:nowrap;margin-right:14px">
<p style="margin:1em 0 .6em .4em"><a id="reusable-block-open" style="cursor:pointer"><?php echo __( 'Create a pattern from the reusable block.', 'luxeritas' ), ' ( ', __( 'Recommended', 'luxeritas' ), ' )'; ?></a></p>
<form id="reusable-block" style="display:none" method="post" action="">
<?php
if( function_exists( 'thk_dropdown_posts' ) === true ) {
	$dropdown_posts = thk_dropdown_posts( array( 
		//'selected' => !empty( $block_id ) ? (int)$block_id : '',
		//'select_name' => $this->get_field_name( 'block_id' ),
		'echo' => false,
		'class' => 'widefat',
		'post_type' => 'wp_block',
		'show_option_none' => __( 'Please select a reusable block.', 'luxeritas' ),
		'option_none_value' => '',
	));
}
if( !empty( $dropdown_posts ) )	{
	echo $dropdown_posts, "\n";
}
else {
	echo '<input type="text" value="', __( 'No reusable block available to select.', 'luxeritas' ), '" disabled />', "\n";
}
settings_fields( 'pattern' );
?>
<input type="submit" id="reusable-block-submit" class="button secondary reusable-block" value="<?php echo __( 'Add', 'luxeritas' ); ?>" name="" disabled />
</form>
</li>
</ul>
<ul>
<li style="white-space:nowrap;margin-right:14px">
<p style="margin:1em 0 .6em .4em"><a id="html-pattern-open" style="cursor:pointer"><?php echo __( 'Create a pattern from the HTML pattern.', 'luxeritas' ); ?></a></p>
<form id="html-pattern" style="display:none"  method="post" action="">
<select name="html_pattern_label">
<option value=""><?php echo __( 'Please select a HTML pattern.', 'luxeritas' ); ?></option>
<?php
if( !empty( $fp_mods ) ) {
	ksort( $fp_mods );
	foreach( (array)$fp_mods as $key => $val ) {
?>
<option value="<?php echo $key; ?>"><?php echo $key; ?></option>
<?php
	}
}
?>
</select>
<input type="submit" id="html-pattern-submit" class="button secondary html-pattern" value="<?php echo __( 'Add', 'luxeritas' ); ?>" name="" disabled />
<?php
settings_fields( 'pattern' );
?>
</form>
</li>
</ul>

<div style="display:none;">
<script>
var cname = '';

function thkEditBtn( b ) {
	cname = b.getAttribute('name');
}
function thkDeleteBtn( b ) {
	cname = b.getAttribute('name');
}
function thkFileSaveBtn( b ) {
	cname = b.getAttribute('name');
}
function thkToReusableBlocks( b ) {
	cname = b.getAttribute('name');
}
</script>
</div>

<?php
$values = array( 'close' => false );
$yes = '<i class="dashicons dashicons-yes"></i>';
$no  = '-';

if( !empty( $bp_mods ) ) {
	ksort( $bp_mods );
?>
<table id="thk-phrase-table" class="wp-list-table widefat striped" style="margin-top:2.4em">
<colgroup span="1" style="width:auto;" />
<colgroup span="1" style="width:auto;" />
<thead>
<tr>
<th scope="col" class="manage-column column-title column-primary al-l"><?php echo __( 'Label', 'luxeritas' ); ?></th>
<th class="al-c"></th>
</tr>
</thead>
<tbody id="the-list">
<?php
	foreach( (array)$bp_mods as $key => $val ) {
		$values = wp_parse_args( @json_decode( $val ), $values );
?>
<tr>
<td class="column-primary">
<?php echo $key; ?>
<p><button type="button" class="toggle-row"></button></p>
</td>
<td class="phrase-operation">
<input type="button" class="edit-phrase" value="<?php echo __( 'Edit', 'luxeritas' ); ?>" name="<?php echo $key; ?>" onclick="thkEditBtn(this)" />
<input type="button" class="delete-phrase" value="<?php echo __( 'Delete', 'luxeritas' ); ?>" name="<?php echo $key; ?>" onclick="thkDeleteBtn(this)" />
<input type="button" class="to-reusable-blocks" value="<?php echo __( 'Add to Reusable blocks', 'luxeritas' ); ?>" name="<?php echo $key; ?>" onclick="thkToReusableBlocks(this)" />
<input type="button" class="file-save-phrase" value="<?php echo __( 'Export', 'luxeritas' ); ?>" name="<?php echo $key; ?>" onclick="thkFileSaveBtn(this)" />
</td>
</tr>
<?php
	}
?>
</tbody>
</table>
<?php
}
?>

<?php
add_action( 'admin_footer', function() use( &$bp_mods ) {
	$popup_nonce = wp_create_nonce( 'pattern_popup' );
?>
<!-- #dialog-form  -->
<div id="thk-code-form" title="<?php echo __( 'Block pattern Edit', 'luxeritas' ); ?>">
	<form id="dialog-form">
		<p id="code-regist-err" style="color:red"></p>
		<?php settings_fields( 'pattern' ); ?>
		<table>
			<tr>
				<td><?php echo __( 'Label', 'luxeritas' ), ' ( ', __( 'required', 'luxeritas' ), ' )'; ?></td>
				<td><input type="text" id="thk-code-name" name="code_name" value="" size="30" /></td>
			</tr>
			<tr>
		                <td colspan="2"><?php echo __( 'Block pattern', 'luxeritas' ); ?></td>
			</tr>
			<tr>
				<td colspan="2"><textarea id="thk-code-text" name="code_text" rows="12" cols="80"></textarea></td>
			</tr>
		</table>
		<input type="hidden" id="thk-code-new" name="code_new" value="1" />
	</form>
</div>

<div style="display:none">
<form id="delete-form">
	<?php settings_fields( 'pattern' ); ?>
	<input type="hidden" id="thk-code-delete" name="code_delete_item" value="" />
	<input type="hidden" name="code_delete" value="1" />
</form>
<form id="file-save-form">
	<?php settings_fields( 'pattern' ); ?>
	<input type="hidden" id="thk-code-save" name="code_save_item" value="" />
	<input type="hidden" name="code_save" value="1" />
</form>
<form id="reusable-form">
	<?php settings_fields( 'pattern' ); ?>
	<input type="hidden" id="thk-code-reusable" name="to_reusable_blocks_item" value="" />
	<input type="hidden" name="to_reusable_blocks" value="1" />
</form>
</div>

<div style="display:none">
<script>
jQuery(function($) {
	var bp = '#thk-code-'
	,   fm = $(bp + 'form')
	,   save_enable = false
	,   err = $('#code-regist-err');

	fm.dialog({
		autoOpen: false,
		height: 'auto',
		width: 'auto',
		maxWidth: 800,
		minWidth: 300,
		modal: true,
		buttons: {  // ダイアログに表示するボタンと処理
			"<?php echo __( 'Save', 'luxeritas' ); ?>": function() {
				var target = document.getElementById("dialog-form");

				target.method = "post";
				if( $(bp + 'name').val() === '' ) {
					err.text( '<?php echo __( "Required items are not entered", "luxeritas" ); ?>' );
					return false;
				}
				target.submit();
				$(this).dialog('close');
			},
			"<?php echo __( 'Cancel', 'luxeritas' ); ?>": function() {
				$(this).dialog('close');
			}
		},
	});

	// export
	$('.file-save-phrase').click( function() {
		var target = document.getElementById("file-save-form");
		$(bp + 'save').val(cname);
		target.method = "post";
		target.submit();
	});

	// import
	$("#add-file-pattern").change(function () {
		$(this).closest("#add-form").submit();
	});

	// 削除ボタンがクリックされたら確認ダイアログを表示
	$('.delete-phrase').click( function() {
		var res = confirm( '<?php echo __( "You are about to delete the selected item. Is it OK ?", "luxeritas" ); ?>' );
		var target = document.getElementById("delete-form");
		if( res === true ) {
			$(bp + 'delete').val(cname);
			target.method = "post";
			target.submit();
		}
	});

	// 再利用ブロックに追加がクリックされたら確認ダイアログを表示
	$('.to-reusable-blocks').click( function() {
		var res = confirm( '<?php echo __( "Add this pattern to the reuse block. Is it OK ?", "luxeritas" ); ?>' );
		var target = document.getElementById("reusable-form");
		if( res === true ) {
			$(bp + 'reusable').val(cname);
			target.method = "post";
			target.submit();
		}
	});

	// 新規追加 or 編集ボタンがクリックされたらダイアログを表示
	$('.edit-phrase, .add-pattern').click( function() {
		var action = 'edit';
		if( $(this).attr('class').indexOf('add-pattern') != -1 ) {
			action = 'add';
		}

		fm.dialog('open');
		fm.find(bp + 'name').val(cname);
		if( action === 'edit' ) {
			fm.find(bp + 'text').val('<?php echo __( "Loding...", "luxeritas" ), "\\n", __( "Please wait a little while.", "luxeritas" ); ?>');
		}
		else {
			fm.find(bp + 'text').val('');
		}
		err.text('');

		if( action === 'add' ) {
			fm.find(bp + 'new').val(1);
			fm.find(bp + 'name').prop('readonly', false);
			fm.find(bp + 'text').attr( 'rows', 12 );
			$('.ui-dialog-buttonset .ui-button:first').button('enable');
		}
		else {
			$('.ui-dialog-buttonset .ui-button:first').button('disable');
			fm.find(bp + 'new').val(0);
			fm.find(bp + 'name').prop('readonly', true);

			jQuery.ajax({
				type: 'POST',
				url: '<?php echo admin_url( "admin-ajax.php" ); ?>',
				data: {action:'thk_pattern_regist', name:cname, bp_popup_nonce:'<?php echo $popup_nonce; ?>'},
				dataType: 'text',
				async: true,
				cache: false,
				timeout: 10000
			}).then( function( response ) {
				$(bp + 'text').val( response );
				save_enable = true;
			}, function() {
				$(bp + 'text').val( '<?php echo __( "Failed to read.", "luxeritas" ); ?>' );
			});
		}
		return false;
	});

	// 値が変更されたら保存ボタン活性化
	$('form').on( "keyup change", function() {
		if( save_enable === true ) {
			$('.ui-dialog-buttonset .ui-button:first').button('enable');
		}
	});

	// ダイアログ用のオーバーレイがクリックされたらダイアログを閉じる
	$(document).on( 'click', '.ui-widget-overlay', function() {
		fm.dialog('close');
		setTimeout( function(){ $('iframe').eq(0).focus(); }, 0 );
		setTimeout( function(){ $('textarea').eq(0).focus(); }, 0 );
	});

	// サンプル登録が押されたらサンプル登録画面表示
	$('#thkSampleBtn').on( 'click', function() {
		var o = document.getElementById("luxe-customize");
		o.style.display = 'block';
	});

	// サンプル登録用のオーバーレイがクリックされたら登録画面を閉じる
	$(document).on( 'click', '#phrase-overlay, #phrase-sample-close', function() {
		var o = document.getElementById("luxe-customize");
		o.style.display = 'none';
	});

	// 再利用ブロック、HTML 定型文からの挿入フォーム開閉
	$('#html-pattern-open').click( function() {
		$('#html-pattern').toggle( 400, 'linear' );
	});

	$('#reusable-block-open').click( function() {
		$('#reusable-block').toggle( 400, 'linear' );
	});

	// 再利用ブロック、HTML 定型文からの挿入フォーム submit ボタンの活性/非活性化
	var bp_array = <?php echo json_encode( $bp_mods ) ?>;

	$('select[name="post_id"]').change( function() {
		if( bp_array[$('select[name="post_id"] option:selected').text()] ) {
			$('#reusable-block-submit').prop('disabled', true );
			$('#reusable-block-submit').val( "<?php echo __( 'Already registered', 'luxeritas' ); ?>" );
		}
		else if( $('select[name="post_id"]').val() != '' ) {
			$('#reusable-block-submit').prop('disabled', false );
			$('#reusable-block-submit').val( "<?php echo __( 'Add', 'luxeritas' ); ?>" );
		}
		else {
			$('#reusable-block-submit').prop('disabled', true );
			$('#reusable-block-submit').val( "<?php echo __( 'Add', 'luxeritas' ); ?>" );
		}
	});

	var fp_array = <?php echo json_encode( $fp_mods ) ?>;

	$('select[name="html_pattern_label"]').change( function() {
		if( bp_array[$('select[name="html_pattern_label"] option:selected').text()] ) {
			$('#html-pattern-submit').prop('disabled', true );
			$('#html-pattern-submit').val( "<?php echo __( 'Already registered', 'luxeritas' ); ?>" );
		}
		else if( $('select[name="html_pattern_label"]').val() != '' ) {
			$('#html-pattern-submit').prop('disabled', false );
			$('#html-pattern-submit').val( "<?php echo __( 'Add', 'luxeritas' ); ?>" );
		}
		else {
			$('#html-pattern-submit').prop('disabled', true );
			$('#html-pattern-submit').val( "<?php echo __( 'Add', 'luxeritas' ); ?>" );
		}
	});
});

// タブの入力ができるようにする
var textareas = document.getElementsByTagName('textarea');
var count = textareas.length;
for( var i = 0; i < count; i++ ) {
	textareas[i].onkeydown = function(e){
		if( e.keyCode === 9 || e.which === 9 ) {
			e.preventDefault();
			var s = this.selectionStart;
			this.value = this.value.substring( 0, this.selectionStart ) + "\t" + this.value.substring( this.selectionEnd );
			this.selectionEnd = s + 1;
		}
	}
}
</script>
</div>
<?php
});
