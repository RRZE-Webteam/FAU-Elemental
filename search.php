<?php
/**
 * Search Results Template
 *
 * @package FAU-Elemental
 */

global $wp_query;

get_header();
?>

<main id="main" class="search-results-page" role="main">
    <?php get_template_part('components/template-parts/search-results/search-results'); ?>
</main>

<?php get_footer(); ?> 
