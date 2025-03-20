<?php
/**
 * Asset Enqueuing Functions
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

// Enqueue Theme Styles
function fau_elemental_enqueue_styles() {
    $theme_asset = include get_theme_file_path('build/css/theme.asset.php');

    wp_enqueue_style(
        'fau-elemental-style',
        get_stylesheet_uri()
    );

    wp_enqueue_style(
        'fau-elemental-theme',
        get_theme_file_uri('build/css/theme.css'),
        $theme_asset['dependencies'],
        $theme_asset['version']
    );
}
add_action('wp_enqueue_scripts', 'fau_elemental_enqueue_styles');

// Enqueue Editor Wrapper Styles
function fau_elemental_enqueue_editor_assets() {
    wp_enqueue_style(
        'fau-elemental-editor-wrapper',
        get_theme_file_uri('build/css/editor-wrapper.css'),
        array(),
        wp_get_theme()->get('Version')
    );
}
add_action('enqueue_block_editor_assets', 'fau_elemental_enqueue_editor_assets');

// Enqueue Frontend Scripts
function fau_elemental_enqueue_scripts() {
    wp_enqueue_script(
        'fau-elemental-example',
        get_parent_theme_file_uri('assets/js/example.js'),
        array(),
        wp_get_theme()->get('Version'),
        true
    );
}
add_action('wp_enqueue_scripts', 'fau_elemental_enqueue_scripts');

// Enqueue Editor Scripts
function fau_elemental_enqueue_block_editor_script() {
    wp_enqueue_script(
        'fau-elemental-block-editor-script',
        get_parent_theme_file_uri('build/js/editor.js'),
        array('wp-dom-ready', 'wp-blocks', 'wp-hooks', 'wp-edit-post', 'wp-element', 'wp-components'),
        wp_get_theme()->get('Version'),
        true
    );
}
add_action('enqueue_block_editor_assets', 'fau_elemental_enqueue_block_editor_script');

// Enqueue Admin Scripts
function fau_elemental_enqueue_admin_scripts($hook) {
    // Only load on our settings page
    if ($hook !== 'toplevel_page_fau-elemental-settings') {
        return;
    }

    $script_asset = include(get_template_directory() . '/build/js/admin.asset.php');
    $style_asset = include(get_template_directory() . '/build/css/admin.asset.php');

    // Enqueue admin script
    wp_enqueue_script(
        'fau-elemental-admin-settings',
        get_template_directory_uri() . '/build/js/admin.js',
        array_merge(['jquery'], $script_asset['dependencies']),
        $script_asset['version'],
        true
    );

    // Enqueue admin styles
    wp_enqueue_style(
        'fau-elemental-admin-styles',
        get_template_directory_uri() . '/build/css/admin.css',
        $style_asset['dependencies'],
        $style_asset['version']
    );
}
add_action('admin_enqueue_scripts', 'fau_elemental_enqueue_admin_scripts'); 