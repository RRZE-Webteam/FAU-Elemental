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
        'posts_per_page' => -1,
        'fields' => 'ids'
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

<main id="primary" class="site-main">
    <header class="blog-header">
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
    // Get pagination variables (validation already handled in template_redirect)
    $current_page = max(1, get_query_var('paged', 1));
    $pagination_type = faue_get_pagination_type();
    $items_per_page = faue_get_items_per_page();
    ?>

    <section class="content-grid" aria-label="<?php esc_attr_e('Pages listing', 'fau-elemental'); ?>">
        <?php
        echo do_blocks('<!-- wp:fau-elemental/fau-teaser-grid {"variant":"page","selectionMode":"auto","displayStyle":"teaser-grid","teaserLayout":"3m","postsPerPage":' . $items_per_page . ',"selectedCategory":0,"orderBy":"title","order":"ASC","headingLevel":"h2","showPagination":true,"paginationType":"' . $pagination_type . '","currentPage":' . $current_page . '} /-->');
        ?>
    </section>
</main>

<?php get_footer(); ?> 