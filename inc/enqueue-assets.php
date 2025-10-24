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
                'themeUrl' => get_template_directory_uri(),
                'websiteType' => get_theme_mod('faue_website_type', 'fau'),
                'facultyType' => get_theme_mod('faue_faculty', 'phil'),
                'fallbackImageUrl' => faue_get_post_fallback_image(),
            )
        );
    }
}
add_action('enqueue_block_editor_assets', 'faue_enqueue_block_editor_script');

/**
 * Refresh JavaScript data when fallback image changes
 * This ensures editor previews stay in sync with customizer changes
 */
function faue_refresh_fallback_image_js() {
    // Only run in admin/editor context
    if (!is_admin() && !wp_doing_ajax()) {
        return;
    }
    
    // Force refresh of editor scripts when fallback image changes
    add_filter('wp_enqueue_scripts', function() {
        // Clear any cached data
        wp_cache_delete('faue_fallback_image', 'theme_mods');
        return true;
    }, 1);
}
add_action('admin_init', 'faue_refresh_fallback_image_js');

// Add this function to handle block view scripts
function faue_enqueue_block_view_scripts() {
    // Get all block folders
    $block_folders = glob(get_theme_file_path('build/blocks/*'), GLOB_ONLYDIR);

    $localized = false;
    
    foreach ($block_folders as $block_folder) {
        $block_name = basename($block_folder);
        $view_script_path = $block_folder . '/view.js';
        $view_asset_path = $block_folder . '/view.asset.php';
        
        if (file_exists($view_script_path) && file_exists($view_asset_path)) {
            $view_asset = include $view_asset_path;
            
            $script_handle = 'faue-' . $block_name . '-view';
            
            wp_enqueue_script(
                $script_handle,
                get_theme_file_uri('build/blocks/' . $block_name . '/view.js'),
                $view_asset['dependencies'],
                $view_asset['version'],
                true
            );
            
            // Localize script data for the first script only
            if (!$localized) {
                wp_localize_script(
                    $script_handle,
                    'fauElemental',
                    array(
                        'themeUrl' => get_template_directory_uri(),
                        'websiteType' => get_theme_mod('faue_website_type', 'fau'),
                        'facultyType' => get_theme_mod('faue_faculty', 'phil'),
                        'nonce' => wp_create_nonce('fau_elemental_nonce'),
                        'ajaxUrl' => admin_url('admin-ajax.php'),
                        'searchDebounceDelay' => faue_get_default('faue_search_debounce_delay'),
                        'fallbackImageUrl' => faue_get_post_fallback_image(),
                    )
                );
                $localized = true;
            }
        }
    }
}
add_action('wp_enqueue_scripts', 'faue_enqueue_block_view_scripts');

// Enqueue Menu Modal Script
function faue_enqueue_menu_modal_script() {
    $script_asset_path = get_theme_file_path('build/js/menu-modal.asset.php');
    if (file_exists($script_asset_path)) {
        $script_asset = include $script_asset_path;
        
        wp_enqueue_script(
            'faue-menu-modal',
            get_theme_file_uri('build/js/menu-modal.js'),
            array_merge($script_asset['dependencies'], array('jquery')),
            $script_asset['version'],
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'faue_enqueue_menu_modal_script'); 

// Register Archive Page Script
function faue_register_archive_page_script() {
    $script_asset_path = get_theme_file_path('build/js/menu-modal.asset.php');
    if (file_exists($script_asset_path)) {
        $script_asset = include $script_asset_path;
        
        wp_register_script(
            'faue-template-archive',
            get_theme_file_uri('build/js/templates-archive.js'),
            $script_asset['dependencies'],
            $script_asset['version'],
        );
    }
}
add_action('wp_enqueue_scripts', 'faue_register_archive_page_script');

// Enqueue Social Media Icons Script
function faue_enqueue_social_media_icons_script() {
    $script_asset_path = get_theme_file_path('build/js/social-media-icons.asset.php');
    if (file_exists($script_asset_path)) {
        $script_asset = include $script_asset_path;
        
        wp_enqueue_script(
            'faue-social-media-icons',
            get_theme_file_uri('build/js/social-media-icons.js'),
            $script_asset['dependencies'],
            $script_asset['version'],
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'faue_enqueue_social_media_icons_script'); 
