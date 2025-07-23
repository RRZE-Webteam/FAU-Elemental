<?php
/**
 * Template Name: All Pages
 * @package FAU-Elemental
 */

// Handle pagination validation before any output
add_action('template_redirect', function() {
    // Only run on this specific template
    if (!is_page() || get_page_template_slug() !== 'page-all-pages.php') {
        return;
    }
    
    // Use WordPress standard pagination query var
    $current_page = max(1, get_query_var('paged', 1));
    
    // Calculate total pages for validation
    $posts_per_page = faue_get_items_per_page();
    $count_query = new WP_Query([
        'post_type' => 'page',
        'post_status' => 'publish',
        'posts_per_page' => 1,  // Only header query
        'no_found_rows' => false  // We need found_posts
    ]);
    $total_posts = $count_query->found_posts; 
    wp_reset_postdata();
    $total_pages = max(1, ceil($total_posts / $posts_per_page));
    
    // Validate current page and redirect if necessary
    if ($current_page > $total_pages && $total_pages > 0) {
        // Redirect to last valid page
        wp_safe_redirect(get_permalink() . 'page/' . $total_pages . '/');
        exit;
    }
});

get_header(); ?>

<main class="wp-block-group all-pages-page">
    <header class="blog-header is-layout-flow">
        <h1 class="blog-title">
            <?php echo esc_html(get_the_title()); ?>
        </h1>
    </header>

    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <div class="is-layout-flow">
            <?php the_content(); ?>
        </div>
    <?php endwhile; endif; ?>

    <?php
    // Get pagination variables and page count information
    $current_page = max(1, get_query_var('paged', 1));
    $pagination_type = faue_get_pagination_type();
    $items_per_page = faue_get_items_per_page();
    
    // Get total page count for display - optimized query
    $count_query = new WP_Query([
        'post_type' => 'page',
        'post_status' => 'publish',
        'posts_per_page' => 1,  // Only header query
        'no_found_rows' => false  // We need found_posts
    ]);
    $total_pages = $count_query->found_posts; 
    wp_reset_postdata();
    
    // Calculate pagination info for display
    $start_item = (($current_page - 1) * $items_per_page) + 1;
    $end_item = min($current_page * $items_per_page, $total_pages);
    ?>

    <div class="archive-info">
        <div class="archive-meta-row">
            <div class="pagination-info">
                <?php
                if ($total_pages > $items_per_page) {
                    printf(
                        '<span class="pagination-number">%1$s</span> %2$s <span class="pagination-number">%3$s</span> %4$s <span class="pagination-number">%5$s</span>',
                        number_format_i18n($start_item),
                        __('to', 'fau-elemental'),
                        number_format_i18n($end_item),
                        __('of', 'fau-elemental'),
                        number_format_i18n($total_pages)
                    );
                } else {
                    printf(
                        '<span class="pagination-number">%1$s</span> %2$s',
                        number_format_i18n($total_pages),
                        __('total', 'fau-elemental')
                    );
                }
                ?>
            </div>
                  
            <div class="archive-sorting">
                <form method="get" class="sorting-form">
                    <label for="pages-sort"><?php _e('Sort by:', 'fau-elemental'); ?></label>
                    <div class="select-wrapper">
                        <select name="orderby" id="pages-sort" onchange="this.form.submit()">
                            <option value="title" <?php selected(isset($_GET['orderby']) ? $_GET['orderby'] : 'title', 'title'); ?>>
                                <?php _e('Title', 'fau-elemental'); ?>
                            </option>
                            <option value="date" <?php selected(isset($_GET['orderby']) ? $_GET['orderby'] : 'title', 'date'); ?>>
                                <?php _e('Date', 'fau-elemental'); ?>
                            </option>
                            <option value="menu_order" <?php selected(isset($_GET['orderby']) ? $_GET['orderby'] : 'title', 'menu_order'); ?>>
                                <?php _e('Menu Order', 'fau-elemental'); ?>
                            </option>
                        </select>
                    </div>
                    <input type="hidden" name="order" value="<?php echo esc_attr(isset($_GET['order']) ? $_GET['order'] : 'ASC'); ?>">
                    <?php if (isset($_GET['paged'])) : ?>
                        <input type="hidden" name="paged" value="<?php echo esc_attr($_GET['paged']); ?>">
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <section class="content-grid" aria-label="<?php esc_attr_e('Pages listing', 'fau-elemental'); ?>">
        <?php
        // Get sorting parameters from URL
        $orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'title';
        $order = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'ASC';
        
        // Validate sorting parameters (pages have different options)
        $valid_orderby = ['title', 'date', 'menu_order'];
        $valid_order = ['ASC', 'DESC'];
        
        if (!in_array($orderby, $valid_orderby)) {
            $orderby = 'title';
        }
        
        if (!in_array($order, $valid_order)) {
            $order = 'ASC';
        }
        
        // Prepare block attributes safely
        $block_args = [
            'variant' => 'page',
            'selectionMode' => 'auto',
            'displayStyle' => 'teaser-grid',
            'teaserLayout' => '3m',
            'postsPerPage' => $items_per_page,
            'selectedCategory' => 0,
            'orderBy' => $orderby,
            'order' => $order,
            'headingLevel' => 'h2',
            'showPagination' => true,
            'paginationType' => $pagination_type,
            'currentPage' => $current_page
        ];
        
        echo do_blocks('<!-- wp:fau-elemental/fau-teaser-grid ' . wp_json_encode($block_args) . ' /-->');
        ?>
    </section>
</main>

<?php get_footer(); ?> 