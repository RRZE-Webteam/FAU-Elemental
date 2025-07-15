<?php

/**
 * Title: Facts Grid with Headline
 * Slug: fau-elemental/facts-grid-with-header
 * Categories: fau-elemental
 * Viewport Width: 1376
 * Block Types: core/post-content
 * Post Types: page
 * Description: Facts grid with 4 facts and a headline
 */

?>

<!-- wp:group {"className":"facts-grid-section"} -->
<div class="wp-block-group facts-grid-section">
    <!-- wp:fau-elemental/fau-meta-headline {"headline":"Dachzeile","id":""} -->
    <div class="wp-block-fau-elemental-fau-meta-headline" id="headline-">Dachzeile</div>
    <!-- /wp:fau-elemental/fau-meta-headline -->
    
    <!-- wp:fau-elemental/fau-facts-grid {"facts":[{"text":"<strong>270+</strong><br>Studiengänge","iconUrl":"","iconId":null,"link":""},{"text":"<strong>40.000+</strong><br>Studierende","iconUrl":"","iconId":null,"link":""},{"text":"<strong>5</strong><br>Fakultäten","iconUrl":"","iconId":null,"link":""},{"text":"<strong>1743</strong><br>Gegründet","iconUrl":"","iconId":null,"link":""}]} -->
    <div class="wp-block-fau-elemental-fau-facts-grid"><div class="fau-facts-grid"><div class="fau-facts-grid-items"><div class="fau-facts-grid-item"><div class="fau-facts-grid-item-icon"><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/fact-icon.png' ); ?>" alt=""></div><div class="fau-facts-grid-item-content"><div class="fau-facts-grid-item-text"><strong>270+</strong><br>Studiengänge</div></div></div><div class="fau-facts-grid-item"><div class="fau-facts-grid-item-icon"><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/fact-icon.png' ); ?>" alt=""></div><div class="fau-facts-grid-item-content"><div class="fau-facts-grid-item-text"><strong>40.000+</strong><br>Studierende</div></div></div><div class="fau-facts-grid-item"><div class="fau-facts-grid-item-icon"><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/fact-icon.png' ); ?>" alt=""></div><div class="fau-facts-grid-item-content"><div class="fau-facts-grid-item-text"><strong>5</strong><br>Fakultäten</div></div></div><div class="fau-facts-grid-item"><div class="fau-facts-grid-item-icon"><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/fact-icon.png' ); ?>" alt=""></div><div class="fau-facts-grid-item-content"><div class="fau-facts-grid-item-text"><strong>1743</strong><br>Gegründet</div></div></div></div></div></div>
    <!-- /wp:fau-elemental/fau-facts-grid -->
</div>
<!-- /wp:group --> 