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

    // Add meta box for post meta style
    add_meta_box(
        'faue_post_meta_style',
        __('Post Meta Style', 'fau-elemental'),
        'faue_post_meta_style_callback',
        'post',
        'side',
        'default'
    );

    // Add meta box for custom last updated date
    add_meta_box(
        'faue_last_updated',
        __('Last Updated Date', 'fau-elemental'),
        'faue_last_updated_callback',
        'post',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'faue_add_post_meta_boxes');

/**
 * Meta box callback function for post meta style
 */
function faue_post_meta_style_callback($post) {
    // Add nonce for security
    wp_nonce_field('faue_post_meta_style_nonce', 'faue_post_meta_style_nonce');

    // Get current value
    $style = get_post_meta($post->ID, '_faue_post_meta_style', true);
    ?>
    <p>
        <label for="faue_post_meta_style">
            <?php esc_html_e('Select post meta style:', 'fau-elemental'); ?>
        </label>
        <select name="faue_post_meta_style" id="faue_post_meta_style">
            <option value="" <?php selected($style, ''); ?>><?php esc_html_e('Light (Default)', 'fau-elemental'); ?></option>
            <option value="is-style-dark" <?php selected($style, 'is-style-dark'); ?>><?php esc_html_e('Dark', 'fau-elemental'); ?></option>
        </select>
    </p>
    <?php
}

/**
 * Meta box callback function for last updated date
 */
function faue_last_updated_callback($post) {
    // Add nonce for security
    wp_nonce_field('faue_last_updated_nonce', 'faue_last_updated_nonce');

    // Get current values
    $use_custom_date = get_post_meta($post->ID, '_faue_use_custom_last_updated', true);
    $custom_date = get_post_meta($post->ID, '_faue_custom_last_updated', true);
    ?>
    <p>
        <label>
            <input type="checkbox" name="faue_use_custom_last_updated" value="1" <?php checked($use_custom_date, '1'); ?>>
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
               value="<?php echo esc_attr($custom_date); ?>"
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
    if (!isset($_POST['faue_post_meta_style_nonce']) || !isset($_POST['faue_last_updated_nonce'])) {
        return;
    }

    // Verify nonces
    if (!wp_verify_nonce($_POST['faue_post_meta_style_nonce'], 'faue_post_meta_style_nonce') ||
        !wp_verify_nonce($_POST['faue_last_updated_nonce'], 'faue_last_updated_nonce')) {
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

    // Save post meta style
    if (isset($_POST['faue_post_meta_style'])) {
        update_post_meta(
            $post_id,
            '_faue_post_meta_style',
            sanitize_text_field($_POST['faue_post_meta_style'])
        );
    }

    // Save custom last updated date
    $use_custom_date = isset($_POST['faue_use_custom_last_updated']) ? '1' : '0';
    update_post_meta($post_id, '_faue_use_custom_last_updated', $use_custom_date);

    if ($use_custom_date === '1' && isset($_POST['faue_custom_last_updated'])) {
        update_post_meta(
            $post_id,
            '_faue_custom_last_updated',
            sanitize_text_field($_POST['faue_custom_last_updated'])
        );
    }
}
add_action('save_post', 'faue_save_post_meta'); 

