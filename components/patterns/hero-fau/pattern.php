<?php

/**
 * Title: Hero: FAU
 * Slug: fau-elemental/hero-fau
 * Categories: hero, fau-elemental
 * Viewport Width: 1376
 * Block Types: core/post-content
 * Post Types: page
 */

$show_text_mobile = get_theme_mod('hero_show_text_mobile', true);
$no_text_class = $show_text_mobile ? '' : 'no-text-and-link';
?>

<!-- wp:group {"templateLock":"all", "className":"hero-fau"} -->
<div class="wp-block-group hero-fau">
    <!-- wp:cover {"templateLock":"all","url":"<?php echo esc_url(get_theme_file_uri('assets/images/hero-fau.jpg')); ?>","id":99,"dimRatio":50,"isUserOverlayColor":false} -->
    <div class="wp-block-cover <?php echo esc_attr($no_text_class); ?>">
        <span aria-hidden="true" class="wp-block-cover__background has-background-dim"></span>
        <img class="wp-block-cover__image-background wp-image-99" alt="" src="<?php echo esc_url(get_theme_file_uri('assets/images/hero-fau.jpg')); ?>" data-object-fit="cover">
        <div class="wp-block-cover__inner-container">
            <!-- wp:group {"templateLock":"all","className":"hero-content is-style-dark"} -->
            <div class="wp-block-group hero-content is-style-dark">
                <!-- wp:heading {"level":1,"className":"hero-front-page-title"} -->
                <h1 class="wp-block-heading hero-front-page-title">Wir bewegen Wissen</h1>
                <!-- /wp:heading -->
                <!-- wp:group {"templateLock":"all","className":"hero-mobile-optional"} -->
                <div class="wp-block-group hero-mobile-optional is-style-dark">
                    <!-- wp:paragraph {"className":"hero-text"} -->
                    <p class="hero-text">Die FAU bietet Ihnen mit über 270 Studiengängen eine inspirierende Lernumgebung, studentische Gemeinschaft und zahlreiche Möglichkeiten, Ihre Leidenschaft zu entdecken.</p>
                    <!-- /wp:paragraph -->
                    <!-- wp:buttons -->
                    <div class="wp-block-buttons">
                        <!-- wp:button {"className":"is-style-tertiary"} -->
                        <div class="wp-block-button is-style-tertiary"><a class="wp-block-button__link wp-element-button ">Mehr erfahren</a></div>
                        <!-- /wp:button -->
                    </div>
                    <!-- /wp:buttons -->
                </div>
                <!-- /wp:group -->
            </div>
            <!-- /wp:group -->
        </div>
    </div>
    <!-- /wp:cover -->
</div>
<!-- /wp:group -->