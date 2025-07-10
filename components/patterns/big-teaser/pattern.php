<?php

/**
 * Title: Big Teaser
 * Slug: fau-elemental/big-teaser
 * Categories: fau-elemental
 * Description: A featured content teaser with headline, text, link and image.
 * Block Types: core/post-content
 * Post Types: post, page
 * Viewport Width: 1376
 */

?>

<!-- wp:group {"className":"pattern-big-teaser"} -->
<div class="wp-block-group pattern-big-teaser">
    <!-- wp:fau-elemental/fau-meta-headline {"headline":"Forschung an der FAU","id":""} -->
    <div class="wp-block-fau-elemental-fau-meta-headline" id="headline-">Forschung an der FAU</div>
    <!-- /wp:fau-elemental/fau-meta-headline -->

    <!-- wp:fau-elemental/fau-big-teaser {"headline":"Internationale Spitzenforschung an der FAU","teaserText":"Die FAU bietet Ihnen mit über 270 Studiengängen eine inspirierende Lernumgebung, studentische Gemeinschaft und zahlreiche Möglichkeiten, Ihre Leidenschaft zu entdecken.","linkText":"Mehr erfahren","linkUrl":"#","image":{"id":1287,"url":"<?php echo esc_url(get_theme_file_uri('assets/images/faue-demo-1.jpg')); ?>","alt":"Demo Content"}} -->
    <section class="wp-block-fau-elemental-fau-big-teaser fau-big-teaser">
        <div class="fau-big-teaser__content">
            <h3 class="fau-big-teaser__headline">Internationale Spitzenforschung an der FAU</h3>
            <p class="fau-big-teaser__teaser-text">Die FAU bietet Ihnen mit über 270 Studiengängen eine inspirierende Lernumgebung, studentische Gemeinschaft und zahlreiche Möglichkeiten, Ihre Leidenschaft zu entdecken.</p>
            <div class="wp-block-buttons is-layout-flex wp-block-buttons-is-layout-flex">
                <div class="wp-block-button is-style-tertiary"><a href="#" class="wp-block-button__link wp-element-button">Mehr erfahren</a></div>
            </div>
        </div>
        <div class="fau-big-teaser__image"><img src="<?php echo esc_url(get_theme_file_uri('assets/images/faue-demo-1.jpg')); ?>" alt="Demo Content" loading="lazy" /></div>
    </section>
    <!-- /wp:fau-elemental/fau-big-teaser -->
</div>
<!-- /wp:group -->