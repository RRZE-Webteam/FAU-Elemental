<?php
/**
 * Search Results Template Part
 *
 * @package FAU-Elemental
 */

// Enqueue the archive template script for sorting functionality
wp_enqueue_script("faue-template-archive");

// Configuration values from theme config
$search_config = array(
    'excerpt_length' => faue_get_default('faue_search_excerpt_length'),
    'separator' => faue_get_default('faue_search_separator'),
    'arrow' => faue_get_default('faue_search_arrow')
);

// Get custom SQL results for better search accuracy
$current_site_custom_results = array();
global $wpdb;
$search_query = get_search_query();

// Debug: Check if we have a search query
if (empty($search_query)) {
    $search_query = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
}

// If no search query, show all posts for testing
if (empty($search_query)) {
    $all_posts_query = new WP_Query(array(
        'post_type' => array('post', 'page'),
        'post_status' => 'publish',
        'posts_per_page' => 10
    ));
    
    if ($all_posts_query->have_posts()) {
        while ($all_posts_query->have_posts()) {
            $all_posts_query->the_post();
            $current_site_custom_results[] = array(
                'post_result' => get_post(),
                'title' => get_the_title(),
                'link' => get_permalink(),
                'excerpt' => !empty(get_the_excerpt()) ? get_the_excerpt() : wp_trim_words(get_the_content(), 30),
                'date' => get_the_date('Y-m-d H:i:s'),
                'type' => get_post_type_object(get_post_type())->labels->singular_name,
                'is_current_site' => true,
                'blog_name' => get_bloginfo('name')
            );
        }
        wp_reset_postdata();
    }
} else {
    // Use SQL search logic for better results
    $search_terms = explode(' ', $search_query);
    $where_clauses = array();
    $like_terms = array();

    foreach ($search_terms as $term) {
        $term = trim($term);
        if (strlen($term) >= 2) {
            $like_term = $wpdb->esc_like($term);
            $like_terms[] = $like_term;
            $where_clauses[] = $wpdb->prepare(
                "(p.post_title LIKE %s OR p.post_content LIKE %s OR p.post_excerpt LIKE %s)",
                "%{$like_term}%",
                "%{$like_term}%", 
                "%{$like_term}%"
            );
        }
    }

    if (!empty($where_clauses)) {
        $where_sql = implode(' AND ', $where_clauses);
        
        $sql_query = "
            SELECT p.*, u.display_name as author_name
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->users} u ON p.post_author = u.ID  
            WHERE p.post_status = 'publish' 
            AND p.post_type IN ('post', 'page')
            AND ({$where_sql})
            ORDER BY p.post_date DESC
            LIMIT 50
        ";
        
        $current_site_results = $wpdb->get_results($sql_query);
        
        foreach ($current_site_results as $post_result) {
            // Validate that search terms actually appear in content
            $title_clean = strip_tags($post_result->post_title);
            $content_clean = strip_tags($post_result->post_content);
            $excerpt_clean = strip_tags($post_result->post_excerpt);
            $combined_content = $title_clean . ' ' . $content_clean . ' ' . $excerpt_clean;
            
            $found_terms = array();
            $title_match_type = 'No Match';
            $content_match_type = 'No Match';
            
            foreach ($like_terms as $term) {
                // Check title matches
                if (preg_match('/\b' . preg_quote($term, '/') . '\b/i', $title_clean)) {
                    $title_match_type = 'Word Boundary';
                    $found_terms[] = $term;
                } elseif (stripos($title_clean, $term) !== false) {
                    if ($title_match_type === 'No Match') $title_match_type = 'Substring';
                    $found_terms[] = $term;
                }
                
                // Check content matches  
                if (preg_match('/\b' . preg_quote($term, '/') . '\b/i', $combined_content)) {
                    $content_match_type = 'Word Boundary';
                    $found_terms[] = $term;
                } elseif (stripos($combined_content, $term) !== false) {
                    if ($content_match_type === 'No Match') $content_match_type = 'Substring';
                    $found_terms[] = $term;
                }
            }
            
            // Only include if we found at least one term
            if (!empty($found_terms)) {
                $current_site_custom_results[] = array(
                    'post_result' => $post_result,
                    'title' => $post_result->post_title,
                    'link' => get_permalink($post_result->ID),
                    'excerpt' => !empty($post_result->post_excerpt) ? $post_result->post_excerpt : wp_trim_words($post_result->post_content, 30),
                    'date' => $post_result->post_date,
                    'type' => get_post_type_object($post_result->post_type)->labels->singular_name,
                    'is_current_site' => true,
                    'blog_name' => get_bloginfo('name')
                );
            }
        }
    }

    // Fallback to WordPress default search if custom search returns no results
    if (empty($current_site_custom_results) && !empty($search_query)) {
        // Use WordPress default search
        $default_search_query = new WP_Query(array(
            's' => $search_query,
            'post_type' => array('post', 'page'),
            'post_status' => 'publish',
            'posts_per_page' => 50
        ));
        
        if ($default_search_query->have_posts()) {
            while ($default_search_query->have_posts()) {
                $default_search_query->the_post();
                $current_site_custom_results[] = array(
                    'post_result' => get_post(),
                    'title' => get_the_title(),
                    'link' => get_permalink(),
                    'excerpt' => !empty(get_the_excerpt()) ? get_the_excerpt() : wp_trim_words(get_the_content(), 30),
                    'date' => get_the_date('Y-m-d H:i:s'),
                    'type' => get_post_type_object(get_post_type())->labels->singular_name,
                    'is_current_site' => true,
                    'blog_name' => get_bloginfo('name')
                );
            }
            wp_reset_postdata();
        }
    }
}

$has_results = !empty($current_site_custom_results);

// Pagination setup for custom results
$results_per_page = 10;
$total_results = count($current_site_custom_results);
$current_page = max(1, get_query_var('paged', 1));
$total_pages = (int) ceil($total_results / $results_per_page);

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

// Sort the results based on the selected criteria
if (!empty($current_site_custom_results)) {
    usort($current_site_custom_results, function($a, $b) use ($orderby, $order) {
        $value_a = '';
        $value_b = '';
        
        if ($orderby === 'date') {
            $value_a = strtotime($a['date']);
            $value_b = strtotime($b['date']);
        } elseif ($orderby === 'title') {
            $value_a = strtolower($a['title']);
            $value_b = strtolower($b['title']);
        }
        
        if ($order === 'ASC') {
            return $value_a <=> $value_b;
        } else {
            return $value_b <=> $value_a;
        }
    });
}

// Slice the results for the current page
$offset = ($current_page - 1) * $results_per_page;
$paged_results = array_slice($current_site_custom_results, $offset, $results_per_page);
?>

<div class="search-header">
    <h1 class="search-title">
        <?php 
        if ($has_results) {
            if (!empty($search_query)) {
                // translators: search query
                printf(__('Search Results for: %s', 'fau-elemental'), '<span class="search-query">' . get_search_query() . '</span>'); 
            } else {
                echo __('All Posts', 'fau-elemental');
            }
        } else {
            echo __('Search Results', 'fau-elemental');
        }
        ?>
    </h1>
    
    <!-- Global Search Block -->
    <div class="search-form-container">
        <?php
        // Render the FAU Global Search block with full-grid width for the search results page
        $block_content = '<!-- wp:fau-elemental/fau-global-search {"width":"content-size","heading":"' . esc_attr(__('Search', 'fau-elemental')) . '"} /-->';
        echo do_blocks($block_content);
        ?>
    </div>
</div>

<?php if ($has_results) : ?>
    <div class="search-results" itemscope itemtype="https://schema.org/SearchResultsPage">
        <div class="archive-info">
            <div class="archive-meta-row">
                <div class="pagination-info">
                    <?php
                    $total_results = count($current_site_custom_results);
                    $items_per_page = 10; // Using the same pagination as in the search results
                    $start_item = (($current_page - 1) * $items_per_page) + 1;
                    $end_item = min($current_page * $items_per_page, $total_results);
                    
                    if ($total_results > $items_per_page) {
                        printf(
                            '<span class="pagination-number">%1$s</span> %2$s <span class="pagination-number">%3$s</span> %4$s <span class="pagination-number">%5$s</span>',
                            number_format_i18n($start_item),
                            __('to', 'fau-elemental'),
                            number_format_i18n($end_item),
                            __('of', 'fau-elemental'),
                            number_format_i18n($total_results)
                        );
                    } else {
                        printf(
                            '<span class="pagination-number">%1$s</span> %2$s',
                            number_format_i18n($total_results),
                            __('total', 'fau-elemental')
                        );
                    }
                    ?>
                </div>
                
                <div class="archive-sorting">
                    <form method="get" class="sorting-form">
                        <label for="archive-sort"><?php _e('Sort by', 'fau-elemental'); ?></label>
                        <div class="select-wrapper">
                            <select name="sort" id="archive-sort">
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
                        <?php if (!empty($search_query)) : ?>
                            <input type="hidden" name="s" value="<?php echo esc_attr($search_query); ?>">
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <h2 class="screen-reader-text"><?php _e('Search Results List', 'fau-elemental'); ?></h2>
        <div class="search-results-list">
            <?php 
            $result_counter = 1;
            
            // Display search results using paged results
            foreach ($paged_results as $formatted_result) : 
                $post_result = $formatted_result['post_result'];
                $excerpt = strip_shortcodes($formatted_result['excerpt']);
                // Remove any remaining shortcode-like patterns (complete and incomplete)
                $excerpt = preg_replace('/\[[a-z\-_]+\s*[^\]]*(?:\]|$)/', '', $excerpt);
                ?>
                <article class="search-result-item search-result-item--current-site" itemscope itemtype="https://schema.org/Article">
                    <h3 class="screen-reader-text">
                        <?php 
                            // translators: index of the search result
                            printf(__('Search Result %d', 'fau-elemental'), $result_counter);
                        ?>
                    </h3>
                    <header class="result-header">
                        <time class="result-date" 
                              datetime="<?php echo date('c', strtotime($formatted_result['date'])); ?>" 
                              itemprop="datePublished">
                            <span class="result-date-icon"></span>
                            <?php echo date('d.m.Y', strtotime($formatted_result['date'])); ?>
                        </time>
                        <span class="result-separator" aria-hidden="true"><?php echo $search_config['separator']; ?></span>
                        <span class="result-content-type">
                            <?php echo esc_html($formatted_result['type']); ?>
                        </span>
                        <?php 
                        // Handle categories based on post type
                        $post_type = get_post_type($post_result->ID);
                        $categories = array();
                        $category_class = '';
                        $category_label = '';
                        
                        if ($post_type === 'post') {
                            $categories = get_the_category($post_result->ID);
                            $category_class = 'result-category result-category--post';
                            $category_label = __('Post Category', 'fau-elemental');
                        } elseif ($post_type === 'page') {
                            $categories = get_the_terms($post_result->ID, 'page_category');
                            if (is_wp_error($categories)) {
                                $categories = array();
                            }
                            $category_class = 'result-category result-category--page';
                            $category_label = __('Page Category', 'fau-elemental');
                        }
                        
                        if (!empty($categories)) : ?>
                            <span class="result-separator" aria-hidden="true"><?php echo $search_config['separator']; ?></span>
                            <span class="<?php echo esc_attr($category_class); ?>" 
                                  itemprop="articleSection"
                                  title="<?php echo esc_attr($category_label); ?>">
                                <?php echo esc_html($categories[0]->name); ?>
                            </span>
                        <?php endif; ?>
                    </header>
                    
                    <div class="result-content">
                        <?php 
                        $thumbnail_id = get_post_thumbnail_id($post_result->ID);
                        if ($thumbnail_id) : ?>
                            <div class="result-image">
                                <a href="<?php echo esc_url($formatted_result['link']); ?>" tabindex="-1" aria-hidden="true">
                                    <?php 
                                    $thumbnail_alt = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true);
                                    $alt_text = !empty($thumbnail_alt) ? $thumbnail_alt : sprintf(__('Featured image for %s', 'fau-elemental'), $formatted_result['title']);
                                    
                                    echo wp_get_attachment_image($thumbnail_id, 'medium', false, array(
                                        'class' => 'result-thumbnail',
                                        'itemprop' => 'image',
                                        'alt' => $alt_text
                                    )); 
                                    ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <div class="result-text">
                            <h2 class="result-title" itemprop="headline">
                                <a href="<?php echo esc_url($formatted_result['link']); ?>" 
                                   class="result-link" 
                                   itemprop="url">
                                    <?php echo esc_html($formatted_result['title']); ?>
                                </a>
                            </h2>
                            
                            <?php if (!empty($excerpt)) : ?>
                            <p class="result-excerpt" itemprop="description">
                                <?php echo esc_html(wp_trim_words($excerpt, $search_config['excerpt_length'], '…')); ?>
                            </p>
                            <?php endif; ?>
                            
                            <div class="wp-block-buttons">
                                <div class="wp-block-button is-style-tertiary">
                                    <a href="<?php echo esc_url($formatted_result['link']); ?>" 
                                       class="wp-block-button__link result-read-more">
                                        <span class="screen-reader-text"><?php
                                            // translators: title of the search result
                                            printf(__('Read more about %s', 'fau-elemental'), $formatted_result['title']); 
                                        ?></span>
                                        <span aria-hidden="true"><?php _e('Read more', 'fau-elemental'); ?> </span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
                <?php 
                $result_counter++;
            endforeach; ?>
        </div>
        
        <?php
        // Custom Pagination
        echo fau_elemental_generate_pagination($current_page, $total_pages, 'numbers');
        ?>
    </div>

<?php else : ?>
    <div class="no-results" role="region" aria-labelledby="no-results-heading">
        <h2 id="no-results-heading" class="screen-reader-text"><?php _e('No Search Results', 'fau-elemental'); ?></h2>
        <p class="no-results-message">
            <?php _e('Unfortunately, we were unable to find any matching search results for the following query:', 'fau-elemental'); ?>
        </p>
        <p class="search-query">
            <?php echo esc_html($search_query); ?>
        </p>
        <?php if (has_nav_menu('search_options_menu')) : ?>
            <div class="search-options-menu">
                <h4><?php _e('Additional search options', 'fau-elemental'); ?></h4>
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'search_options_menu',
                    'container' => false,
                    'menu_class' => 'search-options-menu__list',
                    'depth' => 2,
                    'fallback_cb' => false,
                ));
                ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sortSelect = document.getElementById('archive-sort');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            const selectedValue = this.value;
            const [orderby, order] = selectedValue.split('-');
            
            // Update the hidden inputs
            const orderbyInput = document.querySelector('input[name="orderby"]');
            const orderInput = document.querySelector('input[name="order"]');
            
            if (orderbyInput) orderbyInput.value = orderby;
            if (orderInput) orderInput.value = order;
            
            // Submit the form
            this.form.submit();
        });
    }
});
</script>
