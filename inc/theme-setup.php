<?php
/**
 * Theme Setup Functions
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

function fau_elemental_setup() {
    add_editor_style(array(
        'style.css',
        'build/css/editor.css'
    ));
}
add_action('after_setup_theme', 'fau_elemental_setup');

// Add organization-specific body classes
function fau_elemental_get_org_classes() {
    $classes = array('fau-theme', 'fau-elemental');

    // Get website type from options
    $website_type = get_option('fau_elemental_website_type', 'fau');

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
    $faculty = get_option('fau_elemental_faculty', '');
    if ($faculty) {
        $classes[] = 'faculty-' . sanitize_html_class($faculty);
    }

    return $classes;
}

// Frontend body classes
function fau_elemental_body_class($classes) {
    return array_merge($classes, fau_elemental_get_org_classes());
}
add_filter('body_class', 'fau_elemental_body_class'); 