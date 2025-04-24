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
function fau_elemental_add_gallery_slide_counters($block_content, $block) {
    // Only modify core/gallery blocks
    if (isset($block['blockName']) && 'core/gallery' === $block['blockName']) {
        // Don't modify if the content is null
        if (is_null($block_content)) {
            return $block_content;
        }
        
        // Count the total number of image blocks in the gallery
        preg_match_all('/<figure[^>]*class="[^"]*wp-block-image[^"]*"[^>]*>/s', $block_content, $matches);
        $total_slides = count($matches[0]);
        
        if ($total_slides <= 1) {
            return $block_content; // No need for counters if there's only one image
        }
        
        // First, find all figure elements to determine their positions
        $figure_positions = array();
        $pos = 0;
        preg_match_all('/<figure[^>]*class="[^"]*wp-block-image[^"]*"[^>]*>/s', $block_content, $matches, PREG_OFFSET_CAPTURE);
        foreach ($matches[0] as $match) {
            $pos++;
            $figure_positions[$match[1]] = $pos; // Store position by offset
        }
        
        // Replace each figcaption with one that includes the counter
        $block_content = preg_replace_callback(
            '/<figcaption([^>]*)>(.*?)<\/figcaption>/s',
            function($match) use ($figure_positions, $total_slides, &$block_content) {
                // Find the position of this figcaption in the block content
                $figcaption_pos = strpos($block_content, $match[0]);
                
                // Find the closest figure before this figcaption
                $current_position = 1; // Default to first position
                foreach ($figure_positions as $offset => $position) {
                    if ($offset < $figcaption_pos) {
                        $current_position = $position;
                    } else {
                        break;
                    }
                }
                
                // Check if counter already exists
                if (strpos($match[0], 'slide-counter') !== false) {
                    return $match[0];
                }
                
                // Add the counter to the figcaption
                return '<figcaption' . $match[1] . '>' . $match[2] . 
                       '<span class="slide-counter" aria-hidden="true">' . $current_position . ' / ' . $total_slides . '</span></figcaption>';
            },
            $block_content
        );
    }
    
    return $block_content;
}
add_filter('render_block', 'fau_elemental_add_gallery_slide_counters', 10, 2); 