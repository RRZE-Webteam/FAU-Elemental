<?php
/**
 * Title: Featured event teaser
 * Slug: fau-elemental/featured-event-teaser
 * Categories: fau-elemental
 * Description: A section with meta headline and featured event teaser
 * Block Types: core/post-content
 * Post Types: post, page
 */

// Get current date in the format expected by the block (DD MM YYYY)
$current_date = date('j n Y'); // j = day without leading zero, n = month without leading zero, Y = full year

?>
<!-- wp:group {"layout":{"type":"constrained"},"className":"featured-event-teaser"} -->
<div class="wp-block-group featured-event-teaser"><!-- wp:fau-elemental/fau-meta-headline {"headline":"Optionale Dachzeile","id":""} -->
<div class="wp-block-fau-elemental-fau-meta-headline" id="headline-">Optionale Dachzeile</div>
<!-- /wp:fau-elemental/fau-meta-headline -->

<!-- wp:fau-elemental/featured-event-teaser {"eventTitle":"Hier steht ein Veranstaltungstitel der eine variable länge bietet","eventDescription":"Lorem ipsum dolor sit amet consectetur. Phasellus ut dignissim odio amet scelerisque in tortor faucibus. Tellus massa integer aenean molestie vitae quis egestas integer. Imperdiet lorem porttitor vitae integer risus. Facilisi blandit turpis vestibulum iaculis scelerisque fusce vitae egestas. Arcu et sit lobortis tincidunt felis.","eventDate":"<?php echo $current_date; ?>","buttonText":"Mehr erfahren","buttonUrl":"#"} -->
<!-- /wp:fau-elemental/featured-event-teaser --></div>
<!-- /wp:group -->