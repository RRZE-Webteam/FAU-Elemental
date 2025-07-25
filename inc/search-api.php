<?php
/**
 * Search API Endpoints
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register REST API routes
 */
function fau_register_rest_routes() {
    register_rest_route('fau/v1', '/search-suggestions', array(
        'methods' => 'GET',
        'callback' => 'fau_get_search_suggestions',
        'permission_callback' => 'fau_check_search_rate_limit',
        'args' => array(
            'search' => array(
                'required' => true,
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => 'fau_validate_search_term',
            ),
        ),
    ));
}
add_action('rest_api_init', 'fau_register_rest_routes');

/**
 * Validate search term to prevent abuse
 *
 * @param string $param The search parameter.
 * @param WP_REST_Request $request The request object.
 * @param string $key The parameter key.
 * @return bool|WP_Error True if valid, WP_Error if invalid.
 */
function fau_validate_search_term($param, $request, $key) {
    // Check minimum length
    if (strlen($param) < 2) {
        return new WP_Error('invalid_search_term', __('Search term must be at least 2 characters long.', 'fau-elemental'), array('status' => 400));
    }
    
    // Check maximum length
    $max_length = faue_get_default('faue_search_max_length');
    if (strlen($param) > $max_length) {
        /* translators: %d: maximum number of characters allowed for search term */
        return new WP_Error('invalid_search_term', sprintf(__('Search term must be less than %d characters.', 'fau-elemental'), $max_length), array('status' => 400));
    }
    
    // Check for suspicious patterns
    $suspicious_patterns = array(
        '/[<>]/', // HTML tags
        '/javascript:/i', // JavaScript protocol
        '/data:/i', // Data protocol
        '/vbscript:/i', // VBScript protocol
        '/on\w+\s*=/i', // Event handlers
    );
    
    foreach ($suspicious_patterns as $pattern) {
        if (preg_match($pattern, $param)) {
            return new WP_Error('invalid_search_term', __('Search term contains invalid characters.', 'fau-elemental'), array('status' => 400));
        }
    }
    
    return true;
}

/**
 * Check rate limit for search requests
 *
 * @param WP_REST_Request $request The request object.
 * @return bool|WP_Error True if allowed, WP_Error if rate limited.
 */
function fau_check_search_rate_limit($request) {
    // Check WAF integration first
    $waf_result = fau_waf_integration_hook($request);
    if (is_wp_error($waf_result)) {
        return $waf_result;
    }
    
    // Skip rate limiting if disabled
    if (!get_option('fau_search_rate_limit_enabled', true)) {
        return true;
    }
    
    $client_ip = fau_get_client_ip();
    $rate_limit_key = 'fau_search_rate_limit_' . md5($client_ip);
    $rate_limit_window = faue_get_default('faue_search_rate_limit_window');
    $max_requests = faue_get_default('faue_search_rate_limit_max_requests');
    
    // Get current rate limit data
    $rate_data = get_transient($rate_limit_key);
    if (false === $rate_data) {
        $rate_data = array(
            'count' => 0,
            'window_start' => time(),
        );
    }
    
    // Check if we're in a new time window
    if (time() - $rate_data['window_start'] >= $rate_limit_window) {
        $rate_data = array(
            'count' => 1,
            'window_start' => time(),
        );
    } else {
        $rate_data['count']++;
    }
    
    // Check if rate limit exceeded
    if ($rate_data['count'] > $max_requests) {
        fau_track_rate_limit_violation($client_ip);
        return new WP_Error(
            'rate_limit_exceeded',
            __('Too many search requests. Please try again later.', 'fau-elemental'),
            array('status' => 429)
        );
    }
    
    // Update rate limit data
    set_transient($rate_limit_key, $rate_data, $rate_limit_window);
    
    return true;
}

/**
 * Get client IP address with proxy support
 *
 * @return string The client IP address.
 */
function fau_get_client_ip() {
    $ip_keys = array('HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
    
    foreach ($ip_keys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Get search suggestions using optimized search methods with caching
 *
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response The response object.
 */
function fau_get_search_suggestions($request) {
    $search = $request->get_param('search');
    if (empty($search)) {
        return rest_ensure_response(array());
    }
    
    $client_ip = fau_get_client_ip();
    $was_cached = false;
    
    // Check cache first
    $cache_key = 'fau_search_' . md5($search);
    $cached_results = get_transient($cache_key);
    
    if (false !== $cached_results) {
        $was_cached = true;
        $response = rest_ensure_response($cached_results);
        $cache_duration = faue_get_default('faue_search_cache_duration');
        fau_set_cache_headers($response, $cache_duration);
        fau_log_search_request($search, $client_ip, $was_cached);
        return $response;
    }
    
    $results = array();
    
    // Use WP_Query search with FULLTEXT when available
    $results = fau_wp_query_search($search);
    
    // Cache results using configurable duration
    $cache_duration = faue_get_default('faue_search_cache_duration');
    set_transient($cache_key, $results, $cache_duration);
    
    $response = rest_ensure_response($results);
    fau_set_cache_headers($response, $cache_duration);
    
    // Log the search request
    fau_log_search_request($search, $client_ip, $was_cached);
    
    return $response;
}

/**
 * Set appropriate cache headers for search responses
 *
 * @param WP_REST_Response $response The response object.
 * @param int $cache_duration Cache duration in seconds.
 */
function fau_set_cache_headers($response, $cache_duration) {
    // Use separate browser cache duration (can be much longer than server cache)
    $browser_cache_duration = faue_get_default('faue_search_browser_cache_duration');
    $response->header('Cache-Control', 'public, max-age=' . $browser_cache_duration . ', s-maxage=' . $cache_duration);
    $response->header('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + $browser_cache_duration));
    $response->header('Vary', 'Accept-Encoding');
    
    // Add rate limit headers
    $client_ip = fau_get_client_ip();
    $rate_limit_key = 'fau_search_rate_limit_' . md5($client_ip);
    $rate_data = get_transient($rate_limit_key);
    
    if (false !== $rate_data) {
        $max_requests = faue_get_default('faue_search_rate_limit_max_requests');
        $rate_limit_window = faue_get_default('faue_search_rate_limit_window');
        $remaining = max(0, $max_requests - $rate_data['count']);
        $reset_time = $rate_data['window_start'] + $rate_limit_window;
        
        $response->header('X-RateLimit-Limit', $max_requests);
        $response->header('X-RateLimit-Remaining', $remaining);
        $response->header('X-RateLimit-Reset', $reset_time);
    }
}

/**
 * Check if MySQL FULLTEXT search is supported and index exists
 *
 * @return bool True if FULLTEXT search is available.
 */
function fau_supports_fulltext_search() {
    global $wpdb;
    
    // Check if FULLTEXT index exists on post_title
    $index_exists = $wpdb->get_var("
        SHOW INDEX FROM {$wpdb->posts} 
        WHERE Key_name = 'post_title_fulltext' 
        AND Index_type = 'FULLTEXT'
    ");
    
    return !empty($index_exists);
}
/**
 * Search filter that uses FULLTEXT when available, otherwise optimized LIKE
 *
 * @param string $search The search SQL query.
 * @param WP_Query $query The WP_Query instance.
 * @return string Modified search query.
 */
function fau_search_filter($search, $query) {
    global $wpdb;
    
    if (!empty($search) && !empty($query->query_vars['search_terms'])) {
        $q = $query->query_vars;
        $search = '';
        $searchand = '';
        
        // Check if FULLTEXT search is available
        if (fau_supports_fulltext_search()) {
            // Use FULLTEXT search for better performance
            $search_terms = array_filter($q['search_terms'], function($term) {
                return strlen($term) >= 3; // MySQL FULLTEXT minimum word length
            });
            
            if (!empty($search_terms)) {
                $search_string = implode('* ', $search_terms) . '*';
                $search = $wpdb->prepare(
                    " AND MATCH({$wpdb->posts}.post_title) AGAINST(%s IN BOOLEAN MODE) ",
                    $search_string
                );
            }
        } else {
            // Fallback to optimized LIKE search
            foreach ((array) $q['search_terms'] as $term) {
                $term = $wpdb->esc_like($term);
                
                // Use prefix matching (LIKE 'term%') instead of wildcard (LIKE '%term%')
                // This allows MySQL to use indexes more effectively
                $search .= $wpdb->prepare(
                    "{$searchand}(({$wpdb->posts}.post_title LIKE %s OR {$wpdb->posts}.post_title LIKE %s))",
                    $term . '%',  // Prefix match
                    '% ' . $term . '%'  // Word boundary match
                );
                $searchand = ' AND ';
            }
            
            if (!empty($search)) {
                $search = " AND ({$search}) ";
            }
        }
    }
    
    return $search;
}

/**
 * Filter the core search controller to limit to title-only searches
 * This provides an alternative using WordPress core search API
 */
function fau_filter_core_search_query($query, $request) {
    // Only apply to our custom search endpoint
    if (strpos($request->get_route(), '/fau/v1/search-suggestions') !== false) {
        add_filter('posts_search', 'fau_unified_search_filter', 10, 2);
    }
    
    return $query;
}
add_filter('rest_search_query', 'fau_filter_core_search_query', 10, 2);

/**
 * Create FULLTEXT index on post_title for better search performance
 * This should be run once during theme activation or plugin setup
 */
function fau_create_fulltext_index() {
    global $wpdb;
    
    // Check if index already exists
    $index_exists = $wpdb->get_var("
        SHOW INDEX FROM {$wpdb->posts} 
        WHERE Key_name = 'post_title_fulltext' 
        AND Index_type = 'FULLTEXT'
    ");
    
    if (empty($index_exists)) {
        $result = $wpdb->query("
            ALTER TABLE {$wpdb->posts} 
            ADD FULLTEXT INDEX post_title_fulltext (post_title)
        ");
        
        if ($result !== false) {
            // Log the index creation
            error_log('FAU Elemental: Created FULLTEXT index on post_title for improved search performance');
            
            // Add admin notice
            set_transient('fau_search_performance_notice', true, 60 * 60 * 24); // 24 hours
        } else {
            error_log('FAU Elemental: Failed to create FULLTEXT index on post_title');
        }
    }
}

/**
 * Display admin notice about search performance improvements
 */
function fau_search_performance_admin_notice() {
    if (get_transient('fau_search_performance_notice')) {
        delete_transient('fau_search_performance_notice');
        ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <strong><?php _e('FAU Elemental:', 'fau-elemental'); ?></strong>
                <?php _e('Search performance has been optimized with MySQL FULLTEXT indexing for faster search suggestions.', 'fau-elemental'); ?>
            </p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'fau_search_performance_admin_notice');

/**
 * AJAX handler to manually create FULLTEXT index
 */
function fau_create_fulltext_index_ajax() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'fau_create_fulltext_index')) {
        wp_die('Security check failed');
    }
    
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions');
    }
    
    fau_create_fulltext_index();
    
    if (fau_supports_fulltext_search()) {
        wp_send_json_success(array('message' => __('FULLTEXT index created successfully.', 'fau-elemental')));
    } else {
        wp_send_json_error(array('message' => __('Failed to create FULLTEXT index. Check database permissions.', 'fau-elemental')));
    }
}
add_action('wp_ajax_create_fulltext_index', 'fau_create_fulltext_index_ajax');

/**
 * AJAX handler for search options menu
 */
function fau_elemental_get_search_options_menu() {
    // Verify nonce if provided
    if (isset($_POST['nonce']) && !empty($_POST['nonce'])) {
        if (!wp_verify_nonce($_POST['nonce'], 'fau_elemental_nonce')) {
            wp_die('Security check failed');
        }
    }
    
    // Get the search options menu
    $menu_html = '';
    if (has_nav_menu('search_options_menu')) {
        ob_start();
        wp_nav_menu(array(
            'theme_location' => 'search_options_menu',
            'container' => false,
            'menu_class' => 'fau-global-search__menu-list',
            'depth' => 2,
            'fallback_cb' => false,
        ));
        $menu_html = ob_get_clean();
        
        // Add header if menu exists
        if (!empty($menu_html)) {
            $menu_html = '<div class="fau-global-search__menu-header">' . esc_html__('Search Options', 'fau-elemental') . '</div>' . $menu_html;
        }
    }
    
    if (!empty($menu_html)) {
        wp_send_json_success(array('menu_html' => $menu_html));
    } else {
        wp_send_json_error(array('message' => __('No search options menu found', 'fau-elemental')));
    }
}
add_action('wp_ajax_get_search_options_menu', 'fau_elemental_get_search_options_menu');
add_action('wp_ajax_nopriv_get_search_options_menu', 'fau_elemental_get_search_options_menu');

/**
 * Log search requests for monitoring and abuse detection
 *
 * @param string $search_term The search term.
 * @param string $client_ip The client IP address.
 * @param bool $was_cached Whether the result was served from cache.
 */
function fau_log_search_request($search_term, $client_ip, $was_cached = false) {
    // Only log if logging is enabled
    if (!get_option('fau_search_logging_enabled', false)) {
        return;
    }
    
    $log_entry = array(
        'timestamp' => current_time('mysql'),
        'search_term' => $search_term,
        'client_ip' => $client_ip,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'was_cached' => $was_cached,
        'referer' => $_SERVER['HTTP_REFERER'] ?? '',
    );
    
    // Store in transient for recent searches (last 100)
    $recent_searches = get_transient('fau_recent_searches');
    if (false === $recent_searches) {
        $recent_searches = array();
    }
    
    array_unshift($recent_searches, $log_entry);
    $recent_searches = array_slice($recent_searches, 0, 100); // Keep only last 100
    
    $recent_searches_duration = faue_get_default('faue_search_recent_searches_duration');
    set_transient('fau_recent_searches', $recent_searches, $recent_searches_duration);
    
    // Trigger external monitoring
    fau_monitor_search_request($search_term, $client_ip, $was_cached);
}

/**
 * Clean up old cache entries periodically
 */
function fau_cleanup_search_cache() {
    global $wpdb;
    
    // Clean up old search cache entries (older than configured duration)
    $cache_duration = faue_get_default('faue_search_cache_duration');
    $wpdb->query("
        DELETE FROM {$wpdb->options} 
        WHERE option_name LIKE '_transient_fau_search_%' 
        AND option_value < " . (time() - $cache_duration)
    );
    
    // Clean up expired transients
    $wpdb->query("
        DELETE FROM {$wpdb->options} 
        WHERE option_name LIKE '_transient_timeout_fau_search_%' 
        AND option_value < " . time()
    );
}
add_action('wp_scheduled_delete', 'fau_cleanup_search_cache');

/**
 * Get search abuse protection statistics
 *
 * @return array Statistics about search usage and abuse protection.
 */
function fau_get_search_stats() {
    $stats = array(
        'total_searches' => 0,
        'cached_searches' => 0,
        'rate_limited_requests' => 0,
        'recent_searches' => array(),
    );
    
    // Get recent searches
    $recent_searches = get_transient('fau_recent_searches');
    if (false !== $recent_searches) {
        $stats['recent_searches'] = $recent_searches;
        $stats['total_searches'] = count($recent_searches);
        
        foreach ($recent_searches as $search) {
            if ($search['was_cached']) {
                $stats['cached_searches']++;
            }
        }
    }
    
    // Get rate limit violations (this would need to be tracked separately)
    $rate_limit_violations = get_transient('fau_rate_limit_violations');
    if (false !== $rate_limit_violations) {
        $stats['rate_limited_requests'] = $rate_limit_violations;
    }
    
    return $stats;
}

/**
 * Track rate limit violations
 *
 * @param string $client_ip The client IP that was rate limited.
 */
function fau_track_rate_limit_violation($client_ip) {
    $violations = get_transient('fau_rate_limit_violations');
    if (false === $violations) {
        $violations = 0;
    }
    $violations++;
    $violations_duration = faue_get_default('faue_search_rate_limit_violations_duration');
    set_transient('fau_rate_limit_violations', $violations, $violations_duration);
    
    // Log violation for admin review
    if (get_option('fau_search_logging_enabled', false)) {
        error_log("FAU Search Rate Limit Violation: IP {$client_ip} exceeded rate limit");
    }
}

/**
 * Add admin settings for search abuse protection
 */
function fau_add_search_protection_settings() {
    add_settings_section(
        'fau_search_protection',
        __('Search API Protection', 'fau-elemental'),
        'fau_search_protection_section_callback',
        'reading'
    );
    
    add_settings_field(
        'fau_search_rate_limit_enabled',
        __('Enable Rate Limiting', 'fau-elemental'),
        'fau_search_rate_limit_callback',
        'reading',
        'fau_search_protection'
    );
    
    add_settings_field(
        'fau_search_logging_enabled',
        __('Enable Search Logging', 'fau-elemental'),
        'fau_search_logging_callback',
        'reading',
        'fau_search_protection'
    );
    
    register_setting('reading', 'fau_search_rate_limit_enabled');
    register_setting('reading', 'fau_search_logging_enabled');
}
add_action('admin_init', 'fau_add_search_protection_settings');

/**
 * Settings section callback
 */
function fau_search_protection_section_callback() {
    echo '<p>' . __('Configure abuse protection for the search API endpoints.', 'fau-elemental') . '</p>';
}

/**
 * Rate limit setting callback
 */
function fau_search_rate_limit_callback() {
    $enabled = get_option('fau_search_rate_limit_enabled', true);
    echo '<input type="checkbox" name="fau_search_rate_limit_enabled" value="1" ' . checked(1, $enabled, false) . ' />';
    echo '<p class="description">' . __('Limit search requests to 30 per minute per IP address.', 'fau-elemental') . '</p>';
}

/**
 * Logging setting callback
 */
function fau_search_logging_callback() {
    $enabled = get_option('fau_search_logging_enabled', false);
    echo '<input type="checkbox" name="fau_search_logging_enabled" value="1" ' . checked(1, $enabled, false) . ' />';
    echo '<p class="description">' . __('Log search requests for monitoring and abuse detection.', 'fau-elemental') . '</p>';
}

/**
 * Hook for external WAF integration
 * This allows external security systems to block requests before they reach WordPress
 */
function fau_waf_integration_hook($request) {
    // Allow external systems to hook into our rate limiting
    $client_ip = fau_get_client_ip();
    $search_term = $request->get_param('search');
    
    /**
     * Filter to allow external systems to block search requests
     * 
     * @param bool $allow_request Whether to allow the request.
     * @param string $client_ip The client IP address.
     * @param string $search_term The search term.
     * @param WP_REST_Request $request The request object.
     */
    $allow_request = apply_filters('fau_check_search_request', true, $client_ip, $search_term, $request);
    
    if (!$allow_request) {
        return new WP_Error(
            'waf_blocked',
            __('Request blocked by security system.', 'fau-elemental'),
            array('status' => 403)
        );
    }
    
    return true;
}

/**
 * Add admin menu for search protection
 */
function fau_add_search_protection_menu() {
    add_submenu_page(
        'tools.php',
        __('Search Protection', 'fau-elemental'),
        __('Search Protection', 'fau-elemental'),
        'manage_options',
        'fau-search-protection',
        'fau_search_protection_admin_page'
    );
}
add_action('admin_menu', 'fau_add_search_protection_menu');

/**
 * Admin page for search protection
 */
function fau_search_protection_admin_page() {
    if (isset($_POST['action']) && $_POST['action'] === 'clear_cache') {
        if (wp_verify_nonce($_POST['_wpnonce'], 'fau_clear_search_cache')) {
            fau_clear_all_search_cache();
            echo '<div class="notice notice-success"><p>' . __('Search cache cleared successfully.', 'fau-elemental') . '</p></div>';
        }
    }
    
    $stats = fau_get_search_stats();
    ?>
    <div class="wrap">
        <h1><?php _e('Search API Protection', 'fau-elemental'); ?></h1>
        
        <div class="card">
            <h2><?php _e('Statistics (Last Hour)', 'fau-elemental'); ?></h2>
            <table class="form-table">
                <tr>
                    <th><?php _e('Total Searches:', 'fau-elemental'); ?></th>
                    <td><?php echo esc_html($stats['total_searches']); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Cached Searches:', 'fau-elemental'); ?></th>
                    <td><?php echo esc_html($stats['cached_searches']); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Rate Limited Requests:', 'fau-elemental'); ?></th>
                    <td><?php echo esc_html($stats['rate_limited_requests']); ?></td>
                </tr>
            </table>
        </div>
        
        <div class="card">
            <h2><?php _e('Recent Searches', 'fau-elemental'); ?></h2>
            <?php if (!empty($stats['recent_searches'])): ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Time', 'fau-elemental'); ?></th>
                            <th><?php _e('Search Term', 'fau-elemental'); ?></th>
                            <th><?php _e('IP Address', 'fau-elemental'); ?></th>
                            <th><?php _e('Cached', 'fau-elemental'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats['recent_searches'] as $search): ?>
                            <tr>
                                <td><?php echo esc_html($search['timestamp']); ?></td>
                                <td><?php echo esc_html($search['search_term']); ?></td>
                                <td><?php echo esc_html($search['client_ip']); ?></td>
                                <td><?php echo $search['was_cached'] ? __('Yes', 'fau-elemental') : __('No', 'fau-elemental'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p><?php _e('No recent searches found.', 'fau-elemental'); ?></p>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <h2><?php _e('Cache Management', 'fau-elemental'); ?></h2>
            <form method="post">
                <?php wp_nonce_field('fau_clear_search_cache'); ?>
                <input type="hidden" name="action" value="clear_cache">
                <p><?php _e('Clear all search cache entries. This will force all subsequent searches to be processed fresh.', 'fau-elemental'); ?></p>
                <p class="submit">
                    <input type="submit" class="button button-secondary" value="<?php _e('Clear Search Cache', 'fau-elemental'); ?>">
                </p>
            </form>
        </div>
        
        <div class="card">
            <h2><?php _e('Configuration', 'fau-elemental'); ?></h2>
            <p><?php _e('Configure search protection settings in', 'fau-elemental'); ?> 
                <a href="<?php echo admin_url('options-reading.php#fau_search_protection'); ?>"><?php _e('Settings > Reading', 'fau-elemental'); ?></a>
            </p>
        </div>
    </div>
    <?php
}

/**
 * Clear all search cache entries
 */
function fau_clear_all_search_cache() {
    global $wpdb;
    
    // Delete all search-related transients
    $wpdb->query("
        DELETE FROM {$wpdb->options} 
        WHERE option_name LIKE '_transient_fau_search_%'
    ");
    
    $wpdb->query("
        DELETE FROM {$wpdb->options} 
        WHERE option_name LIKE '_transient_timeout_fau_search_%'
    ");
    
    // Clear rate limit data
    $wpdb->query("
        DELETE FROM {$wpdb->options} 
        WHERE option_name LIKE '_transient_fau_search_rate_limit_%'
    ");
    
    $wpdb->query("
        DELETE FROM {$wpdb->options} 
        WHERE option_name LIKE '_transient_timeout_fau_search_rate_limit_%'
    ");
}

/**
 * AJAX handler to clear search cache
 */
function fau_clear_search_cache_ajax() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'fau_clear_search_cache_ajax')) {
        wp_die('Security check failed');
    }
    
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions');
    }
    
    fau_clear_all_search_cache();
    
    wp_send_json_success(array('message' => __('Search cache cleared successfully.', 'fau-elemental')));
}
add_action('wp_ajax_clear_search_cache', 'fau_clear_search_cache_ajax');

/**
 * Add dashboard widget for search statistics
 */
function fau_add_search_dashboard_widget() {
    wp_add_dashboard_widget(
        'fau_search_stats_widget',
        __('Search API Statistics', 'fau-elemental'),
        'fau_search_dashboard_widget_callback'
    );
}
add_action('wp_dashboard_setup', 'fau_add_search_dashboard_widget');

/**
 * Dashboard widget callback
 */
function fau_search_dashboard_widget_callback() {
    $stats = fau_get_search_stats();
    ?>
    <div class="fau-search-stats">
        <p><strong><?php _e('Last Hour:', 'fau-elemental'); ?></strong></p>
        <ul>
            <li><?php _e('Total Searches:', 'fau-elemental'); ?> <?php echo esc_html($stats['total_searches']); ?></li>
            <li><?php _e('Cached:', 'fau-elemental'); ?> <?php echo esc_html($stats['cached_searches']); ?></li>
            <li><?php _e('Rate Limited:', 'fau-elemental'); ?> <?php echo esc_html($stats['rate_limited_requests']); ?></li>
        </ul>
        <p><a href="<?php echo admin_url('tools.php?page=fau-search-protection'); ?>"><?php _e('View Details', 'fau-elemental'); ?></a></p>
    </div>
    <?php
}

/**
 * Add security headers for search API responses
 */
function fau_add_security_headers() {
    // Only add headers for our search API
    if (strpos($_SERVER['REQUEST_URI'], '/wp-json/fau/v1/search-suggestions') !== false) {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
    }
}
add_action('send_headers', 'fau_add_security_headers');

/**
 * Filter to allow external monitoring systems to track search requests
 */
function fau_monitor_search_request($search_term, $client_ip, $was_cached) {
    /**
     * Action hook for external monitoring systems
     * 
     * @param string $search_term The search term.
     * @param string $client_ip The client IP address.
     * @param bool $was_cached Whether the result was served from cache.
     */
    do_action('fau_search_request_logged', $search_term, $client_ip, $was_cached);
}

// Hook into our logging function
add_action('fau_search_request_logged', 'fau_monitor_search_request', 10, 3);

/**
 * Unified WP_Query search that uses FULLTEXT when available, otherwise optimized LIKE
 * This consolidates both search approaches into a single implementation
 *
 * @param string $search The search term.
 * @return array Search results.
 */
function fau_wp_query_search($search) {
    $results = array();
    
    // Use WP_Query with optimized parameters
    $query_args = array(
        's' => $search,
        'posts_per_page' => 5,
        'post_type' => array('post', 'page'),
        'post_status' => 'publish',
        'orderby' => array(
            'relevance' => 'DESC',
            'date' => 'DESC'
        ),
        'no_found_rows' => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    );
    
    // Add search filter that handles FULLTEXT vs LIKE
    add_filter('posts_search', 'fau_search_filter', 10, 2);
    
    $query = new WP_Query($query_args);
    
    // Remove the filter after query
    remove_filter('posts_search', 'fau_search_filter', 10);
    
    // Cache site name once to avoid repeated function calls
    $site_name = get_bloginfo('name');
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            $post_type_obj = get_post_type_object(get_post_type($post_id));
            
            $results[] = array(
                'title' => get_the_title($post_id),
                'link' => get_permalink($post_id),
                'type' => $post_type_obj ? $post_type_obj->labels->singular_name : get_post_type($post_id),
                'site_name' => $site_name,
                'is_current_site' => true
            );
        }
    }
    
    wp_reset_postdata();
    
    return $results;
}



 