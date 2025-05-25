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
 * QRコード
 *---------------------------------------------------------------------------*/
if( class_exists( 'thk_qr_code' ) === false ):
class thk_qr_code extends WP_Widget {
	public function __construct() {
		$widget_ops = array( 'classname' => 'thk_qr_code', 'description' => __( 'QR Code', 'luxeritas' ) );
		parent::__construct( 'thk_qr_code', '#9 ' . __( 'QR Code', 'luxeritas' ) . ' (' . __( 'by Luxeritas', 'luxeritas' ) . ')', $widget_ops );
	}

	public function widget( $args, $instance ) {
		global $_is;

		$title		= ( !empty( $instance['title'] ) ) ? $instance['title'] : '';
		$title		= apply_filters( 'widget_title', $title, $instance, $this->id_base );
		$width_height	= isset( $instance['size'] ) ? (int)$instance['size'] : 250;
		$margin		= isset( $instance['margin'] ) ? (int)$instance['margin'] : 0;
		$description	= isset( $instance['description'] ) ? esc_attr( $instance['description'] ) : __( 'QR Code', 'luxeritas' );
		$bgcolor	= !empty( $instance['bgcolor'] ) && $instance['bgcolor'] !== '#ffffff' ? '&amp;bgcolor=' . str_replace( '#', '', $instance['bgcolor'] ) : '';
		$align		= '';

		if( $width_height < 40 ) $width_height = 40;
		if( $margin < 0 ) $margin = 0;

		if( isset( $instance['align'] ) ) {
			if( $instance['align'] === 'center' ) {
				$align = ' class="aligncenter"';
			}
			elseif( $instance['align'] === 'right' ) {
				$align = ' class="alignright"';
			}
		}

		//$format = '<figure class="thk-qr-code-img"><img src="//chart.apis.google.com/chart?chs=%1$dx%1$d&amp;cht=qr&amp;chld=%2$s|%3$s&amp;chl=%4$s" width="%1$d" height="%1$d"' . $position . ' alt="QR Code | ' . get_bloginfo('name') . '" /></figure><div class="clearfix"></div>';
		$format = '<figure class="thk-qr-code-img"><img src="https://api.qrserver.com/v1/create-qr-code/?size=data=%1$dx%1$d&amp;data=%3$s&amp;format=png&amp;qzone=%2$d' . $bgcolor . '" width="%1$d" height="%1$d"' . $align . ' alt="QR Code | ' . get_bloginfo('name') . '" /></figure><div class="clearfix"></div>';

		echo $args['before_widget'];
		if( !empty( $title ) ) echo $args['before_title'] . $title . $args['after_title'];

		$protocol = $_is['ssl'] === true ? 'https' : 'http';
		$permalink = $protocol . '://' . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];

		//echo sprintf( $format, $width_height, 'L', $margin, rawurlencode( $permalink ) )
		echo sprintf( $format, $width_height, $margin, rawurlencode( $permalink ) )
		,    $args['after_widget'];
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title']	= sanitize_text_field( $new_instance['title'] );

		$new_instance		= wp_parse_args( (array)$new_instance, array( 'size' => 150, 'margin' => 0, 'align' => 'left' ) );
		$instance['size']	= (int)$new_instance['size'];
		$instance['margin']	= (int)$new_instance['margin'];
		$instance['align']	= $new_instance['align'];
		$instance['bgcolor']	= isset( $new_instance['bgcolor'] ) ? $new_instance['bgcolor'] : "#ffffff";

		if( $instance['size'] < 40 ) $instance['size'] = 40;
		if( $instance['margin'] < 0 ) $instance['margin'] = 0;

		return $instance;
	}

	public function form( $instance ) {
		$title		= isset( $instance['title'] ) ? sanitize_text_field( $instance['title'] ) : '';
		$instance	= wp_parse_args( (array)$instance, array( 'posturl' => '', 'size' => 150 ) );
		$posturl	= $instance['posturl'] ? 'checked="checked"' : '';
		$size		= isset( $instance['size'] ) ? (int)$instance['size'] : 150;
		$margin		= isset( $instance['margin'] ) ? (int)$instance['margin'] : 0;
		$align		= isset( $instance['align'] ) ? $instance['align'] : '';
		$description	= isset( $instance['description'] ) ? esc_attr( $instance['description'] ) : __( 'QR Code', 'luxeritas' );
		$bgcolor	= isset( $instance['bgcolor'] ) ? esc_attr( $instance['bgcolor'] ) : '#ffffff';

		if( $size < 40 ) $size = 40;
		if( $margin < 0 ) $margin = 0;
?>
<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php echo __( 'Title:', 'luxeritas' ); ?></label><br /><input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
<p><label for="<?php echo $this->get_field_id('size'); ?>"><?php echo __( 'Size', 'luxeritas' ); ?>:</label><br /><input class="widefat" style="width:80px" id="<?php echo $this->get_field_id('size'); ?>" name="<?php echo $this->get_field_name('size'); ?>" type="number" min="40" value="<?php echo $size; ?>" /></p>
<p><label for="<?php echo $this->get_field_id('margin'); ?>"><?php echo __( 'Margin:', 'luxeritas' ); ?></label><br /><input class="widefat" style="width:80px" id="<?php echo $this->get_field_id('margin'); ?>" name="<?php echo $this->get_field_name('margin'); ?>" type="number" min="0" value="<?php echo $margin; ?>" /></p>

<label for="<?php echo $this->get_field_id('align'); ?>"><?php echo __( 'Alignment:', 'luxeritas' ); ?></label><br />
<select id="<?php echo $this->get_field_id('align'); ?>" name="<?php echo $this->get_field_name('align'); ?>" style="width:80px">
<option value="left"<?php if( $align === 'left') echo ' selected'; ?>><?php echo __( 'Left', 'luxeritas' ); ?></option>
<option value="center"<?php if( $align === 'center') echo ' selected'; ?>><?php echo __( 'Center', 'luxeritas' ); ?></option>
<option value="right"<?php if( $align === 'right') echo ' selected'; ?>><?php echo __( 'Right', 'luxeritas' ); ?></option>
</select>
<p>
<label for="<?php echo $this->get_field_id('bgcolor'); ?>"><?php echo __( 'Background color', 'luxeritas' ); ?>:</label><br />
<input class="thk-color-picker" type="text" id="<?php echo $this->get_field_id( 'bgcolor' ); ?>" name="<?php echo $this->get_field_name( 'bgcolor' ); ?>" value="<?php echo $bgcolor; ?>" />
</p>
<?php
	}
}
endif;

/*---------------------------------------------------------------------------
 * コメント一覧 (THK オリジナル)
 *---------------------------------------------------------------------------*/
if( class_exists( 'thk_recent_comments' ) === false ):
class thk_recent_comments extends WP_Widget {
	public function __construct() {
		$widget_ops = array( 'classname' => 'thk_recent_comments', 'description' => __( 'Recent Comments', 'luxeritas' ) );
		parent::__construct( 'thk_recent_comments', '#6 ' . __( 'Recent Comments', 'luxeritas' ) . ' (' . __( 'by Luxeritas', 'luxeritas' ) . ')', $widget_ops );
	}

	public function widget( $args, $instance ) {
		global $comments, $comment;

		$title = ( !empty( $instance['title'] ) ) ? $instance['title'] : '';
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		$number = ( !empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 5;
		if( empty( $number ) ) $number = 5;

		$comments = get_comments( apply_filters( 'widget_comments_args', array(
			'number' => $number,		// 表示件数
			'orderby' => 'comment_date',	// コメント日付でソート
			'order' => 'desc',		// 降順ソート
			'status' => 'approve',		// 承認されたものだけ
			'post_status' => 'publish'	// 公開済みの記事だけ
		) ) );

		echo $args['before_widget'];
		if( !empty( $title ) ) echo $args['before_title'], $title, $args['after_title'];
?>
<div id="thk-rcomments">
<?php
		if( is_array( $comments ) === true ) {
			$title_length = '36';	// 表示する記事タイトルの最大文字数
			$auth_length  = '26';

			foreach( (array)$comments as $comment ) {
				$comment->comment_content = thk_admin_comments( $comment->comment_content, $comment );
				$excerpt = $this->com_excerpt( $comment->comment_content );

				if( strlen( get_the_title( $comment->comment_post_ID ) ) >= $title_length ) {
					$post_title = mb_strimwidth( get_the_title( $comment->comment_post_ID ), 0, $title_length ) . "...";
				}
				else {
					$post_title = get_the_title( $comment->comment_post_ID );
				}

				$comment_author = empty( $comment->comment_author ) ? __( 'Anonymous', 'luxeritas' ) : $comment->comment_author;
				if( strlen( $comment->comment_author ) >= $auth_length ) {
					$comment_author = mb_strimwidth( $comment_author, 0, $auth_length ) . "...";
				}

				$suffix = get_locale() === 'ja' ? ' さん' : '';
				if( $comment->comment_author_url ) {
					$author_link = '<a href="' . $comment->comment_author_url . '"';
					if( strpos( $comment->comment_author_url, home_url() ) !== false ) {
						$author_link .= ' class="url" aria-label="' . sprintf( __( '%s&apos; website', 'luxeritas' ), $comment_author ) . '">' . $comment_author . '</a>';
					}
					else {
						$author_link .= ' target="_blank" rel="external nofollow noopener" class="url external" aria-label="' . sprintf( __( '%s&apos; website', 'luxeritas' ), $comment_author . $suffix ) . '">' . $comment_author . '</a>' . $suffix;
					}
				}
				else {
					$author_link = $comment_author . $suffix;
				}
?>
<div class="recentcomments" itemscope itemtype="https://schema.org/UserComments">
<div class="widget_comment_author">
<?php
				$avatar_id = ( !empty( $comment->user_id ) ) ? $comment->user_id : $comment->comment_author_email;
				echo str_replace( array( 'http:', 'https:' ), '', str_replace( "'", '"', get_avatar( $avatar_id, 32, 'mm', $comment_author ) ) );

				// コメント位置までまでスクロールしたくない場合は、
				// get_comment_link($comment->comment_ID) やめて get_permalink($comment->comment_post_ID) にする
?>
<time class="comment_date" itemprop="commentTime" datetime="<?php echo get_comment_date( 'Y-m-d', $comment ); ?>"><?php echo get_comment_date( __( 'F j, Y', 'luxeritas' ) ); ?></time>
<span class="author_link" itemprop="creator name"><?php echo $author_link; ?></span>
</div>
<div class="comment_excerpt" itemprop="commentText"><i class="ico-comment"></i><?php echo $excerpt; ?></div>
<span class="comment_post"><i class="ico-angle-double-right"></i><a href="<?php echo get_comment_link($comment->comment_ID); ?>" aria-label="<?php echo __( 'Post of this comment', 'luxeritas' ); ?>"><?php echo $post_title; ?></a></span>
</div>
<?php
			}
 		}
?>
</div>
<?php
		echo $args['after_widget'];
	}

	private function com_excerpt( $comment ) {
		$length = 60;
		$excerpt = strip_tags( trim( $comment) );
		$excerpt = str_replace( array("\r", "\n"), '', $excerpt );
		$excerpt = str_replace( "\t", '', $excerpt );
		$excerpt = mb_substr( $excerpt, 0, $length );
		$excerpt .= $length > 0 && mb_strlen( $excerpt ) >= $length ? ' ...' : '';

		return $excerpt;
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title']  = sanitize_text_field( $new_instance['title'] );
		$instance['number'] = absint( $new_instance['number'] );

		return $instance;
	}

	public function form( $instance ) {
		$title  = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : __( 'The latest comments to all posts', 'luxeritas' );
		$number = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
?>
<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php echo __( 'Title:', 'luxeritas' ); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>
<p><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php echo __( 'Number of comments to show:', 'luxeritas' ); ?></label>
<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="number" value="<?php echo $number; ?>" min="1" style="max-width:60px" /></p>
<?php
	}
}
endif;

/*---------------------------------------------------------------------------
 * 新着記事 (THK オリジナル)
 *---------------------------------------------------------------------------*/
if( class_exists( 'thk_recent_posts' ) === false ):
class thk_recent_posts extends WP_Widget {
	public function __construct() {
		$widget_ops = array( 'classname' => 'thk_recent_posts', 'description' => __( 'Recent posts', 'luxeritas' ) );
		parent::__construct( 'thk_recent_posts', '#5 ' . __( 'Recent posts', 'luxeritas' ) . ' (' . __( 'by Luxeritas', 'luxeritas' ) . ')', $widget_ops );
	}

	public static function term_style() {
		// 横長サムネイル使用時の figure (.term) の style。AMP の時は thk_widget_concat で呼び出す
		return <<< TERM_STYLE
			float: none;
			overflow: hidden;
			margin: 0 0 16px;
			max-width: 388px;
			max-height: 218px;
TERM_STYLE;
	}

	public static function img_style() {
		// 横長サムネイル使用時の img の style。AMP の時は thk_widget_concat で呼び出す
		return <<< IMG_STYLE
			margin: 0;
			padding: 0;
			border: 0;
			min-width: 100%;
			max-width: none;
			max-height: none;
IMG_STYLE;
	}

	public function widget( $args, $instance ) {
		$title = !empty( $instance['title'] ) ? $instance['title'] : '';
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		$cat = !empty( $instance['cat'] ) ? absint( $instance['cat'] ) : 0;

		$sort = !empty( $instance['sort'] ) && absint( $instance['sort'] ) === 1 ? 'modified' : 'date';

		$number = !empty( $instance['number'] ) ? absint( $instance['number'] ) : 5;
		if( empty( $number ) ) $number = 5;

		$thumb_type = isset( $instance['thumb_type'] ) ? $instance['thumb_type'] : 'suqare';
		$cat_tag    = isset( $instance['cat_tag'] ) ? $instance['cat_tag'] : 'cat';
		$date       = isset( $instance['date'] ) ? $instance['date'] : 0;
		$excerpt    = isset( $instance['excerpt'] ) ? $instance['excerpt'] : 0;
		$thumbnail  = isset( $instance['thumbnail'] ) ? $instance['thumbnail'] : 0;

		echo $args['before_widget'];

		if( !empty( $title ) ) echo $args['before_title'], $title, $args['after_title'];

		if( $thumb_type === 'wide' ) {
			global $luxe;

			if( !isset( $luxe['amp'] ) ) {
				$term_style = '#' . $args['widget_id'] . ' .term {' . thk_recent_posts::term_style() . '}';
				$img_style  = '#' . $args['widget_id'] . ' .term img {' . thk_recent_posts::img_style() . '}';
?><style><?php echo thk_simple_css_minify( $term_style . $img_style ); ?></style><?php
			}
		}
?>
<div id="thk-new">
<?php
		$arr = array( 'cat' => $cat, 'orderby' => $sort, 'posts_per_page' => $number );
		$st_query = new WP_Query( $arr );

		if( $st_query->have_posts() === true ) {
			global $luxe, $post;
			$wp_upload_dir = wp_upload_dir();

			while( $st_query->have_posts() === true ) {
				$st_query->the_post();
?><div class="toc clearfix"><?php
				if( empty( $thumbnail ) ) {
					$attachment_id = false;
					$echo = false;
					$post_thumbnail = has_post_thumbnail();

					if( $post_thumbnail === false && isset( $luxe['no_img'] ) ) {
						$attachment_id = thk_get_image_id_from_url( $luxe['no_img'] );
						if( $attachment_id !== false ) {
							$post_thumbnail = true;
							$echo = true;
							$aria_label = 'aria-label="No Imaage"';
						}
					}

					if( $post_thumbnail === true ) {
						if( $thumb_type === 'wide' ) {
							$thumb = 'thumb320';
							$image_id = $attachment_id;

							if( $attachment_id === false ) {
								$image_id = get_post_thumbnail_id();
							}
							$image_url = wp_get_attachment_image_src( $image_id, $thumb );

							if( isset( $image_url[0] ) && stripos( $image_url[0], '-320x180.' ) !== false ) {
								$image_path = str_replace( $wp_upload_dir['baseurl'], $wp_upload_dir['basedir'], $image_url[0] );

								if( file_exists( $image_path ) === false ) {
									$thumb = 'thumbnail';
								}
							}
							else {
								$thumb = 'full';
							}
						}
						else {
							$thumb = 'thumb100';
							$image_id = $attachment_id;

							if( $attachment_id === false ) {
								$image_id = get_post_thumbnail_id();
							}
							$image_url = wp_get_attachment_image_src( $image_id, $thumb );

							if( isset( $image_url[0] ) && stripos( $image_url[0], '-100x100.' ) !== false ) {
								$image_path = str_replace( $wp_upload_dir['baseurl'], $wp_upload_dir['basedir'], $image_url[0] );

								if( file_exists( $image_path ) === false ) {
									$thumb = 'thumbnail';
								}
							}
							else {
								$thumb = 'thumbnail';
							}
						}

						if( $attachment_id === false ) {
							$attachment_id = get_post_thumbnail_id();
							$attachment_image = wp_get_attachment_image( $attachment_id );

							if( isset( $luxe['amp'] ) ) {
								$aria_label = 'aria-hidden="true"';
							}
							else {
								if( !empty( $attachment_image ) && stripos( $attachment_image, 'alt="' ) !== false ) {
									$aria_label = substr( $attachment_image, stripos( $attachment_image, 'alt="' ) + 5 );
									$aria_label = substr( $aria_label, 0, stripos( $aria_label, '"' ) );
									if( empty( $aria_label ) ) {
										$aria_label = 'aria-label="' . thk_random_alt_or_aria_label( 'Thumbnail of new posts' ) . '"';
									}
									else {
										$aria_label = 'aria-label="' . $aria_label . '"';
									}
								}
								else {
									$aria_label = 'aria-label="' . thk_random_alt_or_aria_label( 'Thumbnail of new posts' ) . '"';
								}
							}
							//the_post_thumbnail( $thumb );
							//$src = preg_replace( '/srcset=.+?sizes/', 'sizes', get_the_post_thumbnail( $post->ID, $thumb ) );
							$src = get_the_post_thumbnail( $post->ID, $thumb );
						}
						else {
							if( isset( $luxe['amp'] ) ) {
								$aria_label = 'aria-hidden="true"';
							}
							else {
								$aria_label = 'aria-label="No Image"';
							}
							//echo wp_get_attachment_image( $attachment_id );
							//$src = preg_replace( '/srcset=.+?sizes/', 'sizes', wp_get_attachment_image( $attachment_id ) );
							$src = wp_get_attachment_image( $attachment_id, $thumb );
						}

						$src = preg_replace( array("/( data-srcset| srcset| sizes)=[\"']{1}((?:\\\.|[^\"\\\])*)[\"']{1}/i"), '', $src );

?><figure class="term"><a href="<?php the_permalink() ?>" <?php echo $aria_label; ?>><?php
						//echo $src;
						echo thk_alt_attribute( $src, 'Thumbnail of new posts' );
					}
					else {
						$aria_label = isset( $luxe['amp'] ) ? 'aria-hidden="true"' : 'aria-label="No Image"';

						$no_img_width  = 100;
						$no_img_height = 100;
						if( $thumb_type === 'wide' ) {
							$no_img_width  = 320;
							$no_img_height = 180;
						}
?><figure class="term"><a href="<?php the_permalink() ?>" <?php echo $aria_label; ?>><img src="<?php echo esc_url( get_template_directory_uri() ); ?>/images/no-img-<?php echo $no_img_width, 'x', $no_img_height; ?>.png" alt="No Image" title="No Image" width="<?php echo $no_img_width; ?>" height="<?php echo $no_img_height; ?>" /><?php
					}
?></a>
</figure>
<?php
				}
?>
<div class="excerpt"<?php if( !empty( $thumbnail ) ) echo ' style="padding:0 10px"'; ?>>
<?php
if( $date === 0 || $cat_tag !== 'none' ) {
	?><p class="new-meta"><?php
	if( $date === 0 ) {
		if( $sort === 'date' ) {
			printf( '<time class="date" datetime="%1$s">%2$s</time>', get_the_date( 'c' ), get_the_date() );
		} else {
			printf( '<time class="date" datetime="%1$s">%2$s</time>', get_the_modified_date( 'c' ), get_the_modified_date() );
		}
	}
	if( $cat_tag !== 'none' ) {
		$cat_array = get_the_category();
		$tag_array = get_the_tags();

		if( $date === 0 && ( isset( $cat_array[0]->cat_name ) || isset( $tag_array[0]->name ) ) ) { ?><span class="sep"> : </span><?php }

		if( $cat_tag === 'cat' ) {
			if( isset( $cat_array[0]->cat_name ) ) {
				?><span class="cat_or_tag"><?php echo $cat_array[0]->cat_name; ?></span><?php
			}
			elseif( isset( $tag_array[0]->name ) ) {
				?><span class="cat_or_tag"><?php echo $tag_array[0]->name; ?></span><?php
			}
		}
		else {
			if( isset( $tag_array[0]->name ) ) {
				?><span class="cat_or_tag"><?php echo $tag_array[0]->name; ?></span><?php
			}
			elseif( isset( $cat_array[0]->cat_name ) ) {
				?><span class="cat_or_tag"><?php echo $cat_array[0]->cat_name; ?></span><?php
			}
		}
	}
	?></p><?php
}
?>
<p class="new-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></p><?php

if( $excerpt === 0 ) echo apply_filters( 'thk_excerpt_no_break', 40 );
?></div></div><?php
			}
		}
		else {
?>
<p><?php echo __( 'No new posts', 'luxeritas' ); ?></p>
<?php
		}
		wp_reset_postdata();
?>
</div>
<?php
		echo $args['after_widget'];
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title']	= sanitize_text_field( $new_instance['title'] );
		$instance['thumb_type']	= sanitize_text_field( $new_instance['thumb_type'] );
		$instance['cat']	= absint( $new_instance['cat'] );
		$instance['sort']	= absint( $new_instance['sort'] );
		$instance['number']	= absint( $new_instance['number'] );
		$instance['cat_tag']	= sanitize_text_field( $new_instance['cat_tag'] );
		$instance['date']       = absint( $new_instance['date'] );
		$instance['excerpt']    = absint( $new_instance['excerpt'] );
		$instance['thumbnail']	= absint( $new_instance['thumbnail'] );

		return $instance;
	}

	public function form( $instance ) {
		$title  = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : __( 'Recent posts', 'luxeritas' );
		$cat    = isset( $instance['cat'] ) ? absint( $instance['cat'] ) : 0;
		$thumb_type = isset( $instance['thumb_type'] ) ? esc_attr( $instance['thumb_type'] ) : 'square';
		$sort   = isset( $instance['sort'] ) ? absint( $instance['sort'] ) : 0;
		$number = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
		$cat_tag = isset( $instance['cat_tag'] ) ? $instance['cat_tag'] : 'cat';
		$date    = isset( $instance['date'] ) ? absint( $instance['date'] ) : 0;
		$excerpt = isset( $instance['excerpt'] ) ? absint( $instance['excerpt'] ) : 0;
		$thumbnail = isset( $instance['thumbnail'] ) ? absint( $instance['thumbnail'] ) : 0;
?>
<p><label for="<?php echo $this->get_field_id( 'title' ); ?>" style="line-height:2"><?php echo __( 'Title:', 'luxeritas' ); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>
<table><tbody><tr>
<td style="padding:10px 0"><label for="<?php echo $this->get_field_id( 'cat' ); ?>"><?php echo __( 'Category', 'luxeritas' ); ?></label></td>
<td><?php wp_dropdown_categories( array('show_option_all' => __( 'All Categories', 'luxeritas' ), 'id' => $this->get_field_id( 'cat' ), 'name' => $this->get_field_name( 'cat' ), 'selected' => $cat ) ) ?></td>
</tr><tr>
<td style="padding:10px 0"><label for="<?php echo $this->get_field_id( 'thumb_type' ); ?>"><?php echo __( 'Thumbnail', 'luxeritas' ); ?></label></td>
<td><select id="<?php echo $this->get_field_id( 'thumb_type' ); ?>" name="<?php echo $this->get_field_name( 'thumb_type' ); ?>">
<option value="square"><?php echo __( 'Small square thumbnail', 'luxeritas' ); ?></option>
<option value="wide" <?php selected( $thumb_type, 'wide' ); ?>><?php echo __( 'Wide thumbnail', 'luxeritas' ); ?></option>
</select>
</td>
</tr><tr>
<td style="padding:10px 0"><label for="<?php echo $this->get_field_id( 'sort' ); ?>"><?php echo __( 'Sort', 'luxeritas' ); ?></label></td>
<td>
<select id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'sort' ); ?>">
<option value="0"><?php echo __( 'publish date', 'luxeritas' ); ?></option>
<option value="1" <?php selected( $sort, 1 ); ?>><?php echo __( 'updated date', 'luxeritas' ); ?></option>
</select>
</td>
</tr><tr>
<td style="padding:10px 0"><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php echo __( 'Number of posts to show:', 'luxeritas' ); ?></label></td>
<td><input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="number" value="<?php echo $number; ?>" min="1" style="max-width:60px" /></td>
</tr></tbody></table>
<p><?php echo __( 'Category or tag display:', 'luxeritas' ); ?></p>
<table><tbody>
<tr><td style="padding:0 0 3px 10px "><input class="radio" id="<?php echo $this->get_field_name('cat_tag'); ?>" name="<?php echo $this->get_field_name('cat_tag'); ?>" type="radio" value="cat" <?php checked( $cat_tag === 'cat' ? 1 : 0 ); ?> />
<label for="<?php echo $this->get_field_id('cat_tag'); ?>"><?php echo __( 'Display category preferentially', 'luxeritas' ); ?></label></td></tr>
<tr><td style="padding:3px 0 3px 10px"><input class="radio" id="<?php echo $this->get_field_name('cat_tag'); ?>" name="<?php echo $this->get_field_name('cat_tag'); ?>" type="radio" value="tag" <?php checked( $cat_tag === 'tag' ? 1 : 0 ); ?> />
<label for="<?php echo $this->get_field_id('cat_tag'); ?>"><?php echo __( 'Display tag preferentially', 'luxeritas' ); ?></label></td></tr>
<tr><td style="padding:3px 0 3px 10px"><input class="radio" id="<?php echo $this->get_field_name('cat_tag'); ?>" name="<?php echo $this->get_field_name('cat_tag'); ?>" type="radio" value="none" <?php checked( $cat_tag === 'none' ? 1 : 0 ); ?> />
<label for="<?php echo $this->get_field_id('cat_tag'); ?>"><?php echo __( 'Do not display', 'luxeritas' ); ?></label></td></tr>
</tbody></table>
<p><input class="checkbox" id="<?php echo $this->get_field_id( 'date' ); ?>" name="<?php echo $this->get_field_name( 'date' ); ?>" type="checkbox" value="1" <?php checked( $date, 1 ); ?> />
<label for="<?php echo $this->get_field_id( 'date' ); ?>"><?php echo __( 'No date', 'luxeritas' ); ?></label></p>
<p><input class="checkbox" id="<?php echo $this->get_field_id( 'excerpt' ); ?>" name="<?php echo $this->get_field_name( 'excerpt' ); ?>" type="checkbox" value="1" <?php checked( $excerpt, 1 ); ?> />
<label for="<?php echo $this->get_field_id( 'excerpt' ); ?>"><?php echo __( 'No excerpt', 'luxeritas' ); ?></label></p>
<p><input class="checkbox" id="<?php echo $this->get_field_id( 'thumbnail' ); ?>" name="<?php echo $this->get_field_name( 'thumbnail' ); ?>" type="checkbox" value="1" <?php checked( $thumbnail, 1 ); ?> />
<label for="<?php echo $this->get_field_id( 'thumbnail' ); ?>"><?php echo __( 'No thumbnail', 'luxeritas' ); ?></label></p>
<?php
	}
}
endif;

/*---------------------------------------------------------------------------
 * SNS フォローボタン
 *---------------------------------------------------------------------------*/
if( class_exists( 'thk_follow_button' ) === false ):
class thk_follow_button extends WP_Widget {
	public function __construct() {
		$widget_ops = array( 'classname' => 'thk_follow_button', 'description' => __( 'SNS Follow Button', 'luxeritas' ) );
		parent::__construct( 'thk_follow_button', '#7 ' . __( 'SNS Follow Button', 'luxeritas' ) . ' (' . __( 'by Luxeritas', 'luxeritas' ) . ')', $widget_ops );
	}

	public function widget( $args, $instance ) {
		global $luxe, $awesome;

		$title = ( !empty( $instance['title'] ) ) ? $instance['title'] : '';
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		echo $args['before_widget'];
		if( !empty( $title ) ) echo $args['before_title'], $title, $args['after_title'];

		if(
			isset( $instance['twitter'] )	||
			isset( $instance['facebook'] )	||
			isset( $instance['instagram'] )	||
			isset( $instance['pinit'] )	||
			isset( $instance['hatena'] )	||
			isset( $instance['youtube'] )	||
			isset( $instance['line'] )	||
			isset( $instance['rss'] )	||
			isset( $instance['feedly'] )
		) {

		$publisher = 'Person';
		if( $luxe['site_name_type'] === 'Organization' ) {
			$publisher = 'Organization';
		}

		$author = '';
		if( isset( $instance['twitter_id'] ) ) {
			$author = $instance['twitter_id'];
		}
		elseif( $luxe['site_name_type'] === 'Organization' ) {
			$author = get_bloginfo( 'name' );
		}
		else {
			$auth = get_users();
			$author = get_the_author_meta( 'user_nicename', $auth[0]->ID );
		}
?>
<div id="thk-follow" itemscope itemtype="https://schema.org/<?php echo $publisher;?>">
<link itemprop="url" href="<?php echo THK_HOME_URL; ?>"><meta itemprop="name" content="<?php echo $author; ?>"/>
<ul>
<?php
	if( isset( $instance['twitter'] ) ):
?><li><span class="snsf twitter"><a href="//twitter.com/<?php echo isset($instance['twitter_id']) ? rawurlencode( rawurldecode( $instance['twitter_id'] ) ) : ''; ?>" target="blank" title="Twitter" rel="nofollow noopener" itemprop="sameAs"><i class="ico-x-twitter"></i><?php if( $instance['icon'] ) echo '<span class="fname">Twitter</span>'; ?></a></span></li>
<?php
	endif;
	if( isset( $instance['facebook'] ) ):
?><li><span class="snsf facebook"><a href="//www.facebook.com/<?php echo isset($instance['facebook_id']) ? rawurlencode( rawurldecode( $instance['facebook_id'] ) ) : ''; ?>" target="blank" title="Facebook" rel="nofollow noopener" itemprop="sameAs"><i class="ico-facebook"></i><?php if( $instance['icon'] ) echo '<span class="fname">Facebook</span>'; ?></a></span></li>
<?php
	endif;
	if( isset( $instance['instagram'] ) ):
?><li><span class="snsf instagram"><a href="//www.instagram.com/<?php echo isset($instance['instagram_id']) ? rawurlencode( rawurldecode( $instance['instagram_id'] ) ) : ''; ?>?ref=badge" target="blank" title="Instagram" rel="nofollow noopener" itemprop="sameAs"><i class="ico-instagram"></i><?php if( $instance['icon'] ) echo '<span class="fname">Instagram</span>'; ?></a></span></li>
<?php
	endif;
	if( isset( $instance['pinit'] ) ):
?><li><span class="snsf pinit"><a href="//www.pinterest.com/<?php echo isset($instance['pinit_id']) ? rawurlencode( rawurldecode( $instance['pinit_id'] ) ) : ''; ?>" target="blank" title="Pinterest" rel="nofollow noopener" itemprop="sameAs"><i class="ico-pinterest-p"></i><?php if( $instance['icon'] ) echo '<span class="fname">Pinterest</span>'; ?></a></span></li>
<?php
	endif;
	if( isset( $instance['hatena'] ) ):
?><li><span class="snsf hatena"><a href="//b.hatena.ne.jp/<?php echo isset($instance['hatena_id']) ? rawurlencode( rawurldecode( $instance['hatena_id'] ) ) : ''; ?>" target="blank" title="<?php echo __( 'Hatena Bookmark', 'luxeritas' ); ?>" rel="nofollow noopener" itemprop="sameAs"><i class="ico-hatena bold">B!</i><?php if( $instance['icon'] ) echo '<span class="fname">Hatena</span>'; ?></a></span></li>
<?php
	endif;
	if( isset( $instance['youtube'] ) ):
		$youtube_id = '';
		$youtube_type = 'channel/';
		if( !empty( $instance['youtube_c_id'] ) ) {
			$youtube_id = rawurlencode( rawurldecode( $instance['youtube_c_id'] ) );
		}
		elseif( !empty( $instance['youtube_id'] ) ) {
			$youtube_id = rawurlencode( rawurldecode( $instance['youtube_id'] ) );
			$youtube_type = 'user/';
		}
?><li><span class="snsf youtube"><a href="//www.youtube.com/<?php echo $youtube_type, $youtube_id; ?>" class="nofloatbox" target="blank" title="YouTube" rel="nofollow noopener" itemprop="sameAs"><i class="ico-youtube-play"></i><?php if( $instance['icon'] ) echo '<span class="fname">YouTube</span>'; ?></a></span></li>
<?php
	endif;
	if( isset( $instance['line'] ) ):
?><li><span class="snsf line"><a href="//line.naver.jp/ti/p/<?php echo isset($instance['line_id']) ? rawurlencode( rawurldecode( $instance['line_id'] ) ) : ''; ?>" target="blank" title="LINE" rel="nofollow noopener" itemprop="sameAs"><i class="ico-line"></i><?php if( $instance['icon'] ) echo '<span class="fname">LINE</span>'; ?></a></span></li>
<?php
	endif;
	if( isset( $instance['rss'] ) ):
?><li><span class="snsf rss"><a href="<?php echo get_bloginfo('rss2_url'); ?>" target="_blank" title="RSS" rel="nofollow noopener" itemprop="sameAs"><?php echo $awesome['rss']; ?><?php if( $instance['icon'] ) echo '<span class="fname">RSS</span>'; ?></a></span></li>
<?php
	endif;
	if( isset( $instance['feedly'] ) ):
?><li><span class="snsf feedly"><a href="//feedly.com/index.html#subscription/feed/<?php echo rawurlencode( get_bloginfo('rss2_url') ); ?>" target="blank" title="Feedly" rel="nofollow noopener" itemprop="sameAs"><i class="ico-feedly"></i><?php if( $instance['icon'] ) echo '<span class="fname">Feedly</span>'; ?></a></span></li>
<?php
	endif;
?></ul>
<div class="clearfix"></div>
</div>
<?php
		}
		echo $args['after_widget'];
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title']	= sanitize_text_field( $new_instance['title'] );
		$instance['icon']	= $new_instance['icon'];

		$sns_list = array(
			'twitter',
			'facebook',
			'instagram',
			'pinit',
			'hatena',
			'youtube_c',
			'youtube',
			'line',
		);

		foreach( $sns_list as $val ) {
			$instance[$val]	= $new_instance[$val];
			$instance[$val . '_id']	= $new_instance[$val . '_id'];
		}

		$instance['rss']	= $new_instance['rss'];
		$instance['feedly']	= $new_instance['feedly'];

		return $instance;
	}

	public function form( $instance ) {
		$title		= isset( $instance['title'] )		? esc_attr( $instance['title'] )	: __( 'Follow me!', 'luxeritas' );
		$twitter_id	= isset( $instance['twitter_id'] )	? esc_attr( $instance['twitter_id'] )	: '';
		$facebook_id	= isset( $instance['facebook_id'] )	? esc_attr( $instance['facebook_id'] )	: '';
		$instagram_id	= isset( $instance['instagram_id'] )	? esc_attr( $instance['instagram_id'] )	: '';
		$pinit_id	= isset( $instance['pinit_id'] )	? esc_attr( $instance['pinit_id'] )	: '';
		$hatena_id	= isset( $instance['hatena_id'] )	? esc_attr( $instance['hatena_id'] )	: '';
		$youtube_c_id	= isset( $instance['youtube_c_id'] )	? esc_attr( $instance['youtube_c_id'] )	: '';
		$youtube_id	= isset( $instance['youtube_id'] )	? esc_attr( $instance['youtube_id'] )	: '';
		$line_id	= isset( $instance['line_id'] )		? esc_attr( $instance['line_id'] )	: '';

		$sns_list = array(
			'twitter'	=> array(
				'Twitter',
				$twitter_id,
				'Twitter ID ( http://twitter.com/XXXXX )'
			),
			'facebook'	=> array(
				'Facebook',
				$facebook_id,
				'Facebook ID ( http://www.facebook.com/XXXXX )'
			),
			'instagram'	=> array(
				'Instagram',
				$instagram_id,
				'Instagram ID ( http://www.instagram.com/XXXXX )'
			),
			'pinit'	=> array(
				'Pinterest',
				$pinit_id,
				'Pinterest ID ( http://www.pinterest.com/XXXXX )'
			),
			'hatena'	=> array(
				__( 'Hatena Bookmark', 'luxeritas' ),
				$hatena_id,
				'Hatena ID ( http://b.hatena.ne.jp/XXXXX )'
			),
			'youtube'	=> array(
				'YouTube',
				array( $youtube_c_id, $youtube_id ),
				array(
					'YouTube ID ( http://www.youtube.com/channel/XXXXX )',
					'YouTube old ID ( http://www.youtube.com/user/XXXXX )'
				)
			),
			'line'		=> array(
				'LINE',
				$line_id,
				'LINE ID ( http://line.naver.jp/ti/p/XXXXX )'
			),
		);

		if( empty( $instance ) ) {
			$instance['rss']	= 1;
			$instance['feedly']	= 1;
		}

?>
<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php echo __( 'Title:', 'luxeritas' ); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>

<p><?php echo __( 'Button appearance:', 'luxeritas' ); ?><br />
<input name="<?php echo $this->get_field_name( 'icon' ); ?>" type="radio" value="0" <?php echo !isset( $instance['icon'] ) || !$instance['icon'] ? 'checked="checked"' : ''; ?> />
<label for="<?php echo $this->get_field_id( 'icon' ); ?>"><?php echo __( 'Icon only', 'luxeritas' ); ?></label><br />
<input name="<?php echo $this->get_field_name( 'icon' ); ?>" type="radio" value="1"  <?php echo isset( $instance['icon'] ) && $instance['icon'] ? 'checked="checked"' : ''; ?> />
<label for="<?php echo $this->get_field_id( 'icon' ); ?>"><?php echo __( 'Icon + SNS name', 'luxeritas' ); ?></label>
</p>
<?php
		$p_style = 'background:#f5f5f5; padding:3% 5%';
		$i_style = 'width:90%';

		foreach( $sns_list as $key => $val ) {
?>
<p><input class="checkbox" id="<?php echo $this->get_field_id( $key ); ?>" name="<?php echo $this->get_field_name( $key ); ?>" type="checkbox" value="1" <?php checked( isset( $instance[$key] ) ? $instance[$key] : 0 ); ?> />
<label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo $val[0], ' ', __( 'follow button display', 'luxeritas' ); ?></label></p>
<?php
			if( $key === 'youtube' ) {
?>
<p style="<?php echo $p_style; ?>"><?php echo $val[2][0]; ?>:<br />
<input class="widefat" style="<?php echo $i_style; ?>" id="<?php echo $this->get_field_id( $key . '_c_id' ); ?>" name="<?php echo $this->get_field_name( $key . '_c_id' ); ?>" type="text" value="<?php echo $val[1][0]; ?>" /></p>
<p style="<?php echo $p_style; ?>"><?php echo $val[2][1]; ?>:<br />
<input class="widefat" style="<?php echo $i_style; ?>" id="<?php echo $this->get_field_id( $key . '_id' ); ?>" name="<?php echo $this->get_field_name( $key . '_id' ); ?>" type="text" value="<?php echo $val[1][1]; ?>" /></p>
<?php
			}
			else {
?>
<p style="<?php echo $p_style; ?>"><?php echo $val[2]; ?>:<br />
<input class="widefat" style="<?php echo $i_style; ?>" id="<?php echo $this->get_field_id( $key . '_id' ); ?>" name="<?php echo $this->get_field_name( $key . '_id' ); ?>" type="text" value="<?php echo $val[1]; ?>" /></p>
<?php
			}
		}
?>
<p><input class="checkbox" id="<?php echo $this->get_field_id( 'rss' ); ?>" name="<?php echo $this->get_field_name( 'rss' ); ?>" type="checkbox" value="1" <?php checked( isset( $instance['rss'] ) ? $instance['rss'] : 0 ); ?> />
<label for="<?php echo $this->get_field_id( 'rss' ); ?>"><?php echo sprintf( __( '%s button display', 'luxeritas' ), 'RSS' ); ?></label></p>

<p><input class="checkbox" id="<?php echo $this->get_field_id( 'feedly' ); ?>" name="<?php echo $this->get_field_name( 'feedly' ); ?>" type="checkbox" value="1" <?php checked( isset( $instance['feedly'] ) ? $instance['feedly'] : 0 ); ?> />
<label for="<?php echo $this->get_field_id( 'feedly' ); ?>"><?php echo sprintf( __( '%s button display', 'luxeritas' ), 'Feedly' ); ?></label></p>
<?php
	}
}
endif;

/*---------------------------------------------------------------------------
 * RSS / Feedly 購読ボタン
 *---------------------------------------------------------------------------*/
if( class_exists( 'thk_rss_feedly' ) === false ):
class thk_rss_feedly extends WP_Widget {
	public function __construct() {
		$widget_ops = array( 'classname' => 'thk_rss_feedly', 'description' => __( 'RSS / Feedly Button', 'luxeritas' ) );
		parent::__construct( 'thk_rss_feedly', '#8 ' . __( 'RSS / Feedly Button', 'luxeritas' ) . ' (' . __( 'by Luxeritas', 'luxeritas' ) . ')', $widget_ops );
	}

	public function widget( $args, $instance ) {
		global $awesome;

		$title = ( !empty( $instance['title'] ) ) ? $instance['title'] : '';
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		echo $args['before_widget'];
		if( !empty( $title ) ) echo $args['before_title'], $title, $args['after_title'];
?>
<div id="thk-rss-feedly">
<ul>
<li><a href="<?php echo get_bloginfo('rss2_url'); ?>" class="icon-rss-button" target="_blank" title="RSS" rel="nofollow noopener"><?php echo $awesome['rss']; ?><span>RSS</span></a></li>
<li><a href="//feedly.com/index.html#subscription/feed/<?php echo rawurlencode( get_bloginfo('rss2_url') ); ?>" class="icon-feedly-button" target="blank" title="feedly" rel="nofollow noopener"><i class="ico-feedly"></i><span>Feedly</span></a></li>
</ul>
<div class="clearfix"></div>
</div>
<?php
		echo $args['after_widget'];
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = sanitize_text_field( $new_instance['title'] );

		return $instance;
	}

	public function form( $instance ) {
		$title  = isset( $instance['title'] ) ? sanitize_text_field( $instance['title'] ) : __( 'Subscribe to this blog', 'luxeritas' );
?>
<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php echo __( 'Title:', 'luxeritas' ); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>
<?php
	}
}
endif;

/*---------------------------------------------------------------------------
 * Advertising / Adsense Widget (主にアドセンスだが、なんでも OK)
 *---------------------------------------------------------------------------*/
if( class_exists( 'thk_adsense_widget' ) === false ):
class thk_adsense_widget extends WP_Widget {
	private $_pages		= array();
	private $_adsense	= array();
	private $_label		= array();

	public function __construct() {
		$widget_ops = array(
			'classname'   => 'thk_adsense_widget',
			'description' => __( 'It&#x27;s mainly for adsense, but you can write whatever you like text, HTML, shortcode etc.', 'luxeritas' ) .
					 __( 'This widget can be switched between &quot;show / hide&quot; on the specified page.', 'luxeritas' )
		);
		parent::__construct( 'thk_adsense_widget', '#1 ' . __( 'Advertising / Adsense', 'luxeritas' ) . ' (' . __( 'by Luxeritas', 'luxeritas' ) . ')', $widget_ops );

		global $_is;

		if( $_is['admin'] === true ) {
			$this->_pages = array(
				'preview'   =>	__( 'Preview page', 'luxeritas' ),
				'customize' =>	__( 'Customize preview page', 'luxeritas' ),
				'is404'     =>	__( '404 Not Found page', 'luxeritas' ),
				'search'    =>	__( 'Search result page', 'luxeritas' ),
				'archive'   =>	__( 'Archive', 'luxeritas' ),
				'category'  =>	__( 'Category', 'luxeritas' ),
				'page'      =>	__( 'Static page', 'luxeritas' ),
				'single'    =>	__( 'Post page', 'luxeritas' ),
				'top'       =>	__( 'Top page', 'luxeritas' ),
				'specified' =>	__( 'Specified page', 'luxeritas' ) . ' ( ' . __( 'Specify post IDs separated by commas or line feeds', 'luxeritas' ) . ' )'
			);
			$this->_adsense = array(
				'none'		=>	__( 'Not specified', 'luxeritas' ),
				'ad-250-250'	=>	'250x250px ( Square )',
				'ad-300-250'	=>	'300x250px ( Rectangle )',
				'ad-336-280'	=>	'336x280px ( Rectangle big )',
				'ad-120-600'	=>	'120x600px ( Skyscraper )',
				'ad-160-600'	=>	'160x600px ( Wide skyscraper )',
				'ad-300-600'	=>	'300x600px ( Large skyscraper )',
				'ad-468-60'	=>	'468x60px  ( Banner )',
				'ad-728-90'	=>	'728x90px  ( Big banner )',
				'ad-970-90'	=>	'970x90px  ( Large big banner )',
				'ad-970-250'	=>	'970x250px ( Billboard )',
				'ad-320-100'	=>	'320x100px ( Mobile banner )',
				'ad-336-280-col-2'	=>	'336x280px ( Rectangle big ' . __( 'Dual Ad horizon', 'luxeritas' ) . ' )',
				'ad-336-280-row-2'	=>	'336x280px ( Rectangle big ' . __( 'Dual Ad vertical', 'luxeritas' ) . ' )',
				'ad-300-250-col-2'	=>	'300x250px ( Rectangle ' . __( 'Dual Ad horizon', 'luxeritas' ) . ' )',
				'ad-300-250-row-2'	=>	'300x250px ( Rectangle ' . __( 'Dual Ad vertical', 'luxeritas' ) . ' )',
			);
			$this->_label = array(
				'none'			=>	__( 'None', 'luxeritas' ),
				'sponsored-links'	=>	__( 'Sponsored Links', 'luxeritas' ),
				'advertisement'		=>	__( 'Advertisement', 'luxeritas' ),
			);
		}
	}

	public function widget( $args, $instance ) {
		global $_is;

		if(
			( isset( $instance['preview'] )   && $_is['preview'] === true ) ||
			( isset( $instance['customize'] ) && $_is['customize_preview'] === true ) ||
			( isset( $instance['is404'] )     && $_is['404'] === true ) ||
			( isset( $instance['search'] )    && $_is['search'] === true ) ||
			( isset( $instance['archive'] )   && $_is['archive'] === true && $_is['category'] === false ) ||
			( isset( $instance['category'] )  && $_is['category'] === true ) ||
			( isset( $instance['page'] )      && $_is['page'] === true && $_is['front_page'] === false ) ||
			( isset( $instance['single'] )    && $_is['single'] === true ) ||
			( isset( $instance['top'] )       && ( $_is['home'] === true || $_is['front_page'] === true ) )
		) {
			return;
		}

		if( isset( $instance['specified'] ) && !empty( $instance['ids'] ) ) {
			$specifieds = array();
			$arr = explode( ',', $instance['ids'] );

			foreach( (array)$arr as $value ) {
				$specifieds = array_merge( $specifieds, explode( "\n", $value ) );
			}

			foreach( (array)$specifieds as $val ) {
				if( (int)$val === get_the_ID() ) {
					return;
				}
			}
		}

		$title = ( !empty( $instance['title'] ) ) ? $instance['title'] : '';
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		if( !empty( $instance['text'] ) ) {
			$instance['text'] = shortcode_unautop( $instance['text'] );
			$instance['text'] = do_shortcode( $instance['text'] );
		}

		$headline = '';
		$about = '';
		$label = '';
		$args['before_widget'] = str_replace( 'thk_adsense_', 'thk_ps_', $args['before_widget'] );

		if( isset( $instance['center'] ) ) {
			$args['before_widget'] = str_replace( 'class="widget ', 'class="widget al-c ', $args['before_widget'] );
		}
		if( isset( $instance['wpadblock'] ) ) {
			$args['before_widget'] = str_replace( '<div', '<div itemscope itemtype="https://schema.org/WPAdBlock"', $args['before_widget'] );
			$headline = ' itemprop="headline name"';
			$about = ' itemprop="about"';
		}

		echo $args['before_widget'];

		if( isset( $instance['aside'] ) ) {
			echo '<aside>';
		}
		if( !empty( $title ) ) echo $args['before_title'], $title, $args['after_title'];

		$wrap = '<div class="ps-wrap">';
		if( isset( $instance['center'] ) ) {
			$wrap = '<div class="ps-wrap al-c">';
		}

		if( isset( $instance['label'] ) && $instance['label'] !== 'none' ) {
			$center = isset( $instance['label-c'] ) ? ' al-c' : '';

			if( $instance['label'] === 'advertisement' ) {
				$label = '<p' . $headline . ' class="ps-label' . $center . '">' . __( 'Advertisement', 'luxeritas' ) . '</p>';
			}
			else {
				$label = '<p' . $headline . ' class="ps-label' . $center . '">' . __( 'Sponsored Links', 'luxeritas' ) . '</p>';
			}
		}

		if( isset( $instance['text'] ) && stripos( $instance['text'], 'data-full-width-responsive' ) !== false ) {
			if( isset( $instance['fullwidth'] ) && (int)$instance['fullwidth'] === 0 ) {
				$instance['text'] = str_replace( "'", '"', $instance['text'] );
				$instance['text'] = str_replace( 'data-full-width-responsive="true"', 'data-full-width-responsive="false"', $instance['text'] );
			}
		}

		if( isset( $instance['adsense'] ) )  {
			if( $instance['adsense'] === 'ad-250-250' ) {
				$instance['text'] = preg_replace( '/data-ad-format=[\"|\'][a-z]+?[\"|\']/', 'data-ad-format="rectangle"', $instance['text'] );
				echo str_replace( 'ps-wrap', 'ps-wrap ps-250-250', $wrap ), $label;
				?><div<?php echo $about; ?> class="ps-widget rectangle ps-250-250"><?php echo $instance['text']; ?></div><?php
			}
			elseif( $instance['adsense'] === 'ad-300-250' ) {
				$instance['text'] = preg_replace( '/data-ad-format=[\"|\'][a-z]+?[\"|\']/', 'data-ad-format="rectangle"', $instance['text'] );
				echo str_replace( 'ps-wrap', 'ps-wrap ps-300-250', $wrap ), $label;
				?><div<?php echo $about; ?> class="ps-widget rectangle ps-300-250"><?php echo $instance['text']; ?></div><?php
			}
			elseif( $instance['adsense'] === 'ad-336-280' ) {
				$instance['text'] = preg_replace( '/data-ad-format=[\"|\'][a-z]+?[\"|\']/', 'data-ad-format="rectangle"', $instance['text'] );
				echo str_replace( 'ps-wrap', 'ps-wrap ps-336-280', $wrap ), $label;
				?><div<?php echo $about; ?> class="ps-widget rectangle ps-336-280"><?php echo $instance['text']; ?></div><?php
			}
			elseif( $instance['adsense'] === 'ad-120-600' ) {
				$instance['text'] = preg_replace( '/data-ad-format=[\"|\'][a-z]+?[\"|\']/', 'data-ad-format="vertical"', $instance['text'] );
				echo str_replace( 'ps-wrap', 'ps-wrap ps-120-600', $wrap ), $label;
				?><div<?php echo $about; ?> class="ps-widget ps-120-600"><?php echo $instance['text']; ?></div><?php
			}
			elseif( $instance['adsense'] === 'ad-160-600' ) {
				$instance['text'] = preg_replace( '/data-ad-format=[\"|\'][a-z]+?[\"|\']/', 'data-ad-format="vertical"', $instance['text'] );
				echo str_replace( 'ps-wrap', 'ps-wrap ps-160-600', $wrap ), $label;
				?><div<?php echo $about; ?> class="ps-widget ps-160-600"><?php echo $instance['text']; ?></div><?php
			}
			elseif( $instance['adsense'] === 'ad-300-600' ) {
				$instance['text'] = preg_replace( '/data-ad-format=[\"|\'][a-z]+?[\"|\']/', 'data-ad-format="vertical"', $instance['text'] );
				echo str_replace( 'ps-wrap', 'ps-wrap ps-300-600', $wrap ), $label;
				?><div<?php echo $about; ?> class="ps-widget ps-300-600"><?php echo $instance['text']; ?></div><?php
			}
			elseif( $instance['adsense'] === 'ad-468-60' ) {
				$instance['text'] = preg_replace( '/data-ad-format=[\"|\'][a-z]+?[\"|\']/', 'data-ad-format="horizontal"', $instance['text'] );
				echo str_replace( 'ps-wrap', 'ps-wrap ps-468-60', $wrap ), $label;
				?><div<?php echo $about; ?> class="ps-widget ps-468-60"><?php echo $instance['text']; ?></div><?php
			}
			elseif( $instance['adsense'] === 'ad-728-90' ) {
				$instance['text'] = preg_replace( '/data-ad-format=[\"|\'][a-z]+?[\"|\']/', 'data-ad-format="horizontal"', $instance['text'] );
				echo str_replace( 'ps-wrap', 'ps-wrap ps-728-90', $wrap ), $label;
				?><div<?php echo $about; ?> class="ps-widget ps-728-90"><?php echo $instance['text']; ?></div><?php
			}
			elseif( $instance['adsense'] === 'ad-970-90' ) {
				$instance['text'] = preg_replace( '/data-ad-format=[\"|\'][a-z]+?[\"|\']/', 'data-ad-format="horizontal"', $instance['text'] );
				echo str_replace( 'ps-wrap', 'ps-wrap ps-970-90', $wrap ), $label;
				?><div<?php echo $about; ?> class="ps-widget ps-970-90"><?php echo $instance['text']; ?></div><?php
			}
			elseif( $instance['adsense'] === 'ad-970-250' ) {
				$instance['text'] = preg_replace( '/data-ad-format=[\"|\'][a-z]+?[\"|\']/', 'data-ad-format="horizontal"', $instance['text'] );
				echo str_replace( 'ps-wrap', 'ps-wrap ps-970-250', $wrap ), $label;
				?><div<?php echo $about; ?> class="ps-widget ps-970-250"><?php echo $instance['text']; ?></div><?php
			}
			elseif( $instance['adsense'] === 'ad-320-100' ) {
				$instance['text'] = preg_replace( '/data-ad-format=[\"|\'][a-z]+?[\"|\']/', 'data-ad-format="horizontal"', $instance['text'] );
				echo str_replace( 'ps-wrap', 'ps-wrap ps-320-100', $wrap ), $label;
				?><div<?php echo $about; ?> class="ps-widget ps-320-100"><?php echo $instance['text']; ?></div><?php
			}
			elseif( $instance['adsense'] === 'ad-336-280-col-2' ) {
				if( !empty( $about ) ) echo '<div' . $about . '>';
				$instance['text'] = preg_replace( '/data-ad-format=[\"|\'][a-z]+?[\"|\']/', 'data-ad-format="rectangle"', $instance['text'] );
				echo str_replace( 'ps-wrap', 'ps-wrap ps-col', $wrap ), $label;
				?><div class="ps-widget rectangle-1-col ps-336-280"><?php echo $instance['text']; ?></div><div class="ps-widget rectangle-2-col ps-336-280"><?php echo $instance['text']; ?></div><?php
				if( !empty( $about ) ) echo '</div>';
			}
			elseif( $instance['adsense'] === 'ad-336-280-row-2' ) {
				$instance['text'] = preg_replace( '/data-ad-format=[\"|\'][a-z]+?[\"|\']/', 'data-ad-format="rectangle"', $instance['text'] );
				if( !empty( $about ) ) echo '<div' . $about . '>';
				echo str_replace( 'ps-wrap', 'ps-wrap ps-336-280', $wrap ), $label;
				?><div class="ps-widget rectangle-1-row ps-336-280"><?php echo $instance['text']; ?></div><br /><div class="ps-widget rectangle-2-row ps-336-280"><?php echo $instance['text']; ?></div><?php
				if( !empty( $about ) ) echo '</div>';
			}
			elseif( $instance['adsense'] === 'ad-300-250-col-2' ) {
				if( !empty( $about ) ) echo '<div' . $about . '>';
				$instance['text'] = preg_replace( '/data-ad-format=[\"|\'][a-z]+?[\"|\']/', 'data-ad-format="rectangle"', $instance['text'] );
				echo str_replace( 'ps-wrap', 'ps-wrap ps-col', $wrap ), $label;
				?><div class="ps-widget rectangle-1-col ps-300-250"><?php echo $instance['text']; ?></div><div class="ps-widget rectangle-2-col ps-300-250"><?php echo $instance['text']; ?></div><?php
				if( !empty( $about ) ) echo '</div>';
			}
			elseif( $instance['adsense'] === 'ad-300-250-row-2' ) {
				if( !empty( $about ) ) echo '<div' . $about . '>';
				$instance['text'] = preg_replace( '/data-ad-format=[\"|\'][a-z]+?[\"|\']/', 'data-ad-format="rectangle"', $instance['text'] );
				echo str_replace( 'ps-wrap', 'ps-wrap ps-300-250', $wrap ), $label;
				?><div class="ps-widget rectangle-1-row ps-300-250"><?php echo $instance['text']; ?></div><br /><div class="ps-widget rectangle-2-row ps-300-250"><?php echo $instance['text']; ?></div><?php
				if( !empty( $about ) ) echo '</div>';
			}
			else {
				echo $wrap, $label;
				?><div<?php echo $about; ?> class="ps-widget"><?php echo $instance['text']; ?></div><?php
			}
		}
		echo '</div>';

		if( isset( $instance['aside'] ) ) {
			echo '</aside>';
		}
		echo $args['after_widget'];
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title']	= isset( $new_instance['title'] )	? sanitize_text_field( $new_instance['title'] ) : null;
		$instance['title']	= sanitize_text_field( $new_instance['title'] );
		if( current_user_can( 'unfiltered_html' ) ) {
			$instance['text'] = isset( $new_instance['text'] )	? $new_instance['text']		: null;
		} else {
			$instance['text'] = isset( $new_instance['text'] )	? esc_attr( $new_instance['text'] ) : null;
		}
		$instance['adsense']	= isset( $new_instance['adsense'] )	? $new_instance['adsense']	: null;
		$instance['center']	= isset( $new_instance['center'] )	? $new_instance['center']	: null;
		$instance['label']	= isset( $new_instance['label'] )	? $new_instance['label']	: null;
		$instance['label-c']	= isset( $new_instance['label-c'] )	? $new_instance['label-c']	: null;
		$instance['wpadblock']	= isset( $new_instance['wpadblock'] )	? $new_instance['wpadblock']	: null;
		$instance['aside']	= isset( $new_instance['aside'] )	? $new_instance['aside']	: null;
		$instance['fullwidth']	= isset( $new_instance['fullwidth'] )	? $new_instance['fullwidth']	: 0;
		if ( current_user_can( 'unfiltered_html' ) ) {
			$instance['ids'] = isset( $new_instance['ids'] )	? $new_instance['ids']	: null;
		} else {
			$instance['ids'] = isset( $new_instance['ids'] )	? esc_attr( $new_instance['ids'] ) : null;
		}

		foreach( $this->_pages as $key => $val ) {
			$instance[$key]  = isset( $new_instance[$key] )		? $new_instance[$key]	: null;
		}

		return $instance;
	}

	public function form( $instance ) {
		$title  = isset( $instance['title'] ) ? sanitize_text_field( $instance['title'] ) : '';
		if ( current_user_can( 'unfiltered_html' ) ) {
			$text = isset( $instance['text'] ) ? $instance['text'] : '';
		} else {
			$text	= isset( $instance['text'] ) ? esc_attr( $instance['text'] ) : '';
		}
		$adsense= isset( $instance['adsense'] ) ? $instance['adsense']	: 'none';
		$label	= isset( $instance['label'] )	? $instance['label']	: 'none';
		$ids	= isset( $instance['ids'] )	? sanitize_text_field( $instance['ids'] ) : '';
		$fullwidth = isset( $instance['fullwidth'] ) ? (int)$instance['fullwidth'] : 0;

		if( empty( $instance ) ) {
			$instance['preview']	= 1;
			$instance['customize']	= 1;
			$instance['is404']	= 1;
			$instance['specified']	= 1;
		}
?>
<p><label style="font-weight:bold" for="<?php echo $this->get_field_id('title'); ?>"><?php echo __( 'Title:', 'luxeritas' ); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
<p><label style="font-weight:bold" for="<?php echo $this->get_field_id('text'); ?>"><?php echo __( 'Content:', 'luxeritas' ); ?></label>
<textarea class="widefat" rows="10" cols="20" id="<?php echo $this->get_field_id('text'); ?>" name="<?php echo $this->get_field_name('text'); ?>"><?php echo $text; ?></textarea></p>
<p><?php echo __( '* Google Adsense please paste responsive ads.', 'luxeritas' ); ?></p>
<p><?php echo __( '* In addition to advertisements, you can write whatever you like text, HTML, shortcode etc.', 'luxeritas' ); ?></p>

<hr style="margin:20px 0 0 0" />
<p style="font-weight:bold"><?php echo __( 'Adsense Size', 'luxeritas' ); ?>:</p>
<?php
		foreach( $this->_adsense as $key => $val ) {
?>
<p style="margin:5px 0"><input class="radio" id="<?php echo $this->get_field_name('adsense'); ?>" name="<?php echo $this->get_field_name('adsense'); ?>" type="radio" value="<?php echo $key; ?>" <?php checked( $key === $adsense ? 1 : 0 ); ?> />
<label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo $val; ?></label></p>
<?php
			if( $key === 'ad-320-100' ) {
				echo '<hr style="margin:10px 0 0 0; border-top:none; border-bottom:1px dotted #ddd;" />';
			}
		}
?>
<p style="margin:15px 0"><input class="checkbox" id="<?php echo $this->get_field_name('center'); ?>" name="<?php echo $this->get_field_name('center'); ?>" type="checkbox" value="1" <?php checked( isset( $instance['center'] ) ? $instance['center'] : 0 ); ?> />
<label for="<?php echo $this->get_field_id('center'); ?>"><?php echo __( 'Align center', 'luxeritas' ); ?></label></p>


<hr style="margin:20px 0 0 0" />
<p style="font-weight:bold">data-full-width-responsive :</p>
<p style="margin:5px 0"><input class="radio" id="<?php echo $this->get_field_name('fullwidth'); ?>" name="<?php echo $this->get_field_name('fullwidth'); ?>" type="radio" value="1" <?php checked( $fullwidth === 1 ? 1 : 0 ); ?> />
<label for="<?php echo $this->get_field_id('fullwidth'); ?>">true</label></p>
<p style="margin:5px 0"><input class="radio" id="<?php echo $this->get_field_name('fullwidth'); ?>" name="<?php echo $this->get_field_name('fullwidth'); ?>" type="radio" value="0" <?php checked( $fullwidth === 0 ? 1 : 0 ); ?> />
<label for="<?php echo $this->get_field_id('fullwidth'); ?>">false</label></p>


<hr style="margin:20px 0 0 0" />
<p style="font-weight:bold"><?php echo __( 'Label', 'luxeritas' ); ?>:</p>
<?php
		foreach( $this->_label as $key => $val ) {
?>
<p style="margin:5px 0"><input class="radio" id="<?php echo $this->get_field_name('label'); ?>" name="<?php echo $this->get_field_name('label'); ?>" type="radio" value="<?php echo $key; ?>" <?php checked( $key === $label ? 1 : 0 ); ?> />
<label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo $val; ?></label></p>
<?php
		}
?>
<p style="margin:15px 0"><input class="checkbox" id="<?php echo $this->get_field_name('label-c'); ?>" name="<?php echo $this->get_field_name('label-c'); ?>" type="checkbox" value="1" <?php checked( isset( $instance['label-c'] ) ? $instance['label-c'] : 0 ); ?> />
<label for="<?php echo $this->get_field_id('label-c'); ?>"><?php echo __( 'Center the label', 'luxeritas' ); ?></label></p>

<hr style="margin:20px 0 0 0" />
<p style="font-weight:bold"><?php echo __( 'Structured data', 'luxeritas' ); ?>:</p>
<p style="margin:5px 0"><input class="checkbox" id="<?php echo $this->get_field_name('wpadblock'); ?>" name="<?php echo $this->get_field_name('wpadblock'); ?>" type="checkbox" value="1" <?php checked( isset( $instance['wpadblock'] ) ? $instance['wpadblock'] : 0 ); ?> />
<label for="<?php echo $this->get_field_id('wpadblock'); ?>"><?php echo __( 'Add structured data representing advertisements', 'luxeritas' ); ?></label></p>
<p style="margin:5px 0"><input class="checkbox" id="<?php echo $this->get_field_name('aside'); ?>" name="<?php echo $this->get_field_name('aside'); ?>" type="checkbox" value="1" <?php checked( isset( $instance['aside'] ) ? $instance['aside'] : 0 ); ?> />
<label for="<?php echo $this->get_field_id('aside'); ?>"><?php echo __( 'Surrounded by &lt;aside&gt; tag', 'luxeritas' ); ?></label></p>

<hr style="margin:20px 0 0 0" />
<p style="font-weight:bold"><?php echo __( 'Pages not to display this widget:', 'luxeritas' ); ?></p>
<?php
		foreach( $this->_pages as $key => $val ) {
?>
<p style="margin:5px 0"><input class="checkbox" id="<?php echo $this->get_field_id( $key ); ?>" name="<?php echo $this->get_field_name( $key ); ?>" type="checkbox" value="1" <?php checked( isset( $instance[$key] ) ? $instance[$key] : 0 ); ?> />
<label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo $val; ?></label></p>
<?php
		}
?>
<textarea class="widefat" rows="5" cols="20" id="<?php echo $this->get_field_id('ids'); ?>" name="<?php echo $this->get_field_name('ids'); ?>"><?php echo $ids; ?></textarea>
<div style="margin-bottom:30px;"></div>
<?php
	}
}
endif;

/*---------------------------------------------------------------------------
 * カルーセルスライダー
 *---------------------------------------------------------------------------*/
if( class_exists( 'thk_swiper_widget' ) === false ):
class thk_swiper_widget extends WP_Widget {
	private $_pages		= array();
	private $_types		= array();
	private $_thumb		= array();
	private $_nav		= array();
	private $_efect		= array();
	private $_center	= array();
	private $_defaults	= array();

	public function __construct() {
		$widget_ops = array(
			'classname'   => 'thk_swiper_widget',
			'description' => __( 'High feature carousel slider. This widget is not displayed in AMP.', 'luxeritas' ),
		);
		parent::__construct( 'thk_swiper_widget', '#4 ' . __( 'Carousel Slider', 'luxeritas') . ' (' . __( 'by Luxeritas', 'luxeritas' ) . ')', $widget_ops );

		global $_is;

		if( $_is['admin'] === true ) {
			$this->_pages = array(
				'top'       =>	__( 'Top page', 'luxeritas' ),
				'category'  =>	__( 'Category', 'luxeritas' ),
				'archive'   =>	__( 'Archive', 'luxeritas' ),
				'single'    =>	__( 'Post page', 'luxeritas' ),
				'page'      =>	__( 'Static page', 'luxeritas' ),
			);
			$this->_types = array(
				'cat_list'  =>	__( 'Latest list of categories to belong', 'luxeritas' ),
				'tag_list'  =>	__( 'Latest list of tags to belong', 'luxeritas' ),
				'all_list'  =>	__( 'Latest list of all posts', 'luxeritas' ),
				'page_list' =>	__( 'Latest list of static pages', 'luxeritas' ),
				'specified' =>	__( 'Specified page', 'luxeritas' ) . ' ( ' . __( 'Specify post IDs separated by commas or line feeds', 'luxeritas' ) . ' )'
			);

			$sizes = array();
			$image_sizes = get_intermediate_image_sizes();
			foreach( $image_sizes as $size ) {
				if( in_array( $size, array( 'thumbnail', 'medium', 'large' ) ) ) {
					$sizes[$size] = get_option( $size . '_size_w' );
				}
			}
			$this->_thumb = array(
				'thumbnail'	=> 'thumbnail ( ' . $sizes['thumbnail'] . 'px )',
				'medium'	=> 'medium ( ' . $sizes['medium'] . 'px )',
				'thumb320'	=> '320x180px ( 320x180px )',
				'large'		=> 'large ( ' . $sizes['large'] . 'px )',
				'full'		=> 'full ( Original resolution )',
			);
			$this->_nav = array(
				'none'		=> __( 'None', 'luxeritas' ),
				'bullets'	=> __( 'Bullets', 'luxeritas' ),
				'fraction'	=> __( 'Fraction', 'luxeritas' ),
				'progress'	=> __( 'Progress bar', 'luxeritas' ),
			);
			$this->_nxt_prv = array(
				'none'		=> __( 'None', 'luxeritas' ),
				'always'	=> __( 'Always display', 'luxeritas' ),
				'hover'		=> __( 'Displayed only when mouse hover', 'luxeritas' ),
			);
			$this->_efect = array(
				'none'		=> __( 'None', 'luxeritas' ),
				'coverflow'	=> __( '3D animation', 'luxeritas' ) . ' ( 3D Cover Flow )',
				'flip' 		=> __( 'Rotate on mouse over', 'luxeritas' ),
				'highlight'	=> __( 'Display active slide a little larger', 'luxeritas' ),
			);
			$this->_center = array(
				'post'	=> __( 'Display the displayed post in the center', 'luxeritas' ),
				'new'	=> __( 'Display the latest post in the center', 'luxeritas' ),
				'order'	=> __( 'Display in order of posting date / time', 'luxeritas' ),
			);
		}
		$this->_defaults = array(
			'title'		=> '',
			'item_types'	=> 'cat_list',
			'ids'		=> '',
			'thumbnail'	=> 'medium',
			'item_max'	=> 10,
			'show_max'	=> 5,
			//'height'	=> 'auto',
			'height'	=> 'full',
			'height_px'	=> 300,
			'width'		=> 'auto',
			'slide_bg'	=> '#ffffff',
			'navigation'	=> 'bullets',
			'next_prev'	=> 'hover',
			'nav_color'	=> '#007aff',
			'efect'		=> 'none',
			'center_view'	=> 'post',
			'darkness'	=> '',
			'no_lazyload'	=> '',
			'autoplay'	=> 0,
			'bg_color'	=> '',
		);
	}

	public function widget( $args, $instance ) {
		global $luxe, $_is;

		if( isset( $luxe['amp'] ) ) return;

		foreach( $this->_defaults as $key => $val ) {
			if( !isset( $instance[$key] ) ) {
				$instance[$key] = $val;
			}
		}

		if(
			( $_is['home'] === true && $_is['front_page'] === false ) ||
			( $_is['home'] === false && $_is['front_page'] === false && $_is['archive'] === false && $_is['singular'] === false )
		) {
			return;
		}
		if(
			( !isset( $instance['top'] )       && ( $_is['home'] === true || $_is['front_page'] === true ) ) ||
			( !isset( $instance['category'] )  && $_is['category'] === true ) ||
			( !isset( $instance['archive'] )   && $_is['archive'] === true && $_is['category'] === false ) ||
			( !isset( $instance['single'] )    && $_is['single'] === true ) ||
			( !isset( $instance['page'] )      && $_is['page'] === true && $_is['front_page'] === false )
		) {
			return;
		}

		$title = ( !empty( $instance['title'] ) ) ? $instance['title'] : '';
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		require_once( INC . 'thk-swiper.php' );
		$autoplay = (int)$instance['autoplay'] * 1000;

		thk_swiper(
			$args,
			$title,
			$instance['item_types'],
			$instance['item_max'],
			$instance['show_max'],
			$instance['navigation'],
			$instance['next_prev'],
			$instance['nav_color'],
			$instance['ids'],
			$instance['thumbnail'],
			$instance['height'],
			$instance['height_px'],
			$instance['width'],
			$instance['slide_bg'],
			$instance['titleview'],
			$instance['efect'],
			$instance['darkness'],
			$instance['center_view'],
			$instance['no_lazyload'],
			$autoplay
		);
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] 	 = isset( $new_instance['title'] )	 ? sanitize_text_field( $new_instance['title'] ) : null;
		$instance['item_types']  = isset( $new_instance['item_types'] )	 ? $new_instance['item_types']			 : null;
		$instance['ids']	 = isset( $new_instance['ids'] )	 ? wp_kses_post( $new_instance['ids'] )		 : null;
		$instance['thumbnail']   = isset( $new_instance['thumbnail'] )	 ? $new_instance['thumbnail']			 : null;
		$instance['item_max']    = (int)$new_instance['item_max'] < 1 ? 1 : (int)$new_instance['item_max'];
		$instance['show_max']    = (int)$new_instance['show_max'] >= (int)$new_instance['item_max'] ? (int)$instance['item_max'] - 1 : (int)$new_instance['show_max'];
		$instance['show_max']    = (int)$instance['show_max'] < 1 ? 1 : (int)$instance['show_max'];
		$instance['height']      = isset( $new_instance['height'] )	 ? $new_instance['height']	   : null;
		$instance['height_px']   = isset( $new_instance['height_px'] )	 ? (int)$new_instance['height_px'] : null;
		$instance['width']       = isset( $new_instance['width'] )	 ? $new_instance['width']	   : null;
		$instance['slide_bg']	 = isset( $new_instance['slide_bg'] )	 ? $new_instance['slide_bg']	   : null;
		$instance['navigation']  = isset( $new_instance['navigation'] )	 ? $new_instance['navigation']	   : null;
		$instance['next_prev']   = isset( $new_instance['next_prev'] )	 ? $new_instance['next_prev']	   : null;
		$instance['nav_color']   = isset( $new_instance['nav_color'] )	 ? $new_instance['nav_color']	   : null;
		$instance['efect']       = isset( $new_instance['efect'] )	 ? $new_instance['efect']	   : null;
		$instance['center_view'] = isset( $new_instance['center_view'] ) ? $new_instance['center_view']	   : null;
		$instance['titleview']   = isset( $new_instance['titleview'] )	 ? $new_instance['titleview']	   : null;
		$instance['darkness']    = isset( $new_instance['darkness'] )	 ? $new_instance['darkness']	   : null;
		$instance['no_lazyload'] = isset( $new_instance['no_lazyload'] ) ? $new_instance['no_lazyload']	   : null;
		$instance['autoplay']    = isset( $new_instance['autoplay'] )	 ? (int)$new_instance['autoplay']  : null;

		foreach( $this->_pages as $key => $val ) {
			$instance[$key]	= $new_instance[$key];
		}

		return $instance;
	}

	public function form( $instance ) {
		$title	 = isset( $instance['title'] ) 		? sanitize_text_field( $instance['title'] )	: $this->_defaults['title'];
		$types   = isset( $instance['item_types'] )	? $instance['item_types']			: $this->_defaults['item_types'];
		$ids	 = isset( $instance['ids'] )		? wp_kses_post( $instance['ids'] )		: $this->_defaults['ids'];
		$thumb   = isset( $instance['thumbnail'] )	? $instance['thumbnail']			: $this->_defaults['thumbnail'];
		$itemmax = isset( $instance['item_max'] )	? (int)$instance['item_max']			: $this->_defaults['item_max'];
		$showmax = isset( $instance['show_max'] )	? (int)$instance['show_max']			: $this->_defaults['show_max'];
		$height  = isset( $instance['height'] )		? $instance['height']				: $this->_defaults['height'];
		$heightpx= isset( $instance['height_px'] )	? (int)$instance['height_px']			: $this->_defaults['height_px'];
		$width	 = isset( $instance['width'] )		? $instance['width']				: $this->_defaults['width'];
		$nav	 = isset( $instance['navigation'] )	? $instance['navigation']			: $this->_defaults['navigation'];
		$net_prv = isset( $instance['next_prev'] )	? $instance['next_prev']			: $this->_defaults['next_prev'];
		$efect	 = isset( $instance['efect'] )		? $instance['efect']				: $this->_defaults['efect'];
		$center	 = isset( $instance['center_view'] )	? $instance['center_view']			: $this->_defaults['center_view'];
		$center	 = isset( $instance['center_view'] )	? $instance['center_view']			: $this->_defaults['center_view'];
		$autoplay= isset( $instance['autoplay'] )	? (int)$instance['autoplay']			: $this->_defaults['autoplay'];

		if( empty( $instance ) ) {
			$instance['top']	= 1;
			$instance['category']	= 1;
			$instance['archive']	= 1;
			$instance['single']	= 1;
			$instance['page']	= 1;
			$instance['slide_bg']	= $this->_defaults['slide_bg'];
			$instance['nav_color']	= $this->_defaults['nav_color'];
			$instance['titleview']	= 1;
			$instance['darkness']	= 0;
			$instance['no_lazyload']	= 0;
		}
?>
<p style="font-weight:bold"><label for="<?php echo $this->get_field_id('title'); ?>"><?php echo __( 'Title:', 'luxeritas' ); ?></label></p>
<p><input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
<p style="font-weight:bold"><?php echo __( 'Page display this widget', 'luxeritas' ); ?>:</p>
<?php
		foreach( $this->_pages as $key => $val ) {
?>
<p style="margin:5px 0"><input class="checkbox" id="<?php echo $this->get_field_name( $key ); ?>" name="<?php echo $this->get_field_name( $key ); ?>" type="checkbox" value="1" <?php checked( isset( $instance[$key] ) ? $instance[$key] : 0 ); ?> />
<label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo $val; ?></label></p>
<?php
		}
?>
<hr style="margin:20px 0 0 0" />
<p style="font-weight:bold"><?php echo __( 'Item type and number of display items', 'luxeritas' ); ?>:</p>
<?php
		foreach( $this->_types as $key => $val ) {
?>
<p style="margin:5px 0"><input class="radio" id="<?php echo $this->get_field_name('item_types'); ?>" name="<?php echo $this->get_field_name('item_types'); ?>" type="radio" value="<?php echo $key; ?>" <?php checked( $key === $types ? 1 : 0 ); ?> />
<label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo $val; ?></label></p>
<?php
		}
?>
<textarea class="widefat" rows="5" cols="20" id="<?php echo $this->get_field_id('ids'); ?>" name="<?php echo $this->get_field_name('ids'); ?>"><?php echo $ids; ?></textarea>

<p><input class="widefat" style="width:50px" id="<?php echo $this->get_field_id('show_max'); ?>" name="<?php echo $this->get_field_name('show_max'); ?>" type="number" value="<?php echo esc_attr($showmax); ?>" /></p>
<label for="<?php echo $this->get_field_id('show_max'); ?>"><?php echo __( ' : Maximum display number ( * a number smaller than the number of items )', 'luxeritas' ); ?></label>

<p><input class="widefat" style="width:50px" id="<?php echo $this->get_field_id('item_max'); ?>" name="<?php echo $this->get_field_name('item_max'); ?>" type="number" value="<?php echo esc_attr($itemmax); ?>" /></p>
<label for="<?php echo $this->get_field_id('item_max'); ?>"><?php echo __( ' : Maximum number of items', 'luxeritas' ); ?></label>

<hr style="margin:20px 0 0 0" />
<p style="font-weight:bold"><?php echo __( 'Thumbnail (Featured Image)', 'luxeritas' ); ?>:</p>
<?php
		foreach( $this->_thumb as $key => $val ) {
?>
<p style="margin:5px 0"><input class="radio" id="<?php echo $this->get_field_name('thumbnail'); ?>" name="<?php echo $this->get_field_name('thumbnail'); ?>" type="radio" value="<?php echo $key; ?>" <?php checked( $key === $thumb ? 1 : 0 ); ?> />
<label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo $val; ?></label></p>
<?php
		}
?>
<p style="font-weight:bold"><label for="<?php echo $this->get_field_id('width'); ?>"><?php echo __( 'Width', 'luxeritas' ); ?>:</label></p>

<p style="margin:5px 0"><input class="radio" id="<?php echo $this->get_field_name('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="radio" value="auto" <?php checked( $width === 'auto' ? 1 : 0 ); ?> />
<label for="<?php echo $this->get_field_id('width'); ?>"><?php echo __( 'Automatic width', 'luxeritas' ); ?></label></p>
<p style="margin:5px 0"><input class="radio" id="<?php echo $this->get_field_name('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="radio" value="full" <?php checked( $width === 'full' ? 1 : 0 ); ?> />
<label for="<?php echo $this->get_field_id('width'); ?>"><?php echo __( 'Stretch the image to full-width', 'luxeritas' ); ?></label></p>

<p style="font-weight:bold"><label for="<?php echo $this->get_field_id('height'); ?>"><?php echo __( 'Height', 'luxeritas' ); ?>:</label></p>

<p style="margin:5px 0"><input class="radio" id="<?php echo $this->get_field_name('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="radio" value="auto" <?php checked( $height === 'auto' ? 1 : 0 ); ?> />
<label for="<?php echo $this->get_field_id('height'); ?>"><?php echo __( 'Automatic height', 'luxeritas' ), ' (', __( 'It will be the height of the largest thumbnail size', 'luxeritas' ) ,')'; ?></label></p>
<p style="margin:5px 0"><input class="radio" id="<?php echo $this->get_field_name('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="radio" value="full" <?php checked( $height === 'full' ? 1 : 0 ); ?> />
<label for="<?php echo $this->get_field_id('height'); ?>"><?php echo __( 'Stretch the image to full height', 'luxeritas' ); ?></label></p>
<p style="margin:5px 0"><input class="radio" id="<?php echo $this->get_field_name('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="radio" value="manual" <?php checked( $height === 'manual' ? 1 : 0 ); ?> />
<label for="<?php echo $this->get_field_id('height'); ?>"><?php echo __( 'Fixed height', 'luxeritas' ); ?> : <input class="widefat" style="width:60px" id="<?php echo $this->get_field_id('height_px'); ?>" name="<?php echo $this->get_field_name('height_px'); ?>" type="number" value="<?php echo esc_attr($heightpx); ?>" /> px</label></p>

<p style="font-weight:bold"><label for="<?php echo $this->get_field_id('slide_bg'); ?>"><?php echo __( 'Background color', 'luxeritas' ), ' (', __( 'Margin color', 'luxeritas' ), ')'; ?>:</label></p>
<p><input class="thk-color-picker" type="text" id="<?php echo $this->get_field_id( 'slide_bg' ); ?>" name="<?php echo $this->get_field_name( 'slide_bg' ); ?>" value="<?php echo esc_attr( $instance['slide_bg'] ); ?>" /></p>

<hr style="margin:20px 0 0 0" />
<p style="font-weight:bold"><?php echo __( 'Navigation', 'luxeritas' ); ?>:</p>
<?php
		foreach( $this->_nav as $key => $val ) {
?>
<p style="margin:5px 0"><input class="radio" id="<?php echo $this->get_field_name('navigation'); ?>" name="<?php echo $this->get_field_name('navigation'); ?>" type="radio" value="<?php echo $key; ?>" <?php checked( $key === $nav ? 1 : 0 ); ?> />
<label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo $val; ?></label></p>
<?php
		}
?>
<p style="font-weight:bold">Next / Prev:</p>
<?php
		foreach( $this->_nxt_prv as $key => $val ) {
?>
<p style="margin:5px 0"><input class="radio" id="<?php echo $this->get_field_name('next_prev'); ?>" name="<?php echo $this->get_field_name('next_prev'); ?>" type="radio" value="<?php echo $key; ?>" <?php checked( $key === $net_prv ? 1 : 0 ); ?> />
<label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo $val; ?></label></p>
<?php
		}
?>

<p style="font-weight:bold"><?php echo __( 'Color', 'luxeritas' ); ?>:</p>
<p><input class="thk-color-picker" type="text" id="<?php echo $this->get_field_id( 'nav_color' ); ?>" name="<?php echo $this->get_field_name( 'nav_color' ); ?>" value="<?php echo esc_attr( $instance['nav_color'] ); ?>" /></p>

<hr style="margin:20px 0 0 0" />
<p style="font-weight:bold"><label for="<?php echo $this->get_field_id('efect'); ?>"><?php echo __( 'Efects', 'luxeritas' ); ?>:</label></p>

<?php
		foreach( $this->_efect as $key => $val ) {
?>
<p style="margin:5px 0"><input class="radio" id="<?php echo $this->get_field_name('efect'); ?>" name="<?php echo $this->get_field_name('efect'); ?>" type="radio" value="<?php echo $key; ?>" <?php checked( $key === $efect ? 1 : 0 ); ?> />
<label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo $val; ?></label></p>
<?php
		}
?>

<p style="margin:10px 0"><input class="checkbox" id="<?php echo $this->get_field_name('darkness'); ?>" name="<?php echo $this->get_field_name('darkness'); ?>" type="checkbox" value="1" <?php checked( isset( $instance['darkness'] ) ? $instance['darkness'] : 0 ); ?> />
<label for="<?php echo $this->get_field_id('darkness'); ?>"><?php echo __( 'Display non-active slides dark', 'luxeritas' ); ?></label></p>

<hr style="margin:20px 0 0 0" />
<p style="font-weight:bold"><label for="<?php echo $this->get_field_id('options'); ?>"><?php echo __( 'Options', 'luxeritas' ); ?>:</label></p>

<?php
		foreach( $this->_center as $key => $val ) {
?>
<p style="margin:5px 0"><input class="radio" id="<?php echo $this->get_field_name('center_view'); ?>" name="<?php echo $this->get_field_name('center_view'); ?>" type="radio" value="<?php echo $key; ?>" <?php checked( $key === $center ? 1 : 0 ); ?> />
<label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo $val; ?></label></p>
<?php
		}
?>

<p><label for="<?php echo $this->get_field_id('autoplay'); ?>"><?php echo __( 'Autoplay', 'luxeritas' ); ?> : </label>
<input class="widefat" style="width:60px" id="<?php echo $this->get_field_id('autoplay'); ?>" name="<?php echo $this->get_field_name('autoplay'); ?>" type="number" value="<?php echo esc_attr($autoplay); ?>" /> <?php echo __( 'sec', 'luxeritas' ); ?></p>

<p style="margin:5px 0"><input class="checkbox" id="<?php echo $this->get_field_name('titleview'); ?>" name="<?php echo $this->get_field_name('titleview'); ?>" type="checkbox" value="1" <?php checked( isset( $instance['titleview'] ) ? $instance['titleview'] : 0 ); ?> />
<label for="<?php echo $this->get_field_id('titleview'); ?>"><?php echo __( 'Display post title ', 'luxeritas' ); ?></label></p>

<p style="margin:5px 0"><input class="checkbox" id="<?php echo $this->get_field_name('no_lazyload'); ?>" name="<?php echo $this->get_field_name('no_lazyload'); ?>" type="checkbox" value="1" <?php checked( isset( $instance['no_lazyload'] ) ? $instance['no_lazyload'] : 0 ); ?> />
<label for="<?php echo $this->get_field_id('no_lazyload'); ?>"><?php echo __( 'Disable Lazy Load', 'luxeritas' ); ?></label></p>

<div style="margin-bottom:30px;"></div>
<?php
	}
}
endif;

/*---------------------------------------------------------------------------
 * 目次
 *---------------------------------------------------------------------------*/
if( class_exists( 'thk_toc_widget' ) === false ):
class thk_toc_widget extends WP_Widget {
	public function __construct() {
		$widget_ops = array(
			'classname'   => 'thk_toc_widget',
			'description' => __( 'Insert table of contents.', 'luxeritas' ),
		);
		parent::__construct( 'thk_toc_widget', '#3 ' . __( 'Table of Contents', 'luxeritas') . ' (' . __( 'by Luxeritas', 'luxeritas' ) . ')', $widget_ops );
	}

	public function widget( $args, $instance ) {
		global $luxe, $_is;
		if( ( $_is['single'] === true && isset( $luxe['toc_single_enable'] ) ) || ( $_is['page'] === true && isset( $luxe['toc_page_enable'] ) ) ) {
			global $wp_query;

			$title = ( !empty( $instance['title'] ) ) ? $instance['title'] : '';
			$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

			$post = get_post( $wp_query->post->ID );
			//$contents = $post->post_content;
			// ページング対応は wpfunc.php でやるので、以下に変更
			$contents = apply_filters( 'the_content', $post->post_content );

			$toc_array = thk_create_toc( $contents, false );

			if( !isset( $instance['no_contents'] ) || ( isset( $instance['no_contents'] ) && !empty( $toc_array[1] ) ) ) {
				echo $args['before_widget'];
				if( !empty( $title ) ) echo $args['before_title'], $title, $args['after_title'];
			}
			if( !empty( $toc_array[1] ) ) {
				echo '<div class="toc_widget">', $toc_array[1], '</div>';
			}
			if( !isset( $instance['no_contents'] ) || ( isset( $instance['no_contents'] ) && !empty( $toc_array[1] ) ) ) {
				echo $args['after_widget'];
			}
		}
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['no_contents'] = isset( $new_instance['no_contents'] ) ? $new_instance['no_contents'] : null;

		return $instance;
	}

	public function form( $instance ) {
		$title  = isset( $instance['title'] ) ? sanitize_text_field( $instance['title'] ) : __( 'Table of Contents', 'luxeritas' );
		if( empty( $instance ) ) {
			$instance['no_contents'] = 1;
		}
?>
<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php echo __( 'Title:', 'luxeritas' ); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>
<p><?php echo __( 'The setting of this widget is in &quot;Table of Contents&quot; of appearance customization.', 'luxeritas' ); ?></p>
<p style="margin:5px 0"><input class="checkbox" id="<?php echo $this->get_field_name('no_contents'); ?>" name="<?php echo $this->get_field_name('no_contents'); ?>" type="checkbox" value="1" <?php checked( isset( $instance['no_contents'] ) ? $instance['no_contents'] : 0 ); ?> />
<label for="<?php echo $this->get_field_id('no_contents'); ?>"><?php echo __( 'Hide widgets when there is no item', 'luxeritas' ); ?></label></p>
<?php
	}
}
endif;

/*---------------------------------------------------------------------------
 * 再利用ブロックウィジェット
 *---------------------------------------------------------------------------*/
if( class_exists( 'thk_reusable_blocks_widget' ) === false ):
class thk_reusable_blocks_widget extends WP_Widget {
	public function __construct() {
		$widget_ops = array(
			'classname'   => 'thk_reusable_blocks_widget',
			'description' => __( 'Display selected reusable block.', 'luxeritas' ),
		);
		parent::__construct( 'thk_reusable_blocks_widget', '#2 ' . __( 'Reusable Blocks', 'luxeritas' ) . ' (' . __( 'by Luxeritas', 'luxeritas' ) . ')', $widget_ops );
	}
	public function widget( $args, $instance ) {
		if( !empty( $instance['title'] ) ) {
			$title = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
			$title = $args['before_title'] . $title . $args['after_title'];
		}
		else {
			$title = '';
		}

		$objects = !empty( $instance['block_id'] ) ? get_post( $instance['block_id'], 'ARRAY_A', 'display' ) : '';

		// 出力
		if( empty( $objects['post_content'] ) ) {
			$block_contents = __( 'This content cannot be displayed in widgets.', 'luxeritas' );
		}
		else {
			$block_contents = apply_filters( 'thk_content', $objects['post_content'] );
		}
		echo $args['before_widget'], $title, $block_contents, $args['after_widget'];
	}
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['block_id'] = absint( $new_instance['block_id'] );
		return $instance;
	}
	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? sanitize_text_field( $instance['title'] ) : '';
		$block_id = isset( $instance['block_id'] ) ? absint( $instance['block_id'] ) : '';
?>
<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php echo __( 'Title:', 'luxeritas' ); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>
<p><label for="<?php echo $this->get_field_id( 'block_id' ); ?>"><?php echo __( 'Reusable Blocks:', 'luxeritas' ); ?></label>
<?php
		$dropdown_posts = '';

		if( function_exists( 'thk_dropdown_posts' ) === true ) {
			$dropdown_posts = thk_dropdown_posts( array( 
				'selected' => !empty( $block_id ) ? (int)$block_id : '',
				'select_name' => $this->get_field_name( 'block_id' ),
				'echo' => false,
				'class' => 'widefat',
				'post_type' => 'wp_block',
				'show_option_none' => __( 'None', 'luxeritas' ),
				'option_none_value' => '',
			));
		}

		if( !empty( $dropdown_posts ) )	{
			echo $dropdown_posts;
		}
		else {
			echo '<input type="text" value="', __( 'No reusable block available to select.', 'luxeritas' ), '" disabled />';
		}
?></p><?php
	}
}
endif;

/*---------------------------------------------------------------------------
 * PWA インストールボタン
 *---------------------------------------------------------------------------*/
if( class_exists( 'thk_pwa_install' ) === false ):
class thk_pwa_install extends WP_Widget {
	public function __construct() {
		$widget_ops = array( 'classname' => 'thk_pwa_install', 'description' => __( 'PWA install button', 'luxeritas' ) );
		parent::__construct( 'thk_pwa_install', '#10 ' . __( 'PWA install button', 'luxeritas' ) . ' (' . __( 'by Luxeritas', 'luxeritas' ) . ')', $widget_ops );
	}

	public function widget( $args, $instance ) {
		global $_is, $luxe, $awesome;

		$mobile = true;

		if( $_is['mobile'] === false && isset( $instance['mobile'] ) && (int)$instance['mobile'] === 1 ) {
			$mobile = false;
		}

		if( isset( $luxe['pwa_enable'] ) && isset( $luxe['pwa_offline_enable'] ) && isset( $luxe['pwa_install_widget'] ) && $mobile === true ) {
			$title = !empty( $instance['title'] ) ? $instance['title'] : '';
			$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

			$name = !empty( $instance['name'] ) ? $instance['name'] : '';
			$description = !empty( $instance['description'] ) ? $instance['description'] : '';
			$color = !empty( $instance['color'] ) ? $instance['color'] : '#333333';
			$bg = !empty( $instance['bg'] ) ? $instance['bg'] : '#fef8ee';
			$border = !empty( $instance['border'] ) ? $instance['border'] : '#f0b849';

			$box_style = '';
			if( $color !== '#333333' || $bg !== '#fef8ee' || $border !== '#f0b849' ) {
				$box_style .= ' style="';
				if( $color  !== '#333333' ) $box_style .= 'color:' . $color . ';';
				if( $bg     !== '#fef8ee' ) $box_style .= 'background:' . $bg . ';';
				if( $border !== '#f0b849' ) $box_style .= 'border-color:' . $border . ';';
				$box_style = rtrim( $box_style, ';' );
				$box_style .= '"';
			}

			echo str_replace( '<div id=', '<div style="display:none" id=', $args['before_widget'] );
			if( !empty( $title ) ) echo $args['before_title'], $title, $args['after_title'];
?>
<div class="pwa_install_box"<?php echo $box_style; ?>><div class="pwa_install_msg">
<?php
			if( !empty( $description ) ) {
?><div><?php echo $description; ?></div><?php
			}
?><button type="button" class="pwa_install"><?php echo !empty( $name ) ? $name : __( 'Install', 'luxeritas'); ?></button>
</div></div>
<?php
			echo $args['after_widget'];
		}
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['name'] = wp_kses_post( $new_instance['name'] );
		/*
		if( current_user_can( 'unfiltered_html' ) ) {
			$instance['description'] = isset( $new_instance['description'] ) ? $new_instance['description'] : null;
		} else {
			$instance['description'] = isset( $new_instance['description'] ) ? wp_kses_post( $new_instance['description'] ) : null;
		}
		*/
		$instance['description'] = isset( $new_instance['description'] ) ? wp_kses_post( $new_instance['description'] ) : null;
		$instance['color'] = sanitize_text_field( $new_instance['color'] );
		$instance['bg'] = sanitize_text_field( $new_instance['bg'] );
		$instance['border'] = sanitize_text_field( $new_instance['border'] );
		$instance['mobile'] = (int)$new_instance['mobile'];

		return $instance;
	}

	public function form( $instance ) {
		$title  = isset( $instance['title'] ) ? sanitize_text_field( $instance['title'] ) : '';
		$name   = isset( $instance['name'] )  ? esc_attr( $instance['name'] ) : __( 'Install', 'luxeritas' );
		/*
		if( current_user_can( 'unfiltered_html' ) ) {
			$description = isset( $instance['description'] ) ? $instance['description'] : 'Add this site to Home screen as a web app.';
		} else {
			$description = isset( $instance['description'] ) ? wp_kses_post( $instance['description'] ) : 'Add this site to Home screen as a web app.';
		}
		*/
		$description = isset( $instance['description'] ) ? esc_attr( $instance['description'] ) : 'Add this site to Home screen as a web app.';
		$color = isset( $instance['color'] ) ? sanitize_text_field( $instance['color'] ) : '#333333';
		$bg = isset( $instance['bg'] ) ? sanitize_text_field( $instance['bg'] ) : '#fef8ee';
		$border = isset( $instance['border'] ) ? sanitize_text_field( $instance['border'] ) : '#f0b849';
		$mobile = isset( $instance['mobile'] ) ? (int)$instance['mobile'] : 0;
?>
<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php echo __( 'Title:', 'luxeritas' ); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>
<p><label for="<?php echo $this->get_field_id( 'name' ); ?>"><?php echo __( 'Button name', 'luxeritas' ); ?>:</label><br />
<input class="widefat" style="max-width:160px" id="<?php echo $this->get_field_id( 'name' ); ?>" name="<?php echo $this->get_field_name( 'name' ); ?>" type="text" value="<?php echo $name; ?>" /></p>
<p><label for="<?php echo $this->get_field_id( 'description' ); ?>"><?php echo __( 'Button description', 'luxeritas' ); ?>:</label><br />
<textarea id="<?php echo $this->get_field_id( 'description' ); ?>" name="<?php echo $this->get_field_name( 'description' ); ?>" rows="6" style="width:100%"><?php echo $description; ?></textarea></p>
<p><label for="<?php echo $this->get_field_id( 'color' ); ?>"><?php echo __( 'Text color', 'luxeritas' ); ?>:</label><br />
<input class="thk-color-picker" type="text" id="<?php echo $this->get_field_id( 'color' ); ?>" name="<?php echo $this->get_field_name( 'color' ); ?>" value="<?php echo esc_attr( $color ); ?>" /></p>
<p><label for="<?php echo $this->get_field_id( 'bg' ); ?>"><?php echo __( 'Background color', 'luxeritas' ); ?>:</label><br />
<input class="thk-color-picker" type="text" id="<?php echo $this->get_field_id( 'bg' ); ?>" name="<?php echo $this->get_field_name( 'bg' ); ?>" value="<?php echo esc_attr( $bg ); ?>" /></p>
<p><label for="<?php echo $this->get_field_id( 'border' ); ?>"><?php echo __( 'Border color', 'luxeritas' ); ?>:</label><br />
<input class="thk-color-picker" type="text" id="<?php echo $this->get_field_id( 'border' ); ?>" name="<?php echo $this->get_field_name( 'border' ); ?>" value="<?php echo esc_attr( $border ); ?>" /></p>
<p><input class="checkbox" id="<?php echo $this->get_field_name('mobile'); ?>" name="<?php echo $this->get_field_name('mobile'); ?>" type="checkbox" value="1" <?php checked( isset( $instance['mobile'] ) ? $instance['mobile'] : 0 ); ?> />
<label for="<?php echo $this->get_field_id('mobile'); ?>"><?php echo __( 'Display only when mobile device.', 'luxeritas' ); ?></label></p>
<?php
	}
}
endif;

/*---------------------------------------------------------------------------
 * カテゴリ・アーカイブウィジェットのaタグをリストの内側にする
 *---------------------------------------------------------------------------*/
if( function_exists('thk_list_categories_archives') === false ):
function thk_list_categories_archives( $out ) {
	$out = str_replace( '&nbsp;', ' ', $out );
	$out = str_replace( "\t", '', $out);
	$out = str_replace( "'", '"', $out );
	//$out = preg_replace( '/>\s*?<\//', '></', $out );
	//$out = str_replace( "\n</li>", "</li>\n", $out );
	$out = preg_replace( '/<\/a> \(([0-9]*)\)/', ' <span class="count_view">(${1})</span></a>', $out );
	return $out;
}
endif;

add_action( 'wp_head', function() {
	if( isset( $luxe['categories_a_inner'] ) ) {
		add_filter( 'wp_list_categories', 'thk_list_categories_archives', 10, 2 );
	}
	if( isset( $luxe['archives_a_inner'] ) ) {
		add_filter( 'get_archives_link', 'thk_list_categories_archives', 10, 2 );
	}
}, 99 );

/*---------------------------------------------------------------------------
 * ウィジェットの WAF 対策 ?
 *---------------------------------------------------------------------------*/
call_user_func( function() {
	global $luxe, $_is;

	if(
		$_is['admin'] === true && $_is['edit_theme_options'] === true &&
		isset( $luxe['measures_against_waf'] ) && !empty( $_POST )
	) {
		$widget_name = '';
		$referer = wp_get_raw_referer();
		if( stripos( (string)$referer, 'wp-admin/widgets.php' ) !== false ) {
			switch( true ) {
				case isset( $_POST['widget-thk_adsense_widget'] ):
					$widget_name = 'widget-thk_adsense_widget';
					break;
				case isset( $_POST['widget-text'] ):
					$widget_name = 'widget-text';
					break;
				default:
					break;
			}
		}
		if( !empty( $widget_name ) ) {
			// $_POST を暗号化
			add_action( 'after_setup_theme', function() use( $widget_name ) {
				if( isset( $_POST[$widget_name] ) ) {
					$func = 'base' . '64' . '_encode';
					$_POST['f'] = $func( $widget_name );
					$_POST['w'] = $func( serialize( $_POST[$widget_name] ) );
					$_POST['i'] = $func( serialize( $_POST['widget-id'] ) );
					$_POST['b'] = $func( serialize( $_POST['id_base'] ) );
					unset(
						$_POST[$widget_name],
						$_POST['widget-id'],
						$_POST['id_base']
					);
				}
			});
			// $_POST を復号化
			add_action( 'update_option', function() use( $widget_name ) {
				if( isset( $_POST['f'] ) && isset( $_POST['w'] ) && isset( $_POST['i'] ) && isset( $_POST['b'] ) ) {
					$func = 'base' . '64' . '_decode';
					if( $func( $_POST['f'] ) === $widget_name ) {
						$_POST[$widget_name] = unserialize( $func( $_POST['w'] ) );
						$_POST['widget-id'] = unserialize( $func( $_POST['i'] ) );
						$_POST['id_base'] = unserialize( $func( $_POST['b'] ) );
					}
				}
			});
		}
		unset( $widget_name, $referer );
	}
});

/*---------------------------------------------------------------------------
 * カラーピッカー追加
 *---------------------------------------------------------------------------*/
add_action( 'load-widgets.php', function() {
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );

	add_action( 'admin_print_footer_scripts', function() {
?>
<script>
/*
jQuery(document).ready(function($) {
	$('.thk-color-picker').wpColorPicker();
});
*/
/* source: https://core.trac.wordpress.org/attachment/ticket/25809/color-picker-widget.php */
(function($){
	var colorPickerClass = '.thk-color-picker';
	function initColorPicker(widget) {
		widget.find( colorPickerClass ).wpColorPicker({
			change: _.throttle( function() {
				$(this).trigger('change');
			}, 3000 )
		});
	}
	function onFormUpdate(event, widget) {
		initColorPicker(widget);
	}
	$(document).on('widget-added widget-updated', onFormUpdate );
	$(document).ready( function() {
		$('#widgets-right .widget:has(' + colorPickerClass + ')').each( function() {
			initColorPicker( $(this) );
		});
	});
}(jQuery));
</script>
<?php
	});
});

/*---------------------------------------------------------------------------
 * Widget Prefix
 *---------------------------------------------------------------------------*/
if( function_exists( 'thk_widget_prefix' ) === false ):
function thk_widget_prefix( $type = 'area' ){
	if( $type === 'area' ) {
		return array(
			'widget_at_',
			'widget_aw_',
			'widget_ap_',
			'widget_ac_',
			'widget_aa_',
			'widget_as_',
			'widget_av_',
			'widget_a4_'
		);
	}
	else {
		return array(
			'widget_bt_',
			'widget_bw_',
			'widget_bp_',
			'widget_bc_',
			'widget_ba_',
			'widget_bs_',
			'widget_bv_',
			'widget_b4_'
		);
	}
}
endif;

/*---------------------------------------------------------------------------
 * widgets init
 *---------------------------------------------------------------------------*/
if( function_exists('thk_widget_areas') === false ):
function thk_widget_areas() {
	return array(
		array(
			'id'		=> 'side-h3',
			'name'		=> __( 'General-purpose sidebar', 'luxeritas' ) . ' (' . sprintf( __( 'title %s type', 'luxeritas' ), 'H3' ) . ')',
			'description'	=> sprintf( __( 'If you want the title to have %s tag.', 'luxeritas' ), 'h3' ),
			'before_widget'	=> '<div id="%1$s" class="widget %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<h3 class="side-title">',
			'after_title'	=> '</h3>'
		),
		array(
			'id'		=> 'side-h4',
			'name'		=> __( 'General-purpose sidebar', 'luxeritas' ) . ' (' . sprintf( __( 'title %s type', 'luxeritas' ), 'H4' ) . ')',
			'description'	=> sprintf( __( 'If you want the title to have %s tag.', 'luxeritas' ), 'h4' ),
			'before_widget'	=> '<div id="%1$s" class="widget %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<h4 class="side-title">',
			'after_title'	=> '</h4>'
		),
		array(
			'id'		=> 'side-top-h3',
			'name'		=> __( 'Front page sidebar', 'luxeritas' ) . ' (' . sprintf( __( 'title %s type', 'luxeritas' ), 'H3' ) . ')',
			'description'	=> __( 'Front page dedicated.', 'luxeritas' ) . sprintf( __( 'If you want the title to have %s tag.', 'luxeritas' ), 'h3' ),
			'before_widget'	=> '<div id="%1$s" class="widget %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<h3 class="side-title">',
			'after_title'	=> '</h3>'
		),
		array(
			'id'		=> 'side-top-h4',
			'name'		=> __( 'Front page sidebar', 'luxeritas' ) . ' (' . sprintf( __( 'title %s type', 'luxeritas' ), 'H4' ) . ')',
			'description'	=> __( 'Front page dedicated.', 'luxeritas' ) . sprintf( __( 'If you want the title to have %s tag.', 'luxeritas' ), 'h4' ),
			'before_widget'	=> '<div id="%1$s" class="widget %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<h4 class="side-title">',
			'after_title'	=> '</h4>'
		),
		array(
			'id'		=> 'side-no-top-h3',
			'name'		=> __( 'Other than the front page sidebar', 'luxeritas' ) . ' (' . sprintf( __( 'title %s type', 'luxeritas' ), 'H3' ) . ')',
			'description'	=> __( 'Pages other than the front page.', 'luxeritas' ) . sprintf( __( 'If you want the title to have %s tag.', 'luxeritas' ), 'h3' ),
			'before_widget'	=> '<div id="%1$s" class="widget %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<h3 class="side-title">',
			'after_title'	=> '</h3>'
		),
		array(
			'id'		=> 'side-no-top-h4',
			'name'		=> __( 'Other than the front page sidebar', 'luxeritas' ) . ' (' . sprintf( __( 'title %s type', 'luxeritas' ), 'H4' ) . ')',
			'description'	=> __( 'Pages other than the front page.', 'luxeritas' ) . sprintf( __( 'If you want the title to have %s tag.', 'luxeritas' ), 'h4' ),
			'before_widget'	=> '<div id="%1$s" class="widget %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<h4 class="side-title">',
			'after_title'	=> '</h4>'
		),
		array(
			'id'		=> 'side-scroll',
			'name'		=> __( 'Scroll follow sidebar (H4 type)', 'luxeritas' ),
			'description'	=> __( 'Widget to follow the scroll. The title is only h4.', 'luxeritas' ),
			'before_widget'	=> '<div id="%1$s" class="widget %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<h4 class="side-title">',
			'after_title'	=> '</h4>'
		),
		array(
			'id'		=> 'side-amp',
			'name'		=> __( 'AMP sidebar (H4 type)', 'luxeritas' ),
			'description'	=> __( 'AMP dedicated. The title is only h4.', 'luxeritas' ),
			'before_widget'	=> '<div id="%1$s" class="widget %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<h4 class="side-title">',
			'after_title'	=> '</h4>'
		),
		array(
			'id'		=> 'head-under',
			'name'		=> __( 'Under Header Widget', 'luxeritas' ),
			'description'	=> __( 'Place the widget just below the header.', 'luxeritas' ),
			'before_widget'	=> '<div id="%1$s" class="widget head-under %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<p class="head-under-title">',
			'after_title'	=> '</p>'
		),
		array(
			'id'		=> 'head-under-amp',
			'name'		=> __( 'Under Header Widget', 'luxeritas' ) . '( ' . __( 'for AMP', 'luxeritas' ) . ' )',
			'description'	=> __( 'Place the widget just below the header.', 'luxeritas' ),
			'before_widget'	=> '<div id="%1$s" class="widget head-under %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<p class="head-under-title">',
			'after_title'	=> '</p>'
		),
		array(
			'id'		=> 'post-title-upper',
			'name'		=> __( 'Above Post Title Widget', 'luxeritas' ),
			'description'	=> __( 'Place the widget above the post title.', 'luxeritas' ),
			'before_widget'	=> '<div id="%1$s" class="widget post-title-upper %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<p class="post-title-upper-title">',
			'after_title'	=> '</p>'
		),
		array(
			'id'		=> 'post-title-upper-amp',
			'name'		=> __( 'Above Post Title Widget', 'luxeritas' ) . '( ' . __( 'for AMP', 'luxeritas' ) . ' )',
			'description'	=> __( 'Place the widget above the post title.', 'luxeritas' ),
			'before_widget'	=> '<div id="%1$s" class="widget post-title-upper %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<p class="post-title-upper-title">',
			'after_title'	=> '</p>'
		),
		array(
			'id'		=> 'post-title-under',
			'name'		=> __( 'Under Post Title Widget', 'luxeritas' ),
			'description'	=> __( 'Place the widget under the post title.', 'luxeritas' ),
			'before_widget'	=> '<div id="%1$s" class="widget post-title-under %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<p class="post-title-under-title">',
			'after_title'	=> '</p>'
		),
		array(
			'id'		=> 'post-title-under-amp',
			'name'		=> __( 'Under Post Title Widget', 'luxeritas' ) . '( ' . __( 'for AMP', 'luxeritas' ) . ' )',
			'description'	=> __( 'Place the widget under the post title.', 'luxeritas' ),
			'before_widget'	=> '<div id="%1$s" class="widget post-title-under %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<p class="post-title-under-title">',
			'after_title'	=> '</p>'
		),
		array(
			'id'		=> 'post-h2-upper',
			'name'		=> __( 'Above the first H2 tag in the post', 'luxeritas' ),
			'description'	=> __( 'Place the widget above first H2 tag in the post.', 'luxeritas' ),
			'before_widget'	=> '<div id="%1$s" class="widget post-h2-title %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<p class="post-h2-upper-title">',
			'after_title'	=> '</p>'
		),
		array(
			'id'		=> 'post-h2-upper-amp',
			'name'		=> __( 'Above the first H2 tag in the post', 'luxeritas' ) . '( ' . __( 'for AMP', 'luxeritas' ) . ' )',
			'description'	=> __( 'Place the widget above first H2 tag in the post.', 'luxeritas' ),
			'before_widget'	=> '<div id="%1$s" class="widget post-h2-title %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<p class="post-h2-upper-title">',
			'after_title'	=> '</p>'
		),
		array(
			'id'		=> 'related-upper',
			'name'		=> __( 'Above Related Posts Widget', 'luxeritas' ),
			'description'	=> __( 'Place the widget above the related posts.', 'luxeritas' ),
			'before_widget'	=> '<div id="%1$s" class="widget related-upper %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<p class="related-upper-title">',
			'after_title'	=> '</p>'
		),
		array(
			'id'		=> 'related-upper-amp',
			'name'		=> __( 'Above Related Posts Widget', 'luxeritas' ) . '( ' . __( 'for AMP', 'luxeritas' ) . ' )',
			'description'	=> __( 'Place the widget above the related posts.', 'luxeritas' ),
			'before_widget'	=> '<div id="%1$s" class="widget related-upper %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<p class="related-upper-title">',
			'after_title'	=> '</p>'
		),
		array(
			'id'		=> 'related-under',
			'name'		=> __( 'Under Related Posts Widget', 'luxeritas' ),
			'description'	=> __( 'Place the widget under the related posts.', 'luxeritas' ),
			'before_widget'	=> '<div id="%1$s" class="widget related-under %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<p class="related-under-title">',
			'after_title'	=> '</p>'
		),
		array(
			'id'		=> 'related-under-amp',
			'name'		=> __( 'Under Related Posts Widget', 'luxeritas' ) . '( ' . __( 'for AMP', 'luxeritas' ) . ' )',
			'description'	=> __( 'Place the widget under the related posts.', 'luxeritas' ),
			'before_widget'	=> '<div id="%1$s" class="widget related-under %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<p class="related-under-title">',
			'after_title'	=> '</p>'
		),
		array(
			'id'		=> 'posts-list-upper',
			'name'		=> __( 'Above Posts List Widget', 'luxeritas' ),
			'description'	=> __( 'Place the widget above the posts list.', 'luxeritas' ),
			'before_widget'	=> '<div id="%1$s" class="widget posts-list-upper %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<p class="posts-list-upper-title">',
			'after_title'	=> '</p>'
		),
		array(
			'id'		=> 'posts-list-middle',
			'name'		=> __( 'Middle of Posts List Widget', 'luxeritas' ),
			'description'	=> __( 'Place the widget middle of the posts list.', 'luxeritas' ),
			'before_widget'	=> '<div id="%1$s" class="widget posts-list-middle %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<p class="posts-list-middle-title">',
			'after_title'	=> '</p>'
		),
		array(
			'id'		=> 'posts-list-under',
			'name'		=> __( 'Under Posts List Widget', 'luxeritas' ),
			'description'	=> __( 'Place the widget under the posts list.', 'luxeritas' ),
			'before_widget'	=> '<div id="%1$s" class="widget posts-list-under %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<p class="posts-list-under-title">',
			'after_title'	=> '</p>'
		),
		array(
			'id'		=> 'footer-left',
			'name'		=> __( 'Footer left', 'luxeritas' ) . ' (' . __( 'Title H4', 'luxeritas' ) . ')',
			'description'	=> __( 'When the sidebar is offscreen, it will be not shown.', 'luxeritas' ),
			'before_widget'	=> '<div id="%1$s" class="widget %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<h4 class="footer-left-title">',
			'after_title'	=> '</h4>'
		),
		array(
			'id'		=> 'footer-center',
			'name'		=> __( 'Footer center', 'luxeritas' ) . ' (' . __( 'Title H4', 'luxeritas' ) . ')',
			'description'	=> __( 'When the sidebar is offscreen, it will be not shown.', 'luxeritas' ),
			'before_widget'	=> '<div id="%1$s" class="widget %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<h4 class="footer-center-title">',
			'after_title'	=> '</h4>'
		),
		array(
			'id'		=> 'footer-right',
			'name'		=> __( 'Footer right', 'luxeritas' ) . ' (' . __( 'Title H4', 'luxeritas' ) . ')',
			'description'	=> __( 'When the sidebar is offscreen, it will be not shown.', 'luxeritas' ),
			'before_widget'	=> '<div id="%1$s" class="widget %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<h4 class="footer-right-title">',
			'after_title'	=> '</h4>'
		),
		array(
			'id'		=> 'post-under-1',
			'name'		=> __( 'Under Post Widget', 'luxeritas' ),
			'description'	=> __( 'Place the widget under the post.', 'luxeritas' ),
			'before_widget'	=> '<div id="%1$s" class="widget posts-under-1 %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<p class="posts-under-1">',
			'after_title'	=> '</p>'
		),
		array(
			'id'		=> 'post-under-1-amp',
			'name'		=> __( 'Under Post Widget', 'luxeritas' ) . '( ' . __( 'for AMP', 'luxeritas' ) . ' )',
			'description'	=> __( 'Place the widget under the post.', 'luxeritas' ),
			'before_widget'	=> '<div id="%1$s" class="widget posts-under-1 %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<p class="posts-under-1">',
			'after_title'	=> '</p>'
		),
		array(
			'id'		=> 'post-under-2',
			'name'		=> __( 'Further below &quot;Under Post Widget&quot;', 'luxeritas' ),
			'description'	=> __( 'Place the widget further below &quot;Under Post Widget&quot;.', 'luxeritas' ),
			'before_widget'	=> '<div id="%1$s" class="widget posts-under-2 %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<p class="posts-under-2">',
			'after_title'	=> '</p>'
		),
		array(
			'id'		=> 'post-under-2-amp',
			'name'		=> __( 'Further below &quot;Under Post Widget&quot;', 'luxeritas' ) . '( ' . __( 'for AMP', 'luxeritas' ) . ' )',
			'description'	=> __( 'Place the widget further below &quot;Under Post Widget&quot;.', 'luxeritas' ),
			'before_widget'	=> '<div id="%1$s" class="widget posts-under-2 %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<p class="posts-under-2-amp">',
			'after_title'	=> '</p>'
		),
		array(
			'id'		=> 'col3-h3',
			'name'		=> __( '3 Column Left Sidebar', 'luxeritas' ) . ' (' . sprintf( __( 'title %s type', 'luxeritas' ), 'H3' ) . ')',
			'description'	=> sprintf( __( 'If you want the title to have %s tag.', 'luxeritas' ), 'h3' ),
			'before_widget'	=> '<div id="%1$s" class="widget %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<h3 class="side-title">',
			'after_title'	=> '</h3>'
		),
		array(
			'id'		=> 'col3-h4',
			'name'		=> __( '3 Column Left Sidebar', 'luxeritas' ) . ' (' . sprintf( __( 'title %s type', 'luxeritas' ), 'H4' ) . ')',
			'description'	=> sprintf( __( 'If you want the title to have %s tag.', 'luxeritas' ), 'h4' ),
			'before_widget'	=> '<div id="%1$s" class="widget %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<h4 class="side-title">',
			'after_title'	=> '</h4>'
		)
	);
}
endif;

add_action( 'widgets_init', function() {
	// recentcomments のインライン消す (style.css に一応書いとく)
	global $_is, $wp_widget_factory, $wp_registered_sidebars;
	remove_action( 'wp_head', array( $wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style' ) );

	$widget_areas = thk_widget_areas();
	//foreach( $widget_areas as $value ) register_sidebars( 1, $value );
	foreach( $widget_areas as $value ) register_sidebar( $value );
	unset( $widget_areas );

	$widgets = array(
		'thk_adsense_widget',
		'thk_qr_code',
		'thk_recent_comments',
		'thk_recent_posts',
		'thk_follow_button',
		'thk_rss_feedly',
		'thk_swiper_widget',
		'thk_toc_widget',
		'thk_reusable_blocks_widget',
		'thk_pwa_install',
	);

	if( $_is['admin'] === true ) {
		foreach( $widgets as $val ) {
			register_widget( $val );
		}
	}
	else {
		global $sidebars_widgets;
		$active_widgets = array();
		foreach( (array)$sidebars_widgets as $key => $value ) {
			foreach( (array)$value as $val ) {
				$widget_name = strstr( $val, '-', true );
				$active_widgets[$widget_name] = true;
			}
		}

		foreach( $widgets as $val ) {
			if( isset( $active_widgets[$val] ) ) register_widget( $val );
		}
	}
});
