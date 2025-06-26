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
        'menu-1' => __('Primary Menu', 'fau-elemental'),
        'footer-menu' => __('Footer Menu', 'fau-elemental'),
        'footer-lists-menu' => __('Footer Lists Menu', 'fau-elemental'),
        'footer-important-links' => __('Footer Important Links', 'fau-elemental'),
    );
    
    register_nav_menus($menus);
}
add_action('after_setup_theme', 'fau_elemental_register_all_menus'); 