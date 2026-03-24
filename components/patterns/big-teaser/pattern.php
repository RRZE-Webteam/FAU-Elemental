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
    <!-- wp:fau-elemental/fau-meta-headline {"headline":"Abschnittsüberschrift","id":"","lock":{"move":true}} -->
    <h2 class="wp-block-fau-elemental-fau-meta-headline" id="headline-">Abschnittsüberschrift</h2>
    <!-- /wp:fau-elemental/fau-meta-headline -->

    <!-- wp:fau-elemental/fau-big-teaser {"headline":"Überschrift","teaserText":"Kurzbeschreibung. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.","linkText":"Button Text","linkUrl":"#","image":{"id":1287,"url":"<?php echo esc_url(get_theme_file_uri('assets/images/faue-demo-1.webp')); ?>","alt":"Demo Content"}} -->
    <!-- /wp:fau-elemental/fau-big-teaser -->
</div>
<!-- /wp:group -->