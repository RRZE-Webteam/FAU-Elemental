<?php
/**
 * Title: Big Button Section
 * Slug: fau-elemental/big-button-section
 * Categories: fau-elemental
 * Viewport Width: 1200
 * Block Types: core/post-content
 * Post Types: page
 * Description: A section with meta headline, main heading, teaser text, and big button group
 * Keywords: buttons, section, headline, teaser
 * Inserter: true
 */

/**
 * Big Button Section Pattern
 *
 * This pattern creates a complete section with:
 * - Meta headline (small text above main heading)
 * - Main heading
 * - Teaser text/description
 * - FAU Big Button group
 *
 * @package FAU-Elemental
 */
?>

<!-- wp:group {"className":"big-button-section","style":{"spacing":{"padding":{"top":"var:preset|spacing|large","bottom":"var:preset|spacing|large"}}}} -->
<div class="wp-block-group big-button-section" style="padding-top:var(--wp--preset--spacing--large);padding-bottom:var(--wp--preset--spacing--large)">
    
    <!-- wp:fau-elemental/fau-meta-headline -->
    <header class="wp-block-fau-elemental-fau-meta-headline">
        <p>Fakultäten</p>
    </header>
    <!-- /wp:fau-elemental/fau-meta-headline -->

    <!-- wp:heading {"level":1,"align":"left"} -->
    <h1 class="wp-block-heading has-text-align-left">Headline Fakultäten</h1>
    <!-- /wp:heading -->

    <!-- wp:paragraph {"align":"left"} -->
    <p class="has-text-align-left">Magna neque purus eget hendrerit cras quam aliquet. Cursus diam ultricies nunc consectetur neque ultrices maecenas. Pellentesque dictum sem dolor sed laoreet vehicula arcu etiam elit. Faucibus ut.</p>
    <!-- /wp:paragraph -->

    <!-- wp:fau-elemental/fau-big-button {"teaserSize":"small","variant":"filled","items":[{"id":1,"title":"First Button","description":"Description for the first button that explains what this link leads to.","url":"#","facultyColor":"phil"},{"id":2,"title":"Second Button","description":"Description for the second button with additional context about the destination.","url":"#","facultyColor":"med"},{"id":3,"title":"Third Button","description":"Description for the third button providing more information about this option.","url":"#","facultyColor":"nat"},{"id":1750759900258,"title":"Fourth Button","description":"Description for the fourth button providing more information about this option.","url":"#","facultyColor":"rw"}]} /-->

</div>
<!-- /wp:group --> 