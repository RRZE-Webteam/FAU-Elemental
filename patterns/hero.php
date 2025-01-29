<?php

/**
 * Title: Hero
 * Slug: fau-elemental/hero
 * Categories: fau-elemental/FAU
 */

$buttons = array(
    __('Button A', 'themeslug'),
    __('Button B', 'themeslug')
);

?>
<!-- wp:cover {"overlayColor":"contrast","isUserOverlayColor":true,"align":"full"} -->
<div class="wp-block-cover alignfull">
    <span aria-hidden="true" class="wp-block-cover__background has-contrast-background-color has-background-dim-100 has-background-dim"></span>
    <div class="wp-block-cover__inner-container">

        <!-- wp:heading {"textAlign":"center"} -->
        <h2 class="wp-block-heading has-text-align-center"><?php esc_html_e('Welcome to My Site', 'fau-elemental'); ?></h2>
        <!-- /wp:heading -->

        <!-- wp:paragraph {"align":"center"} -->
        <p class="has-text-align-center"><?php esc_html_e('This is my little home away from home.', 'fau-elemental'); ?></p>
        <!-- /wp:paragraph -->

        <!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
        <div class="wp-block-buttons">
            <?php foreach ($buttons as $button) : ?>
                <!-- wp:button {"className":"wp-block-button is-style-outline"} -->
                <div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button"><?php echo esc_html($button); ?></a></div>
                <!-- /wp:button -->
            <?php endforeach; ?>
        </div>
        <!-- /wp:buttons -->

    </div>
</div>
<!-- /wp:cover -->