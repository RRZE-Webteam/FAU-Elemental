<?php

/**
 * Title: Header
 * Slug: fau-elemental/header
 * Categories: header
 * Block Types: core/template-part/header
 * Description: A header pattern for the website.
 */
?>

<!-- wp:group {"align":"full","style":{"backgroundColor":"var:preset|color|theme-1000"},"layout":{"type":"default"}} -->
<div class="wp-block-group alignfull has-theme-1000-background-color has-background">
    <!-- wp:group {"layout":{"type":"constrained"}} -->
    <div class="wp-block-group">
        <!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|30"}}},"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
        <div
            class="wp-block-group alignwide"
            style="
        padding-top: var(--wp--preset--spacing--30);
        padding-bottom: var(--wp--preset--spacing--30);
      ">
            <!-- wp:group {"className":"site-logo-container"} -->
            <div class="wp-block-group site-logo-container">
                <?php fau_elemental_display_logo_title(); ?>
            </div>
            <!-- /wp:group -->

            <!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|10"}},"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"right"}} -->
            <div class="wp-block-group">
                <!-- wp:navigation {"textColor":"base","overlayBackgroundColor":"base","overlayTextColor":"contrast","layout":{"type":"flex","justifyContent":"right","flexWrap":"wrap"}} /-->
            </div>
            <!-- /wp:group -->
        </div>
        <!-- /wp:group -->
    </div>
    <!-- /wp:group -->
</div>
<!-- /wp:group -->