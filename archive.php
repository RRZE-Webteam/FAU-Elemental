<?php
/**
 * The template for displaying archive pages with filtering and pagination
 * @package Fau-Elemental
 */

get_header(); ?>

<main class="wp-block-group archive-page">
    <header class="blog-header is-layout-flow">
        <h1 class="blog-title">
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
    } elseif (is_date()) {
        // For date archives, we need to query to get the count
        $date_query_args = [
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ];
        
        if (is_year()) {
            $date_query_args['year'] = get_query_var('year');
        } elseif (is_month()) {
            $date_query_args['year'] = get_query_var('year');
            $date_query_args['monthnum'] = get_query_var('monthnum');
        } elseif (is_day()) {
            $date_query_args['year'] = get_query_var('year');
            $date_query_args['monthnum'] = get_query_var('monthnum');
            $date_query_args['day'] = get_query_var('day');
        }
        
        $date_query = new WP_Query($date_query_args);
        $total_posts = $date_query->found_posts;
        wp_reset_postdata();
    } else {
        // Fallback for other archive types
        global $wp_query;
        $total_posts = $wp_query->found_posts;
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
                            __('%1$s to %2$s of %3$s', 'fau-elemental'),
                            number_format_i18n($start_item),
                            number_format_i18n($end_item),
                            number_format_i18n($total_posts)
                        );
                    } else {
                        printf(
                            __('%s total', 'fau-elemental'),
                            number_format_i18n($total_posts)
                        );
                    }
                    ?>
                </div>
                
                <div class="archive-sorting">
                    <form method="get" class="sorting-form">
                        <label for="archive-sort"><?php _e('Sort by:', 'fau-elemental'); ?></label>
                        <select name="orderby" id="archive-sort" onchange="this.form.submit()">
                            <option value="date" <?php selected(isset($_GET['orderby']) ? $_GET['orderby'] : 'date', 'date'); ?>>
                                <?php _e('Date', 'fau-elemental'); ?>
                            </option>
                            <option value="title" <?php selected(isset($_GET['orderby']) ? $_GET['orderby'] : 'date', 'title'); ?>>
                                <?php _e('Title', 'fau-elemental'); ?>
                            </option>
                        </select>
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
        // Build filter parameters for teaser grid based on archive type
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
        
        echo do_blocks('<!-- wp:fau-elemental/fau-teaser-grid {"variant":"post","selectionMode":"auto","displayStyle":"teaser-grid","teaserLayout":"3m","postsPerPage":' . $items_per_page . ',"orderBy":"' . $orderby . '","order":"' . $order . '","headingLevel":"h2","showPagination":true,"paginationType":"' . $pagination_type . '","currentPage":' . $current_page . $filter_params . '} /-->');
        ?>
    </section>
</main>

<?php get_footer(); ?>
