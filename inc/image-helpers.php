<?php
/**
 * Image Helper Functions
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get the fallback image URL
 *
 * @return string The fallback image URL or empty string if not set
 */
function faue_get_fallback_image() {
    return get_theme_mod('faue_fallback_image', '');
}

/**
 * Check if a fallback image is set
 *
 * @return bool True if fallback image is set, false otherwise
 */
function faue_has_fallback_image() {
    return !empty(faue_get_fallback_image());
}

/**
 * Get a fallback image URL for posts/pages without featured images
 *
 * @param int    $post_id The post ID (optional)
 * @param string $size    The image size to retrieve
 * @return string The fallback image URL or default logo URL
 */
function faue_get_post_fallback_image($post_id = null, $size = 'full') {
    // Check if a custom fallback image is set in customizer
    if (faue_has_fallback_image()) {
        $fallback_image_id = attachment_url_to_postid(faue_get_fallback_image());
        if ($fallback_image_id) {
            $image_url = wp_get_attachment_image_url($fallback_image_id, $size);
            if ($image_url) {
                return $image_url;
            }
        }
    }
    
    // Return default logo as last resort
    return get_template_directory_uri() . '/assets/images/logo.svg';
}

/**
 * Handle fallback image changes in customizer
 * This ensures JavaScript gets updated when the setting changes
 */
function faue_customize_save_fallback_image($wp_customize) {
    // Check if fallback image setting was changed
    if (isset($_POST['customized']['faue_fallback_image'])) {
        $new_value = $_POST['customized']['faue_fallback_image'];
        
        // If the value is empty, clear the setting
        if (empty($new_value)) {
            remove_theme_mod('faue_fallback_image');
        }
    }
}
add_action('customize_save_after', 'faue_customize_save_fallback_image');
