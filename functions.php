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

// Logo display functionality
require_once get_template_directory() . '/inc/logo-display.php';

/**
 * Add logo settings to customizer
 */
function fau_elemental_customize_register($wp_customize) {
    // Add website shorttitle setting
    $wp_customize->add_setting('website_shorttitle', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    // Add website shorttitle control to Site Identity section
    $wp_customize->add_control('website_shorttitle', array(
        'label'    => __('Website Short Title', 'fau-elemental'),
        'section'  => 'title_tagline',
        'type'     => 'text',
    ));

    // Add custom logo setting
    $wp_customize->add_setting('fau_elemental_custom_logo', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ));

    // Add custom logo control
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'fau_elemental_custom_logo', array(
        'label'    => __('Custom Logo', 'fau-elemental'),
        'section'  => 'title_tagline',
        'settings' => 'fau_elemental_custom_logo',
    )));
}
add_action('customize_register', 'fau_elemental_customize_register');