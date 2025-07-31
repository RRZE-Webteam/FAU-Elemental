<?php
/**
 * A utility for archive pages to generate and show the archive grid including sorting.
 * @package Fau-Elemental
 */

/**
 * Render an archive grid.
 */
function fau_render_archive_grid($block_args = [], $aria_label = null, $post_count = 0) {
    wp_enqueue_script("faue-template-archive");

    if (empty($aria_label)) {
        $aria_label = __( 'Archive listing', 'fau-elemental' );
    }

    $current_page = max(1, get_query_var('paged', 1));
    $pagination_type = faue_get_pagination_type();
    $items_per_page = faue_get_items_per_page();

    // Get the post count for this archive
    if ($post_count === 0) {
        $archive = get_queried_object();
        $post_count = $archive->count;
    }

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

    <?php if ($post_count > 0) : ?>
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
                        <label for="archive-sort"><?php _e('Sort by:', 'fau-elemental'); ?></label>
                        <div class="select-wrapper">
                            <select name="" id="archive-sort">
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
    <?php endif; ?>

    <section class="content-grid" aria-label="<?php echo esc_attr($aria_label); ?>">
        <?php
        // Prepare block attributes safely
        $block_default_args = [
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

        $block_args = array_merge($block_default_args, $block_args);
        
        echo do_blocks('<!-- wp:fau-elemental/fau-teaser-grid ' . wp_json_encode($block_args) . ' /-->');
        ?>
    </section>

    <?php
}
