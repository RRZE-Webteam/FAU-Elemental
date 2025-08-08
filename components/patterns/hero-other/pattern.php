<?php
/**
 * * Title: Hero: Other
 * Slug: fau-elemental/hero-other
 * Categories: hero, fau-elemental
 * Website Types: other
 * Viewport Width: 1376
 * Block Types: core/post-content
 * Post Types: page
 * Description: Hero pattern for other websites
 */

?>
<!-- wp:columns {"templateLock":"all", "className": "hero-other"} -->
 <div class="wp-block-columns hero-other-bg hero-other ">
    <!-- wp:column {"layout":{"type":"constrained"}} -->
    <div class="wp-block-column">

        <!-- wp:heading {"level":2,"className":"hero-front-page-title"} -->
        <h2 class="wp-block-heading hero-front-page-title">Lorem ipsum dolor sit amet consectetur. Laoreet sit erat aenean tellus in. Odi</h2>
        <!-- /wp:heading -->

        <!-- wp:group {"className":"hero-mobile-optional"} -->
        <div class="wp-block-group hero-mobile-optional">
            <!-- wp:paragraph {"className":"hero-text"} -->
            <p class="hero-text">Die Naturwissenschaftliche Fakultät der FAU gehört zu den forschungsstärksten naturwissenschaftlichen Fakultäten in Deutschland. Ihre Fachbereiche belegen regelmäßig vorderste Plätze in unterschiedlichsten Rankings.</p>
            <!-- /wp:paragraph -->
             
            <!-- wp:buttons -->
            <div class="wp-block-buttons">
                 <!-- wp:button {"className":"is-style-tertiary"} -->
                 <div class="wp-block-button is-style-tertiary">
                    <a class="wp-block-button__link wp-element-button">Mehr erfahren</a>
                </div>
                <!-- /wp:button -->
            </div>
            <!-- /wp:buttons -->
        </div>
        <!-- /wp:group -->
    </div>
    <!-- /wp:column -->

         <!-- wp:column -->
         <div class="wp-block-column">
        <!-- wp:cover {"url":"<?php echo esc_url(get_theme_file_uri('assets/images/hero-faculty-tf.png')); ?>","id":99,"dimRatio":0,"isUserOverlayColor":false,"layout":{"type":"constrained"}} -->
        <div class="wp-block-cover">
            <span aria-hidden="true" class="wp-block-cover__background  has-background-dim-0 has-background-dim"></span>
            <img class="wp-block-cover__image-background wp-image-99" alt="" src="<?php echo esc_url(get_theme_file_uri('assets/images/hero-faculty-tf.png')); ?>" data-object-fit="cover" />
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