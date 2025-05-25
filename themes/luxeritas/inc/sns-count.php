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

if( class_exists( 'getSnsCount' ) === false ):
class getSnsCount {
	private $_ret = '!';
	private $_args = array(
		'timeout'	=> 30,
		'redirection'	=> 5,
		'httpversion'	=> '2',
		'user-agent'	=> 'Mozilla/9.9 (X11; Linux x86_64) AppleWebKit/999.99 (KHTML, like Gecko) Chrome/999.9.999.99 Safari/999.99',
		'blocking'	=> true,
		'compress'	=> true,
		'sslverify'	=> false,
	);

	public function __construct() {
	}

	public function numberUnformat( $number ) {
		return str_replace( ',', '', $number );
	}

	/* facebook count */
	public function facebookCount( $url, $app_id = '', $app_secret = '', $access_token = '' ) {
		// 新 API で取得
		if( ( !empty( $app_id ) && !empty( $app_secret ) ) || !empty( $access_token ) ) {
			if( empty( $access_token ) ) {
				$access_token = $app_id . '|' . $app_secret;
			}

			$share = wp_remote_get( 'https://graph.facebook.com/v8.0/?fields=og_object{engagement},engagement&access_token=' . $access_token . '&id=' . $url . '', $this->_args );
			if( !is_wp_error( $share ) ) {
				if( $share['response']['code'] === 200 && isset( $share['body'] ) ) {
					$engagement = @json_decode( $share['body'] )->engagement;
					if( empty( $engagement ) ) {
						$this->_ret = @json_decode( $share['body'] )->og_object->engagement->count;
					}
					else {
						$this->_ret  = !empty( $engagement->reaction_count )       ? (int)$engagement->reaction_count       : 0;
						$this->_ret += !empty( $engagement->comment_count )        ? (int)$engagement->comment_count        : 0;
						$this->_ret += !empty( $engagement->share_count )          ? (int)$engagement->share_count          : 0;
						$this->_ret += !empty( $engagement->comment_plugin_count ) ? (int)$engagement->comment_plugin_count : 0;
					}

					$this->_ret = $this->numberUnformat( $this->_ret );
				}
				elseif( $share['response']['code'] !== 200 ) {
					return $share['response']['message'];
				}
			}

			// 旧 API で取得その１
			if( !is_numeric( $this->_ret ) ) {
				$share = wp_remote_get( 'https://graph.facebook.com/?id=' . $url . '&fields=og_object{engagement}', $this->_args );
				if( !is_wp_error( $share ) ) {
					if( $share['response']['code'] === 200 && isset( $share['body'] ) ) {
						$this->_ret = @json_decode( $share['body'] )->og_object->engagement->count;
						$this->_ret = $this->numberUnformat( $this->_ret );

						if( empty( $this->_ret ) ) {
							$id_confirm = @json_decode( $share['body'] );
							if( isset( $id_confirm->id ) ) {
								$this->_ret = 0;
							}
						}
					}
					elseif( $share['response']['code'] !== 200 ) {
						$this->_ret = '!';
					}
				}
			}

			if( !is_numeric( $this->_ret ) ) $this->_ret = '!';
		}
		else {
			$this->_ret = '!';
		}
		return $this->_ret;
	}

	/* pinterest count */
	public function pinterestCount( $url ) {
		$share = wp_remote_get( 'https://api.pinterest.com/v1/urls/count.json?url=' . $url, $this->_args );
		if( !is_wp_error( $share ) ) {
			if( $share['response']['code'] === 200 && isset( $share['body'] ) ) {
				$this->_ret = rtrim( $share['body'] , ');' ) ;
				$this->_ret = ltrim( $this->_ret , 'receiveCount(' ) ;
				$this->_ret = @json_decode( $this->_ret )->count;
				$this->_ret = $this->numberUnformat( $this->_ret ); 
			}
			elseif( $share['response']['code'] !== 200 ) {
				return $share['response']['message'];
			}
		}
		if( !is_numeric( $this->_ret ) ) $this->_ret = '!';
		return $this->_ret;
	}

	/* linkedin count */
	/*
	public function linkedinCount( $url ) {
		$share = wp_remote_get( 'https://www.linkedin.com/countserv/count/share?format=json&url=' . $url, $this->_args );
		if( !is_wp_error( $share ) ) {
			if( $share['response']['code'] === 200 && isset( $share['body'] ) ) {
				$this->_ret = @json_decode( $share['body'] )->count;
				$this->_ret = $this->numberUnformat( $this->_ret ); 
			}
			elseif( $share['response']['code'] !== 200 ) {
				return $share['response']['message'];
			}
		}
		if( !is_numeric( $this->_ret ) ) $this->_ret = '!';
		return $this->_ret;
	}
	*/

	/* hatena count */
	public function hatenaCount( $url ) {
		$share = wp_remote_get( 'https://api.b.st-hatena.com/entry.count?url=' . $url, $this->_args );
		if( !is_wp_error( $share ) ) {
			if( $share['response']['code'] === 200 && !empty( $share['body'] ) ) {
				$this->_ret = $this->numberUnformat( $share['body'] );
			}
			elseif( $share['response']['code'] !== 200 ) {
				return $share['response']['message'];
			}
			else {
				$this->_ret = 0;
			}
		}
		if( !is_numeric( $this->_ret ) ) $this->_ret = '!';
		return $this->_ret;
	}

	/* pocket count */
	public function pocketCount( $url ) {
		//$share = wp_remote_get( 'https://widgets.getpocket.com/v1/button?v=1&count=horizontal&url=' . $url .'&src=https', $this->_args );
		$share = wp_remote_get( 'https://widgets.getpocket.com/api/saves?url=' . $url, $this->_args );
		if( !is_wp_error( $share ) ) {
			if( $share['response']['code'] === 200 ) {
				if( is_array( $share ) ) {
					$share_cnt = @json_decode( $share['body'] );
				}
				if( !isset( $share_cnt->saves ) ) {
					$this->_ret = 0;
				}
				else {
					$this->_ret = $share_cnt->saves;
				}
			}
			elseif( $share['response']['code'] !== 200 ) {
				return $share['response']['message'];
			}
		}
		if( !is_numeric( $this->_ret ) ) $this->_ret = '!';
		return $this->_ret;
	}

	/* feedly count */
	public function feedlyCount( $url ) {
		$share = wp_remote_get( 'http://cloud.feedly.com/v3/feeds/feed%2F' . $url, $this->_args );
		if( !is_wp_error( $share ) ) {
			if( $share['response']['code'] === 200 && isset( $share['body'] ) ) {
				$this->_ret = @json_decode( $share['body'] )->subscribers;
				if( empty( $this->_ret ) ) {
					$this->_ret = 0;
				}
				else {
					$this->_ret = $this->numberUnformat( $this->_ret ); 
				}
			}
			elseif( $share['response']['code'] !== 200 ) {
				return $share['response']['message'];
			}
		}
		if( !is_numeric( $this->_ret ) ) $this->_ret = '!';
		return $this->_ret;
	}
}
endif;
