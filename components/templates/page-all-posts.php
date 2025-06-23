<?php
/**
 * Template Name: All Posts
 * 
 * A template for displaying all posts with filtering and search functionality.
 * 
 * @package FAU-Elemental
 */

get_header(); ?>

<main class="wp-block-group" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);">
    
    <!-- Page Header -->
    <div class="wp-block-group" style="margin-bottom:var(--wp--preset--spacing--40);">
        <h1 class="wp-block-heading has-primary-color has-text-color" style="font-size:2.5rem;font-weight:700;">
            <?php echo esc_html(get_the_title()); ?>
        </h1>
        
        <p class="has-secondary-color has-text-color" style="font-size:1.1rem;">
            <?php 
            $page_description = get_post_meta(get_the_ID(), 'page_description', true);
            if (empty($page_description)) {
                $page_description = __('Browse and filter through all our posts using the options below.', 'fau-elemental');
            }
            echo esc_html($page_description);
            ?>
        </p>
    </div>

    <!-- List Filters Block -->
    <?php
    $filter_block_id = 'fau-list-filters-all-posts-page';
    echo do_blocks('<!-- wp:fau-elemental/fau-list-filters {"enableSearch":true,"searchPlaceholder":"Search posts...","enableFilters":true,"filterFields":[],"showMoreFiltersButton":true,"enableViewSwitcher":true,"availableViews":["cards","table"],"defaultView":"cards","enableSorting":true,"sortOptions":[{"value":"date","label":"Latest First"},{"value":"title","label":"Alphabetical"},{"value":"modified","label":"Recently Updated"}],"defaultSort":"date","showResultsCount":true,"resultsPerPage":50,"gridWidth":"12","customBlockId":"' . $filter_block_id . '"} /-->');
    ?>

    <!-- Spacer -->
    <div style="height:2rem" aria-hidden="true" class="wp-block-spacer"></div>

    <!-- Teaser Grid Block -->
    <?php
    echo do_blocks('<!-- wp:fau-elemental/fau-teaser-grid {"variant":"post","selectionMode":"auto","displayStyle":"teaser-grid","teaserLayout":"3m","postsPerPage":-1,"selectedCategory":0,"orderBy":"date","order":"DESC","headingLevel":"h3","showLoadMore":false,"filterBlockId":"' . $filter_block_id . '"} /-->');
    ?>

</main>

<?php 
// Enqueue template-specific styles
wp_enqueue_style(
    'fau-all-posts-template',
    get_theme_file_uri('assets/css/templates/page-all-posts.css'),
    [],
    wp_get_theme()->get('Version')
);

get_footer(); ?> 