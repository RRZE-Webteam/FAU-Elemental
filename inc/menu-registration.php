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
 * Register navigation menus for the theme
 */
function fau_elemental_register_menus() {
    // Always register the footer menu
    register_nav_menus(array(
        'footer-menu' => __('Footer Menu', 'fau-elemental'),
    ));
    
    // Only register the Footer Wichtige Links menu for website types other than fauorg-home
    $website_type = get_option('fau_website_type', 'fauorg-home');
    if ($website_type !== 'fauorg-home') {
        register_nav_menus(array(
            'footer-wichtige-links' => __('Footer Wichtige Links', 'fau-elemental')
        ));
    }
}
add_action('after_setup_theme', 'fau_elemental_register_menus'); 