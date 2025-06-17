<?php
/**
 * Menu Registration Functions
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register all navigation menus for the theme
 */
function fau_elemental_register_all_menus() {
    $website_type = get_theme_mod('faue_website_type', 'fau');
    
    // Core menus that are always registered
    $menus = array(
        // Main navigation menus
        'header_primary_menu' => __('Header Primary Menu', 'fau-elemental'),
        'header_menu_links' => __('Header Menu Links', 'fau-elemental'),
        
        // FAU system menus
        'top_header_nav_services' => __('Top Header Nav Services', 'fau-elemental'),
        'top_header_nav_structure' => __('Top Header Nav Structure', 'fau-elemental'),
        
        // Footer menus
        'footer-menu' => __('Footer Menu', 'fau-elemental'),
    );
    
    // Add menus based on website type
    if ($website_type === 'fau') {
        // Main FAU site gets the footer lists menu
        $menus['footer-lists-menu'] = __('Footer Lists Menu', 'fau-elemental');
    } else {
        // Instance sites get the important links menu
        $menus['footer-wichtige-links'] = __('Footer Important Links', 'fau-elemental');
    }
    
    // Social menu if needed (check if it's actually used anywhere)
    // $menus['social'] = __('Social Menu', 'fau-elemental');
    
    // Portal menu if needed (used dynamically, not through wp_nav_menu theme_location)
    // $menus['portal'] = __('Portal Menu', 'fau-elemental');
    
    register_nav_menus($menus);
}
add_action('after_setup_theme', 'fau_elemental_register_all_menus'); 