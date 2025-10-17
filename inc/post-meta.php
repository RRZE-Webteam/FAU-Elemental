<?php
/**
 * Post Meta Box Functions
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add meta boxes for post meta
 */
function faue_add_post_meta_boxes() {
    // Only add meta boxes if post meta is enabled in customizer
    if (!function_exists('faue_show_post_meta') || !faue_show_post_meta()) {
        return;
    }
    
    // Add meta box for custom last updated date
    add_meta_box(
        'faue_last_updated',
        __('Last Updated Date', 'fau-elemental'),
        'faue_last_updated_callback',
        'post',
        'side',
        'default'
    );
    
    // Also add to pages
    add_meta_box(
        'faue_last_updated',
        __('Last Updated Date', 'fau-elemental'),
        'faue_last_updated_callback',
        'page',
        'side',
        'default'
    );
    
    // Add meta box for breadcrumb title
    add_meta_box(
        'faue_breadcrumb_title',
        __('Breadcrumb Settings', 'fau-elemental'),
        'faue_breadcrumb_title_callback',
        'post',
        'side',
        'default'
    );
    
    // Also add to pages
    add_meta_box(
        'faue_breadcrumb_title',
        __('Breadcrumb Settings', 'fau-elemental'),
        'faue_breadcrumb_title_callback',
        'page',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'faue_add_post_meta_boxes');

/**
 * Enqueue admin scripts and styles for post meta
 */
function faue_enqueue_post_meta_admin_assets($hook) {
    // Only enqueue on post edit screens
    if (!in_array($hook, ['post.php', 'post-new.php'], true)) {
        return;
    }
    
    // Only enqueue if post meta is enabled
    if (!function_exists('faue_show_post_meta') || !faue_show_post_meta()) {
        return;
    }
    
    // Check if we're editing a post or page
    global $post_type;
    if (!in_array($post_type, ['post', 'page'], true)) {
        return;
    }
    
    wp_enqueue_script(
        'faue-post-meta-admin',
        get_template_directory_uri() . '/build/js/template-parts-post-meta.js',
        ['jquery'],
        wp_get_theme()->get('Version'),
        true
    );
}
add_action('admin_enqueue_scripts', 'faue_enqueue_post_meta_admin_assets');

/**
 * Meta box callback function for last updated date
 */
function faue_last_updated_callback($post) {
    // Add nonce for security
    wp_nonce_field('faue_last_updated_nonce', 'faue_last_updated_nonce');

    // Get current values
    $use_custom_date = get_post_meta($post->ID, '_faue_use_custom_last_updated', true);
    $custom_date = get_post_meta($post->ID, '_faue_custom_last_updated', true);
    
    // Default to current modified date if empty
    if (empty($custom_date)) {
        $custom_date = get_the_modified_date('Y-m-d H:i:s', $post->ID);
    }

    ?>
    <p>
        <input type="checkbox" 
               id="faue_use_custom_date" 
               name="faue_use_custom_last_updated" 
               value="1" 
               <?php checked($use_custom_date, '1'); ?>>
        <label for="faue_use_custom_date">
            <?php esc_html_e('Use custom last updated date', 'fau-elemental'); ?>
        </label>
    </p>
    <p class="custom-date-field" style="<?php echo $use_custom_date ? '' : 'display: none;'; ?>">
        <label for="faue_custom_last_updated">
            <?php esc_html_e('Custom date:', 'fau-elemental'); ?>
        </label>
        <input type="datetime-local" 
               name="faue_custom_last_updated" 
               id="faue_custom_last_updated" 
               value="<?php echo esc_attr(str_replace(' ', 'T', substr($custom_date, 0, 16))); ?>"
               class="widefat">
    </p>
    <?php
}

/**
 * Meta box callback function for breadcrumb title
 */
function faue_breadcrumb_title_callback($post) {
    // Add nonce for security
    wp_nonce_field('faue_breadcrumb_title_nonce', 'faue_breadcrumb_title_nonce');

    // Get current value
    $breadcrumb_title = get_post_meta($post->ID, '_fau_breadcrumb_title', true);

    ?>
    <p>
        <label for="faue_breadcrumb_title">
            <?php esc_html_e('Custom Breadcrumb Title', 'fau-elemental'); ?>
        </label>
        <input type="text" 
               id="faue_breadcrumb_title" 
               name="faue_breadcrumb_title" 
               value="<?php echo esc_attr($breadcrumb_title); ?>" 
               class="widefat">
    </p>
    <p class="description">
        <?php esc_html_e('Set a custom title for breadcrumb navigation. Leave empty to use the post/page title.', 'fau-elemental'); ?>
    </p>
    <?php
}

/**
 * Validate and sanitize datetime input
 */
function faue_validate_datetime($datetime_string) {
    if (empty($datetime_string)) {
        return false;
    }
    
    // Try to create DateTime object from the format
    $datetime = DateTime::createFromFormat('Y-m-d\TH:i', $datetime_string);
    
    // Check if the date was created successfully and matches the input
    if ($datetime && $datetime->format('Y-m-d\TH:i') === $datetime_string) {
        return $datetime->format('Y-m-d H:i:s');
    }
    
    return false;
}

/**
 * Save meta box data
 */
function faue_save_post_meta($post_id) {
    // Check for revision or wrong post type early
    if (wp_is_post_revision($post_id) || !in_array(get_post_type($post_id), ['post', 'page'], true)) {
        return;
    }
    
    // Check if nonce is set (either for last updated or breadcrumb title)
    if (!isset($_POST['faue_last_updated_nonce']) && !isset($_POST['faue_breadcrumb_title_nonce'])) {
        return;
    }

    // Verify nonce for last updated date
    if (isset($_POST['faue_last_updated_nonce']) && !wp_verify_nonce($_POST['faue_last_updated_nonce'], 'faue_last_updated_nonce')) {
        return;
    }
    
    // Verify nonce for breadcrumb title
    if (isset($_POST['faue_breadcrumb_title_nonce']) && !wp_verify_nonce($_POST['faue_breadcrumb_title_nonce'], 'faue_breadcrumb_title_nonce')) {
        return;
    }

    // If this is an autosave, don't do anything
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check user permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save custom last updated date
    if (isset($_POST['faue_last_updated_nonce'])) {
        $use_custom_date = isset($_POST['faue_use_custom_last_updated']) ? '1' : '0';
        update_post_meta($post_id, '_faue_use_custom_last_updated', $use_custom_date);

        if ($use_custom_date === '1' && isset($_POST['faue_custom_last_updated'])) {
            $custom_date = sanitize_text_field($_POST['faue_custom_last_updated']);
            
            // Validate the date format properly
            $validated_date = faue_validate_datetime($custom_date);
            if ($validated_date) {
                update_post_meta($post_id, '_faue_custom_last_updated', $validated_date);
            }
        } else {
            // Remove custom date if not using custom date
            delete_post_meta($post_id, '_faue_custom_last_updated');
        }
    }
    
    // Save custom breadcrumb title
    if (isset($_POST['faue_breadcrumb_title_nonce'])) {
        if (isset($_POST['faue_breadcrumb_title'])) {
            $breadcrumb_title = sanitize_text_field($_POST['faue_breadcrumb_title']);
            if (!empty($breadcrumb_title)) {
                update_post_meta($post_id, '_fau_breadcrumb_title', $breadcrumb_title);
            } else {
                delete_post_meta($post_id, '_fau_breadcrumb_title');
            }
        }
    }
}
add_action('save_post', 'faue_save_post_meta');

/**
 * Filter the modified date to use custom date if set
 */
function faue_filter_modified_date($date, $format, $post) {
    if (!$post || !isset($post->ID)) {
        return $date;
    }
    
    $post_id = $post->ID;
    $use_custom_date = get_post_meta($post_id, '_faue_use_custom_last_updated', true);
    
    if ($use_custom_date === '1') {
        $custom_date = get_post_meta($post_id, '_faue_custom_last_updated', true);
        if (!empty($custom_date)) {
            $timestamp = strtotime($custom_date);
            if ($timestamp !== false) {
                return wp_date($format, $timestamp, wp_timezone());
            }
        }
    }
    
    return $date;
}
add_filter('get_the_modified_date', 'faue_filter_modified_date', 10, 3);

/**
 * Filter the modified time as well to be consistent
 */
function faue_filter_modified_time($time, $format, $post) {
    if (!$post || !isset($post->ID)) {
        return $time;
    }
    
    $post_id = $post->ID;
    $use_custom_date = get_post_meta($post_id, '_faue_use_custom_last_updated', true);
    
    if ($use_custom_date === '1') {
        $custom_date = get_post_meta($post_id, '_faue_custom_last_updated', true);
        if (!empty($custom_date)) {
            $timestamp = strtotime($custom_date);
            if ($timestamp !== false) {
                return wp_date($format, $timestamp, wp_timezone());
            }
        }
    }
    
    return $time;
}
add_filter('get_the_modified_time', 'faue_filter_modified_time', 10, 3);

