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
function faue_enqueue_styles() {
    $theme_asset = include get_theme_file_path('build/css/theme.asset.php');

    wp_enqueue_style(
        'faue-style',
        get_stylesheet_uri()
    );

    wp_enqueue_style(
        'faue-theme',
        get_theme_file_uri('build/css/theme.css'),
        $theme_asset['dependencies'],
        $theme_asset['version']
    );
}
add_action('wp_enqueue_scripts', 'faue_enqueue_styles');

// Enqueue Editor Wrapper Styles
function faue_enqueue_editor_assets() {
    wp_enqueue_style(
        'faue-editor-wrapper',
        get_theme_file_uri('build/css/editor-wrapper.css'),
        array(),
        wp_get_theme()->get('Version')
    );
}
add_action('enqueue_block_editor_assets', 'faue_enqueue_editor_assets');

// Enqueue Frontend Scripts
function faue_enqueue_scripts() {
    wp_enqueue_script(
        'faue-example',
        get_parent_theme_file_uri('assets/js/example.js'),
        array(),
        wp_get_theme()->get('Version'),
        true
    );
}
add_action('wp_enqueue_scripts', 'faue_enqueue_scripts');

// Enqueue Editor Scripts
function faue_enqueue_block_editor_script() {
    wp_enqueue_script(
        'faue-block-editor-script',
        get_parent_theme_file_uri('build/js/editor.js'),
        array('wp-dom-ready', 'wp-blocks', 'wp-hooks', 'wp-edit-post', 'wp-element', 'wp-components'),
        wp_get_theme()->get('Version'),
        true
    );

    // Add theme URL localization
    wp_localize_script(
        'faue-block-editor-script',
        'fauElemental',
        array(
            'themeUrl' => get_template_directory_uri()
        )
    );
}
add_action('enqueue_block_editor_assets', 'faue_enqueue_block_editor_script');

// Enqueue Admin Scripts
function faue_enqueue_admin_scripts($hook) {
    // Only load on our settings page
    if ($hook !== 'toplevel_page_faue-settings') {
        return;
    }

    $script_asset = include(get_template_directory() . '/build/js/admin.asset.php');
    $style_asset = include(get_template_directory() . '/build/css/admin.asset.php');

    // Enqueue admin script
    wp_enqueue_script(
        'faue-admin-settings',
        get_template_directory_uri() . '/build/js/admin.js',
        array_merge(['jquery'], $script_asset['dependencies']),
        $script_asset['version'],
        true
    );

    // Enqueue admin styles
    wp_enqueue_style(
        'faue-admin-styles',
        get_template_directory_uri() . '/build/css/admin.css',
        $style_asset['dependencies'],
        $style_asset['version']
    );
}
add_action('admin_enqueue_scripts', 'faue_enqueue_admin_scripts');

// Add this function to handle block view scripts
function faue_enqueue_block_view_scripts() {
    // Get all block folders
    $block_folders = glob(get_theme_file_path('build/fau-*'), GLOB_ONLYDIR);

    foreach ($block_folders as $block_folder) {
        $block_json_file = $block_folder . '/block.json';
        
        if (file_exists($block_json_file)) {
            $block_json = json_decode(file_get_contents($block_json_file), true);
            
            // Check if block has a view script
            if (isset($block_json['viewScript'])) {
                $view_script_path = str_replace('file:', '', $block_json['viewScript']);
                $view_script_url = get_theme_file_uri('build/' . basename($block_folder) . '/' . $view_script_path);
                
                wp_enqueue_script(
                    'faue-' . basename($block_folder) . '-view',
                    $view_script_url,
                    array(),
                    wp_get_theme()->get('Version'),
                    true
                );
            }
        }
    }
}
add_action('wp_enqueue_scripts', 'faue_enqueue_block_view_scripts'); 