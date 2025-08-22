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
 * Get the fallback image HTML
 *
 * @param string $size The image size to retrieve
 * @param array  $attr Additional attributes for the image
 * @return string The fallback image HTML or empty string if not set
 */
function faue_get_fallback_image_html($size = 'full', $attr = array()) {
    $fallback_image_id = attachment_url_to_postid(faue_get_fallback_image());
    
    if ($fallback_image_id) {
        return wp_get_attachment_image($fallback_image_id, $size, false, $attr);
    }
    
    return '';
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
    // First check if the post has a featured image
    if ($post_id && has_post_thumbnail($post_id)) {
        $thumbnail_id = get_post_thumbnail_id($post_id);
        $image_url = wp_get_attachment_image_url($thumbnail_id, $size);
        if ($image_url) {
            return $image_url;
        }
    }
    
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
 * Get a fallback image HTML for posts/pages without featured images
 *
 * @param int    $post_id The post ID (optional)
 * @param string $size    The image size to retrieve
 * @param array  $attr    Additional attributes for the image
 * @return string The fallback image HTML
 */
function faue_get_post_fallback_image_html($post_id = null, $size = 'full', $attr = array()) {
    // First check if the post has a featured image
    if ($post_id && has_post_thumbnail($post_id)) {
        $thumbnail_id = get_post_thumbnail_id($post_id);
        return wp_get_attachment_image($thumbnail_id, $size, false, $attr);
    }
    
    // Check if a custom fallback image is set in customizer
    if (faue_has_fallback_image()) {
        $fallback_image_id = attachment_url_to_postid(faue_get_fallback_image());
        if ($fallback_image_id) {
            return wp_get_attachment_image($fallback_image_id, $size, false, $attr);
        }
    }
    
    // Return default logo as last resort
    $default_logo_url = get_template_directory_uri() . '/assets/images/logo.svg';
    $default_alt = __('Default logo', 'fau-elemental');
    
    $attr = wp_parse_args($attr, array(
        'alt' => $default_alt,
        'loading' => 'lazy'
    ));
    
    return sprintf(
        '<img src="%s" alt="%s" loading="%s">',
        esc_url($default_logo_url),
        esc_attr($attr['alt']),
        esc_attr($attr['loading'])
    );
}
