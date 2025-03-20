<?php
/**
 * Theme Settings Functions
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

function fau_elemental_settings_page() {
    add_menu_page(
        'FAU Elemental Settings',
        'FAU Elemental',
        'manage_options',
        'fau-elemental-settings',
        'fau_elemental_settings_callback'
    );
}
add_action('admin_menu', 'fau_elemental_settings_page');

function fau_elemental_settings_callback() {
    echo '<h1>FAU Elemental Settings</h1>';

    // Add settings update message
    if (isset($_GET['settings-updated'])) {
        if (get_settings_errors()) {
            foreach (get_settings_errors() as $error) {
                echo '<div class="notice notice-' . esc_attr($error['type']) . ' is-dismissible"><p>' . esc_html($error['message']) . '</p></div>';
            }
        } else {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Settings saved successfully.', 'fau-elemental') . '</p></div>';
        }
    }

    echo '<form method="post" action="options.php">';
    settings_fields('fau-elemental-settings-group');
    do_settings_sections('fau-elemental-settings');
    submit_button();
    echo '</form>';
}

function fau_elemental_register_settings() {
    register_setting(
        'fau-elemental-settings-group',
        'fau_elemental_website_type',
        array(
            'sanitize_callback' => 'fau_elemental_sanitize_website_type'
        )
    );

    register_setting(
        'fau-elemental-settings-group',
        'fau_elemental_faculty',
        array(
            'sanitize_callback' => 'fau_elemental_sanitize_faculty'
        )
    );

    add_settings_section('fau-elemental-section', 'Custom Options', null, 'fau-elemental-settings');

    // Add new website type field
    add_settings_field(
        'fau_elemental_website_type',
        'Website Type',
        'fau_elemental_website_type_callback',
        'fau-elemental-settings',
        'fau-elemental-section'
    );

    // Add faculty field
    add_settings_field(
        'fau_elemental_faculty',
        'Faculty',
        'fau_elemental_faculty_callback',
        'fau-elemental-settings',
        'fau-elemental-section'
    );
}
add_action('admin_init', 'fau_elemental_register_settings');

function fau_elemental_website_type_callback() {
    $website_type = get_option('fau_elemental_website_type', 'fau');
    $options = array(
        'fau' => __('FAU.de', 'fau-elemental'),
        'faculty' => __('Fakultät', 'fau-elemental'),
        'chair' => __('Lehrstuhl', 'fau-elemental'),
        'other' => __('Sonstige', 'fau-elemental'),
        'cooperation' => __('Kooperation', 'fau-elemental')
    );

    echo '<select name="fau_elemental_website_type">';
    foreach ($options as $value => $label) {
        echo '<option value="' . esc_attr($value) . '" ' . selected($website_type, $value, false) . '>';
        echo esc_html($label);
        echo '</option>';
    }
    echo '</select>';
}

function fau_elemental_faculty_callback() {
    $faculty = get_option('fau_elemental_faculty', 'phil');
    $options = array(
        'phil' => __('Philosophische Fakultät', 'fau-elemental'),
        'nat' => __('Naturwissenschaftliche Fakultät', 'fau-elemental'),
        'med' => __('Medizinische Fakultät', 'fau-elemental'),
        'rw' => __('Rechtswissenschaftliche Fakultät', 'fau-elemental'),
        'tf' => __('Technische Fakultät', 'fau-elemental')
    );

    echo '<select name="fau_elemental_faculty">';
    foreach ($options as $value => $label) {
        echo '<option value="' . esc_attr($value) . '" ' . selected($faculty, $value, false) . '>';
        echo esc_html($label);
        echo '</option>';
    }
    echo '</select>';
}

function fau_elemental_sanitize_website_type($input) {
    $valid_types = array('fau', 'faculty', 'chair', 'other', 'cooperation');

    if (!in_array($input, $valid_types)) {
        add_settings_error(
            'fau_elemental_website_type',
            'invalid_website_type',
            __('Invalid website type selected.', 'fau-elemental'),
            'error'
        );
        return get_option('fau_elemental_website_type', 'fau');
    }

    return $input;
}

function fau_elemental_sanitize_faculty($input) {
    $valid_faculties = array('phil', 'nat', 'med', 'rw', 'tf', '');

    if (!in_array($input, $valid_faculties)) {
        add_settings_error(
            'fau_elemental_faculty',
            'invalid_faculty',
            __('Invalid faculty selected.', 'fau-elemental'),
            'error'
        );
        return get_option('fau_elemental_faculty', '');
    }

    return $input;
} 