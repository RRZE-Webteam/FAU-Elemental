<?php
/**
 * Theme Settings Functions
 *
 * @package faue
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add theme settings to the WordPress Customizer
 */
function faue_customize_register($wp_customize) {
    // Add FAU Elemental section
    $wp_customize->add_section('faue_theme_settings', array(
        'title'    => __('FAU Elemental Settings', 'fau-elemental'),
        'priority' => 30,
    ));

    // Website Type Setting
    $wp_customize->add_setting('faue_website_type', array(
        'default'           => 'fau',
        'sanitize_callback' => 'faue_sanitize_website_type',
    ));

    $wp_customize->add_control('faue_website_type', array(
        'label'    => __('Website Type', 'fau-elemental'),
        'section'  => 'faue_theme_settings',
        'type'     => 'select',
        'choices'  => array(
            'fau'          => __('FAU.de', 'fau-elemental'),
            'faculty'      => __('Fakultät', 'fau-elemental'),
            'chair'        => __('Lehrstuhl', 'fau-elemental'),
            'other'        => __('Sonstige', 'fau-elemental'),
            'cooperation'  => __('Kooperation', 'fau-elemental'),
        ),
    ));

    // Faculty Setting
    $wp_customize->add_setting('faue_faculty', array(
        'default'           => 'phil',
        'sanitize_callback' => 'faue_sanitize_faculty',
    ));

    $wp_customize->add_control('faue_faculty', array(
        'label'           => __('Faculty', 'fau-elemental'),
        'section'         => 'faue_theme_settings',
        'type'            => 'select',
        'choices'         => array(
            'phil' => __('Philosophische Fakultät', 'fau-elemental'),
            'nat'  => __('Naturwissenschaftliche Fakultät', 'fau-elemental'),
            'med'  => __('Medizinische Fakultät', 'fau-elemental'),
            'rw'   => __('Rechtswissenschaftliche Fakultät', 'fau-elemental'),
            'tf'   => __('Technische Fakultät', 'fau-elemental'),
        ),
        'active_callback' => 'faue_is_faculty_website',
    ));
}
add_action('customize_register', 'faue_customize_register');

/**
 * Check if the website type is set to faculty
 */
function faue_is_faculty_website($control) {
    $setting = $control->manager->get_setting('faue_website_type');
    if (!$setting) {
        return false;
    }
    return 'faculty' === $setting->value();
}

/**
 * Sanitize website type input
 */
function faue_sanitize_website_type($input) {
    $valid_types = array('fau', 'faculty', 'chair', 'other', 'cooperation');

    if (!in_array($input, $valid_types)) {
        return 'fau';
    }

    return $input;
}

/**
 * Sanitize faculty input
 */
function faue_sanitize_faculty($input) {
    $valid_faculties = array('phil', 'nat', 'med', 'rw', 'tf', '');

    if (!in_array($input, $valid_faculties)) {
        return '';
    }

    return $input;
} 