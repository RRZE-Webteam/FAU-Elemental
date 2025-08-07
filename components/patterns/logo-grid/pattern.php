<?php

/**
 * Title: Logo Grid
 * Slug: fau-elemental/logo-grid
 * Categories: fau-elemental
 * Description: A logo grid section with meta headline and grid of logos.
 * Block Types: core/post-content
 * Post Types: post, page
 * Viewport Width: 1376
 */

?>

<!-- wp:group {"className":"pattern-logo-grid"} -->
<div class="wp-block-group pattern-logo-grid">
    <!-- wp:fau-elemental/fau-meta-headline {"headline":"Logo Grid","id":"","lock":{"move":true}} -->
    <div class="wp-block-fau-elemental-fau-meta-headline" id="headline-">Logo Grid</div>
    <!-- /wp:fau-elemental/fau-meta-headline -->

    <!-- wp:fau-elemental/logo-grid {"logos":[{"imageUrl":"<?php echo esc_url(get_theme_file_uri('assets/images/logo-1.png')); ?>","link":"#","category":"partner"},{"imageUrl":"<?php echo esc_url(get_theme_file_uri('assets/images/logo-2.png')); ?>","link":"#","category":"partner"},{"imageUrl":"<?php echo esc_url(get_theme_file_uri('assets/images/logo-3.png')); ?>","link":"#","category":"partner"},{"imageUrl":"<?php echo esc_url(get_theme_file_uri('assets/images/logo-4.png')); ?>","link":"#","category":"partner"},{"imageUrl":"<?php echo esc_url(get_theme_file_uri('assets/images/logo-5.png')); ?>","link":"#","category":"partner"},{"imageUrl":"<?php echo esc_url(get_theme_file_uri('assets/images/logo-6.png')); ?>","link":"#","category":"partner"},{"imageUrl":"<?php echo esc_url(get_theme_file_uri('assets/images/logo-7.png')); ?>","link":"#","category":"partner"},{"imageUrl":"<?php echo esc_url(get_theme_file_uri('assets/images/logo-8.png')); ?>","link":"#","category":"partner"}],"lock":{"move":true}} -->
    <div class="wp-block-fau-elemental-logo-grid fau-logo-grid wp-block-fau-logo-grid">
        <div class="fau-logo-grid__container">
            <div class="fau-logo-grid__item">
                <a href="#" class="fau-logo-grid__link">
                    <img src="<?php echo esc_url(get_theme_file_uri('assets/images/logo-1.png')); ?>" alt="" class="fau-logo-grid__image" loading="lazy" />
                </a>
            </div>
            <div class="fau-logo-grid__item">
                <a href="#" class="fau-logo-grid__link">
                    <img src="<?php echo esc_url(get_theme_file_uri('assets/images/logo-2.png')); ?>" alt="" class="fau-logo-grid__image" loading="lazy" />
                </a>
            </div>
            <div class="fau-logo-grid__item">
                <a href="#" class="fau-logo-grid__link">
                    <img src="<?php echo esc_url(get_theme_file_uri('assets/images/logo-3.png')); ?>" alt="" class="fau-logo-grid__image" loading="lazy" />
                </a>
            </div>
            <div class="fau-logo-grid__item">
                <a href="#" class="fau-logo-grid__link">
                    <img src="<?php echo esc_url(get_theme_file_uri('assets/images/logo-4.png')); ?>" alt="" class="fau-logo-grid__image" loading="lazy" />
                </a>
            </div>
            <div class="fau-logo-grid__item">
                <a href="#" class="fau-logo-grid__link">
                    <img src="<?php echo esc_url(get_theme_file_uri('assets/images/logo-5.png')); ?>" alt="" class="fau-logo-grid__image" loading="lazy" />
                </a>
            </div>
            <div class="fau-logo-grid__item">
                <a href="#" class="fau-logo-grid__link">
                    <img src="<?php echo esc_url(get_theme_file_uri('assets/images/logo-6.png')); ?>" alt="" class="fau-logo-grid__image" loading="lazy" />
                </a>
            </div>
            <div class="fau-logo-grid__item">
                <a href="#" class="fau-logo-grid__link">
                    <img src="<?php echo esc_url(get_theme_file_uri('assets/images/logo-7.png')); ?>" alt="" class="fau-logo-grid__image" loading="lazy" />
                </a>
            </div>
            <div class="fau-logo-grid__item">
                <a href="#" class="fau-logo-grid__link">
                    <img src="<?php echo esc_url(get_theme_file_uri('assets/images/logo-8.png')); ?>" alt="" class="fau-logo-grid__image" loading="lazy" />
                </a>
            </div>
        </div>
    </div>
    <!-- /wp:fau-elemental/logo-grid -->
</div>
<!-- /wp:group -->
