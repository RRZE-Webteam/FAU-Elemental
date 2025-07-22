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
    
    $posts_per_page = faue_get_items_per_page();
    $query_args = [
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => 1,  // Only header query
        'no_found_rows' => false  // We need found_posts
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
    <header class="blog-header is-layout-flow">
        <h1 class="blog-title">
            <?php 
            if (is_home() && !is_front_page()) {
                // Use the title from the Posts Page
                $posts_page_id = get_option('page_for_posts');
                if ($posts_page_id) {
                    $posts_page = get_post($posts_page_id);
                    if ($posts_page) {
                        echo esc_html($posts_page->post_title);
                    } else {
                        echo esc_html__('Blog', 'fau-elemental');
                    }
                } else {
                    echo esc_html__('Blog', 'fau-elemental');
                }
            } elseif (is_home()) {
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
            if (is_home() && !is_front_page()) {
                // Use the excerpt from the Posts Page
                $posts_page_id = get_option('page_for_posts');
                if ($posts_page_id) {
                    $posts_page = get_post($posts_page_id);
                    if ($posts_page) {
                        $description = '';
                        if (!empty($posts_page->post_excerpt)) {
                            $description = $posts_page->post_excerpt;
                        } elseif (!empty($posts_page->post_content)) {
                            $description = wp_trim_words($posts_page->post_content, 30);
                        }
                        if (empty($description)) {
                            $description = __('Welcome to our blog. Browse and filter through all our posts using the options below.', 'fau-elemental');
                        }
                    } else {
                        $description = __('Welcome to our blog. Browse and filter through all our posts using the options below.', 'fau-elemental');
                    }
                } else {
                    $description = __('Welcome to our blog. Browse and filter through all our posts using the options below.', 'fau-elemental');
                }
            } elseif (is_home()) {
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

    <?php 
    // Check if there's a posts page set and get its content
    if (is_home() && !is_front_page()) {
        $posts_page_id = get_option('page_for_posts');
        if ($posts_page_id) {
            $posts_page = get_post($posts_page_id);
            if ($posts_page && !empty($posts_page->post_content)) {
                ?>
                <div class="is-layout-flow">
                    <?php echo apply_filters('the_content', $posts_page->post_content); ?>
                </div>
                <?php
            }
        }
    }
    ?>

    <?php
    $current_page = max(1, get_query_var('paged', 1));
    $pagination_type = faue_get_pagination_type();
    $items_per_page = faue_get_items_per_page();
    
    // Get total post count for pagination info
    $total_posts = 0;
    if (is_category()) {
        $category = get_queried_object();
        $total_posts = $category->count;
    } elseif (is_tag()) {
        $tag = get_queried_object();
        $total_posts = $tag->count;
    } elseif (is_author()) {
        $author = get_queried_object();
        $total_posts = count_user_posts($author->ID);
    } else {
        // For general blog homepage - optimized query
        $posts_query = new WP_Query([
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => 1,  // Only header query
            'no_found_rows' => false  // We need found_posts
        ]);
        $total_posts = $posts_query->found_posts;
        wp_reset_postdata();
    }
    
    // Calculate pagination info for display
    $start_item = (($current_page - 1) * $items_per_page) + 1;
    $end_item = min($current_page * $items_per_page, $total_posts);
    ?>

    <?php if ($total_posts > 0) : ?>
        <div class="archive-info">
            <div class="archive-meta-row">
                <div class="pagination-info">
                    <?php
                    if ($total_posts > $items_per_page) {
                        printf(
                            '<span class="pagination-number">%1$s</span> %2$s <span class="pagination-number">%3$s</span> %4$s <span class="pagination-number">%5$s</span>',
                            number_format_i18n($start_item),
                            __('to', 'fau-elemental'),
                            number_format_i18n($end_item),
                            __('of', 'fau-elemental'),
                            number_format_i18n($total_posts)
                        );
                    } else {
                        printf(
                            '<span class="pagination-number">%1$s</span> %2$s',
                            number_format_i18n($total_posts),
                            __('total', 'fau-elemental')
                        );
                    }
                    ?>
                </div>
                
                <div class="archive-sorting">
                    <form method="get" class="sorting-form">
                        <label for="index-sort"><?php _e('Sort by:', 'fau-elemental'); ?></label>
                        <div class="select-wrapper">
                            <select name="orderby" id="index-sort" onchange="this.form.submit()">
                                <option value="date" <?php selected(isset($_GET['orderby']) ? $_GET['orderby'] : 'date', 'date'); ?>>
                                    <?php _e('Date', 'fau-elemental'); ?>
                                </option>
                                <option value="title" <?php selected(isset($_GET['orderby']) ? $_GET['orderby'] : 'date', 'title'); ?>>
                                    <?php _e('Title', 'fau-elemental'); ?>
                                </option>
                            </select>
                        </div>
                        <input type="hidden" name="order" value="<?php echo esc_attr(isset($_GET['order']) ? $_GET['order'] : 'DESC'); ?>">
                        <?php if (isset($_GET['paged'])) : ?>
                            <input type="hidden" name="paged" value="<?php echo esc_attr($_GET['paged']); ?>">
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

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
        
        // Get sorting parameters from URL
        $orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'date';
        $order = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'DESC';
        
        // Validate sorting parameters
        $valid_orderby = ['date', 'title'];
        $valid_order = ['ASC', 'DESC'];
        
        if (!in_array($orderby, $valid_orderby)) {
            $orderby = 'date';
        }
        
        if (!in_array($order, $valid_order)) {
            $order = 'DESC';
        }
        
        // Prepare block attributes safely
        $block_args = [
            'variant' => 'post',
            'selectionMode' => 'auto',
            'displayStyle' => 'teaser-grid',
            'teaserLayout' => '3m',
            'postsPerPage' => $items_per_page,
            'orderBy' => $orderby,
            'order' => $order,
            'headingLevel' => 'h2',
            'showPagination' => true,
            'paginationType' => $pagination_type,
            'currentPage' => $current_page
        ];
        
        // Add filter parameters based on page type
        if (is_category()) {
            $block_args['selectedCategory'] = get_queried_object_id();
        } elseif (is_tag()) {
            $block_args['selectedTags'] = [get_queried_object_id()];
        } elseif (is_author()) {
            $block_args['selectedAuthor'] = get_queried_object_id();
        }
        
        echo do_blocks('<!-- wp:fau-elemental/fau-teaser-grid ' . wp_json_encode($block_args) . ' /-->');
        ?>
    </section>
</main>

<?php get_footer(); ?>
