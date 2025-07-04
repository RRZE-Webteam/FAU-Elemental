<?php

/**
 * Template part for displaying page hero section
 *
 * @package Fau-Elemental
 */
?>

<div class="hero-page">

  <?php if (has_post_thumbnail()) : ?>
    <div class="faue-featured-image">
      <?php
      $featured_img_id = get_post_thumbnail_id();
      $featured_img_src = wp_get_attachment_image_src($featured_img_id, 'full');
      $featured_img_alt = get_post_meta($featured_img_id, '_wp_attachment_image_alt', true);
      $featured_img_srcset = wp_get_attachment_image_srcset($featured_img_id, 'full');
      ?>
      <img src="<?php echo esc_url($featured_img_src[0]); ?>" class="wp-block-cover__image-background wp-post-image" alt="<?php echo esc_attr($featured_img_alt); ?>" <?php if ($featured_img_srcset) : ?> srcset="<?php echo esc_attr($featured_img_srcset); ?>" <?php endif; ?>>
    </div>
  <?php endif; ?>


  <div>
    <h1 class="wp-block-post-title"><?php the_title(); ?></h1>
  </div>

</div>