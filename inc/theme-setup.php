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
    // Menus are now registered in inc/menu-registration.php

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