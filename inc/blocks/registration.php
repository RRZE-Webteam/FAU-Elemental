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

/**
 * Register all custom blocks from the build directory
 */
function fau_elemental_register_blocks() {
    // Get all directories in the build folder that start with 'fau-'
    $block_folders = glob(get_theme_file_path('build/fau-*'), GLOB_ONLYDIR);

    // Register each block
    foreach ($block_folders as $block_folder) {
        if (file_exists($block_folder . '/block.json')) {
            $block_json = json_decode(file_get_contents($block_folder . '/block.json'), true);
            
            // If render.php exists, explicitly set the render callback
            if (file_exists($block_folder . '/render.php')) {
                $block_name = substr($block_json['name'], strrpos($block_json['name'], '/') + 1);
                $render_function = 'render_block_' . str_replace('-', '_', $block_name);
                $block_json['render_callback'] = $render_function;
            }
            
            register_block_type($block_folder, $block_json);
        }
    }
}
add_action('init', 'fau_elemental_register_blocks'); 