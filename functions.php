<?php
/**
 * FAU Elemental Theme Functions
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

// Theme setup and core functionality
require_once get_template_directory() . '/inc/theme-setup.php';

// Asset management
require_once get_template_directory() . '/inc/enqueue-assets.php';

// Block functionality
require_once get_template_directory() . '/inc/blocks/loader.php';
require_once get_template_directory() . '/inc/block-patterns.php';

// Theme settings
require_once get_template_directory() . '/inc/theme-settings.php';

/**
 * Add custom logo support
 */
function fau_elemental_custom_logo_setup() {
    add_theme_support('custom-logo', array(
        'height'      => 100,
        'width'       => 400,
        'flex-height' => true,
        'flex-width'  => true,
        'header-text' => array('site-title', 'site-description'),
    ));
}
add_action('after_setup_theme', 'fau_elemental_custom_logo_setup');

/**
 * Add logo settings to customizer
 */
function fau_elemental_customize_register($wp_customize) {

    // Add logo setting
    $wp_customize->add_setting('fau_elemental_logo', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ));

    // Add logo control
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'fau_elemental_logo', array(
        'label'    => __('Upload Logo', 'fau-elemental'),
        'section'  => 'fau_elemental_logo_section',
        'settings' => 'fau_elemental_logo',
    )));
}
add_action('customize_register', 'fau_elemental_customize_register');