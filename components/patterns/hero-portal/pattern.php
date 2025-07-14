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
    <div class="wp-block-column" >
        <!-- wp:post-title {"level":1} /-->
        <!-- wp:paragraph {"className":"post-description"} -->
        <p class="post-description">Sie interessieren sich für ein naturwissenschaftliches oder mathematisches Studium? Dann sind Sie an der FAU in Erlangen genau richtig. Die Universität Erlangen-Nürnberg bietet Studiengänge aus allen Bereichen der Naturwissenschaften und Mathematik an – sowohl als grundständige Bachelorstudiengänge als auch weiterführende Masterstudiengänge. Die Studiengänge für Lebensmittelchemie und Pharmazie sowie für das Lehramt schließen mit einem Staatsexamen ab.</p>
        <!-- /wp:paragraph -->

        <!-- wp:buttons -->
        <div class="wp-block-buttons">
             <!-- wp:button {"className":"is-style-tertiary"} -->
             <div class="wp-block-button is-style-tertiary"><a class="wp-block-button__link wp-element-button">Mehr erfahren</a></div>
            <!-- /wp:button -->
        </div>
        <!-- /wp:buttons -->
    </div>
    <!-- /wp:column -->

    <!-- wp:column  -->
    <div class="wp-block-column" >
        <!-- wp:cover {"url":"<?php echo esc_url(get_theme_file_uri('assets/images/hero-portal.png')); ?>","dimRatio":0,"contentPosition":"center","className":"is-dark-theme"} -->
        <div class="wp-block-cover is-dark-theme">
            <span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span>
            <img class="wp-block-cover__image-background" alt="" src="<?php echo esc_url(get_theme_file_uri('assets/images/hero-portal.png')); ?>" data-object-fit="cover">
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
