<?php
/**
 * Block Registration
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register all custom blocks from the build directory
 */
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