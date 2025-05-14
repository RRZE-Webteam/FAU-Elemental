<?php
/**
 * Block Registration
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once get_theme_file_path('src/fau-copyright-info/render.php');
require_once get_theme_file_path('src/blocks/fau-teaser-grid/render.php');
require_once get_theme_file_path('src/blocks/fau-info-box/render.php');

/**
 * Register all custom blocks from the build directory
 */
function fau_elemental_register_blocks() {
    // Get all directories in the build/blocks and build folders that start with 'fau-'
    $block_folders = array_merge(
        glob(get_theme_file_path('build/blocks/fau-*'), GLOB_ONLYDIR),
        glob(get_theme_file_path('build/fau-*'), GLOB_ONLYDIR)
    );

    // Register each block
    foreach ($block_folders as $block_folder) {
        if (file_exists($block_folder . '/block.json')) {
            $block_json = json_decode(file_get_contents($block_folder . '/block.json'), true);
            
            // If render.php exists, explicitly set the render callback
            if (file_exists($block_folder . '/render.php')) {
                $block_name = substr($block_json['name'], strrpos($block_json['name'], '/') + 1);
                
                // Special case for featured-event-teaser to use the correct function name
                if ($block_name === 'fau-teaser_grid') {
                    $render_function = 'render_block_fau_list_item';
                } else {
                    $render_function = 'render_block_' . str_replace('-', '_', $block_name);
                }
                
                $block_json['render_callback'] = $render_function;
            }
            
            register_block_type($block_folder, $block_json);
        }
    }
}
add_action('init', 'fau_elemental_register_blocks'); 