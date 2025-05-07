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
    // Add logo section
    $wp_customize->add_section('fau_elemental_logo_section', array(
        'title'    => __('Logo & Title Settings', 'fau-elemental'),
        'priority' => 30,
    ));

    // Add website logotitle setting
    $wp_customize->add_setting('website_logotitle', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    // Add website logotitle control
    $wp_customize->add_control('website_logotitle', array(
        'label'    => __('Website Title', 'fau-elemental'),
        'section'  => 'fau_elemental_logo_section',
        'type'     => 'text',
    ));

    // Add website shorttitle setting
    $wp_customize->add_setting('website_shorttitle', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    // Add website shorttitle control
    $wp_customize->add_control('website_shorttitle', array(
        'label'    => __('Website Short Title', 'fau-elemental'),
        'section'  => 'fau_elemental_logo_section',
        'type'     => 'text',
    ));
}
add_action('customize_register', 'fau_elemental_customize_register');