<?php
/**
 * Theme Settings Functions
 *
 * @package faue
 */

if (!defined('ABSPATH')) {
    exit;
}

function faue_settings_page() {
    add_menu_page(
        'FAU Elemental Settings',
        'FAU Elemental',
        'manage_options',
        'faue-settings',
        'faue_settings_callback'
    );
}
add_action('admin_menu', 'faue_settings_page');

function faue_settings_callback() {
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
    settings_fields('faue-settings-group');
    do_settings_sections('faue-settings');
    submit_button();
    echo '</form>';
}

function faue_register_settings() {
    register_setting(
        'faue-settings-group',
        'faue_website_type',
        array(
            'sanitize_callback' => 'faue_sanitize_website_type'
        )
    );

    register_setting(
        'faue-settings-group',
        'faue_faculty',
        array(
            'sanitize_callback' => 'faue_sanitize_faculty'
        )
    );

    add_settings_section('faue-section', 'Custom Options', null, 'faue-settings');

    // Add new website type field
    add_settings_field(
        'faue_website_type',
        'Website Type',
        'faue_website_type_callback',
        'faue-settings',
        'faue-section'
    );

    // Add faculty field
    add_settings_field(
        'faue_faculty',
        'Faculty',
        'faue_faculty_callback',
        'faue-settings',
        'faue-section'
    );
}
add_action('admin_init', 'faue_register_settings');

function faue_website_type_callback() {
    $website_type = get_option('faue_website_type', 'fau');
    $options = array(
        'fau' => __('FAU.de', 'fau-elemental'),
        'faculty' => __('Fakultät', 'fau-elemental'),
        'chair' => __('Lehrstuhl', 'fau-elemental'),
        'other' => __('Sonstige', 'fau-elemental'),
        'cooperation' => __('Kooperation', 'fau-elemental')
    );

    echo '<div class="faue-faculty-field"><div class="faue-faculty-field__content">';
    echo '<select name="faue_website_type">';
    foreach ($options as $value => $label) {
        echo '<option value="' . esc_attr($value) . '" ' . selected($website_type, $value, false) . '>';
        echo esc_html($label);
        echo '</option>';
    }
    echo '</select>';
    echo '</div></div>';
}

function faue_faculty_callback() {
    $faculty = get_option('faue_faculty', 'phil');
    $options = array(
        'phil' => __('Philosophische Fakultät', 'fau-elemental'),
        'nat' => __('Naturwissenschaftliche Fakultät', 'fau-elemental'),
        'med' => __('Medizinische Fakultät', 'faue'),
        'rw' => __('Rechtswissenschaftliche Fakultät', 'fau-elemental'),
        'tf' => __('Technische Fakultät', 'fau-elemental')
    );

    echo '<div class="faue-faculty-field"><div class="faue-faculty-field__content">';
    echo '<select name="faue_faculty">';
    foreach ($options as $value => $label) {
        echo '<option value="' . esc_attr($value) . '" ' . selected($faculty, $value, false) . '>';
        echo esc_html($label);
        echo '</option>';
    }
    echo '</select>';
    echo '</div></div>';
}

function faue_sanitize_website_type($input) {
    $valid_types = array('fau', 'faculty', 'chair', 'other', 'cooperation');

    if (!in_array($input, $valid_types)) {
        add_settings_error(
            'faue_website_type',
            'invalid_website_type',
            __('Invalid website type selected.', 'fau-elemental'),
            'error'
        );
        return get_option('faue_website_type', 'fau');
    }

    return $input;
}

function faue_sanitize_faculty($input) {
    $valid_faculties = array('phil', 'nat', 'med', 'rw', 'tf', '');

    if (!in_array($input, $valid_faculties)) {
        add_settings_error(
            'faue_faculty',
            'invalid_faculty',
            __('Invalid faculty selected.', 'fau-elemental'),
            'error'
        );
        return get_option('faue_faculty', '');
    }

    return $input;
} 