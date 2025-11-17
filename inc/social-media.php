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
    return get_theme_mod('faue_social_media_mode', 'menu');
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
    // Get menu items from the theme location
    $locations = get_nav_menu_locations();
    $menu_id = isset($locations['FAU_SocialMedia_Menu_Footer']) ? $locations['FAU_SocialMedia_Menu_Footer'] : 0;
    
    if (!$menu_id) {
        return array();
    }
    
    $menu_items = wp_get_nav_menu_items($menu_id);
    $links = array();
    
    if (!$menu_items) {
        return $links;
    }
    
    $wp_default_classes = array('menu-item', 'menu-item-type-custom', 'menu-item-object-custom');
    
    foreach ($menu_items as $item) {
        $platform = faue_extract_platform_from_menu_item($item);
        
        // Fallback: Use first non-WordPress CSS class as platform identifier
        if (!$platform && !empty($item->classes)) {
            foreach ($item->classes as $class) {
                if (!in_array($class, $wp_default_classes)) {
                    $platform = sanitize_key($class);
                    break;
                }
            }
        }
        
        // Final fallback: Use sanitized menu title
        if (!$platform) {
            $platform = sanitize_key($item->title);
        }
        
        if ($platform) {
            $links[$platform] = $item->url;
        }
    }
    
    return $links;
}

/**
 * Extract platform name from menu item
 * 
 * Attempts to identify the platform by checking CSS classes, menu title, and URL.
 * 
 * @param WP_Post $item Menu item object
 * @return string|false Platform name or false if not found
 */
function faue_extract_platform_from_menu_item($item) {
    $platforms = faue_get_combined_social_platforms();
    $platform_keys = array_keys($platforms);
    
    // Check CSS classes for platform name
    foreach ($item->classes as $class) {
        if (in_array($class, $platform_keys)) {
            return $class;
        }
    }
    
    // Check menu title for platform name (case-insensitive)
    $label = strtolower(trim($item->title));
    foreach ($platforms as $platform => $platform_label) {
        if ($label === strtolower($platform_label)) {
            return $platform;
        }
    }
    
    // Fallback: Check URL for platform name (backward compatibility)
    foreach ($platform_keys as $platform) {
        if (strpos($item->url, $platform) !== false) {
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
    
    $platforms = faue_get_combined_social_platforms();
    $built_in_platforms = faue_get_social_platforms();
    
    echo '<ul class="social-links">';
    foreach ($links as $platform => $url) {
        $label = isset($platforms[$platform]) ? $platforms[$platform] : ucfirst($platform);
        $custom_icon = faue_get_custom_social_icon($platform);
        $is_built_in = isset($built_in_platforms[$platform]);
        
        $css_class = $platform;
        $data_attr = '';
        
        if ($custom_icon) {
            $css_class .= ' custom-icon';
            $data_attr = ' data-custom-icon="' . esc_url($custom_icon) . '"';
        } elseif (!$is_built_in && $mode === 'menu') {
            $css_class .= ' default-icon';
        }
        
        echo '<li>';
        echo '<a href="' . esc_url($url) . '" class="' . esc_attr($css_class) . '"' . $data_attr . '>';
        echo '<span class="sr-only">' . esc_html($label) . '</span>';
        echo '</a>';
        echo '</li>';
    }
    echo '</ul>';
}

/**
 * Social media menu walker for icon support
 */
class FAU_Social_Menu_Walker extends Walker_Nav_Menu {
    
    function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $platform = faue_extract_platform_from_menu_item($item);
        $platforms = faue_get_combined_social_platforms();
        $label = $platform ? $platforms[$platform] : $item->title;
        
        $output .= '<li>';
        $output .= '<a href="' . esc_url($item->url) . '" class="' . esc_attr($platform) . '">';
        $output .= '<span class="sr-only">' . esc_html($label) . '</span>';
        $output .= '</a>';
    }
    
    function end_el(&$output, $item, $depth = 0, $args = null) {
        $output .= '</li>';
    }
}
