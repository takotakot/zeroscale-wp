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

if( class_exists( 'thk_analytics' ) === false ):
class thk_analytics {
	public function __construct() {
	}

	public function analytics( $add_php_file = 'add-analytics.php' ) {
		global $luxe;

		$f = TDEL === SDEL ? TPATH . DSEP : SPATH . DSEP;
		ob_start();
		if( file_exists( $f . $add_php_file ) === true ) {
			require( $f . $add_php_file );
		}
		$analytics = trim( ob_get_clean() );

		if( !empty( $analytics ) ) {
			if( isset( $luxe['amp'] ) ) {
				$amplink  = thk_get_amp_permalink( get_queried_object_id() );
				$amptitle = wp_get_document_title();

				if( strpos( $analytics, "'UA-"  ) !== false ) {
					preg_match( '/[\'|"](UA-[0-9]+?-[0-9]+?)[\'|"]/sm', $analytics, $ua );	// Google Analytics ( old )
				}

				if( strpos( $analytics, "'G-"  ) !== false ) {
					preg_match( '/[\'|"](G-[0-9A-Z-]+?)[\'|"]/sm', $analytics, $ga );	// Google Analytics 4
				}

				// img タグを埋め込んでトラッキングするタイプのアクセス解析は <noscript> 等を外して amp-pixel に置換
				$analytics = preg_replace( '/<img[^>]+?src=([\'|\"][^>]+?[\'|\"])[^>]*?>/ism', '<amp-pixel src=$1></amp-pixel>', $analytics );
				$analytics = thk_amp_not_allowed_tag_replace( $analytics );
				$analytics = thk_amp_tag_replace( $analytics );
				// プロトコルが https じゃない場合は、amp-pixel を width="1" height="1" の amp-img に置換
				$analytics = preg_replace( '/<amp-pixel[^>]+?src=([\'|\"]http\:\/\/[^>]+?[\'|\"])[^>]*?><\/amp-pixel>/ism', '<amp-img src=$1 width="1" height="1" alt=""></amp-img>', $analytics );

				// Google Analytics が記述されていたら amp-analytics に置換

				// Google Analytics 4
				if( !empty( $ga[1] ) ) {
					$parent_file = TPATH . DSEP . 'add-amp-analytics4.php';
					$child_file  = SPATH . DSEP . 'add-amp-analytics4.php';

					if( TPATH !== SPATH && file_exists( $child_file ) === true ) {
						require( $child_file );
					}
					elseif( file_exists( $parent_file ) === true ) {
						require( $parent_file );
					}
					else {
						$analytics .= <<<AMP_ANALYTICS4
<amp-analytics type="googleanalytics" config="https://amp.analytics-debugger.com/ga4.json" data-credentials="include">
<script type="application/json">
{
	"vars": {
		"GA4_MEASUREMENT_ID": "{$ga[1]}",
		"GA4_ENDPOINT_HOSTNAME": "www.google-analytics.com",
		"DEFAULT_PAGEVIEW_ENABLED": true,
		"GOOGLE_CONSENT_ENABLED": false,
		"WEBVITALS_TRACKING": false,
		"PERFORMANCE_TIMING_TRACKING": false,
		"SEND_DOUBLECLICK_BEACON": false
	}
}
</script>
</amp-analytics>
AMP_ANALYTICS4;
					}
				}

				// Google Analytics (old)
				if( !empty( $ua[1] ) ) {
					$parent_file = TPATH . DSEP . 'add-amp-analytics.php';
					$child_file  = SPATH . DSEP . 'add-amp-analytics.php';

					if( TPATH !== SPATH && file_exists( $child_file ) === true ) {
						require( $child_file );
					}
					elseif( file_exists( $parent_file ) === true ) {
						require( $parent_file );
					}
					else {
						$analytics .= <<<AMP_ANALYTICS
<amp-analytics type="googleanalytics" id="analytics1">
<script type="application/json">
{
	"vars": {
		"account": "{$ua[1]}"
	},
	"triggers": {
		"trackPageviewWithAmpdocUrl": {
			"on": "visible",
			"request": "pageview",
			"vars": {
				"title": "{$amptitle}",
				"ampdocUrl": "{$amplink}"
			}
		}
	}
}
</script>
</amp-analytics>
AMP_ANALYTICS;
					}
				}
			}
			$analytics .= "\n";
		}
		return $analytics;
	}
}
endif;
