<?php


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

function fau_elemental_setup()
{
    add_editor_style(array(
        'style.css',
        'assets/css/primary.css',
    ));
}
add_action('after_setup_theme', 'fau_elemental_setup');

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

function register_fau_gallary_block()
{
    register_block_type(__DIR__ . '/build/fau-gallary');
}
add_action('init', 'register_fau_gallary_block');

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
