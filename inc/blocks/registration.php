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
    // Get all directories in the build/blocks and build folders that start with 'fau-'
    $block_folders = array_merge(
        glob(get_theme_file_path('build/blocks/fau-*'), GLOB_ONLYDIR),
    );

    // Register each block
    foreach ($block_folders as $block_folder) {
        $block_json = json_decode(file_get_contents($block_folder . '/block.json'), true);
        register_block_type($block_folder, $block_json);

    }
}
add_action('init', 'fau_elemental_register_blocks'); 