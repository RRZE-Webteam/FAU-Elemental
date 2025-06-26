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



    add_editor_style(array(
        'style.css',
        'build/css/editor.css'
    ));
}
add_action('after_setup_theme', 'faue_setup');

// Add organization-specific body classes
function faue_get_org_classes() {
    $classes = array('fau-theme', 'fau-elemental');

    // Get website type from customizer
    $faue_website_type = get_theme_mod('faue_website_type');

    // Add website type specific classes
    switch ($faue_website_type) {
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