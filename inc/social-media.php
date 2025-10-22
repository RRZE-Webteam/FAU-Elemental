<?php
/**
 * Social Media Management for FAU-Elemental
 * 
 * Provides flexible social media display options:
 * 1. WordPress menu-based social media (with icons)
 * 2. Customizer-based individual platform settings
 * 3. Platform enable/disable controls
 * 
 * @package FAU_Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get social media display mode
 * 
 * @return string 'menu' or 'customizer'
 */
function faue_get_social_media_mode() {
    return get_theme_mod('faue_social_media_mode', 'customizer');
}

/**
 * Get all available social media platforms
 * 
 * @return array Array of all platform keys
 */
function faue_get_available_social_platforms() {
    return faue_get_combined_social_platforms();
}

/**
 * Get social media links from customizer
 * 
 * @return array Array of platform => url pairs
 */
function faue_get_social_media_links() {
    $all_platforms = faue_get_combined_social_platforms();
    $links = array();
    
    foreach ($all_platforms as $platform => $label) {
        $url = get_theme_mod("social_{$platform}");
        if (!empty($url)) {
            $links[$platform] = $url;
        }
    }
    
    return $links;
}

/**
 * Get social media links from WordPress menu
 * 
 * @return array Array of platform => url pairs
 */
function faue_get_social_media_menu_links() {
    $menu_items = wp_get_nav_menu_items('social-media-menu');
    $links = array();
    
    if (!$menu_items) {
        return $links;
    }
    
    foreach ($menu_items as $item) {
        // Extract platform from CSS class or URL
        $platform = faue_extract_platform_from_menu_item($item);
        if ($platform) {
            $links[$platform] = $item->url;
        }
    }
    
    return $links;
}

/**
 * Extract platform name from menu item
 * 
 * @param WP_Post $item Menu item object
 * @return string|false Platform name or false if not found
 */
function faue_extract_platform_from_menu_item($item) {
    // Check CSS classes for platform name
    $classes = $item->classes;
    foreach ($classes as $class) {
        if (in_array($class, array_keys(faue_get_combined_social_platforms()))) {
            return $class;
        }
    }
    
    // Check label for platform name (case-insensitive)
    $label = strtolower(trim($item->title));
    $platforms = faue_get_combined_social_platforms();
    foreach ($platforms as $platform => $platform_label) {
        if ($label === strtolower($platform_label)) {
            return $platform;
        }
    }
    
    // Fallback: Check URL for platform name (for backward compatibility)
    $url = $item->url;
    foreach ($platforms as $platform => $platform_label) {
        if (strpos($url, $platform) !== false) {
            return $platform;
        }
    }
    
    return false;
}

/**
 * Render social media links
 * 
 * @param string $mode 'menu' or 'customizer' or 'auto'
 * @return void
 */
function faue_render_social_media_links($mode = 'auto') {
    if ($mode === 'auto') {
        $mode = faue_get_social_media_mode();
    }
    
    $links = array();
    
    if ($mode === 'menu') {
        $links = faue_get_social_media_menu_links();
    } else {
        $links = faue_get_social_media_links();
    }
    
    if (empty($links)) {
        return;
    }
    
    echo '<ul class="social-links">';
    foreach ($links as $platform => $url) {
        $platforms = faue_get_combined_social_platforms();
        $label = isset($platforms[$platform]) ? $platforms[$platform] : ucfirst($platform);
        
        // Check if this is a custom platform
        $custom_icon = faue_get_custom_social_icon($platform);
        $css_class = $platform;
        $style = '';
        
        if ($custom_icon) {
            $css_class .= ' custom-' . $platform;
            $style = ' style="background-image: url(' . esc_url($custom_icon) . ');"';
        }
        
        echo '<li>';
        echo '<a href="' . esc_url($url) . '" class="' . esc_attr($css_class) . '" target="_blank" rel="noopener"' . $style . '>';
        echo '<span class="sr-only">' . esc_html($label) . '</span>';
        echo '</a>';
        echo '</li>';
    }
    echo '</ul>';
}

/**
 * Register social media menu location
 */
function faue_register_social_media_menu() {
    register_nav_menus(array(
        'social-media-menu' => __('Social Media Menu', 'fau-elemental'),
    ));
}
add_action('init', 'faue_register_social_media_menu');

/**
 * Add social media menu walker for icon support
 */
class FAU_Social_Menu_Walker extends Walker_Nav_Menu {
    
    function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $platform = faue_extract_platform_from_menu_item($item);
        $platforms = faue_get_combined_social_platforms();
        $label = $platform ? $platforms[$platform] : $item->title;
        
        $output .= '<li>';
        $output .= '<a href="' . esc_url($item->url) . '" class="' . esc_attr($platform) . '" target="_blank" rel="noopener">';
        $output .= '<span class="sr-only">' . esc_html($label) . '</span>';
        $output .= '</a>';
    }
    
    function end_el(&$output, $item, $depth = 0, $args = null) {
        $output .= '</li>';
    }
}
