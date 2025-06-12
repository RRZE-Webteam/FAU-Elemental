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
require_once get_template_directory() . '/components/ui/navigation/menu-modal.php';

// Get global menu modal instance
global $menu_modal;

// Ensure the menu modal instance exists - if not, create it
if (!$menu_modal || !is_object($menu_modal)) {
    $menu_modal = new Menu_Modal();
}

// Configure Services Modal (Global Menu - replaces menu-meta-nav functionality)
$menu_modal->register_modal('services', array(
    'theme_locations' => array('meta_navigation_services'),
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
$menu_modal->register_modal('structure', array(
    'theme_locations' => array('meta_navigation_structure'),
    'use_global_menu' => true,
    'modal_class' => 'menu-meta-nav__modal',
    'menu_class' => 'menu-meta-nav__menu',
    'aria_label' => __('Structure', 'fau-elemental'),
    'depth' => 0,
    'walker' => 'Menu_Modal_Walker',
    'show_back_button' => true,
    'show_close_button' => true,
));

// Configure Website Menu Modal (Local Menu - replaces menu-website functionality)
$menu_modal->register_modal('menu-website', array(
    'theme_locations' => array('primary', 'secondary_links'),
    'use_global_menu' => false,
    'modal_class' => 'menu-website-modal',
    'menu_class' => 'menu-website-modal__menu',
    'aria_label' => __('Website Menu', 'fau-elemental'),
    'depth' => 0,
    'walker' => 'Menu_Modal_Walker',
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
    global $menu_modal;
    if (!$menu_modal || !is_object($menu_modal)) {
        return false;
    }
    $result = $menu_modal->has_menu(array('meta_navigation_services'), true);
    return $result;
}

/**
 * Check if structure menu exists (global or local)
 *
 * @return bool
 */
function fau_elemental_has_structure_menu() {
    global $menu_modal;
    if (!$menu_modal || !is_object($menu_modal)) {
        return false;
    }
    $result = $menu_modal->has_menu(array('meta_navigation_structure'), true);
    return $result;
}

/**
 * Check if website menu exists
 *
 * @return bool
 */
function fau_elemental_has_website_menu() {
    global $menu_modal;
    if (!$menu_modal || !is_object($menu_modal)) {
        return false;
    }
    $result = $menu_modal->has_menu(array('primary'));
    return $result;
}

/**
 * Get services menu items (for use in navigation buttons)
 *
 * @return array|false
 */
function fau_elemental_get_services_menu_items() {
    global $menu_modal;
    if (!$menu_modal || !is_object($menu_modal)) {
        return false;
    }
    return $menu_modal->get_main_site_menu('meta_navigation_services');
}

/**
 * Get structure menu items (for use in navigation buttons)
 *
 * @return array|false
 */
function fau_elemental_get_structure_menu_items() {
    global $menu_modal;
    if (!$menu_modal || !is_object($menu_modal)) {
        return false;
    }
    return $menu_modal->get_main_site_menu('meta_navigation_structure');
} 