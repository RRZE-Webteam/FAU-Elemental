<?php
/**
 * Menu Modal Configuration
 * Sets up the unified menu modal component for both global and local menus
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

// Include the unified menu modal component
require_once get_template_directory() . '/components/template-parts/navigation/menu-modal.php';

// Include the mixed navigation walker
require_once get_template_directory() . '/components/template-parts/navigation/mixed-navigation-walker.php';

add_action('init', function () {
    // Cache the Menu_Modal instance to avoid multiple get_instance() calls
    $menu_modal = Menu_Modal::get_instance();
    
    // Configure Services Modal (Global Menu - replaces menu-meta-nav functionality)
    $menu_modal->register_modal('services', array(
        'theme_locations' => array('top_header_nav_services', 'header_menu_links'),
        'use_global_menu' => true,
        'modal_class' => 'menu-meta-nav__modal',
        'menu_class' => 'menu-meta-nav__menu',
        'aria_label' => __('Services', 'fau-elemental'),
        'depth' => 0,
        'walker' => 'Menu_Modal_Walker',
        'show_back_button' => true,
        'show_close_button' => true,
        'location_depths' => array(
            'header_menu_links' => 1
        ),
        'global_locations' => array('top_header_nav_services'),
    ));

    // Configure Structure Modal (Global Menu - replaces menu-meta-nav functionality)
    $menu_modal->register_modal('structure', array(
        'modal_class' => 'menu-meta-nav__modal',
        'menu_class' => 'menu-meta-nav__menu menu-meta-nav__menu--hierarchy',
        'aria_label' => __('Structure', 'fau-elemental'),
        'show_back_button' => true,
        'show_close_button' => true,
    ));

    // Configure Website Menu Modal (Local Menu - replaces menu-website functionality)
    $menu_modal->register_modal('menu-website', array(
        'theme_locations' => array('header_primary_menu', 'header_menu_links'),
        'use_global_menu' => false,
        'modal_class' => 'menu-website-modal',
        'menu_class' => 'menu-website-modal__menu',
        'aria_label' => __('Website Menu', 'fau-elemental'),
        'depth' => 0,
        'walker' => 'Mixed_Navigation_Walker',
        'show_back_button' => true,
        'show_close_button' => true,
        'location_depths' => array(
            'header_menu_links' => 1
        ),
    ));

    // Configure Search Modal (Special modal for search functionality)
    $menu_modal->register_modal('search', array(
        'theme_locations' => array(), // No menu locations needed for search
        'use_global_menu' => false,
        'modal_class' => 'menu-modal',
        'menu_class' => 'menu-modal__menu',
        'aria_label' => __('Search', 'fau-elemental'),
        'depth' => 0,
        'walker' => null,
        'show_back_button' => false,
        'show_close_button' => true,
    ));
});

/**
 * Helper functions to check if menus exist (for use in navigation components)
 */

/**
 * Get cached Menu_Modal instance
 *
 * @return Menu_Modal
 */
function fau_elemental_get_menu_modal_instance() {
    static $menu_modal = null;
    if ($menu_modal === null) {
        $menu_modal = Menu_Modal::get_instance();
    }
    return $menu_modal;
}

/**
 * Check if services menu exists (global or local)
 *
 * @return bool
 */
function fau_elemental_has_services_menu() {
    return fau_elemental_get_menu_modal_instance()->has_menu(array('top_header_nav_services'), true);
}

/**
 * Check if structure menu exists (via shortcode)
 *
 * @return bool
 */
function fau_elemental_has_structure_menu() {
    return shortcode_exists('fauorga');
}

/**
 * Check if website menu exists
 *
 * @return bool
 */
function fau_elemental_has_website_menu() {
    return fau_elemental_get_menu_modal_instance()->has_menu(array('header_primary_menu'));
}
