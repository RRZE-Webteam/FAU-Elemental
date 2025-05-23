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
        'primary' => esc_html__('Primary Menu', 'fau-elemental'),
        'primary_direct' => esc_html__('Direct Links Menu', 'fau-elemental'),
        'secondary_links'  => esc_html__('Secondary Links', 'fau-elemental'),
        'footer' => esc_html__('Footer Menu', 'fau-elemental'),
        'fau_top_navigation' => esc_html__('FAU Top Navigation', 'fau-elemental'),
        'meta_navigation_services' => esc_html__('Meta Navigation Services', 'fau-elemental'),
        'meta_navigation_structure' => esc_html__('Meta Navigation Structure', 'fau-elemental'),
    ));

    add_editor_style(array(
        'style.css',
        'build/css/editor.css'
    ));
}
add_action('after_setup_theme', 'faue_setup');

// Add organization-specific body classes
function faue_get_org_classes() {
    $classes = array('fau-theme', 'fau-elemental');

    // Get website type from options
    $website_type = get_option('faue_website_type', 'fau');

    // Add website type specific classes
    switch ($website_type) {
        case 'fau':
            $classes[] = 'fauorg-home';
            break;
        case 'faculty':
            $classes[] = 'fauorg-fakultaet';
            break;
        case 'chair':
            $classes[] = 'fauorg-unterorg';
            break;
        case 'cooperation':
            $classes[] = 'fauorg-kooperation';
            break;
    }

    // Add faculty-specific class if set
    $faculty = get_option('faue_faculty', '');
    if ($faculty) {
        $classes[] = 'faculty-' . sanitize_html_class($faculty);
    }

    return $classes;
}

// Frontend body classes
function faue_body_class($classes) {
    return array_merge($classes, faue_get_org_classes());
}
add_filter('body_class', 'faue_body_class'); 