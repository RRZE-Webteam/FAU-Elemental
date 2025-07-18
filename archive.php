<?php
/**
 * The template for displaying archive pages with filtering and pagination
 * @package Fau-Elemental
 */

get_header(); ?>

<main class="wp-block-group archive-page">
    <header class="archive-header">
        <h1 class="archive-title">
            <?php 
            if (is_category()) {
                echo esc_html(single_cat_title('', false));
            } elseif (is_tag()) {
                echo esc_html(single_tag_title('', false));
            } elseif (is_author()) {
                echo esc_html(get_the_author());
            } elseif (is_date()) {
                if (is_year()) {
                    echo esc_html(get_the_date('Y'));
                } elseif (is_month()) {
                    echo esc_html(get_the_date('F Y'));
                } elseif (is_day()) {
                    echo esc_html(get_the_date());
                }
            } else {
                the_archive_title();
            }
            ?>
        </h1>
    </header>

    <?php if (is_category() && category_description()) : ?>
        <div class="is-layout-flow">
            <div class="category-description">
                <?php echo wp_kses_post(category_description()); ?>
            </div>
        </div>
    <?php elseif (is_tag() && tag_description()) : ?>
        <div class="is-layout-flow">
            <div class="tag-description">
                <?php echo wp_kses_post(tag_description()); ?>
            </div>
        </div>
    <?php endif; ?>

    <?php
    // Get pagination variables
    $current_page = max(1, get_query_var('paged', 1));
    $pagination_type = faue_get_pagination_type();
    $items_per_page = faue_get_items_per_page();
    ?>

    <section class="content-grid" aria-label="<?php esc_attr_e('Posts listing', 'fau-elemental'); ?>">
        <?php
        // Build filter parameters for teaser grid based on archive type
        $filter_params = '';
        if (is_category()) {
            $filter_params = ',"selectedCategory":' . get_queried_object_id();
        } elseif (is_tag()) {
            $filter_params = ',"selectedTags":[' . get_queried_object_id() . ']';
        } elseif (is_author()) {
            $filter_params = ',"selectedAuthor":' . get_queried_object_id();
        }
        
        echo do_blocks('<!-- wp:fau-elemental/fau-teaser-grid {"variant":"post","selectionMode":"auto","displayStyle":"teaser-grid","teaserLayout":"3m","postsPerPage":' . $items_per_page . ',"orderBy":"date","order":"DESC","headingLevel":"h2","showPagination":true,"paginationType":"' . $pagination_type . '","currentPage":' . $current_page . $filter_params . '} /-->');
        ?>
    </section>
</main>

<?php get_footer(); ?>
