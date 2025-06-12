<?php
/**
 * Menu Registration Functions
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register navigation menus for the theme
 */
function fau_elemental_register_menus() {
    // Register all navigation menus
    register_nav_menus(array(
        // Navigation component menus
        'fau'              => esc_html__('FAU Navigation', 'fau-elemental'),
        'primary'          => esc_html__('Main Navigation', 'fau-elemental'),
        'primary_direct'   => esc_html__('Direct Links', 'fau-elemental'),
        'secondary_links'  => esc_html__('Secondary Links', 'fau-elemental'),
        
        // Footer menus
        'footer'           => esc_html__('Footer Menu', 'fau-elemental'),
        'footer-menu'      => __('Footer Menu', 'fau-elemental'),
    ));
    
    // Only register the Footer Wichtige Links menu for website types other than fauorg-home
    $website_type = get_option('fau_website_type', 'fauorg-home');
    if ($website_type !== 'fauorg-home') {
        register_nav_menus(array(
            'footer-wichtige-links' => __('Footer Wichtige Links', 'fau-elemental')
        ));
    }
}
add_action('after_setup_theme', 'fau_elemental_register_menus');

/**
 * Add menu classes
 */
function fau_elemental_menu_classes($classes, $item, $args) {
    if ($args->theme_location === 'fau') {
        $classes[] = 'fau-navigation__item';
    } elseif ($args->theme_location === 'primary') {
        $classes[] = 'menu-website-modal__item';
    } elseif ($args->theme_location === 'primary_direct') {
        $classes[] = 'main-navigation__direct-item';
    } elseif ($args->theme_location === 'secondary_links') {
        $classes[] = 'menu-website-modal__secondary-item';
    } elseif ($args->theme_location === 'footer') {
        $classes[] = 'footer-navigation__item';
    }
    return $classes;
}
add_filter('nav_menu_css_class', 'fau_elemental_menu_classes', 10, 3);

/**
 * Add menu link classes
 */
function fau_elemental_menu_link_classes($atts, $item, $args) {
    if ($args->theme_location === 'fau') {
        $atts['class'] = 'fau-navigation__link';
    } elseif ($args->theme_location === 'primary') {
        $atts['class'] = 'menu-website-modal__link';
    } elseif ($args->theme_location === 'primary_direct') {
        $atts['class'] = 'main-navigation__direct-link';
    } elseif ($args->theme_location === 'secondary_links') {
        $atts['class'] = 'menu-website-modal__secondary-link';
    } elseif ($args->theme_location === 'footer') {
        $atts['class'] = 'footer-navigation__link';
    }
    return $atts;
}
add_filter('nav_menu_link_attributes', 'fau_elemental_menu_link_classes', 10, 3); 