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
 * Validate and sanitize search term
 * 
 * This function performs basic validation and sanitization without blocking legitimate searches.
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
    
    // Basic sanitization: remove null bytes and excessive whitespace
    $sanitized = str_replace("\0", '', $param);
    $sanitized = preg_replace('/\s+/', ' ', trim($sanitized));
    
    // If sanitization changed the input significantly, reject it
    if (strlen($sanitized) < 2) {
        return new WP_Error('invalid_search_term', __('Search term is too short after sanitization.', 'fau-elemental'), array('status' => 400));
    }
    
    // Update the request parameter with sanitized value
    $request->set_param($key, $sanitized);
    
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
    
    $was_cached = false;
    
    // Check cache first
    $cache_key = 'fau_search_' . md5($search);
    $cached_results = get_transient($cache_key);
    
    if (false !== $cached_results) {
        $was_cached = true;
        $response = rest_ensure_response($cached_results);
        $cache_duration = faue_get_default('faue_search_cache_duration');
        fau_set_cache_headers($response, $cache_duration);
        fau_log_search_request($search, fau_get_client_ip(), $was_cached);
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
    fau_log_search_request($search, fau_get_client_ip(), $was_cached);
    
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
    
    // Check if MySQL version supports FULLTEXT (5.6+)
    $mysql_version = $wpdb->db_version();
    if (version_compare($mysql_version, '5.6', '<')) {
        return false;
    }
    
    // Check if the posts table uses a storage engine that supports FULLTEXT
    $table_info = $wpdb->get_row("SHOW CREATE TABLE {$wpdb->posts}");
    if (!$table_info) {
        return false;
    }
    
    // Check if the table uses InnoDB or MyISAM (both support FULLTEXT)
    return strpos($table_info->{'Create Table'}, 'ENGINE=InnoDB') !== false || 
           strpos($table_info->{'Create Table'}, 'ENGINE=MyISAM') !== false;
}

/**
 * Check if the FULLTEXT index exists on post_title
 *
 * @return bool True if index exists, false otherwise.
 */
function fau_has_fulltext_index() {
    global $wpdb;
    
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
        
        // Check if FULLTEXT search is available and index exists
        if (fau_supports_fulltext_search() && fau_has_fulltext_index()) {
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
        add_filter('posts_search', 'fau_search_filter', 10, 2);
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
            
            // Clear any failure notices since we succeeded
            delete_option('fau_search_index_failed');
        } else {
            // Log the failure
            error_log('FAU Elemental: Failed to create FULLTEXT index on post_title');
            
            // Set persistent failure flag
            update_option('fau_search_index_failed', true);
        }
    }
}

/**
 * Display admin notice about search performance issues
 */
function fau_search_performance_admin_notice() {
    // Only show to administrators
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Check if FULLTEXT search is supported but index is missing
    if (fau_supports_fulltext_search() && !fau_has_fulltext_index()) {
        ?>
        <div class="notice notice-warning is-dismissible">
            <p>
                <strong><?php _e('FAU Elemental:', 'fau-elemental'); ?></strong>
                <?php _e('Search performance can be improved with MySQL FULLTEXT indexing. The index is missing and should be created for optimal search performance.', 'fau-elemental'); ?>
                <a href="<?php echo admin_url('tools.php?page=fau-search-protection'); ?>" class="button button-small"><?php _e('Create Index', 'fau-elemental'); ?></a>
            </p>
        </div>
        <?php
    }
    
    // Check if index creation failed
    if (get_option('fau_search_index_failed', false)) {
        ?>
        <div class="notice notice-error is-dismissible">
            <p>
                <strong><?php _e('FAU Elemental:', 'fau-elemental'); ?></strong>
                <?php _e('Failed to create MySQL FULLTEXT index for search optimization. This may be due to insufficient database permissions or MySQL configuration. Search performance may be degraded.', 'fau-elemental'); ?>
                <a href="<?php echo admin_url('tools.php?page=fau-search-protection'); ?>" class="button button-small"><?php _e('Retry', 'fau-elemental'); ?></a>
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
 * Track recent searches for admin statistics (always enabled)
 *
 * @param string $search_term The search term.
 * @param string $client_ip The client IP address.
 * @param bool $was_cached Whether the result was served from cache.
 */
function fau_track_recent_search($search_term, $client_ip, $was_cached = false) {
    $log_entry = array(
        'timestamp' => current_time('mysql'),
        'search_term' => $search_term,
        'client_ip' => $client_ip,
        'was_cached' => $was_cached,
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
}

/**
 * Log search requests for monitoring and abuse detection (detailed logging)
 *
 * @param string $search_term The search term.
 * @param string $client_ip The client IP address.
 * @param bool $was_cached Whether the result was served from cache.
 */
function fau_log_search_request($search_term, $client_ip, $was_cached = false) {
    // Always track recent searches for admin interface
    fau_track_recent_search($search_term, $client_ip, $was_cached);
    
    // Only do detailed logging if logging is enabled
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
    
    // Store detailed logs in a separate transient for admin review
    $detailed_logs = get_transient('fau_detailed_search_logs');
    if (false === $detailed_logs) {
        $detailed_logs = array();
    }
    
    array_unshift($detailed_logs, $log_entry);
    $detailed_logs = array_slice($detailed_logs, 0, 200); // Keep last 200 detailed logs
    
    $logs_duration = faue_get_default('faue_search_recent_searches_duration');
    set_transient('fau_detailed_search_logs', $detailed_logs, $logs_duration);
    
    /**
     * Action hook for external monitoring systems
     * 
     * @param string $search_term The search term.
     * @param string $client_ip The client IP address.
     * @param bool $was_cached Whether the result was served from cache.
     */
    do_action('fau_search_request_logged', $search_term, $client_ip, $was_cached);
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
        'fau-search-protection'
    );
    
    add_settings_field(
        'fau_search_rate_limit_enabled',
        __('Enable Rate Limiting', 'fau-elemental'),
        'fau_search_rate_limit_callback',
        'fau-search-protection',
        'fau_search_protection'
    );
    
    add_settings_field(
        'fau_search_logging_enabled',
        __('Enable Search Logging', 'fau-elemental'),
        'fau_search_logging_callback',
        'fau-search-protection',
        'fau_search_protection'
    );
    
    register_setting('fau-search-protection', 'fau_search_rate_limit_enabled');
    register_setting('fau-search-protection', 'fau_search_logging_enabled');
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
    echo '<p class="description">' . __('Limit search requests to 20 per 10 seconds per IP address.', 'fau-elemental') . '</p>';
}

/**
 * Logging setting callback
 */
function fau_search_logging_callback() {
    $enabled = get_option('fau_search_logging_enabled', false);
    echo '<input type="checkbox" name="fau_search_logging_enabled" value="1" ' . checked(1, $enabled, false) . ' />';
    echo '<p class="description">' . __('Enable detailed logging with user agent, referer, and other metadata for monitoring and abuse detection. Recent searches are always tracked for admin statistics.', 'fau-elemental') . '</p>';
    
    // Show current logging status
    if ($enabled) {
        echo '<p class="description"><strong>' . __('Status:', 'fau-elemental') . '</strong> <span class="fau-status-success">' . __('Detailed logging is enabled', 'fau-elemental') . '</span></p>';
    } else {
        echo '<p class="description"><strong>' . __('Status:', 'fau-elemental') . '</strong> <span class="fau-status-info">' . __('Only basic search tracking is active', 'fau-elemental') . '</span></p>';
    }
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
    $hookname = add_submenu_page(
        'tools.php',
        __('Search Protection', 'fau-elemental'),
        __('Search Protection', 'fau-elemental'),
        'manage_options',
        'fau-search-protection',
        'fau_search_protection_admin_page'
    );
    
    // Add admin styles for this page
    add_action('load-' . $hookname, 'fau_search_protection_admin_styles');
}
add_action('admin_menu', 'fau_add_search_protection_menu');

/**
 * Enqueue admin styles and scripts for search protection page
 */
function fau_search_protection_admin_styles() {
    wp_enqueue_style(
        'fau-search-protection-admin',
        get_template_directory_uri() . '/build/css/admin.css',
        array(),
        '1.0.0'
    );
    
    wp_enqueue_script(
        'fau-search-protection-admin',
        get_template_directory_uri() . '/components/admin/search-protection-admin.js',
        array('jquery'),
        '1.0.0',
        true
    );
    
    wp_localize_script('fau-search-protection-admin', 'fauSearchProtection', array(
        'fulltextNonce' => wp_create_nonce('fau_create_fulltext_index')
    ));
}



/**
 * Admin page for search protection
 */
function fau_search_protection_admin_page() {
    $stats = fau_get_search_stats();
    ?>
    <div class="wrap">
        <h1><?php _e('Search API Protection', 'fau-elemental'); ?></h1>
        
        <form method="post" action="options.php">
            <?php
            settings_fields('fau-search-protection');
            do_settings_sections('fau-search-protection');
            submit_button();
            ?>
        </form>
        
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
            <h2><?php _e('FULLTEXT Index Management', 'fau-elemental'); ?></h2>
            <?php
            $supports_fulltext = fau_supports_fulltext_search();
            $has_index = fau_has_fulltext_index();
            ?>
            <table class="form-table">
                <tr>
                    <th><?php _e('MySQL FULLTEXT Support:', 'fau-elemental'); ?></th>
                    <td>
                        <?php if ($supports_fulltext): ?>
                            <span class="fau-status-success">✓ <?php _e('Supported', 'fau-elemental'); ?></span>
                        <?php else: ?>
                            <span class="fau-status-error">✗ <?php _e('Not Supported', 'fau-elemental'); ?></span>
                            <p class="description"><?php _e('Your MySQL version or storage engine does not support FULLTEXT indexes.', 'fau-elemental'); ?></p>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('FULLTEXT Index Status:', 'fau-elemental'); ?></th>
                    <td>
                        <?php if ($has_index): ?>
                            <span class="fau-status-success">✓ <?php _e('Index Exists', 'fau-elemental'); ?></span>
                        <?php else: ?>
                            <span class="fau-status-warning">⚠ <?php _e('Index Missing', 'fau-elemental'); ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
            
            <?php if ($supports_fulltext && !$has_index): ?>
                <div id="create-index-section">
                    <p><?php _e('Create a FULLTEXT index on post titles to improve search performance:', 'fau-elemental'); ?></p>
                    <p class="submit">
                        <button type="button" id="create-fulltext-index" class="button button-primary">
                            <?php _e('Create FULLTEXT Index', 'fau-elemental'); ?>
                        </button>
                        <span id="create-index-status"></span>
                    </p>
                </div>
                

            <?php elseif ($has_index): ?>
                <p><span class="fau-status-success">✓ <?php _e('FULLTEXT index is already created and working.', 'fau-elemental'); ?></span></p>
            <?php else: ?>
                <p><span class="fau-status-error">✗ <?php _e('FULLTEXT indexes are not supported on your system.', 'fau-elemental'); ?></span></p>
            <?php endif; ?>
        </div>
        

    </div>
    <?php
}





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
 * Log search requests from regular WordPress search pages
 */
function fau_log_wordpress_search() {
    // Only log on search results pages
    if (!is_search()) {
        return;
    }
    
    $search_query = get_search_query();
    if (empty($search_query)) {
        return;
    }
    
    // Log the search request
    fau_log_search_request($search_query, fau_get_client_ip(), false);
}
add_action('wp', 'fau_log_wordpress_search');



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



 
 