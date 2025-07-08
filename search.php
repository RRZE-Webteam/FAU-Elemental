<?php
/**
 * Search Results Template
 *
 * Handles both normal and FAU-wide (network) search results.
 *
 * @package FAU-Elemental
 */

global $wp_query;

// Check if this is a FAU-wide search
$is_fau_wide = isset($_GET['fau_search_scope']) && $_GET['fau_search_scope'] === 'fau-wide';
$network_results = false;
if ($is_fau_wide) {
    // Try current blog
    $network_results = get_transient('fau_network_search_results_' . get_current_blog_id());
    // Fallback: try main site
    if (empty($network_results)) {
        $network_results = get_transient('fau_network_search_results_1');
    }
    // If still empty, run the network search logic directly
    if (empty($network_results)) {
        $search_query = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        if (!empty($search_query)) {
            // Store the search query to avoid losing it when switching sites
            $original_search_query = $search_query;
            
            $sites = get_sites([
                'network_id' => get_current_network_id(),
                'public' => 1,
                'archived' => 0,
                'mature' => 0,
                'spam' => 0,
                'deleted' => 0,
                'number' => 10 // Limit to 10 sites for performance
            ]);
            $results = [];
            $current_blog_id = get_current_blog_id();

            // Alternative: Use direct SQL search for more reliable results
            global $wpdb;
            
            // Build SQL search query for current site
            $search_like = '%' . $wpdb->esc_like($original_search_query) . '%';
            $current_site_sql = $wpdb->prepare("
                SELECT ID, post_title, post_content, post_excerpt, post_date, post_type 
                FROM {$wpdb->posts} 
                WHERE post_status = 'publish' 
                AND post_type IN ('post', 'page')
                AND (
                    post_title LIKE %s 
                    OR post_content LIKE %s 
                    OR post_excerpt LIKE %s
                )
                ORDER BY post_date DESC 
                LIMIT 10
            ", $search_like, $search_like, $search_like);
            
            $current_site_posts = $wpdb->get_results($current_site_sql);
            
            // Debug: Log the SQL query and search term
            if (WP_DEBUG) {
                error_log("FAU Search Debug - Search Term: '$original_search_query'");
                error_log("FAU Search Debug - Search LIKE: '$search_like'");
                error_log("FAU Search Debug - SQL Query: $current_site_sql");
                error_log("FAU Search Debug - Results Count: " . count($current_site_posts));
            }
            
            // Process current site results
            foreach ($current_site_posts as $post) {
                // Get full post data
                $post_obj = get_post($post->ID);
                if (!$post_obj) continue;
                
                $post_title = $post->post_title;
                $post_content = $post->post_content;
                $post_excerpt = $post->post_excerpt;
                
                // Generate excerpt if empty
                if (empty($post_excerpt)) {
                    $post_excerpt = wp_trim_words(strip_tags($post_content), 30, '...');
                }
                
                // Clean and prepare content for validation
                $searchable_content = strtolower(strip_tags($post_title . ' ' . $post_content . ' ' . $post_excerpt));
                $search_term_lower = strtolower($original_search_query);
                
                // More strict validation - check for exact word matches
                $search_term_regex = '/\b' . preg_quote(strtolower($original_search_query), '/') . '\b/i';
                $title_match = preg_match($search_term_regex, strtolower($post_title));
                $content_match = preg_match($search_term_regex, $searchable_content);
                
                // Also keep the simple substring match as fallback
                $title_substring = stripos($post_title, $original_search_query) !== false;
                $content_substring = stripos($searchable_content, $search_term_lower) !== false;
                
                // Require at least a word boundary match OR exact substring match
                $has_match = ($title_match || $content_match) || ($title_substring || $content_substring);
                
                if ($has_match) {
                    $results[] = [
                        'blog_id' => $current_blog_id,
                        'blog_url' => get_site_url(),
                        'blog_name' => get_bloginfo('name'),
                        'title' => $post_title,
                        'link' => get_permalink($post->ID),
                        'excerpt' => $post_excerpt,
                        'date' => date('Y-m-d', strtotime($post->post_date)),
                        'type' => get_post_type_object($post->post_type)->labels->singular_name,
                        'is_current_site' => true
                    ];
                } else {
                    // Debug: Log posts that were found by SQL but rejected by validation
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        error_log("FAU Search Debug - REJECTED Post: ID={$post->ID}, Title='$post_title'");
                        error_log("FAU Search Debug - Content preview: " . substr($searchable_content, 0, 100));
                    }
                }
            }
            wp_reset_postdata();

            // Search other sites (limit to 5 other sites for performance)
            $other_sites_searched = 0;
            foreach ($sites as $site) {
                if ($site->blog_id === $current_blog_id || $other_sites_searched >= 5) {
                    continue;
                }
                
                if (!switch_to_blog($site->blog_id)) {
                    continue;
                }
                
                // Use direct SQL search for other sites as well
                global $wpdb;
                
                // Build SQL search query for this site
                $search_like = '%' . $wpdb->esc_like($original_search_query) . '%';
                $site_sql = $wpdb->prepare("
                    SELECT ID, post_title, post_content, post_excerpt, post_date, post_type 
                    FROM {$wpdb->posts} 
                    WHERE post_status = 'publish' 
                    AND post_type IN ('post', 'page')
                    AND (
                        post_title LIKE %s 
                        OR post_content LIKE %s 
                        OR post_excerpt LIKE %s
                    )
                    ORDER BY post_date DESC 
                    LIMIT 3
                ", $search_like, $search_like, $search_like);
                
                $site_posts = $wpdb->get_results($site_sql);
                
                // Process this site's results
                foreach ($site_posts as $post) {
                    // Get full post data
                    $post_obj = get_post($post->ID);
                    if (!$post_obj) continue;
                    
                    $post_title = $post->post_title;
                    $post_content = $post->post_content;
                    $post_excerpt = $post->post_excerpt;
                    
                    // Generate excerpt if empty
                    if (empty($post_excerpt)) {
                        $post_excerpt = wp_trim_words(strip_tags($post_content), 30, '...');
                    }
                    
                    // Clean and prepare content for validation
                    $searchable_content = strtolower(strip_tags($post_title . ' ' . $post_content . ' ' . $post_excerpt));
                    $search_term_lower = strtolower($original_search_query);
                    
                    // Validate that the search term actually appears
                    $title_match = stripos($post_title, $original_search_query) !== false;
                    $content_match = stripos($searchable_content, $search_term_lower) !== false;
                    
                    // This should always be true with SQL LIKE search, but double-check
                    if ($title_match || $content_match) {
                        $results[] = [
                            'blog_id' => $site->blog_id,
                            'blog_url' => get_site_url(),
                            'blog_name' => get_bloginfo('name'),
                            'title' => $post_title,
                            'link' => get_permalink($post->ID),
                            'excerpt' => $post_excerpt,
                            'date' => date('Y-m-d', strtotime($post->post_date)),
                            'type' => get_post_type_object($post->post_type)->labels->singular_name,
                            'is_current_site' => false
                        ];
                    }
                }
                
                restore_current_blog();
                $other_sites_searched++;
            }

            // Sort results by date, most recent first
            usort($results, function($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });

            $network_results = $results;
        } else {
            $network_results = [];
        }
    }
}

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
            $has_results = $is_fau_wide ? !empty($network_results) : have_posts();
            if ($has_results) {
                $scope_text = $is_fau_wide ? __('FAU-wide Search Results for: %s', 'fau-elemental') : __('Search Results for: %s', 'fau-elemental');
                printf($scope_text, '<span class="search-query">' . get_search_query() . '</span>'); 
            } else {
                $no_results_text = $is_fau_wide ? __('FAU-wide Search Results', 'fau-elemental') : __('Search Results', 'fau-elemental');
                echo $no_results_text;
            }
            ?>
        </h1>
        
        <!-- Search form -->
        <div class="search-form-container">
            <form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
                <div class="search-form-wrapper">
                    <label class="screen-reader-text" for="search-field"><?php _e('Search for:', 'fau-elemental'); ?></label>
                    <input type="search" 
                           id="search-field" 
                           class="search-field" 
                           placeholder="<?php _e('Enter search term...', 'fau-elemental'); ?>" 
                           value="<?php echo get_search_query(); ?>" 
                           name="s" 
                           aria-describedby="search-instructions" />
                    <button type="submit" class="search-submit" aria-label="<?php _e('Submit search', 'fau-elemental'); ?>">
                        <?php _e('Search', 'fau-elemental'); ?>
                    </button>
                </div>
                <div id="search-instructions" class="screen-reader-text">
                    <?php _e('Press Enter to search or use the search button to submit your query', 'fau-elemental'); ?>
                </div>
            </form>
        </div>
    </div>

    <?php 
    // For regular searches, get custom SQL results first to count them properly
    $current_site_custom_results = array();
    if (!$is_fau_wide) {
        global $wpdb;
        $search_query = get_search_query();
        
        // Use the same SQL search logic as FAU-wide search
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
    }
    
    $has_results = $is_fau_wide ? !empty($network_results) : !empty($current_site_custom_results);
    if ($has_results) : ?>
        <div class="search-results" itemscope itemtype="https://schema.org/SearchResultsPage">
            <div class="results-count">
                <p role="status" aria-live="polite"><?php 
                    if ($is_fau_wide) {
                        $total_results = count($network_results);
                        printf(
                            _nx(
                                '%s result found across FAU network',
                                '%s results found across FAU network', 
                                $total_results,
                                'network search results count', 
                                'fau-elemental'
                            ), 
                            number_format_i18n($total_results)
                        );
                    } else {
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
                    }
                ?></p>
            </div>

            <h2 class="screen-reader-text"><?php _e('Search Results List', 'fau-elemental'); ?></h2>
            <div class="search-results-list">
                <?php 
                $result_counter = 1;
                
                if ($is_fau_wide && !empty($network_results)) :
                    // Display network search results
                    foreach ($network_results as $result) : ?>
                        <article class="search-result-item<?php echo $result['is_current_site'] ? ' search-result-item--current-site' : ''; ?>" itemscope itemtype="https://schema.org/Article">
                            <h3 class="screen-reader-text">
                                <?php printf(__('Search Result %d', 'fau-elemental'), $result_counter); ?>
                            </h3>
                            <header class="result-header">
                                <time class="result-date" 
                                      datetime="<?php echo date('c', strtotime($result['date'])); ?>" 
                                      itemprop="datePublished">
                                    <?php echo date('d.m.Y', strtotime($result['date'])); ?>
                                </time>
                                <span class="result-separator" aria-hidden="true"><?php echo $search_config['separator']; ?></span>
                                <span class="result-content-type">
                                    <?php echo esc_html($result['type']); ?>
                                </span>
                                <?php if (!$result['is_current_site']) : ?>
                                    <span class="result-separator" aria-hidden="true"><?php echo $search_config['separator']; ?></span>
                                    <span class="result-site-name">
                                        <?php echo esc_html($result['blog_name']); ?>
                                    </span>
                                <?php endif; ?>
                            </header>
                            
                            <div class="result-content">
                                <div class="result-text">
                                    <h4 class="result-title" itemprop="headline">
                                        <a href="<?php echo esc_url($result['link']); ?>" 
                                           class="result-link" 
                                           itemprop="url">
                                            <?php echo esc_html($result['title']); ?>
                                        </a>
                                    </h4>
                                    
                                    <div class="result-excerpt" itemprop="description">
                                        <?php echo esc_html(wp_trim_words($result['excerpt'], $search_config['excerpt_length'], '...')); ?>
                                    </div>
                                    
                                    <a href="<?php echo esc_url($result['link']); ?>" 
                                       class="result-read-more">
                                        <span class="screen-reader-text"><?php printf(__('Read more about %s', 'fau-elemental'), $result['title']); ?></span>
                                        <span aria-hidden="true"><?php _e('Read more', 'fau-elemental'); ?> <?php echo $search_config['arrow']; ?></span>
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php 
                    $result_counter++;
                    endforeach;
                else :
                    // Display regular site search results using pre-computed custom results
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
                    endforeach;
                endif; ?>
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
