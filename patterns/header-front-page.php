<?php

/**
 * Title: Header Front Page
 * Slug: fau-elemental/header-front-page
 * Categories: header
 * Block Types: core/template-part/header
 * Description: A header pattern for the front page.
 */
?>

<!-- wp:group {"align":"full","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull">
    <!-- wp:group {"layout":{"type":"constrained"}} -->
    <div class="wp-block-group">
        <!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|30"}}},"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
        <div
            class="wp-block-group alignwide"
            style="
        padding-top: var(--wp--preset--spacing--30);
        padding-bottom: var(--wp--preset--spacing--30);
      ">
            <!-- wp:site-logo {"width":306,"shouldSyncIcon":true} /-->
            <!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|10"}},"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"right"}} -->
            <div class="wp-block-group">
                <!-- wp:navigation {"overlayBackgroundColor":"base","overlayTextColor":"contrast","layout":{"type":"flex","justifyContent":"right","flexWrap":"wrap"}} /-->
            </div>
            <!-- /wp:group -->
        </div>
        <!-- /wp:group -->
    </div>
    <!-- /wp:group -->
</div>
<!-- /wp:group -->