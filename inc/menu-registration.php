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
    $faue_website_type = get_theme_mod('faue_website_type', 'fau');
    
    // Core menus that are always registered
    $menus = array(
        // Main navigation menus
        'header_primary_menu' => __('Header Primary Menu', 'fau-elemental'),
        'header_direct_links_menu' => __('Header Direct Links Menu', 'fau-elemental'),
        'header_menu_links' => __('Header Menu Links', 'fau-elemental'),
        
        // FAU system menus
        'top_header_nav_services' => __('Top Header Nav Services', 'fau-elemental'),
        'top_header_nav_structure' => __('Top Header Nav Structure', 'fau-elemental'),
        
        // Footer menus
        'footer-menu' => __('Footer Menu', 'fau-elemental'),
        'footer-lists-menu' => __('Footer Lists Menu', 'fau-elemental'),
        'footer-important-links' => __('Footer Important Links', 'fau-elemental'),
    );
    
    register_nav_menus($menus);
}
add_action('after_setup_theme', 'fau_elemental_register_all_menus'); 