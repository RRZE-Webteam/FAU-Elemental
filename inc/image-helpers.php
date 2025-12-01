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
    
    // Return default fallback image as last resort
    return get_template_directory_uri() . '/assets/images/Default_FAU_Schloss_blau.jpg';
}

/**
 * Get responsive fallback image HTML for posts/pages without featured images
 *
 * @param int    $post_id The post ID (optional)
 * @param string $alt     The alt text for the image
 * @param string $size    The image size to retrieve
 * @param array  $attr    Additional attributes for the image
 * @return string|null The responsive image HTML or null if fallback is not an attachment
 */
function faue_get_post_fallback_image_html($post_id = null, $alt = '', $size = 'medium_large', $attr = []) {
    // Check if a custom fallback image is set in customizer
    if (faue_has_fallback_image()) {
        $fallback_image_id = attachment_url_to_postid(faue_get_fallback_image());
        if ($fallback_image_id) {
            // Merge default attributes with provided ones
            $default_attr = [
                'alt' => $alt,
                'loading' => 'lazy',
            ];
            $attr = array_merge($default_attr, $attr);
            
            // Use wp_get_attachment_image for responsive images with srcset
            return wp_get_attachment_image($fallback_image_id, $size, false, $attr);
        }
    }
    
    // Return null if fallback is not an attachment (e.g., SVG logo)
    return null;
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
