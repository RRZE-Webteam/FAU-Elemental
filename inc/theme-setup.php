<?php
/**
 * Theme Setup Functions
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

function faue_setup() {
    // Load theme text domain for translations
    load_theme_textdomain('fau-elemental', get_template_directory() . '/languages');

    // Add default posts and comments RSS feed links to head
    add_theme_support('automatic-feed-links');

    // Let WordPress manage the document title
    add_theme_support('title-tag');

    // Enable support for Post Thumbnails on posts and pages
    add_theme_support('post-thumbnails');

    // Add support for responsive embeds
    add_theme_support('responsive-embeds');

    // Add support for custom logo
    add_theme_support('custom-logo', array(
        'height'      => 100,
        'width'       => 400,
        'flex-width'  => true,
        'flex-height' => true,
    ));

    // Add support for menus
    add_theme_support('menus');

    // Register navigation menus
    register_nav_menus(array(
        // Main navigation menus
        'header_primary_menu' => esc_html__('Header Primary Menu', 'fau-elemental'),
        'header_menu_links' => esc_html__('Header Menu Links', 'fau-elemental'),
        
        // FAU system menus
        'top_header_nav_services' => esc_html__('Top Header Nav Services', 'fau-elemental'),
        'top_header_nav_structure' => esc_html__('Top Header Nav Structure', 'fau-elemental'),
        
        // Footer menus
        'footer' => esc_html__('Footer Menu', 'fau-elemental'),
        'footer-menu' => esc_html__('Footer Menu (Alternative)', 'fau-elemental'),
        'footer-wichtige-links' => esc_html__('Footer Wichtige Links', 'fau-elemental'),
    ));

    add_editor_style(array(
        'style.css',
        'build/css/editor.css'
    ));
}
add_action('after_setup_theme', 'faue_setup');

/**
 * Add menu classes based on theme location
 */
function fau_elemental_menu_classes($classes, $item, $args) {
    if ($args->theme_location === 'header_primary_menu') {
        $classes[] = 'menu-website-modal__item';
    } elseif ($args->theme_location === 'header_menu_links') {
        $classes[] = 'menu-website-modal__secondary-item';
    } elseif ($args->theme_location === 'footer') {
        $classes[] = 'footer-navigation__item';
    }
    return $classes;
}
add_filter('nav_menu_css_class', 'fau_elemental_menu_classes', 10, 3);

/**
 * Add menu link classes based on theme location
 */
function fau_elemental_menu_link_classes($atts, $item, $args) {
    if ($args->theme_location === 'header_primary_menu') {
        $atts['class'] = 'menu-website-modal__link';
    } elseif ($args->theme_location === 'header_menu_links') {
        $atts['class'] = 'menu-website-modal__secondary-link';
    } elseif ($args->theme_location === 'footer') {
        $atts['class'] = 'footer-navigation__link';
    }
    return $atts;
}
add_filter('nav_menu_link_attributes', 'fau_elemental_menu_link_classes', 10, 3);

// Add organization-specific body classes
function faue_get_org_classes() {
    $classes = array('fau-theme', 'fau-elemental');

    // Get website type from customizer
    $website_type = get_theme_mod('faue_website_type', 'fau');

    // Add website type specific classes
    switch ($website_type) {
        case 'fau':
            $classes[] = 'fauorg-home';
            break;
        case 'faculty':
            $classes[] = 'fauorg-fakultaet';
            // Add faculty-specific class only if website type is faculty
            $faculty = get_theme_mod('faue_faculty', 'phil');
            if ($faculty) {
                $classes[] = 'faculty-' . sanitize_html_class($faculty);
            }
            break;
        case 'chair':
            $classes[] = 'fauorg-unterorg';
            break;
        case 'cooperation':
            $classes[] = 'fauorg-kooperation';
            break;
    }

    return $classes;
}

// Frontend body classes
function faue_body_class($classes) {
    return array_merge($classes, faue_get_org_classes());
}
add_filter('body_class', 'faue_body_class'); 