<?php

/**
 * Title: Hero: Portal
 * Slug: fau-elemental/hero-portal
 * Categories: hero, fau-elemental
 * Viewport Width: 1376
 * Block Types: core/post-content
 * Post Types: page
 */
?>

<!-- wp:columns {"align":"wide","templateLock":"all", "className": "hero-portal"} -->
<div class="wp-block-columns alignwide hero-portal">
    <!-- wp:column {"templateLock":"contentOnly"} -->
    <div class="wp-block-column">
        <!-- wp:post-title {"level":1, "lock":{"move":true}} /-->
        <!-- wp:paragraph {"className":"post-description", "lock":{"move":true}} -->
        <p class="post-description"><?php echo esc_html__('Are you interested in studying natural sciences or mathematics? Then FAU in Erlangen is the right place for you. Friedrich-Alexander-Universität Erlangen-Nürnberg offers degree programs in all areas of natural sciences and mathematics – both as undergraduate Bachelor’s and advanced Master’s programs. The degree programs in food chemistry, pharmacy, and teaching end with a state examination.', 'fau-elemental'); ?></p>
        <!-- /wp:paragraph -->

        <!-- wp:buttons -->
        <div class="wp-block-buttons">
            <!-- wp:button {"className":"is-style-tertiary", "lock":{"remove":false}} -->
            <div class="wp-block-button is-style-tertiary">
                <a class="wp-block-button__link wp-element-button"><?php echo esc_html__('Learn more', 'fau-elemental'); ?></a>
            </div>
            <!-- /wp:button -->
        </div>
        <!-- /wp:buttons -->
    </div>
    <!-- /wp:column -->

    <!-- wp:column  -->
    <div class="wp-block-column">
        <!-- wp:cover {"url":"<?php echo esc_url(get_theme_file_uri('assets/images/hero-portal.png')); ?>","dimRatio":0,"contentPosition":"center","className":"is-dark-theme"} -->
        <div class="wp-block-cover is-dark-theme">
            <span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span>
            <img class="wp-block-cover__image-background" alt="<?php echo esc_attr__('Students at FAU Erlangen-Nürnberg', 'fau-elemental'); ?>" src="<?php echo esc_url(get_theme_file_uri('assets/images/hero-portal.png')); ?>" data-object-fit="cover">
            <div class="wp-block-cover__inner-container">
                <!-- wp:paragraph {"align":"center","placeholder":"Write title…","fontSize":"large","className":"hideParagraph"} -->
                <p class="has-text-align-center has-large-font-size hideParagraph"></p>
                <!-- /wp:paragraph -->
            </div>
        </div>
        <!-- /wp:cover -->
    </div>
    <!-- /wp:column -->
</div>
<!-- /wp:columns -->
 