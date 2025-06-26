<?php
/**
 * Gallery Block Modifications
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add slide counters to core/gallery blocks
 *
 * @param string $block_content The block content.
 * @param array  $block         The full block, including name and attributes.
 * @return string Modified block content.
 */
// function fau_elemental_add_gallery_slide_counters($block_content, $block) {
//     // Only modify core/gallery blocks
//     if (isset($block['blockName']) && 'core/gallery' === $block['blockName']) {
//         // Don't modify if the content is null
//         if (is_null($block_content)) {
//             return $block_content;
//         }
        
//         // Count the total number of image blocks in the gallery
//         preg_match_all('/<figure[^>]*class="[^"]*wp-block-image[^"]*"[^>]*>/s', $block_content, $matches);
//         $total_slides = count($matches[0]);
        
//         if ($total_slides <= 1) {
//             return $block_content; // No need for counters if there's only one image
//         }
        
//         // First, find all figure elements to determine their positions
//         $figure_positions = array();
//         $pos = 0;
//         preg_match_all('/<figure[^>]*class="[^"]*wp-block-image[^"]*"[^>]*>/s', $block_content, $matches, PREG_OFFSET_CAPTURE);
//         foreach ($matches[0] as $match) {
//             $pos++;
//             $figure_positions[$match[1]] = $pos; // Store position by offset
//         }
//     }
    
//     return $block_content;
// }
// add_filter('render_block', 'fau_elemental_add_gallery_slide_counters', 10, 2); 

/**
 * Register the gallery slider script
 */
function faue_register_gallery_view_script() {
    wp_register_script(
        'faue-gallery-slider',
        get_theme_file_uri('build/js/gallery-slider.js'),
        array('jquery'),
        filemtime(get_theme_file_path('build/js/gallery-slider.js')),
        true
    );
}
add_action('init', 'faue_register_gallery_view_script', 5);

/**
 * Extend core gallery block metadata to include our view script
 */
function faue_extend_core_gallery($metadata) {
    if (!empty($metadata['name']) && 'core/gallery' === $metadata['name']) {
        $metadata['viewScript'] = array_merge(
            (array) ($metadata['viewScript'] ?? []),
            array('faue-gallery-slider')
        );
    }
    return $metadata;
}
add_filter('block_type_metadata', 'faue_extend_core_gallery'); 