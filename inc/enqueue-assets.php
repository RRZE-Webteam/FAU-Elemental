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
    $theme_asset_path = get_theme_file_path('build/css/theme.asset.php');
    if (file_exists($theme_asset_path)) {
        $theme_asset = include $theme_asset_path;
        
        wp_enqueue_style(
            'faue-theme',
            get_theme_file_uri('build/css/theme.css'),
            $theme_asset['dependencies'],
            $theme_asset['version']
        );
    }

    // Note: Pattern styles are already included in the main theme bundle above
    // No need to load them separately anymore since they're compiled into css/theme.css
}
add_action('wp_enqueue_scripts', 'faue_enqueue_styles');

// Enqueue Editor Styles
function faue_enqueue_editor_assets() {
    // Load main editor styles (compiled from editor.scss)
    $editor_asset_path = get_theme_file_path('build/css/editor.asset.php');
    if (file_exists($editor_asset_path)) {
        $editor_asset = include $editor_asset_path;
        wp_enqueue_style(
            'faue-editor',
            get_theme_file_uri('build/css/editor.css'),
            $editor_asset['dependencies'],
            $editor_asset['version']
        );
    }

    // Load editor wrapper styles
    $wrapper_asset_path = get_theme_file_path('build/css/editor-wrapper.asset.php');
    if (file_exists($wrapper_asset_path)) {
        $wrapper_asset = include $wrapper_asset_path;
        wp_enqueue_style(
            'faue-editor-wrapper',
            get_theme_file_uri('build/css/editor-wrapper.css'),
            $wrapper_asset['dependencies'],
            $wrapper_asset['version']
        );
    }
}
add_action('enqueue_block_editor_assets', 'faue_enqueue_editor_assets');

// Enqueue Frontend Scripts
function faue_enqueue_scripts() {
    // Post meta script for share functionality
    if (is_singular()) {
        $post_meta_asset_path = get_theme_file_path('build/js/template-parts-post-meta.asset.php');
        if (file_exists($post_meta_asset_path)) {
            $post_meta_asset = include $post_meta_asset_path;
            
            wp_enqueue_script(
                'faue-post-meta',
                get_theme_file_uri('build/js/template-parts-post-meta.js'),
                $post_meta_asset['dependencies'],
                $post_meta_asset['version'],
                true
            );
        }
    }

    // Enqueue navigation JavaScript
    wp_enqueue_script('menu-modal', get_theme_file_uri('build/js/menu-modal.js'), array('jquery'), '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'faue_enqueue_scripts');

// Enqueue Editor Scripts
function faue_enqueue_block_editor_script() {
    $editor_script_path = get_theme_file_path('build/js/editor.asset.php');
    if (file_exists($editor_script_path)) {
        $editor_asset = include $editor_script_path;
        
        wp_enqueue_script(
            'faue-block-editor-script',
            get_parent_theme_file_uri('build/js/editor.js'),
            $editor_asset['dependencies'],
            $editor_asset['version'],
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
}
add_action('enqueue_block_editor_assets', 'faue_enqueue_block_editor_script');

// Add this function to handle block view scripts
function faue_enqueue_block_view_scripts() {
    // Get all block folders
    $block_folders = glob(get_theme_file_path('build/blocks/*'), GLOB_ONLYDIR);

    foreach ($block_folders as $block_folder) {
        $block_name = basename($block_folder);
        $view_script_path = $block_folder . '/view.js';
        $view_asset_path = $block_folder . '/view.asset.php';
        
        if (file_exists($view_script_path) && file_exists($view_asset_path)) {
            $view_asset = include $view_asset_path;
            
            wp_enqueue_script(
                'faue-' . $block_name . '-view',
                get_theme_file_uri('build/blocks/' . $block_name . '/view.js'),
                $view_asset['dependencies'],
                $view_asset['version'],
                true
            );
        }
    }
}
add_action('wp_enqueue_scripts', 'faue_enqueue_block_view_scripts');

/**
 * Enqueue theme assets
 */
function fau_enqueue_assets() {
    // Theme styles
    wp_enqueue_style(
        'fau-elemental-style',
        get_template_directory_uri() . '/build/css/theme.css',
        array(),
        wp_get_theme()->get('Version')
    );

    // Global Search block scripts
    if (has_block('fau-elemental/fau-global-search')) {
        wp_enqueue_script(
            'fau-global-search',
            get_template_directory_uri() . '/components/blocks/fau-global-search/search-suggestions.js',
            array(),
            wp_get_theme()->get('Version'),
            true
        );

        // Add translations and configuration
        wp_localize_script('fau-global-search', 'fauGlobalSearch', array(
            'strings' => array(
                'faqsTitle' => __('Frequently Asked Questions', 'fau-elemental'),
                'suggestionsTitle' => __('Search Suggestions', 'fau-elemental'),
                'noResults' => __('No results found', 'fau-elemental'),
            ),
            'restUrl' => rest_url(),
            'restNonce' => wp_create_nonce('wp_rest'),
        ));
    }
}
add_action('wp_enqueue_scripts', 'fau_enqueue_assets'); 