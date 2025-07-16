<?php
/**
 * * Title: Hero: Chair
 * Slug: fau-elemental/hero-chair
 * Categories: hero, fau-elemental
 * Website Types: chair
 * Viewport Width: 1376
 * Block Types: core/post-content
 * Post Types: page
 * Description: Hero pattern for chair websites
 */

?>
<!-- wp:columns {"templateLock":"all", "className": "hero-chair"} -->
<div class="wp-block-columns hero-chair">
    <!-- wp:column {"layout":{"type":"constrained"},"className":"hero-faculty-left"} -->
    <div class="wp-block-column">

        <!-- wp:heading {"level":2,"className":"hero-front-page-title"} -->
        <h2 class="wp-block-heading hero-front-page-title">Institut für Politische Wissenschaft</h2>
        <!-- /wp:heading -->

         <!-- wp:group {"className":"hero-mobile-optional"} -->
         <div class="wp-block-group hero-mobile-optional">
            <!-- wp:paragraph {"className":"hero-text"} -->
            <p class="hero-text">Die Naturwissenschaftliche Fakultät der FAU gehört zu den forschungsstärksten naturwissenschaftlichen Fakultäten in Deutschland. Ihre Fachbereiche belegen regelmäßig vorderste Plätze in unterschiedlichsten Rankings.</p>
            <!-- /wp:paragraph -->

            <!-- wp:buttons {"className":"hero-link-wrapper"} -->
            <div class="wp-block-buttons hero-link-wrapper">
                 <!-- wp:button {"className":"hero-link"} -->
                 <div class="wp-block-button hero-link">
                     <a class="wp-block-button__link" href="#">Mehr erfahren</a>
                 </div>
                 <!-- /wp:button -->
             </div>
             <!-- /wp:buttons -->
        </div>
        <!-- /wp:group -->
    </div>
    <!-- /wp:column -->

    <!-- wp:column  -->
    <div class="wp-block-column" >
        <!-- wp:cover {"url":"<?php echo esc_url(get_theme_file_uri('assets/images/hero-chair.png')); ?>","dimRatio":0,"contentPosition":"center","className":"is-dark-theme"} -->
        <div class="wp-block-cover is-dark-theme">
            <span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span>
            <img class="wp-block-cover__image-background" alt="" src="<?php echo esc_url(get_theme_file_uri('assets/images/hero-chair.png')); ?>" data-object-fit="cover">
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