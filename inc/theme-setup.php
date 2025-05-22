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

    // Register navigation menus
    register_nav_menus(
        array(
            'primary' => esc_html__('Primary Menu', 'fau-elemental'),
            'footer' => esc_html__('Footer Menu', 'fau-elemental'),
            'social' => esc_html__('Social Menu', 'fau-elemental'),
            'portal' => esc_html__('Portal Menu', 'fau-elemental'),
        )
    );

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
    $faculty = get_theme_mod('faue_faculty', 'phil');
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