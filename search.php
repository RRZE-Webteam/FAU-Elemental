<?php
/**
 * Search Results Template
 *
 * @package FAU-Elemental
 */

global $wp_query;

get_header();

// Configuration values from theme config
$search_config = array(
    'excerpt_length' => faue_get_default('faue_search_excerpt_length'),
    'separator' => faue_get_default('faue_search_separator'),
    'arrow' => faue_get_default('faue_search_arrow')
);
?>

<main class="search-results-page" role="main">
    <div class="search-header">
        <h1 class="search-title">
            <?php 
            $has_results = have_posts();
            if ($has_results) {
                printf(__('Search Results for: %s', 'fau-elemental'), '<span class="search-query">' . get_search_query() . '</span>'); 
            } else {
                echo __('Search Results', 'fau-elemental');
            }
            ?>
        </h1>
        
        <!-- Global Search Block -->
        <div class="search-form-container">
            <?php
            // Render the FAU Global Search block with full-grid width for the search results page
            $block_content = '<!-- wp:fau-elemental/fau-global-search {"width":"full-grid","heading":"' . esc_attr(__('Search', 'fau-elemental')) . '"} /-->';
            echo do_blocks($block_content);
            ?>
        </div>
    </div>

    <?php 
    // Get custom SQL results for better search accuracy
    $current_site_custom_results = array();
    global $wpdb;
    $search_query = get_search_query();
    
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
    
    $has_results = !empty($current_site_custom_results);
    if ($has_results) : ?>
        <div class="search-results" itemscope itemtype="https://schema.org/SearchResultsPage">
            <div class="results-count">
                <p role="status" aria-live="polite"><?php 
                    $total_results = count($current_site_custom_results);
                    printf(
                        _nx(
                            '%s result found',
                            '%s results found', 
                            $total_results,
                            'search results count', 
                            'fau-elemental'
                        ), 
                        number_format_i18n($total_results)
                    );
                ?></p>
            </div>

            <h2 class="screen-reader-text"><?php _e('Search Results List', 'fau-elemental'); ?></h2>
            <div class="search-results-list">
                <?php 
                $result_counter = 1;
                
                // Display search results using pre-computed custom results
                foreach ($current_site_custom_results as $formatted_result) : 
                    $post_result = $formatted_result['post_result'];
                    ?>
                    <article class="search-result-item search-result-item--current-site" itemscope itemtype="https://schema.org/Article">
                        <h3 class="screen-reader-text">
                            <?php printf(__('Search Result %d', 'fau-elemental'), $result_counter); ?>
                        </h3>
                        <header class="result-header">
                            <time class="result-date" 
                                  datetime="<?php echo date('c', strtotime($formatted_result['date'])); ?>" 
                                  itemprop="datePublished">
                                <?php echo date('d.m.Y', strtotime($formatted_result['date'])); ?>
                            </time>
                            <span class="result-separator" aria-hidden="true"><?php echo $search_config['separator']; ?></span>
                            <span class="result-content-type">
                                <?php echo esc_html($formatted_result['type']); ?>
                            </span>
                            <?php 
                            $categories = get_the_category($post_result->ID);
                            if (!empty($categories)) : ?>
                                <span class="result-separator" aria-hidden="true"><?php echo $search_config['separator']; ?></span>
                                <span class="result-category" itemprop="articleSection">
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
                                <h4 class="result-title" itemprop="headline">
                                    <a href="<?php echo esc_url($formatted_result['link']); ?>" 
                                       class="result-link" 
                                       itemprop="url">
                                        <?php echo esc_html($formatted_result['title']); ?>
                                    </a>
                                </h4>
                                
                                <div class="result-excerpt" itemprop="description">
                                    <?php echo esc_html(wp_trim_words($formatted_result['excerpt'], $search_config['excerpt_length'], '...')); ?>
                                </div>
                                
                                <a href="<?php echo esc_url($formatted_result['link']); ?>" 
                                   class="result-read-more">
                                    <span class="screen-reader-text"><?php printf(__('Read more about %s', 'fau-elemental'), $formatted_result['title']); ?></span>
                                    <span aria-hidden="true"><?php _e('Read more', 'fau-elemental'); ?> <?php echo $search_config['arrow']; ?></span>
                                </a>
                            </div>
                        </div>
                    </article>
                    <?php 
                    $result_counter++;
                endforeach; ?>
            </div>
            
            <?php 
            // Pagination
            $pagination_args = array(
                'mid_size' => 2,
                'prev_text' => __('&laquo; Previous', 'fau-elemental'),
                'next_text' => __('Next &raquo;', 'fau-elemental'),
                'class' => 'search-pagination',
                'screen_reader_text' => __('Search results navigation', 'fau-elemental')
            );
            
            $pagination = get_the_posts_pagination($pagination_args);
            if (!empty($pagination)) : ?>
                <nav class="search-pagination-nav" aria-labelledby="search-pagination-heading">
                    <h2 id="search-pagination-heading" class="screen-reader-text"><?php _e('Search Results Pages', 'fau-elemental'); ?></h2>
                    <?php the_posts_pagination($pagination_args); ?>
                </nav>
            <?php endif; ?>
        </div>

    <?php else : ?>
        <div class="no-results" role="region" aria-labelledby="no-results-heading">
            <h2 id="no-results-heading" class="screen-reader-text"><?php _e('No Search Results', 'fau-elemental'); ?></h2>
            <p class="no-results-message">
                <?php _e('No results were found.', 'fau-elemental'); ?>
            </p>
            <div class="no-results-suggestions">
                <h3><?php _e('Try:', 'fau-elemental'); ?></h3>
                <ul>
                    <li><?php _e('Check the spelling of your search terms.', 'fau-elemental'); ?></li>
                    <li><?php _e('Use more general search terms.', 'fau-elemental'); ?></li>
                    <li><?php _e('Use fewer search terms.', 'fau-elemental'); ?></li>
                </ul>
            </div>
        </div>
    <?php endif; ?>
</main>
<?php get_footer(); ?> 
