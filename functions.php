<?php

// Enqueue Styles

function fau_elemental_enqueue_styles()
{
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

    // wp_add_inline_style(
    //     'fau-elemental-primary',
    //     'body { background: green; }'
    // );
}
add_action('wp_enqueue_scripts', 'fau_elemental_enqueue_styles');

// Enqueue Editor Styles

function fau_elemental_setup()
{
    add_editor_style(array(
        'style.css',
        'build/css/editor-style.css'
    ));
}
add_action('after_setup_theme', 'fau_elemental_setup');

// Enqueue Scripts

function fau_elemental_enqueue_scripts()
{
    wp_enqueue_script(
        'fau-elemental-example',
        get_parent_theme_file_uri('assets/js/example.js'),
        array(),
        wp_get_theme()->get('Version'),
        true
    );

    // wp_add_inline_script(
    //     'fau-elemental-example',
    //     'console.log( "Inline script" );',
    // );
}
add_action('wp_enqueue_scripts', 'fau_elemental_enqueue_scripts');

// Enqueue Editor Scripts

function fau_elemental_enqueue_editor_scripts()
{
    wp_enqueue_script(
        'fau-elemental-editor',
        get_parent_theme_file_uri('assets/js/example.js'),
        array(),
        wp_get_theme()->get('Version'),
        true
    );
}
add_action('enqueue_block_editor_assets', 'fau_elemental_enqueue_editor_scripts');


// Register Block Category

function fau_elemental_register_block_categories($categories)
{
    return array_merge(
        array(
            array(
                'slug'  => 'fau-elemental/FAU',
                'title' => __('FAU Elemental', 'fau-elemental'),
            ),
        ),
        $categories
    );
}
add_filter('block_categories_all', 'fau_elemental_register_block_categories');

// Register Blocks

function fau_elemental_register_blocks() {
    // Get all directories in the build folder that start with 'fau-'
    $block_folders = glob(get_theme_file_path('build/fau-*'), GLOB_ONLYDIR);
    
    // Register each block
    foreach ($block_folders as $block_folder) {
        if (file_exists($block_folder . '/block.json')) {
            register_block_type($block_folder);
        }
    }
}
add_action('init', 'fau_elemental_register_blocks');

// Theme Settings

function fau_elemental_settings_page()
{
    add_menu_page(
        'FAU Elemental Settings',
        'FAU Elemental',
        'manage_options',
        'fau-elemental-settings',
        'fau_elemental_settings_callback'
    );
}
add_action('admin_menu', 'fau_elemental_settings_page');

function fau_elemental_settings_callback()
{
    echo '<h1>FAU Elemental Settings</h1>';
    echo '<form method="post" action="options.php">';
    settings_fields('fau-elemental-settings-group');
    do_settings_sections('fau-elemental-settings');
    submit_button();
    echo '</form>';
}

function fau_elemental_register_settings()
{
    register_setting('fau-elemental-settings-group', 'fau_elemental_setting');
    add_settings_section('fau-elemental-section', 'Custom Options', null, 'fau-elemental-settings');
    add_settings_field('fau-elemental-setting', 'Custom Setting', 'fau_elemental_setting_callback', 'fau-elemental-settings', 'fau-elemental-section');
}
add_action('admin_init', 'fau_elemental_register_settings');

function custom_setting_callback()
{
    $value = get_option('custom_setting', '');
    echo '<input type="text" name="custom_setting" value="' . esc_attr($value) . '">';
}

// Allow SVG Upload

function allow_svg_upload($mimes)
{
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter('upload_mimes', 'allow_svg_upload');
