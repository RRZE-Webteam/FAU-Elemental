<?php
/**
 * Post Customizer Settings
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add post options to the Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function faue_post_customize_register($wp_customize) {
    // Add section for post settings
    $wp_customize->add_section('faue_post_options', array(
        'title'    => esc_html__('Post Options', 'fau-elemental'),
        'priority' => 130,
    ));

    // Add setting for showing/hiding post meta
    $wp_customize->add_setting('faue_show_post_meta', array(
        'default'           => true,
        'sanitize_callback' => 'faue_sanitize_checkbox',
        'transport'         => 'refresh',
    ));

    // Add control for showing/hiding post meta
    $wp_customize->add_control('faue_show_post_meta', array(
        'label'    => esc_html__('Show post meta information', 'fau-elemental'),
        'section'  => 'faue_post_options',
        'type'     => 'checkbox',
        'priority' => 10,
    ));
}
add_action('customize_register', 'faue_post_customize_register');

/**
 * Sanitize checkbox value.
 *
 * @param bool $checked Whether the checkbox is checked.
 * @return bool Whether the checkbox is checked.
 */
function faue_sanitize_checkbox($checked) {
    return (bool) $checked;
}

/**
 * Check if post meta should be displayed.
 *
 * @return bool
 */
function faue_show_post_meta() {
    return get_theme_mod('faue_show_post_meta', true);
} 