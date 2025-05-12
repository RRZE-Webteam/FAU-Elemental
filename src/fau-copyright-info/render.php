<?php
/**
 * Server-side rendering of the copyright-info block.
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Recursively gather copyright information from blocks and their inner blocks
 *
 * @param array $blocks Array of blocks to check
 * @return array Array of copyright information
 */
function fau_elemental_gather_copyright_info_recursive($blocks) {
    $copyright_info = array();

    if (empty($blocks)) {
        return $copyright_info;
    }

    foreach ($blocks as $block) {
        // Check for copyright info in any block type
        if (!empty($block['attrs']['copyrightInfo'])) {
            $copyright_info[] = $block['attrs']['copyrightInfo'];
        }  
        // Check for image and media-text blocks and extract copyright info from metadata if available
        elseif (($block['blockName'] === 'core/image' && !empty($block['attrs']['id'])) || 
                ($block['blockName'] === 'core/media-text' && !empty($block['attrs']['mediaId']))) {
            $image_id = $block['attrs']['id'] ?: $block['attrs']['mediaId'];
            $image_metadata = wp_get_attachment_metadata($image_id);
            
            // Check for copyright info in image metadata
            if (!empty($image_metadata['image_meta']['copyright'])) {
                $copyright_info[] = $image_metadata['image_meta']['copyright'];
            }
            
            // Check for copyright info in image description
            $image_description = get_post_meta($image_id, '_wp_attachment_image_alt', true);
            if (!empty($image_description) && strpos(strtolower($image_description), 'copyright') !== false) {
                $copyright_info[] = $image_description;
            }
        }

        // Recursively check inner blocks
        if (!empty($block['innerBlocks'])) {
            $copyright_info = array_merge(
                $copyright_info,
                fau_elemental_gather_copyright_info_recursive($block['innerBlocks'])
            );
        }
    }

    return $copyright_info;
}

/**
 * Gather copyright information from all blocks in the content
 *
 * @return array Array of copyright information
 */
if (!function_exists('fau_elemental_gather_copyright_info')) {
    function fau_elemental_gather_copyright_info() {
        global $post;
        $copyright_info = array();

        if (!$post) {
            return $copyright_info;
        }

        // Parse blocks to find blocks with copyright info
        $blocks = parse_blocks($post->post_content);
        
        // Recursively gather copyright info from all blocks
        $copyright_info = fau_elemental_gather_copyright_info_recursive($blocks);

        // Allow other plugins to add their copyright information
        return apply_filters('fau_elemental_copyright_info', $copyright_info);
    }
}

/**
 * Render the copyright-info block
 *
 * @param array  $attributes Block attributes.
 * @param string $content    Block content.
 * @return string Rendered block output.
 */
if (!function_exists('render_block_fau_copyright_info')) {
    function render_block_fau_copyright_info($attributes, $content) {
        $copyright_info = fau_elemental_gather_copyright_info();

        if (empty($copyright_info)) {
            return '';
        }

        $output = '<div class="wp-block-fau-elemental-copyright-info">';
        $output .= '<h3>' . esc_html__('Copyright Information', 'fau-elemental') . '</h3>';
        $output .= '<ul class="copyright-info-list">';
        
        foreach ($copyright_info as $info) {
            $output .= '<li>' . esc_html($info) . '</li>';
        }
        
        $output .= '</ul>';
        $output .= '</div>';

        return $output;
    }
} 