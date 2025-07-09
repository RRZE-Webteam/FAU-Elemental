<?php
/**
 * The main template file - Enhanced for post archives
 *
 * @package Fau-Elemental
 */

add_action('template_redirect', function() {
    if (!is_home() && !is_archive()) {
        return;
    }
    
    $current_page = max(1, get_query_var('paged', 1));
    
    $posts_per_page = 6;
    $query_args = [
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids'
    ];
    
    $count_query = new WP_Query($query_args);
    $total_posts = $count_query->found_posts; 
    wp_reset_postdata();
    $total_pages = max(1, ceil($total_posts / $posts_per_page));
    
    if ($current_page > $total_pages && $total_pages > 0) {
        wp_safe_redirect(home_url('/page/' . $total_pages . '/'));
        exit;
    }
});

get_header(); ?>

<main class="wp-block-group blog-homepage">
    <header class="blog-header">
        <h1 class="blog-title">
            <?php 
            if (is_home()) {
                $blog_title = get_bloginfo('name');
                $page_title = get_theme_mod('blog_homepage_title', $blog_title);
                echo esc_html($page_title);
            } elseif (is_category()) {
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
                echo esc_html__('Blog', 'fau-elemental');
            }
            ?>
        </h1>
        
        <p class="blog-description">
            <?php 
            if (is_home()) {
                // Blog homepage
                $blog_description = get_bloginfo('description');
                $description = get_theme_mod('blog_homepage_description', $blog_description);
                if (empty($description)) {
                    $description = __('Welcome to our blog. Browse and filter through all our posts using the options below.', 'fau-elemental');
                }
            } elseif (is_category()) {
                $description = category_description();
                if (empty($description)) {
                    $description = sprintf(__('Browse all posts in the %s category. Use the filters below to refine your search.', 'fau-elemental'), single_cat_title('', false));
                }
            } elseif (is_tag()) {
                $description = tag_description();
                if (empty($description)) {
                    $description = sprintf(__('Browse all posts tagged with %s. Use the filters below to refine your search.', 'fau-elemental'), single_tag_title('', false));
                }
            } elseif (is_author()) {
                $description = get_the_author_meta('description');
                if (empty($description)) {
                    $description = sprintf(__('Browse all posts by %s. Use the filters below to refine your search.', 'fau-elemental'), get_the_author());
                }
            } elseif (is_date()) {
                if (is_year()) {
                    $description = sprintf(__('Browse all posts from %s. Use the filters below to refine your search.', 'fau-elemental'), get_the_date('Y'));
                } elseif (is_month()) {
                    $description = sprintf(__('Browse all posts from %s. Use the filters below to refine your search.', 'fau-elemental'), get_the_date('F Y'));
                } elseif (is_day()) {
                    $description = sprintf(__('Browse all posts from %s. Use the filters below to refine your search.', 'fau-elemental'), get_the_date());
                }
            } else {
                $description = __('Browse and filter through all our posts using the options below. Use pagination to navigate through multiple pages.', 'fau-elemental');
            }
            echo wp_kses_post($description);
            ?>
        </p>
    </header>

    <section class="content-filters" aria-label="<?php esc_attr_e('Filter and search options', 'fau-elemental'); ?>">
        <?php
        // Generate unique block IDs
        $page_type = 'index';
        if (is_home()) {
            $page_type = 'blog-homepage';
        } elseif (is_category()) {
            $page_type = 'category-' . get_queried_object_id();
        } elseif (is_tag()) {
            $page_type = 'tag-' . get_queried_object_id();
        } elseif (is_author()) {
            $page_type = 'author-' . get_queried_object_id();
        }
        
        $filter_block_id = 'fau-list-filters-' . $page_type;
        $grid_block_id = 'fau-teaser-grid-' . $page_type;
        $pagination_block_id = 'fau-pagination-' . $page_type;
        
        $current_page = max(1, get_query_var('paged', 1));
        $posts_per_page = 6;
        $query_args = [
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ];
        
        if (is_category()) {
            $query_args['cat'] = get_queried_object_id();
        } elseif (is_tag()) {
            $query_args['tag_id'] = get_queried_object_id();
        } elseif (is_author()) {
            $query_args['author'] = get_queried_object_id();
        }
        
        $count_query = new WP_Query($query_args);
        $total_posts = $count_query->found_posts; 
        wp_reset_postdata();
        $total_pages = max(1, ceil($total_posts / $posts_per_page));
        
        echo do_blocks('<!-- wp:fau-elemental/fau-list-filters {"enableSearch":true,"searchPlaceholder":"' . esc_attr__('Search posts...', 'fau-elemental') . '","enableFilters":true,"filterFields":[{"name":"categories","label":"' . esc_attr__('All Topics', 'fau-elemental') . '","type":"taxonomy","taxonomy":"category"},{"name":"tags","label":"' . esc_attr__('All Tags', 'fau-elemental') . '","type":"taxonomy","taxonomy":"post_tag"},{"name":"authors","label":"' . esc_attr__('All Authors', 'fau-elemental') . '","type":"author"}],"showMoreFiltersButton":true,"enableViewSwitcher":true,"availableViews":["cards","table"],"defaultView":"cards","enableSorting":true,"sortOptions":[{"value":"date","label":"' . esc_attr__('Latest First', 'fau-elemental') . '"},{"value":"title","label":"' . esc_attr__('Alphabetical', 'fau-elemental') . '"},{"value":"modified","label":"' . esc_attr__('Recently Updated', 'fau-elemental') . '"}],"defaultSort":"date","showResultsCount":true,"resultsPerPage":6,"gridWidth":"12","customBlockId":"' . $filter_block_id . '"} /-->');
        ?>
    </section>

    <section class="content-grid" aria-label="<?php esc_attr_e('Posts listing', 'fau-elemental'); ?>">
        <?php
        // Build filter parameters for teaser grid based on page type
        $filter_params = '';
        if (is_category()) {
            $filter_params = ',"selectedCategory":' . get_queried_object_id();
        } elseif (is_tag()) {
            $filter_params = ',"selectedTags":[' . get_queried_object_id() . ']';
        } elseif (is_author()) {
            $filter_params = ',"selectedAuthor":' . get_queried_object_id();
        }
        
        echo do_blocks('<!-- wp:fau-elemental/fau-teaser-grid {"variant":"post","selectionMode":"auto","displayStyle":"teaser-grid","teaserLayout":"3m","postsPerPage":6,"orderBy":"date","order":"DESC","headingLevel":"h2","showLoadMore":false,"showPagination":true,"currentPage":' . $current_page . ',"customBlockId":"' . $grid_block_id . '","filterBlockId":"' . $filter_block_id . '","paginationBlockId":"' . $pagination_block_id . '"' . $filter_params . '} /-->');
        ?>
    </section>

    <nav class="content-pagination" aria-label="<?php esc_attr_e('Posts pagination', 'fau-elemental'); ?>">
        <?php
        echo do_blocks('<!-- wp:fau-elemental/fau-pagination {"variant":"basic","currentPage":' . $current_page . ',"totalPages":' . $total_pages . ',"customBlockId":"' . $pagination_block_id . '","gridBlockId":"' . $grid_block_id . '","filterBlockId":"' . $filter_block_id . '"} /-->');
        ?>
    </nav>
</main>

<?php get_footer(); ?>
