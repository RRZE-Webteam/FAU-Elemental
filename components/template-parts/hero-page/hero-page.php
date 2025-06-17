<?php
/**
 * Template part for displaying page hero section
 *
 * @package Fau-Elemental
 */
?>

<div class="wp-block-group hero-page is-layout-flow wp-block-group-is-layout-flow">
  
  <div class="wp-block-cover is-dark-theme">
    <span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span>
    <?php if (has_post_thumbnail()) : ?>
      <?php 
      $featured_img_id = get_post_thumbnail_id();
      $featured_img_src = wp_get_attachment_image_src($featured_img_id, 'full');
      $featured_img_alt = get_post_meta($featured_img_id, '_wp_attachment_image_alt', true);
      $featured_img_srcset = wp_get_attachment_image_srcset($featured_img_id, 'full');
      $featured_img_sizes = wp_get_attachment_image_sizes($featured_img_id, 'full');
      ?>
      <img width="<?php echo $featured_img_src[1]; ?>" height="<?php echo $featured_img_src[2]; ?>" src="<?php echo esc_url($featured_img_src[0]); ?>" class="wp-block-cover__image-background wp-post-image" alt="<?php echo esc_attr($featured_img_alt); ?>" data-object-fit="cover" decoding="async" fetchpriority="high"<?php if ($featured_img_srcset) : ?> srcset="<?php echo esc_attr($featured_img_srcset); ?>"<?php endif; ?><?php if ($featured_img_sizes) : ?> sizes="<?php echo esc_attr($featured_img_sizes); ?>"<?php endif; ?>>
    <?php endif; ?>
    <div class="wp-block-cover__inner-container is-layout-flow wp-block-cover-is-layout-flow">
      
      <p class="hideParagraph"></p>
      
    </div>
  </div>
  
  
  <div class="wp-block-group alignfull is-layout-flow wp-block-group-is-layout-flow">
    <h1 class="wp-block-post-title"><?php the_title(); ?></h1>
  </div>
  
</div> 