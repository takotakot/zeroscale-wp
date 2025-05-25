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

call_user_func( function() use( &$_is, &$luxe ) {
	global $awesome;

	$icons = [];

	if( isset( $luxe['awesome_load'] ) ) {
		if( isset( $luxe['awesome_version'] ) && (int)$luxe['awesome_version'] === 4 ) {
			$ver = '4.7.0';
			$cdn = 'maxcdn.bootstrapcdn' . '.com';

			$icons['awesome'] = [
				'ver' => $ver,
				'cdn' => $cdn,
				'uri' => 'https://' . $cdn . '/font-awesome/' . $ver . '/',
				'css' => 'css/font-awesome.min.css',
			];
		}
		elseif( isset( $luxe['awesome_version'] ) && (int)$luxe['awesome_version'] === 5 ) {
			$ver = '5.15.4';
			$cdn = 'use.fontawesome' . '.com';

			$icons['awesome'] = [
				'ver' => $ver,
				'cdn' => $cdn,
				'uri' => 'https://' . $cdn . '/releases/v' . $ver . '/',
				'css' => 'css/all.css',
			];
		}
		else {
			$ver = '6.4.2';
			$cdn = 'use.fontawesome' . '.com';

			$icons['awesome'] = [
				'ver' => $ver,
				'cdn' => $cdn,
				'uri' => 'https://' . $cdn . '/releases/v' . $ver . '/',
				'css' => 'css/all.css',
			];
		}
	}

	if( isset( $luxe['material_load'] ) ) {
		$ver = '85';
		$cdn = 'fonts.googleapis' . '.com';
		$family = 'Material+Icons|Material+Icons+Outlined';

		if( isset( $luxe['material_add_rounded'] ) )  $family .= '|Material+Icons+Rounded';
		if( isset( $luxe['material_add_sharp'] ) )    $family .= '|Material+Icons+Sharp';
		if( isset( $luxe['material_add_two_tone'] ) ) $family .= '|Material+Icons+Two+Tone';

		$icons['material'] = [
			'ver' => '85',
			'cdn' => 'fonts.googleapis' . '.com',
			'uri' => 'https://' . $cdn . '/icon',
			'css' => '?family=' . $family . '&display=swap',
		];
	}

	if( !isset( $luxe['material_load'] ) && isset( $luxe['awesome_load'] ) ) {
		if( isset( $luxe['awesome_version'] ) && (int)$luxe['awesome_version'] === 4 ) {
			$icons += [
				'home'			=> '<i class="fa fa-home"></i>',
				'nav-home'		=> '<i class="fa fa-home navi-home"></i>',
				'circle-back'		=> '<i class="fa fa-chevron-circle-left"></i>',
				'arrow-back'		=> '<i class="fa fa-arrow-left fa-pull-left"></i>',
				'arrow-forward'		=> '<i class="fa fa-arrow-right fa-pull-right"></i>',
				'file'			=> '<i class="fa fa-file-o"></i>',
				'text'			=> '<i class="fa fa-file-text"></i>',
				'text-rotate'		=> '<i class="fa fa-file-text fa-rotate-180"></i>',
				'folder'		=> '<i class="fa fa-folder"></i>',
				'folder-open'		=> '<i class="fa fa-folder-open"></i>',
				'tags'			=> '<i class="fa fa-tags"></i>',
				'tag'			=> '<i class="fa fa-tag"></i>',
				'calendar'		=> '<i class="fa fa-calendar"></i>',
				'clock'			=> '<i class="fa fa-clock-o"></i>',
				'repeat'		=> '<i class="fa fa-repeat"></i>',
				'close'			=> '<i class="fa fa-times"></i>',
				'menu'			=> '<i class="fa fa-bars"></i>',
				'sidebar'		=> '<i class="fa fa-exchange"></i>',
				'share'			=> '<i class="fa fa-share-alt"></i>',
				'toc'			=> '<i class="fa fa-list"></i>',
				'rss'			=> '<i class="fa fa-rss"></i>',
				'smile'			=> '<i class="fa fa-smile-o"></i>',
				'double-arrow-left'	=> '<i class="fa fa-angle-double-left"></i>',
				'double-arrow-right'	=> '<i class="fa fa-angle-double-right"></i>',
				'search'		=> '<i class="fa fa-search"></i>',
				'install'		=> '<i class="fa fa-download"></i>',
				'pencil'		=> '<i class="fa fa-pencil"></i>',
				'related'		=> '<i class="fa fa-th-list"></i>',
				'trackback'		=> '<i class="fa fa-reply-all"></i>',
				'comment'		=> '<i class="fa fa-commenting-o"></i>',
				'renew'			=> '<i class="fa fa-refresh"></i>',
			];

			$page_top = isset( $luxe['page_top_icon'] ) ? $luxe['page_top_icon'] : 'fa_arrow_up';
			$icons['page-top'] = '<i class="fa ' . str_replace( '_', '-', $page_top ) . '"></i>';

			if( isset( $luxe['amp'] ) && $_is['user_logged_in'] === true ) {
				$icons += [
					'circle-left'		=> '<i class="fa fa-chevron-circle-left"></i>',
					'desktop'		=> '<i class="fa fa-desktop"></i>',
					'tablet'		=> '<i class="fa fa-tablet"></i>',
					'mobile'		=> '<i class="fa fa-mobile"></i>',
				];
			}
		}
		else {
			$icons += [
				'home'			=> '<i class="fas fa-home"></i>',
				'nav-home'		=> '<i class="fas fa-home navi-home"></i>',
				'circle-back'		=> '<i class="fas fa-chevron-circle-left"></i>',
				'arrow-back'		=> '<i class="fas fa-arrow-left fa-pull-left"></i>',
				'arrow-forward'		=> '<i class="fas fa-arrow-right fa-pull-right"></i>',
				'file'			=> '<i class="far fa-file"></i>',
				'text'			=> '<i class="fas fa-file-alt"></i>',
				'text-rotate'		=> '<i class="fas fa-file-alt fa-rotate-180"></i>',
				'folder'		=> '<i class="fas fa-folder"></i>',
				'folder-open'		=> '<i class="fas fa-folder-open"></i>',
				'tags'			=> '<i class="fas fa-tags"></i>',
				'tag'			=> '<i class="fas fa-tag"></i>',
				'calendar'		=> '<i class="far fa-calendar-alt"></i>',
				'clock'			=> '<i class="far fa-clock"></i>',
				'repeat'		=> '<i class="fas fa-redo-alt"></i>',
				'close'			=> '<i class="fas fa-times"></i>',
				'menu'			=> '<i class="fas fa-bars"></i>',
				'sidebar'		=> '<i class="fas fa-exchange-alt"></i>',
				'share'			=> '<i class="fas fa-share-alt"></i>',
				'toc'			=> '<i class="fas fa-list"></i>',
				'rss'			=> '<i class="fas fa-rss"></i>',
				'smile'			=> '<i class="far fa-smile"></i>',
				'double-arrow-left'	=> '<i class="fas fa-angle-double-left"></i>',
				'double-arrow-right'	=> '<i class="fas fa-angle-double-right"></i>',
				'search'		=> '<i class="fas fa-search"></i>',
				'install'		=> '<i class="fas fa-download"></i>',
				'pencil'		=> '<i class="fas fa-pencil-alt"></i>',
				'related'		=> '<i class="fas fa-th-list"></i>',
				'trackback'		=> '<i class="fas fa-reply-all"></i>',
				'comment'		=> '<i class="far fa-comment"></i>',
				'renew'			=> '<i class="fas fa-sync-alt"></i>',
			];

			$page_top = isset( $luxe['page_top_icon'] ) ? $luxe['page_top_icon'] : 'fa_arrow_up';
			$icons['page-top'] = '<i class="fas ' . str_replace( '_', '-', $page_top ) . '"></i>';

			if( isset( $luxe['amp'] ) && $_is['user_logged_in'] === true ) {
				$icons += [
					'circle-left'		=> '<i class="fas fa-chevron-circle-left"></i>',
					'desktop'		=> '<i class="fas fa-desktop"></i>',
					'tablet'		=> '<i class="fas fa-tablet-alt"></i>',
					'mobile'		=> '<i class="fas fa-mobile-alt"></i>',
				];
			}
		}
	}
	else {
		$icons += [
			'home'			=> '<i class="material-icons">&#xe88a;</i>',		// home
			'nav-home'		=> '<i class="material-icons navi-home">&#xe88a;</i>',	// home
			'circle-back'		=> '<i class="material-icons flip-h">&#xe038;</i>',	// play_circle_filled
			'arrow-back'		=> '<i class="material-icons pull-left">&#xe5c4;</i>',	// arrow_back
			'arrow-forward'		=> '<i class="material-icons pull-right">&#xe5c8;</i>',	// arrow_forward
			'file'			=> '<i class="material-icons-outlined">&#xe24d;</i>',	// insert_drive_file
			'text'			=> '<i class="material-icons">&#xf009;</i>',		// feed
			'text-rotate'		=> '<i class="material-icons rotate">&#xf009;</i>',	// feed
			'folder'		=> '<i class="material-icons">&#xe2c7;</i>',		// folder
			'folder-open'		=> '<i class="material-icons">&#xe2c8;</i>',		// folder_open
			'tags'			=> '<i class="material-icons">&#xf05b;</i>',		// sell
			'tag'			=> '<i class="material-icons">&#xe892;</i>',		// label
			'calendar'		=> '<i class="material-icons">&#xe614;</i>',		// event_available
			'clock'			=> '<i class="material-icons">&#xe8b5;</i>',		// schedule
			'repeat'		=> '<i class="material-icons">&#xe5d5;</i>',		// refresh
			'close'			=> '<i class="material-icons">&#xe5cd;</i>',		// close
			'menu'			=> '<i class="material-icons">&#xe5d2;</i>',		// menu
			'sidebar'		=> '<i class="material-icons">&#xea18;</i>',		// sync_alt
			'share'			=> '<i class="material-icons">&#xe80d;</i>',		// share
			'toc'			=> '<i class="material-icons">&#xe8de;</i>',		// toc
			'rss'			=> '<i class="material-icons">&#xe0e5;</i>',		// rss_feed
			'smile'			=> '<i class="material-icons">&#xe0ed;</i>',		// sentiment_satisfied_alt
			'double-arrow-left'	=> '<i class="material-icons flip-h">&#xea50;</i>',	// double_arrow
			'double-arrow-right'	=> '<i class="material-icons">&#xea50;</i>',		// double_arrow
			'search'		=> '<i class="material-icons">&#xe8b6;</i>',		// search
			'install'		=> '<i class="material-icons-outlined">&#xf000;</i>',	// download_for_offline
			'pencil'		=> '<i class="material-icons">&#xe3c9;</i>',		// edit
			'related'		=> '<i class="material-icons">&#xe8ef;</i>',		// view_list
			'trackback'		=> '<i class="material-icons">&#xe15f;</i>',		// reply_all
			'comment'		=> '<i class="material-icons-outlined">&#xe0cb;</i>',	// chat_bubble_outline
			'renew'			=> '<i class="material-icons-outlined">&#xe86a;</i>',	// cached
		];

		if( isset( $luxe['page_top_icon'] ) ) {
			switch( $luxe['page_top_icon'] ) {
				case 'fa_caret_up':
					$code_point = '&#xe8fb;';	// eject
					break;
				case 'fa_chevron_up':
					$code_point = '&#xe5ce;';	// expand_less
					break;
				case 'fa_arrow_circle_up':
					$code_point = '&#xf182;';	// arrow_circle_up
					break;
				case 'fa_angle_double_up':
					$code_point = '&#xe318;';	// keyboard_capslock
					break;
				default:
					$code_point = '&#xe5d8;';	// arrow_upward
			}
		}
		if( !isset( $code_point ) ) $code_point = '&#xe5d8;';	// arrow_upward
		$icons['page-top'] = '<i class="material-icons">' . $code_point . '</i>';

		if( isset( $luxe['amp'] ) && $_is['user_logged_in'] === true ) {
			$icons += [
				'circle-left'		=> '<i class="material-icons flip-h big-ico">&#xe038;</i>',	// play_circle_filled
				'desktop'		=> '<i class="material-icons big-ico">&#xe30c;</i>',		// desktop_windows
				'tablet'		=> '<i class="material-icons">&#xe331;</i>',			// tablet_mac
				'mobile'		=> '<i class="material-icons">&#xe325;</i>',			// phone_iphone
			];
		}
	}

	// アイコンが何も設定されてなかったらゼロ幅スペース入れる ( 高さを維持するため )
	if( !isset( $luxe['material_load'] ) && !isset( $luxe['awesome_load'] ) ) {
		foreach( $icons as $key => $val ) $icons[$key] = '<i>&#8203;</i>';	// ゼロ幅スペース (10進:&#8203; 16進:&#x200B;)
	}

	if( !isset( $luxe['pwa_install_button'] ) ) {
		unset( $icons['install'] );
	}

	if( !isset( $luxe['captcha_enable'] ) || ( isset( $luxe['captcha_enable'] ) && $luxe['captcha_enable'] !== 'securimage' ) ) {
		unset( $icons['renew'] );
	}

	if( !isset( $luxe['related_visible'] ) ) {
		unset( $icons['related'] );
	}

	if( !isset( $luxe['comment_visible'] ) && !isset( $luxe['comment_page_visible'] ) ) {
		unset( $icons['comment'] );
	}

	if( !isset( $luxe['trackback_visible'] ) && !isset( $luxe['trackback_page_visible'] ) ) {
		unset( $icons['trackback'] );
	}

	$awesome = $icons;
});
