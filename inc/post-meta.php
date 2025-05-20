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
}
add_action('add_meta_boxes', 'faue_add_post_meta_boxes');

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
    <script>
    jQuery(document).ready(function($) {
        $('input[name="faue_use_custom_last_updated"]').on('change', function() {
            $('.custom-date-field').toggle(this.checked);
        });
    });
    </script>
    <?php
}

/**
 * Save meta box data
 */
function faue_save_post_meta($post_id) {

   
    
    // Check if nonce is set
    if (!isset($_POST['faue_last_updated_nonce'])) {
       
        return;
    }

    // Verify nonce
    if (!wp_verify_nonce($_POST['faue_last_updated_nonce'], 'faue_last_updated_nonce')) {
     
        return;
    }

    // If this is an autosave, don't do anything
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
       
        return;
    }

    // Check user permissions
    if (!current_user_can('edit_post', $post_id)) {
        error_log('User does not have permission to edit post');
        return;
    }

    // Save custom last updated date
    $use_custom_date = isset($_POST['faue_use_custom_last_updated']) ? '1' : '0';

    
    update_post_meta($post_id, '_faue_use_custom_last_updated', $use_custom_date);
  

    if ($use_custom_date === '1' && isset($_POST['faue_custom_last_updated'])) {
        $custom_date = sanitize_text_field($_POST['faue_custom_last_updated']);
        // Convert HTML datetime-local format to MySQL format
        $custom_date = str_replace('T', ' ', $custom_date) . ':00';

        
        update_post_meta($post_id, '_faue_custom_last_updated', $custom_date);
       
    }
}
add_action('save_post', 'faue_save_post_meta');

/**
 * Filter the modified date to use custom date if set
 */
function faue_filter_modified_date($date, $format, $post) {
    $post_id = $post->ID;
    $use_custom_date = get_post_meta($post_id, '_faue_use_custom_last_updated', true);
    
    if ($use_custom_date === '1') {
        $custom_date = get_post_meta($post_id, '_faue_custom_last_updated', true);
        if (!empty($custom_date)) {
            $date = mysql2date($format, $custom_date);
        }
    }
    
    return $date;
}
add_filter('get_the_modified_date', 'faue_filter_modified_date', 10, 3);

/**
 * Filter the modified time as well to be consistent
 */
function faue_filter_modified_time($time, $format, $post) {
    $post_id = $post->ID;
    $use_custom_date = get_post_meta($post_id, '_faue_use_custom_last_updated', true);
    
    if ($use_custom_date === '1') {
        $custom_date = get_post_meta($post_id, '_faue_custom_last_updated', true);
        if (!empty($custom_date)) {
            $time = mysql2date($format, $custom_date);
        }
    }
    
    return $time;
}
add_filter('get_the_modified_time', 'faue_filter_modified_time', 10, 3);

