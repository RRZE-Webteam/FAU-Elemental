<?php
/**
 * Page Settings
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add breadcrumb mode meta box
 */
function faue_add_breadcrumb_mode_meta_box() {
    add_meta_box(
        'faue_breadcrumb_mode',
        __('Breadcrumb Mode', 'fau-elemental'),
        'faue_breadcrumb_mode_callback',
        'page',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'faue_add_breadcrumb_mode_meta_box');

/**
 * Breadcrumb mode meta box callback
 */
function faue_breadcrumb_mode_callback($post) {
    wp_nonce_field('faue_breadcrumb_mode', 'faue_breadcrumb_mode_nonce');
    
    $mode = get_post_meta($post->ID, '_faue_breadcrumb_mode', true);
    if (empty($mode)) {
        $mode = 'light'; // Default to light mode
    }
    ?>
    <select name="faue_breadcrumb_mode" id="faue_breadcrumb_mode">
        <option value="light" <?php selected($mode, 'light'); ?>><?php _e('Light', 'fau-elemental'); ?></option>
        <option value="dark" <?php selected($mode, 'dark'); ?>><?php _e('Dark', 'fau-elemental'); ?></option>
    </select>
    <?php
}

/**
 * Save breadcrumb mode meta box data
 */
function faue_save_breadcrumb_mode_meta_box($post_id) {
    if (!isset($_POST['faue_breadcrumb_mode_nonce'])) {
        return;
    }

    if (!wp_verify_nonce($_POST['faue_breadcrumb_mode_nonce'], 'faue_breadcrumb_mode')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['faue_breadcrumb_mode'])) {
        update_post_meta($post_id, '_faue_breadcrumb_mode', sanitize_text_field($_POST['faue_breadcrumb_mode']));
    }
}
add_action('save_post', 'faue_save_breadcrumb_mode_meta_box'); 