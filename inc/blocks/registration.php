<?php
/**
 * Block Registration
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once get_theme_file_path('components/blocks/fau-copyright-info/render.php');
require_once get_theme_file_path('components/blocks/fau-teaser-grid/render.php');
require_once get_theme_file_path('components/blocks/fau-global-search/render.php');

/**
 * Register all custom blocks from the build directory
 */
function fau_elemental_register_blocks() {
    // Get all directories in both build/blocks and components/blocks that start with 'fau-'
    $block_folders = array_merge(
        glob(get_theme_file_path('build/blocks/fau-*'), GLOB_ONLYDIR),
        glob(get_theme_file_path('components/blocks/fau-*'), GLOB_ONLYDIR)
    );

    // Register each block
    foreach ($block_folders as $block_folder) {
        if (file_exists($block_folder . '/block.json')) {
            $block_json_content = file_get_contents($block_folder . '/block.json');
            if ($block_json_content === false) {
                // Handle error: block.json not readable
                continue;
            }
            $block_json = json_decode($block_json_content, true);
            if ($block_json === null) {
                // Handle error: block.json invalid JSON
                continue;
            }
            
            // If render.php exists in the block folder, explicitly set the render callback
            if (file_exists($block_folder . '/render.php')) {
                // Ensure block name is valid before using it
                if (isset($block_json['name']) && is_string($block_json['name'])) {
                    $block_name_parts = explode('/', $block_json['name']);
                    $block_name_slug = end($block_name_parts);
                    
                    // Special case for fau-teaser-grid (as in original code, though it was fau-teaser_grid)
                    if ($block_name_slug === 'fau-teaser-grid') {
                        $render_function = 'render_block_fau_list_item';
                    } else {
                        $render_function = 'render_block_' . str_replace('-', '_', $block_name_slug);
                    }
                    
                    // Only set render_callback if the function actually exists
                    if (function_exists($render_function)) {
                        $block_json['render_callback'] = $render_function;
                    } else {
                        // Optional: Log a warning if the intended render function doesn't exist
                        // error_log('Render function ' . $render_function . ' not found for block ' . $block_json['name']);
                        // In this case, WordPress will fall back to using the 'render' field in block.json if present
                    }
                }
            }
            
            register_block_type($block_folder, $block_json);
        }
    }
}
add_action('init', 'fau_elemental_register_blocks'); 