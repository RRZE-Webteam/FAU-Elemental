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
require_once get_theme_file_path('components/blocks/fau-big-button-teaser-group/render.php');
require_once get_theme_file_path('components/blocks/fau-portalmenu/render.php');

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
        $block_json_path = $block_folder . '/block.json';
        if (file_exists($block_json_path)) {
            $block_json = json_decode(file_get_contents($block_json_path), true);
            
            // Special handling for blocks with custom render callbacks
            $block_name = $block_json['name'] ?? '';
            $render_callback = null;
            
            switch ($block_name) {
                case 'fau-elemental/fau-teaser-grid':
                    $render_callback = 'render_block_fau_teaser_grid';
                    break;
                case 'fau-elemental/fau-copyright-info':
                    $render_callback = 'render_block_fau_copyright_info';
                    break;
                case 'fau-elemental/fau-big-button-teaser-group':
                    $render_callback = 'render_block_fau_big_button_teaser_group';
                    break;
                case 'fau-elemental/fau-portalmenu':
                    $render_callback = 'render_block_fau_portalmenu';
                    break;
            }
            
            // Register the block with custom render callback if needed
            if ($render_callback && function_exists($render_callback)) {
                register_block_type($block_folder, array(
                    'render_callback' => $render_callback
                ));
            } else {
                register_block_type($block_folder);
            }
        }
    }
}
add_action('init', 'fau_elemental_register_blocks'); 