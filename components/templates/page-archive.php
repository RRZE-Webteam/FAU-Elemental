<?php
/**
 * Template Name: Page Archive
 * 
 * A template for displaying all pages with filtering and search functionality.
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
                $page_description = __('Browse and search through all pages on our website.', 'fau-elemental');
            }
            echo esc_html($page_description);
            ?>
        </p>
    </div>

    <!-- List Filters Block -->
    <?php
    $filter_block_id = 'fau-list-filters-page-archive';
    echo do_blocks('<!-- wp:fau-elemental/fau-list-filters {"enableSearch":true,"searchPlaceholder":"Search pages...","enableFilters":true,"filterFields":[],"showMoreFiltersButton":true,"enableViewSwitcher":true,"availableViews":["cards","table"],"defaultView":"cards","enableSorting":true,"sortOptions":[{"value":"date","label":"Latest First"},{"value":"title","label":"Alphabetical"},{"value":"modified","label":"Recently Updated"}],"defaultSort":"title","showResultsCount":true,"resultsPerPage":50,"gridWidth":"12","customBlockId":"' . $filter_block_id . '"} /-->');
    ?>

    <!-- Spacer -->
    <div style="height:2rem" aria-hidden="true" class="wp-block-spacer"></div>

    <!-- Teaser Grid Block for Pages -->
    <?php
    echo do_blocks('<!-- wp:fau-elemental/fau-teaser-grid {"variant":"page","selectionMode":"auto","displayStyle":"teaser-grid","teaserLayout":"3m","postsPerPage":-1,"selectedCategory":0,"orderBy":"title","order":"ASC","headingLevel":"h3","showLoadMore":false,"filterBlockId":"' . $filter_block_id . '"} /-->');
    ?>

</main>

<?php get_footer(); ?> 