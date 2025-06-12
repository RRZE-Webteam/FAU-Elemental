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
        
        // Extract the image source from the block content
        preg_match('/src="([^"]+)"/', $block_content, $matches);
        $img_src = isset($matches[1]) ? $matches[1] : '';
        
        // Create the fullscreen button
        $fullscreen_button = '<button class="image-fullscreen-btn" onclick="openImageFullscreen(\'' . esc_attr($img_src) . '\')">⛶</button>';
        
        // Wrap the img tag in a div
        $block_content = preg_replace('/(<img[^>]+>)/', '<div class="image-wrapper">$1</div>', $block_content);
        
        // Add gallery index display if it exists
        if (isset($block['attrs']['galleryIndexText']) && !empty($block['attrs']['galleryIndexText'])) {
            $index_display = '<span class="gallery-index-display">' . esc_html($block['attrs']['galleryIndexText']) . '</span>';
            
            // Check if there's a caption
            if (strpos($block_content, '<figcaption') !== false) {
                // Add to caption
                $block_content = preg_replace('/(<figcaption[^>]*>.*?)(<\/figcaption>)/s', '$1' . $index_display . '$2', $block_content);
            } else {
                // Add to image wrapper
                $block_content = preg_replace('/(<div class="image-wrapper">.*?)(<\/div>)/s', '$1' . $index_display . '$2', $block_content);
            }
        }
        
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

/**
 * Register the image scripts
 */
function faue_register_image_scripts() {
    // Register fullscreen script
    $asset_file = include get_theme_file_path('build/js/image-fullscreen.asset.php');
    wp_register_script(
        'image-fullscreen',
        get_theme_file_uri('build/js/image-fullscreen.js'),
        array_merge($asset_file['dependencies'], ['jquery']),
        $asset_file['version'],
        true
    );

    // Register aspect ratio script
    wp_register_script(
        'image-aspect-ratio',
        get_theme_file_uri('build/js/image-aspect-ratio.js'),
        ['jquery'],
        filemtime(get_theme_file_path('build/js/image-aspect-ratio.js')),
        true
    );
}
add_action('init', 'faue_register_image_scripts', 5);

/**
 * Extend core image block metadata to include our view scripts
 */
function faue_extend_core_image($metadata) {
    if (!empty($metadata['name']) && 'core/image' === $metadata['name']) {
        $metadata['viewScript'] = array_merge(
            (array) ($metadata['viewScript'] ?? []),
            array('image-fullscreen', 'image-aspect-ratio')
        );
    }
    return $metadata;
}
add_filter('block_type_metadata', 'faue_extend_core_image'); 