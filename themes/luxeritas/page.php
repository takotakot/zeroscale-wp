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

global $luxe, $_is, $awesome;
get_header();

?>
<article>
<div id="core" class="grid">
<?php
if( $luxe['breadcrumb_view'] === 'inner' ) get_template_part( 'breadcrumb' );
?>
<div itemprop="mainEntityOfPage" id="mainEntity" <?php post_class('post'); ?>>
<?php
if( function_exists('dynamic_sidebar') === true ) {
	if( isset( $luxe['amp'] ) ) {
		if( is_active_sidebar('post-title-upper-amp') === true ) {
			$amp_widget = thk_amp_dynamic_sidebar( 'post-title-upper-amp' );
			if( !empty( $amp_widget ) ) echo $amp_widget;
		}
	}
	else {
		if( is_active_sidebar('post-title-upper') === true ) {
			dynamic_sidebar( 'post-title-upper' );
		}
	}
}

if( have_posts() === true ) {
	while( have_posts() === true ):
	the_post();

	if( $_is['front_page'] === true && !isset( $luxe['front_page_post_title'] ) ) {
?>
<div class="clearfix">
<?php
		//the_content();
		echo apply_filters( 'thk_content', '' ); // 本文
	}
	else {
		// <h1> or <h2> タイトル
		?><header id="article-header"><?php
		$h_tag = $_is['front_page'] === true ? 2 : 1;	// 固定ページがフロントページだった場合は H2
		$cls   = $_is['front_page'] === true ? 'front-page-title' : '';
		$span = false;

		if( ( ( $_is['front_page'] === true && isset( $luxe['thumb_auto_front_page'] ) ) || ( $_is['front_page'] === false && isset( $luxe['thumb_auto_page'] ) ) ) && isset( $luxe['thumb_auto_insert_position'] ) ) {
			if(  $luxe['thumb_auto_insert_position'] === 'top' || strpos( $luxe['thumb_auto_insert_position'], 'back' ) === 0 ) {
				// タイトル上サムネイル
				echo apply_filters( 'thk_thumb_auto_insert', $post->ID );

				if( strpos( $luxe['thumb_auto_insert_position'], 'back' ) === 0 ) {
					$span = true;	// <span> タグ付き (タイトルに background 入れる場合)
				}
			}
		}
		echo apply_filters( 'thk_h_tag', $h_tag, '', 'headline name', $cls, 'entry-title', $span );

		?></header><?php

		if( ( $_is['front_page'] === true && isset( $luxe['thumb_auto_front_page'] ) ) || ( $_is['front_page'] === false && isset( $luxe['thumb_auto_page'] ) ) ) {
			if( isset( $luxe['thumb_auto_insert_position'] ) && $luxe['thumb_auto_insert_position'] === 'below' ) {
				// タイトル下サムネイル
				echo apply_filters( 'thk_thumb_auto_insert', $post->ID );
			}
		}
		?><div class="clearfix"><?php

		get_template_part('meta');
		if( isset( $luxe['sns_page_view'] ) ) { // SNS 記事上
			if( isset( $luxe['sns_tops_enable'] ) ) {
				$luxe['sns_layout'] = 'tops';
				if( isset( $luxe['amp'] ) ) {
					ob_start();
					get_template_part( 'sns' );
					$sns_top = ob_get_clean();
					echo thk_amp_not_allowed_tag_replace( $sns_top );
				}
				else {
					get_template_part( 'sns' );
				}
			}
		}

		if( ( $_is['front_page'] === true && isset( $luxe['thumb_auto_front_page'] ) ) || ( $_is['front_page'] === false && isset( $luxe['thumb_auto_page'] ) ) ) {
			if( isset( $luxe['thumb_auto_insert_position'] ) && $luxe['thumb_auto_insert_position'] === 'above' ) {
				// 記事上サムネイル
				echo apply_filters( 'thk_thumb_auto_insert', $post->ID );
			}
		}

		if( function_exists('dynamic_sidebar') === true ) {
			if( isset( $luxe['amp'] ) ) {
				if( is_active_sidebar('post-title-under-amp') === true ) {
					$amp_widget = thk_amp_dynamic_sidebar( 'post-title-under-amp' );
					if( !empty( $amp_widget ) ) echo $amp_widget;
				}
			}
			else {
				if( is_active_sidebar('post-title-under') === true ) {
					dynamic_sidebar( 'post-title-under' );
				}
			}
		}

		//the_content();
		echo apply_filters( 'thk_content', '' ); // 本文
	}

	if( function_exists('dynamic_sidebar') === true ) {
		if( isset( $luxe['amp'] ) ) {
			if( is_active_sidebar('post-under-1-amp') === true ) {
				$amp_widget = thk_amp_dynamic_sidebar( 'post-under-1-amp' );
				if( !empty( $amp_widget ) ) echo $amp_widget;
			}
		}
		else {
			if( is_active_sidebar('post-under-1') === true ) {
				dynamic_sidebar( 'post-under-1' );
			}
		}
	}
?>
</div>
<?php
	echo apply_filters( 'thk_link_pages', '' );
?>
<div class="meta-box">
<?php
	$luxe['meta_under'] = true;
	get_template_part('meta');

	$author = get_the_author();

	if( isset( $luxe['author_visible'] ) && !empty( $author ) ) {
		if( $luxe['author_page_type'] === 'auth' ) {
?>
<p class="vcard author"><?php echo $awesome['pencil'], __( 'Posted by', 'luxeritas' ); ?> <span class="fn" itemprop="editor author creator copyrightHolder"><a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>"><?php echo $author; ?></a></span><?php
		}
		else {
?>
<p class="vcard author"><?php echo $awesome['pencil'], __( 'Posted by', 'luxeritas' ); ?> <span class="fn" itemprop="editor author creator copyrightHolder"><a href="<?php echo isset( $luxe['thk_author_url'] ) ? $luxe['thk_author_url'] : THK_HOME_URL; ?>"><?php echo $author; ?></a></span><?php
		}
?></p>
<?php
	}
	if( isset( $luxe['hide_luxe_adminbar'] ) && $_is['edit_posts'] === true && $_is['customize_preview'] === false ) {
		$post_link = get_edit_post_link();
		if( isset( $post_link ) ) {
			echo '<p class="vcard author">[ ';
			if( isset( $luxe['amp_enable'] ) ) {
				$amp_permalink = thk_get_amp_permalink( get_queried_object_id() );
				if( isset( $luxe['amp'] ) ) {
					echo	' ', $awesome['circle-back'], '<a href="', wp_get_canonical_url(), '">', __( 'Origin', 'luxeritas' ), '</a>'
					,	' &#x26A1; <a href="https://validator.ampproject.org/#url=', $amp_permalink, '" target="_blank" rel="noopener noreferrer">', __( 'Validate', 'luxeritas' ), '</a>'
					,	' &#x26A1; <a href="https://cdn.ampproject.org/c/', ( stripos( $amp_permalink, 'https:' ) !== false ) ? 's/' : '', str_replace( array( 'http://', 'https://'), '', $amp_permalink ), '" target="_blank" rel="noopener noreferrer">', __( 'Cache', 'luxeritas' ), '</a>';
				}
				else {
					echo	' ', $awesome['text'];
					edit_post_link( __( 'Edit This', 'luxeritas' ) );
					echo	' &#x26A1; <a href="', $amp_permalink, '#development=1">AMP</a>';
				}
			}
			else {
				edit_post_link( __( 'Edit This', 'luxeritas' ) );
			}
			echo ' ]</p>';
		}
	}
?>
</div><!--/.meta-box-->
<?php
	if( isset( $luxe['sns_bottoms_enable'] ) || ( function_exists('dynamic_sidebar') === true && is_active_sidebar('post-under-2') === true ) ) {
		echo '<hr class="pbhr" />';
	}
?>
</div><!--/.post-->
<aside>
<?php
	if( function_exists('dynamic_sidebar') === true ) {
		if( isset( $luxe['amp'] ) ) {
			if( is_active_sidebar('post-under-2-amp') === true ) {
				$amp_widget = thk_amp_dynamic_sidebar( 'post-under-2-amp' );
				if( !empty( $amp_widget ) ) echo $amp_widget;
			}
		}
		else {
			if( is_active_sidebar('post-under-2') === true ) {
				dynamic_sidebar( 'post-under-2' );
			}
		}
	}

	if( isset( $luxe['sns_page_view'] ) ) { // SNS 記事下
		if( isset( $luxe['sns_bottoms_enable'] ) ) {
			if( isset( $luxe['sns_bottoms_msg'] ) ) {
?>
<div class="sns-msg" ><h2><?php echo $luxe['sns_bottoms_msg']; ?></h2></div>
<?php
			}
			// SNS 記事下
			$luxe['sns_layout'] = null;
			if( isset( $luxe['amp'] ) ) {
				ob_start();
				get_template_part( 'sns' );
				$sns_bottom = ob_get_clean();
				echo thk_amp_not_allowed_tag_replace( $sns_bottom );
			}
			else {
				get_template_part( 'sns' );
			}
		}
	}
	endwhile;
}
else {
?>
<p><?php echo __( 'No posts yet', 'luxeritas' ); ?></p>
<?php
}
?>
</aside>
</div><!--/#core-->
<aside>
<?php
if( isset( $luxe['next_prev_nav_page_visible'] ) ) {
?>
<div id="pnavi" class="grid">
<?php
	$wp_upload_dir = wp_upload_dir();

	//$next_post = get_next_post();
	$next_post = get_adjacent_post( false, '', false );
	if( $next_post ) {
		$thumb = 'thumb100';
		$image_id = get_post_thumbnail_id( $next_post->ID );
		$image_url = wp_get_attachment_image_src( $image_id, $thumb );

		if( isset( $image_url[0] ) ) {
			$image_path = str_replace( $wp_upload_dir['baseurl'], $wp_upload_dir['basedir'], $image_url[0] );

			if( file_exists( $image_path ) === false ) {
				$thumb = 'thumbnail';
			}
		}
		else {
			$thumb = 'thumbnail';
		}
		$next_thumb = get_the_post_thumbnail( $next_post->ID, $thumb );
		if( empty( $next_thumb ) ) $next_thumb = '<div class="no-img-next">' . $awesome['text'] . '</div>';
?>
<div class="next"><?php next_post_link( '%link', $next_thumb . '<div class="ntitle">' . $next_post->post_title . '</div><div class="next-arrow">' . $awesome['arrow-forward'] . '<span>' . __( 'Next', 'luxeritas' ) . '</span></div>' ); ?></div>
<?php
	}
	else {
?>
<div class="next"><a href="<?php echo THK_HOME_URL; ?>"><?php echo $awesome['nav-home']; ?><div class="next-arrow"><?php echo $awesome['arrow-forward'], '<span>', __( 'Home ', 'luxeritas' ); ?></span></div></a></div>
<?php
	}
	//$prev_post = get_previous_post();
	$prev_post = get_adjacent_post( false, '', true );
	if( $prev_post ) {
		$thumb = 'thumb100';
		$image_id = get_post_thumbnail_id( $prev_post->ID );
		$image_url = wp_get_attachment_image_src( $image_id, $thumb );

		if( isset( $image_url[0] ) ) {
			$image_path = str_replace( $wp_upload_dir['baseurl'], $wp_upload_dir['basedir'], $image_url[0] );

			if( file_exists( $image_path ) === false ) {
				$thumb = 'thumbnail';
			}
		}
		else {
			$thumb = 'thumbnail';
		}
		$prev_thumb = get_the_post_thumbnail( $prev_post->ID, $thumb );
		if( empty( $prev_thumb ) ) $prev_thumb = '<div class="no-img-prev">' . $awesome['text-rotate'] . '</div>';
?>
<div class="prev"><?php previous_post_link( '%link', $prev_thumb . '<div class="ptitle">' . $prev_post->post_title . '</div><div class="prev-arrow">' . $awesome['arrow-back'] . '<span>' . __( 'Prev', 'luxeritas' ) . '</span></div>' ); ?></div>
<?php
	}
	else {
?>
<div class="prev"><a href="<?php echo THK_HOME_URL; ?>"><?php echo $awesome['nav-home']; ?><div class="prev-arrow"><?php echo $awesome['arrow-back'], '<span>', __( 'Home ', 'luxeritas' ); ?></span></div></a></div>
<?php
	}
?>
</div><!--/.pnavi-->
<?php
}

if( isset( $luxe['comment_page_visible'] ) ) {
	if( $_is['comments_open'] === true || get_comments_number() > 0 ){
		echo apply_filters('thk_comments', '');
	}
}

if( isset( $luxe['trackback_page_visible'] ) && pings_open() === true ) {
?>
<div id="trackback" class="grid">
<h3 class="tb"><?php echo $awesome['trackback'], __( 'TrackBack URL', 'luxeritas' ); ?></h3>
<input type="text" name="trackback_url" aria-label="Trackback url" size="60" value="<?php trackback_url() ?>" readonly="readonly" class="trackback-url" tabindex="0" accesskey="t" />
</div>
<?php
}
?>
</aside>
</article>
</main>
<?php thk_call_sidebar(); ?>
</div><!--/#primary-->
<?php echo apply_filters( 'thk_footer', '' ); ?>
