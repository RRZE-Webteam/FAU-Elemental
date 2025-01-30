<?php

// Enqueue Styles

function fau_elemental_enqueue_styles()
{
    wp_enqueue_style(
        'fau-elemental-style',
        get_stylesheet_uri()
    );

    wp_enqueue_style(
        'fau-elemental-primary',
        get_parent_theme_file_uri('assets/css/primary.css'),
        array(),
        wp_get_theme()->get('Version'),
        'all'
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
        'assets/css/primary.css',
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

    wp_add_inline_script(
        'fau-elemental-example',
        'console.log( "Inline script" );',
    );
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

// Register Blocks

function register_fau_blocks() {
    $build_dir = __DIR__ . '/build';
    if (is_dir($build_dir)) {
        $blocks = array_filter(glob($build_dir . '/*'), 'is_dir');
        
        foreach ($blocks as $block) {
            register_block_type($block);
        }
    }
}
add_action('init', 'register_fau_blocks');

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

// Theme Settings

function my_theme_settings_page() {
    add_menu_page(
        'My Theme Settings',
        'Theme Settings',
        'manage_options',
        'my-theme-settings',
        'my_theme_settings_callback'
    );
}
add_action('admin_menu', 'my_theme_settings_page');

function my_theme_settings_callback() {
    echo '<h1>My Theme Settings</h1>';
    echo '<form method="post" action="options.php">';
    settings_fields('my-theme-settings-group');
    do_settings_sections('my-theme-settings');
    submit_button();
    echo '</form>';
}

function my_theme_register_settings() {
    register_setting('my-theme-settings-group', 'custom_setting');
    add_settings_section('my-theme-section', 'Custom Options', null, 'my-theme-settings');
    add_settings_field('custom-setting', 'Custom Setting', 'custom_setting_callback', 'my-theme-settings', 'my-theme-section');
}
add_action('admin_init', 'my_theme_register_settings');

function custom_setting_callback() {
    $value = get_option('custom_setting', '');
    echo '<input type="text" name="custom_setting" value="' . esc_attr($value) . '">';
}

// Allow SVG Upload

function allow_svg_upload($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter('upload_mimes', 'allow_svg_upload');