<?php
/**
 * The template for displaying category archive pages
 * @package Fau-Elemental
 */

get_header(); ?>

<main class="wp-block-group category-archive">
    <header class="archive-header">
        <h1 class="archive-title">
            <?php echo esc_html(single_cat_title('', false)); ?>
        </h1>
        
        <p class="archive-description">
            <?php 
            $description = category_description();
            if (empty($description)) {
                $description = sprintf(__('Browse all posts in the %s category. Use the filters below to refine your search and find exactly what you\'re looking for.', 'fau-elemental'), single_cat_title('', false));
            }
            echo wp_kses_post($description);
            ?>
        </p>
        
        <div class="category-meta">
            <?php
            $category = get_queried_object();
            $post_count = $category->count;
            printf(
                _n('This category contains %s post', 'This category contains %s posts', $post_count, 'fau-elemental'),
                number_format_i18n($post_count)
            );
            ?>
        </div>
    </header>

    <?php
    $category_id = get_queried_object_id();
    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    if ($current_page === 1) {
        $current_page = get_query_var('paged') ? get_query_var('paged') : 1;
    }
    $pagination_type = faue_get_pagination_type();
    $items_per_page = faue_get_items_per_page();
    ?>

    <section class="content-grid" aria-label="<?php esc_attr_e('Category posts listing', 'fau-elemental'); ?>">
        <?php
        echo do_blocks('<!-- wp:fau-elemental/fau-teaser-grid {"variant":"post","selectionMode":"auto","displayStyle":"teaser-grid","teaserLayout":"3m","postsPerPage":' . $items_per_page . ',"selectedCategory":' . $category_id . ',"orderBy":"date","order":"DESC","headingLevel":"h2","showPagination":true,"paginationType":"' . $pagination_type . '","currentPage":' . $current_page . '} /-->');
        ?>
    </section>

    <aside class="related-categories" aria-labelledby="related-categories-heading">
        <?php
        $related_categories = get_categories([
            'exclude' => $category_id,
            'number' => 5,
            'orderby' => 'count',
            'order' => 'DESC'
        ]);
        
        if (!empty($related_categories)) : ?>
            <h2 id="related-categories-heading" class="related-categories-title">
                <?php _e('Explore Other Categories', 'fau-elemental'); ?>
            </h2>
            
            <div class="category-links">
                <?php foreach ($related_categories as $related_cat) : ?>
                    <a href="<?php echo esc_url(get_category_link($related_cat->term_id)); ?>" 
                       class="category-link">
                        <?php echo esc_html($related_cat->name); ?>
                        <span class="category-count">(<?php echo $related_cat->count; ?>)</span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </aside>
</main>

<?php get_footer(); ?> 