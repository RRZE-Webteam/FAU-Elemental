<?php
/**
 * Menu Modal Configuration
 * Sets up the unified menu modal component for both global and local menus
 * Now includes mixed navigation support (menu + page children)
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

// Include required components
require_once get_template_directory() . '/src/components/navigation/mixed-navigation-walker.php';
require_once get_template_directory() . '/src/components/navigation/menu-modal.php';

// Get global menu modal instance
global $menu_modal;

// Ensure the menu modal instance exists
if (!$menu_modal || !is_object($menu_modal)) {
    return;
}

// Configure Services Modal (Global Menu - with mixed navigation support)
$menu_modal->register_modal('services', array(
    'theme_locations' => array('meta_navigation_services'),
    'use_global_menu' => true,
    'modal_class' => 'menu-meta-nav__modal',
    'menu_class' => 'menu-meta-nav__menu',
    'aria_label' => __('Services', 'fau-elemental'),
    'depth' => 0,
    'walker' => 'Mixed_Navigation_Walker',
    'show_back_button' => true,
    'show_close_button' => true,
    'enable_mixed_navigation' => true,
));

// Configure Structure Modal (Global Menu - with mixed navigation support)
$menu_modal->register_modal('structure', array(
    'theme_locations' => array('meta_navigation_structure'),
    'use_global_menu' => true,
    'modal_class' => 'menu-meta-nav__modal',
    'menu_class' => 'menu-meta-nav__menu',
    'aria_label' => __('Structure', 'fau-elemental'),
    'depth' => 0,
    'walker' => 'Mixed_Navigation_Walker',
    'show_back_button' => true,
    'show_close_button' => true,
    'enable_mixed_navigation' => true,
));

// Configure Website Menu Modal (Local Menu - with mixed navigation support)
$menu_modal->register_modal('menu-website', array(
    'theme_locations' => array('primary', 'secondary_links'),
    'use_global_menu' => false,
    'modal_class' => 'menu-website-modal',
    'menu_class' => 'menu-website-modal__menu',
    'aria_label' => __('Website Menu', 'fau-elemental'),
    'depth' => 0,
    'walker' => 'Mixed_Navigation_Walker',
    'show_back_button' => true,
    'show_close_button' => true,
    'enable_mixed_navigation' => true,
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

/**
 * Helper functions for mixed navigation features
 */

/**
 * Check if a page should be shown in navigation
 *
 * @param int $page_id Page ID
 * @return bool Whether page should be shown
 */
function fau_elemental_show_page_in_navigation($page_id) {
    return !FAU_Page_Meta_Fields::should_hide_from_menu($page_id);
}

/**
 * Get child pages for navigation
 *
 * @param int $parent_id Parent page ID
 * @return array Array of visible child pages
 */
function fau_elemental_get_navigation_child_pages($parent_id) {
    $args = array(
        'post_type' => 'page',
        'post_status' => 'publish',
        'post_parent' => $parent_id,
        'orderby' => array('menu_order' => 'ASC', 'title' => 'ASC'),
        'posts_per_page' => -1,
    );
    
    $pages = get_posts($args);
    
    // Filter out pages marked as hidden from menu
    $pages = array_filter($pages, function($page) {
        return !FAU_Page_Meta_Fields::should_hide_from_menu($page->ID);
    });
    
    return $pages;
}

/**
 * Get navigation data for a menu item (for AJAX requests)
 *
 * @param object $menu_item Menu item object
 * @return array Navigation data
 */
function fau_elemental_get_menu_item_navigation_data($menu_item) {
    return Mixed_Navigation_Walker::get_navigation_data($menu_item);
}

/**
 * Check if menu item has mixed navigation (both menu and page children)
 *
 * @param object $menu_item Menu item object
 * @return bool Whether item has mixed navigation
 */
function fau_elemental_has_mixed_navigation($menu_item) {
    $data = fau_elemental_get_menu_item_navigation_data($menu_item);
    return $data['navigation_type'] === 'mixed';
}

/**
 * AJAX handler for dynamic page children loading (optional enhancement)
 */
function fau_elemental_ajax_get_page_children() {
    // Verify nonce for security
    if (!wp_verify_nonce($_POST['nonce'], 'fau_navigation_nonce')) {
        wp_die('Security check failed');
    }
    
    $page_id = intval($_POST['page_id']);
    $children = fau_elemental_get_navigation_child_pages($page_id);
    
    $response = array();
    foreach ($children as $child) {
        $child_children = fau_elemental_get_navigation_child_pages($child->ID);
        $response[] = array(
            'id' => $child->ID,
            'title' => $child->post_title,
            'url' => get_permalink($child->ID),
            'has_children' => !empty($child_children),
            'child_count' => count($child_children)
        );
    }
    
    wp_send_json_success($response);
}

// Register AJAX handlers (both for logged in and non-logged in users)
add_action('wp_ajax_fau_get_page_children', 'fau_elemental_ajax_get_page_children');
add_action('wp_ajax_nopriv_fau_get_page_children', 'fau_elemental_ajax_get_page_children');

/**
 * Enqueue additional scripts for mixed navigation
 */
function fau_elemental_enqueue_mixed_navigation_scripts() {
    
    // Localize script for AJAX
    wp_localize_script('fau-mixed-navigation', 'fauMixedNav', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('fau_navigation_nonce'),
        'labels' => array(
            'expand_submenu' => __('Expand submenu', 'fau-elemental'),
            'collapse_submenu' => __('Collapse submenu', 'fau-elemental'),
            'back_to_parent' => __('Back to parent menu', 'fau-elemental'),
            'menu_items' => __('Menu Items', 'fau-elemental'),
            'pages' => __('Pages', 'fau-elemental'),
            'submenu' => __('submenu', 'fau-elemental'),
            'loading' => __('Loading...', 'fau-elemental'),
        )
    ));
}

add_action('wp_enqueue_scripts', 'fau_elemental_enqueue_mixed_navigation_scripts'); 