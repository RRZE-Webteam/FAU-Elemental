<?php
/**
 * FAU Elemental Theme Functions
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

// Theme setup and core functionality
require_once get_template_directory() . '/inc/theme-setup.php';

// Asset management
require_once get_template_directory() . '/inc/enqueue-assets.php';

// Block functionality
require_once get_template_directory() . '/inc/blocks/loader.php';
require_once get_template_directory() . '/inc/block-patterns.php';

// Theme settings
require_once get_template_directory() . '/inc/theme-settings.php';

require_once get_template_directory() . '/inc/customizer.php';



add_filter('render_block_core/template-part', function($block_content, $block) {
    if (isset($block['attrs']['slug']) && $block['attrs']['slug'] === 'post-meta') {
        if (!function_exists('faue_show_post_meta') || !faue_show_post_meta()) {
            return ''; // Don't render it
        }
    }
    return $block_content;
}, 10, 2);

