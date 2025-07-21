<?php
/**
 * The front page template - Enhanced for both static pages and blog homepage
 *
 * @package Fau-Elemental
 */

get_header();

// Check if we should show posts or a static page
if (is_home() && is_front_page()) {
    // Front page is set to show latest posts
    ?>
    <main class="wp-block-group blog-homepage">
        <header class="blog-header is-layout-flow">
            <h1 class="blog-title">
                <?php 
                $blog_title = get_bloginfo('name');
                $page_title = get_theme_mod('blog_homepage_title', $blog_title);
                echo esc_html($page_title);
                ?>
            </h1>
        </header>

        <?php 
        // Check if there's a front page set and get its content
        $front_page_id = get_option('page_on_front');
        if ($front_page_id) {
            $front_page = get_post($front_page_id);
            if ($front_page && !empty($front_page->post_content)) {
                ?>
                <div class="is-layout-flow">
                    <?php echo apply_filters('the_content', $front_page->post_content); ?>
                </div>
                <?php
            }
        }
        ?>

        <?php
        // Get pagination variables
        $current_page = max(1, get_query_var('paged', 1));
        $pagination_type = faue_get_pagination_type();
        $items_per_page = faue_get_items_per_page();
        
        // Get total post count for pagination info
        $posts_query = new WP_Query([
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ]);
        $total_posts = $posts_query->found_posts;
        wp_reset_postdata();
        
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
                            <label for="homepage-sort"><?php _e('Sort by:', 'fau-elemental'); ?></label>
                            <div class="select-wrapper">
                                <select name="orderby" id="homepage-sort" onchange="this.form.submit()">
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
            
            echo do_blocks('<!-- wp:fau-elemental/fau-teaser-grid {"variant":"post","selectionMode":"auto","displayStyle":"teaser-grid","teaserLayout":"3m","postsPerPage":' . $items_per_page . ',"orderBy":"' . $orderby . '","order":"' . $order . '","headingLevel":"h2","showPagination":true,"paginationType":"' . $pagination_type . '","currentPage":' . $current_page . '} /-->');
            ?>
        </section>
    </main>
    <?php
} else {
    // Front page is set to a static page - display the page content
    ?>
    <main>
        <?php
        while (have_posts()) :
            the_post();
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <div class="entry-content">
                    <?php
                    the_content();
                    
                    wp_link_pages([
                        'before' => '<div class="page-links">' . __('Pages:', 'fau-elemental'),
                        'after'  => '</div>',
                    ]);
                    ?>
                </div>
                
                <?php if (comments_open() || get_comments_number()) : ?>
                    <div class="comments-area">
                        <?php comments_template(); ?>
                    </div>
                <?php endif; ?>
            </article>
            <?php
        endwhile;
        ?>
    </main>
    <?php
}

get_footer();
?>
