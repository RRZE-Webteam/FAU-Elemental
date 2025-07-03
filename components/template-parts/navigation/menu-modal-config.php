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

// Configure Services Modal (Global Menu - replaces menu-meta-nav functionality)
Menu_Modal::get_instance()->register_modal('services', array(
    'theme_locations' => array('top_header_nav_services'),
    'use_global_menu' => true,
    'modal_class' => 'menu-meta-nav__modal',
    'menu_class' => 'menu-meta-nav__menu',
    'aria_label' => __('Services', 'fau-elemental'),
    'depth' => 0,
    'walker' => 'Menu_Modal_Walker',
    'show_back_button' => true,
    'show_close_button' => true,
));

// Configure Structure Modal (Global Menu - replaces menu-meta-nav functionality)
Menu_Modal::get_instance()->register_modal('structure', array(
    'theme_locations' => array('top_header_nav_structure'),
    'use_global_menu' => true,
    'modal_class' => 'menu-meta-nav__modal',
    'menu_class' => 'menu-meta-nav__menu menu-meta-nav__menu--hierarchy',
    'aria_label' => __('Structure', 'fau-elemental'),
    'depth' => 0,
    'walker' => 'Menu_Modal_Hierarchy_Walker',
    'show_back_button' => true,
    'show_close_button' => true,
));

// Configure Website Menu Modal (Local Menu - replaces menu-website functionality)
Menu_Modal::get_instance()->register_modal('menu-website', array(
    'theme_locations' => array('header_primary_menu', 'header_menu_links'),
    'use_global_menu' => false,
    'modal_class' => 'menu-website-modal',
    'menu_class' => 'menu-website-modal__menu',
    'aria_label' => __('Website Menu', 'fau-elemental'),
    'depth' => 0,
    'walker' => 'Mixed_Navigation_Walker',
    'show_back_button' => true,
    'show_close_button' => true,
));

/**
 * Helper functions to check if menus exist (for use in navigation components)
 */

/**
 * Check if services menu exists (global or local)
 *
 * @return bool
 */
function fau_elemental_has_services_menu() {
    return Menu_Modal::get_instance()->has_menu(array('top_header_nav_services'), true);
}

/**
 * Check if structure menu exists (global or local)
 *
 * @return bool
 */
function fau_elemental_has_structure_menu() {
    return Menu_Modal::get_instance()->has_menu(array('top_header_nav_structure'), true);
}

/**
 * Check if website menu exists
 *
 * @return bool
 */
function fau_elemental_has_website_menu() {
    return Menu_Modal::get_instance()->has_menu(array('header_primary_menu'));
}

/**
 * Get services menu items (for use in navigation buttons)
 *
 * @return array|false
 */
function fau_elemental_get_services_menu_items() {
    return Menu_Modal::get_instance()->get_main_site_menu('top_header_nav_services');
}

/**
 * Get structure menu items (for use in navigation buttons)
 *
 * @return array|false
 */
function fau_elemental_get_structure_menu_items() {
    return Menu_Modal::get_instance()->get_main_site_menu('top_header_nav_structure');
} 