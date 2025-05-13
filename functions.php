<?php
/**
 * FAU Elemental Theme Functions
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

// Theme setup and core functionality
require_once get_template_directory() . '/inc/theme-setup.php';

// Asset management
require_once get_template_directory() . '/inc/enqueue-assets.php';

// Block functionality
require_once get_template_directory() . '/inc/blocks/loader.php';
require_once get_template_directory() . '/inc/block-patterns.php';

// Theme settings
require_once get_template_directory() . '/inc/theme-settings.php';

require_once get_template_directory() . '/inc/customizer.php';

// Include post meta functionality
require_once get_template_directory() . '/inc/post-meta.php';

/**
 * Replace block template parts with PHP template parts if they exist
 */
add_filter('render_block_core/template-part', function($block_content, $block) {
    if (isset($block['attrs']['slug'])) {
        $slug = $block['attrs']['slug'];
        
        // Check if we have a PHP template for this part
        $template_path = get_template_directory() . '/template-parts/' . $slug . '.php';
        
        if (file_exists($template_path)) {
            ob_start();
            include $template_path;
            return ob_get_clean();
        }
        
        // Special handling for post-meta
        if ($slug === 'post-meta') {
            if (!function_exists('faue_show_post_meta') || !faue_show_post_meta()) {
                return ''; // Don't render it if disabled
            }
        }
    }
    
    return $block_content;
}, 10, 2);

/**
 * Custom Last Updated Date Meta Box
 */
function faue_add_last_updated_meta_box() {
    add_meta_box(
        'faue_last_updated_meta_box',
        __('Custom Last Updated Date', 'fau-elemental'),
        'faue_render_last_updated_meta_box',
        'post', // Add to posts
        'side', // Show in sidebar
        'default' // Priority
    );
    
    // Also add to pages
    add_meta_box(
        'faue_last_updated_meta_box',
        __('Custom Last Updated Date', 'fau-elemental'),
        'faue_render_last_updated_meta_box',
        'page',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'faue_add_last_updated_meta_box');

/**
 * Render the Last Updated meta box
 */
function faue_render_last_updated_meta_box($post) {
    // Add nonce for security
    wp_nonce_field('faue_save_last_updated_meta', 'faue_last_updated_nonce');
    
    // Get current value
    $custom_date = get_post_meta($post->ID, '_faue_custom_last_updated', true);
    $use_custom_date = get_post_meta($post->ID, '_faue_use_custom_last_updated', true);
    
    // Default to current modified date if empty
    if (empty($custom_date)) {
        $custom_date = get_the_modified_date('Y-m-d H:i:s', $post->ID);
    }
    
    // Output field
    ?>
    <p>
        <input type="checkbox" id="faue_use_custom_date" name="faue_use_custom_date" value="1" <?php checked($use_custom_date, '1'); ?> />
        <label for="faue_use_custom_date"><?php _e('Override default last updated date', 'fau-elemental'); ?></label>
    </p>
    
    <p>
        <label for="faue_custom_last_updated"><?php _e('Date and time:', 'fau-elemental'); ?></label><br>
        <input type="datetime-local" id="faue_custom_last_updated" name="faue_custom_last_updated" 
               value="<?php echo esc_attr(str_replace(' ', 'T', substr($custom_date, 0, 16))); ?>" 
               class="widefat" <?php echo $use_custom_date ? '' : 'disabled'; ?> />
    </p>
    
    <script>
    jQuery(document).ready(function($) {
        $('#faue_use_custom_date').change(function() {
            if ($(this).is(':checked')) {
                $('#faue_custom_last_updated').prop('disabled', false);
            } else {
                $('#faue_custom_last_updated').prop('disabled', true);
            }
        });
    });
    </script>
    <?php
}

/**
 * Save the Last Updated meta box data
 */
function faue_save_last_updated_meta($post_id) {
    // Check if nonce is valid
    if (!isset($_POST['faue_last_updated_nonce']) || !wp_verify_nonce($_POST['faue_last_updated_nonce'], 'faue_save_last_updated_meta')) {
        return;
    }
    
    // Check if user has permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Check if autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Save use custom date checkbox
    $use_custom_date = isset($_POST['faue_use_custom_date']) ? '1' : '0';
    update_post_meta($post_id, '_faue_use_custom_last_updated', $use_custom_date);
    
    // Save custom date if provided
    if (isset($_POST['faue_custom_last_updated']) && !empty($_POST['faue_custom_last_updated'])) {
        $custom_date = sanitize_text_field($_POST['faue_custom_last_updated']);
        // Convert HTML datetime-local format (2023-04-25T15:30) to MySQL format (2023-04-25 15:30:00)
        $custom_date = str_replace('T', ' ', $custom_date) . ':00';
        update_post_meta($post_id, '_faue_custom_last_updated', $custom_date);
    }
}
add_action('save_post', 'faue_save_last_updated_meta');

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

