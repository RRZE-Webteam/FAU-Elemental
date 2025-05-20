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

// Include post meta functionality
require_once get_template_directory() . '/inc/post-meta.php';

/**
 * Replace block template parts with PHP template parts if they exist
 */
add_filter('render_block_core/template-part', function($block_content, $block) {
    if (isset($block['attrs']['slug'])) {
        $slug = $block['attrs']['slug'];
        
        // Check if we have a PHP template for this part
        $template_path = get_template_directory() . '/template-parts/' . $slug . '.php';
        $html_path = get_template_directory() . '/parts/' . $slug . '.html';
        
        // First try PHP template
        if (file_exists($template_path)) {
            ob_start();
            include $template_path;
            return ob_get_clean();
        }
        
        // Then try HTML template
        if (file_exists($html_path)) {
            return file_get_contents($html_path);
        }
        
        // Special handling for post-meta
        if ($slug === 'post-meta') {
            if (!function_exists('faue_show_post_meta') || !faue_show_post_meta()) {
                return ''; // Don't render it if disabled
            }
        }
    }
    
    return $block_content;
}, 10, 2);

