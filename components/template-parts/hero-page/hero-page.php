<?php

/**
 * Template part for displaying page hero section
 *
 * @package Fau-Elemental
 */
?>

<section class="hero-page" role="region" aria-label="<?php esc_attr_e('Page Hero', 'fau-elemental'); ?>">

  <?php if (has_post_thumbnail()) : ?>
    <div class="faue-featured-image">
      <?php
      $featured_img_id = get_post_thumbnail_id();
      $featured_img_alt = get_post_meta($featured_img_id, '_wp_attachment_image_alt', true);
      $alt = $featured_img_alt ?: get_the_title();
      ?>
      <?php echo wp_get_attachment_image($featured_img_id, 'full', false, [
        'alt' => $alt,
        'sizes' => '(max-width: 393px) 100vw, (max-width: 1199px) 90vw, 1320px'
      ]); ?>
    </div>
  <?php endif; ?>


  <div>
    <h1 class="wp-block-post-title"><?php the_title(); ?></h1>
  </div>

</section>