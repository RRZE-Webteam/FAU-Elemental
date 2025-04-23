<?php
/**
 * Image Block Modifications
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add fullscreen button to core/image blocks and wrap image in a div
 *
 * @param string $block_content The block content.
 * @param array  $block         The full block, including name and attributes.
 * @return string Modified block content.
 */
function fau_elemental_add_image_fullscreen($block_content, $block) {
    // Only modify core/image blocks
    if (isset($block['blockName']) && 'core/image' === $block['blockName']) {
        // Don't modify if the content is null
        if (is_null($block_content)) {
            return $block_content;
        }
        
        // Enqueue the fullscreen script only once per page
        static $script_enqueued = false;
        if (!$script_enqueued) {
            $asset_file = include get_theme_file_path('build/js/image-fullscreen.asset.php');
            wp_enqueue_script(
                'image-fullscreen',
                get_theme_file_uri('build/js/image-fullscreen.js'),
                array_merge($asset_file['dependencies'], ['jquery']),
                $asset_file['version'],
                true
            );
            $script_enqueued = true;
        }
        
        // Extract the image source from the block content
        preg_match('/src="([^"]+)"/', $block_content, $matches);
        $img_src = isset($matches[1]) ? $matches[1] : '';
        
        // Create the fullscreen button
        $fullscreen_button = '<button class="image-fullscreen-btn" onclick="openImageFullscreen(\'' . esc_attr($img_src) . '\')">⛶</button>';
        
        // Wrap the img tag in a div
        $block_content = preg_replace('/(<img[^>]+>)/', '<div class="image-wrapper">$1</div>', $block_content);
        
        // Insert the button into the block content
        $pos = strpos($block_content, '</figure>');
        if ($pos === false) {
            $pos = strpos($block_content, '</div>');
        }
        
        if ($pos !== false) {
            // Insert the button before the closing tag
            $block_content = substr_replace($block_content, $fullscreen_button, $pos, 0);
        }
    }
    
    return $block_content;
}
add_filter('render_block', 'fau_elemental_add_image_fullscreen', 10, 2); 