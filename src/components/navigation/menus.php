<?php
/**
 * Register Navigation Menus
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register navigation menus
 */
function fau_elemental_register_menus() {
    register_nav_menus(array(
        'fau'     => esc_html__('FAU Navigation', 'fau-elemental'),
        'primary' => esc_html__('Main Navigation', 'fau-elemental'),
        'footer'  => esc_html__('Footer Menu', 'fau-elemental'),
    ));
}
add_action('init', 'fau_elemental_register_menus');

/**
 * Add menu classes
 */
function fau_elemental_menu_classes($classes, $item, $args) {
    if ($args->theme_location === 'fau') {
        $classes[] = 'fau-navigation__item';
    } elseif ($args->theme_location === 'primary') {
        $classes[] = 'main-navigation__item';
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
        $atts['class'] = 'main-navigation__link';
    } elseif ($args->theme_location === 'footer') {
        $atts['class'] = 'footer-navigation__link';
    }
    return $atts;
}
add_filter('nav_menu_link_attributes', 'fau_elemental_menu_link_classes', 10, 3); 