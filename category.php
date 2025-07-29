<?php
/**
 * The template for displaying category archive pages
 * @package Fau-Elemental
 */

get_header(); ?>

<main class="wp-block-group category-archive">
    <header class="blog-header is-layout-flow">
        <h1 class="blog-title">
            <?php echo esc_html(single_cat_title('', false)); ?>
        </h1>
        
        <div class="archive-description">
            <?php 
            $description = category_description();
            if (empty($description)) {
                // translators: name of the wordpress category
                $description = sprintf(__('Browse all posts in the %s category.', 'fau-elemental'), single_cat_title('', false));
            }
            echo wp_kses_post($description);
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
        
        // Get the post count for this category
        $category = get_queried_object();
        $post_count = $category->count;
        
        // Calculate pagination info for display
        $start_item = (($current_page - 1) * $items_per_page) + 1;
        $end_item = min($current_page * $items_per_page, $post_count);

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

        $order_select = "$orderby-$order";
    ?>

    <div class="archive-info">
        <div class="archive-meta-row">
            <div class="pagination-info">
                <?php
                if ($post_count > $items_per_page) {
                    printf(
                        '<span class="pagination-number">%1$s</span> %2$s <span class="pagination-number">%3$s</span> %4$s <span class="pagination-number">%5$s</span>',
                        number_format_i18n($start_item),
                        __('to', 'fau-elemental'),
                        number_format_i18n($end_item),
                        __('of', 'fau-elemental'),
                        number_format_i18n($post_count)
                    );
                } else {
                    printf(
                        '<span class="pagination-number">%1$s</span> %2$s',
                        number_format_i18n($post_count),
                        __('total', 'fau-elemental')
                    );
                }
                ?>
            </div>
            
            <div class="archive-sorting">
                <form method="get" class="sorting-form">
                    <label for="category-sort"><?php _e('Sort by:', 'fau-elemental'); ?></label>
                    <div class="select-wrapper">
                        <select name="" id="category-sort">
                            <option value="date-DESC" <?php selected($order_select, 'date-DESC'); ?>>
                                <?php _e('Date - newest first', 'fau-elemental'); ?>
                            </option>
                            <option value="date-ASC" <?php selected($order_select, 'date-ASC'); ?>>
                                <?php _e('Date - oldest first', 'fau-elemental'); ?>
                            </option>
                            <option value="title-ASC" <?php selected($order_select, 'title-ASC'); ?>>
                                <?php _e('Title - ascending', 'fau-elemental'); ?>
                            </option>
                            <option value="title-DESC" <?php selected($order_select, 'title-DESC'); ?>>
                                <?php _e('Title - descending', 'fau-elemental'); ?>
                            </option>
                        </select>
                    </div>
                    <input type="hidden" name="orderby" value="<?php echo esc_attr($orderby); ?>">
                    <input type="hidden" name="order" value="<?php echo esc_attr($order); ?>">
                    <?php if (isset($_GET['paged'])) : ?>
                        <input type="hidden" name="paged" value="<?php echo esc_attr($_GET['paged']); ?>">
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <script>
        // TODO This MUST be put in a JS file, DO NOT MERGE WITH dev until this is fixed!
        document.addEventListener( 'DOMContentLoaded', function () {
            document.getElementById("category-sort").addEventListener("change", function() {
                const [orderby, order] = this.value.split('-');
                this.form.elements["order"].value = order;
                this.form.elements["orderby"].value = orderby;
                this.form.submit();
            });
        });
    </script>

    <section class="content-grid" aria-label="<?php esc_attr_e('Category posts listing', 'fau-elemental'); ?>">
        <?php
        // Prepare block attributes safely
        $block_args = [
            'variant' => 'post',
            'selectionMode' => 'auto',
            'displayStyle' => 'teaser-grid',
            'teaserLayout' => '3m',
            'postsPerPage' => $items_per_page,
            'selectedCategory' => $category_id,
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