<?php

/**
 * Title: Big Teaser: Feature
 * Slug: fau-elemental/big-teaser-feature
 * Categories: fau-elemental
 * Description: A featured content teaser with headline, text, link and image.
 * Block Types: core/post-content
 * Post Types: post, page
 * Viewport Width: 1376
 */

?>

<!-- wp:fau-elemental/fau-meta-headline {"headline":"Forschung an der FAU","id":"featured-content"} -->
<div class="wp-block-fau-elemental-fau-meta-headline" id="headline-featured-content">Forschung an der FAU</div>
<!-- /wp:fau-elemental/fau-meta-headline -->

<!-- wp:spacer {"height":"40px"} -->
<div style="height:40px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:fau-elemental/fau-big-teaser {"headline":"Internationale Spitzenforschung an der FAU","teaserText":"Die FAU ist eine Volluniversität mit langer Forschungstradition und gefestigte Kooperationen mit internationalen Forschungseinrichtungen wie Max Planck, Fraunhofer und Helmholtz.","linkText":"Mehr erfahren","linkUrl":"#","image":{"id":0,"url":"<?php echo esc_url(get_theme_file_uri('assets/images/hero-fau.jpg')); ?>","alt":"FAU Campus"}} -->
<section class="wp-block-fau-elemental-fau-big-teaser fau-big-teaser">
    <div class="fau-big-teaser__image">
        <img src="<?php echo esc_url(get_theme_file_uri('assets/images/hero-fau.jpg')); ?>" alt="FAU Campus" loading="lazy" />
    </div>
    <div class="fau-big-teaser__content">
        <h3 class="fau-big-teaser__headline">Internationale Spitzenforschung an der FAU</h3>
        <p class="fau-big-teaser__teaser-text"> Die FAU ist eine Volluniversität mit langer Forschungstradition und gefestigte Kooperationen mit internationalen Forschungseinrichtungen wie Max Planck, Fraunhofer und Helmholtz.</p>
        <div class="wp-block-button is-style-tertiary">
            <a href="#" class="wp-block-button__link wp-element-button">Mehr erfahren</a>
        </div>
    </div>
</section>
<!-- /wp:fau-elemental/fau-big-teaser --> 