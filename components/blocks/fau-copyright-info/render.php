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
 * Get the copyright info from a block attribute 'copyrightInfo' if it is set. 
 * null if not.
 * 
 * @param array $block a single block
 * @return string The copyright info or null if not present
 */
function fau_elemental_gather_copyright_info_from_attribute($block) {
    if (!empty($block['attrs']['copyrightInfo'])) {
        return $block['attrs']['copyrightInfo'];
    }
    return null;
}

/**
 * Get the copyright info from the block or image metadata if it is set. null if not.
 * Only works for core/image and core/media-text blocks.
 * 
 * @param array $block a single block
 * @return string The copyright info or null if not present
 */
function fau_elemental_gather_copyright_info_from_metadata($block) {
    $image_id = null;
    if ($block['blockName'] === 'core/image' && !empty($block['attrs']['id'])) {
        $image_id = $block['attrs']['id'];
    } else if ($block['blockName'] === 'core/media-text' && !empty($block['attrs']['mediaId'])) {
        $image_id = $block['attrs']['mediaId'];
    } else {
        return null;
    }

    $image_metadata = wp_get_attachment_metadata($image_id);
    
    // Check for copyright info in image metadata
    if (!empty($image_metadata['image_meta']['copyright'])) {
        return $image_metadata['image_meta']['copyright'];
    }
    
    // Check for copyright info in image description
    $image_description = get_post_meta($image_id, '_wp_attachment_image_alt', true);
    if (!empty($image_description) && strpos(strtolower($image_description), 'copyright') !== false) {
        return $image_description;
    }

    // Nothing found
    return null;
}

/**
 * Recursively gather copyright information from blocks and their inner blocks
 *
 * @param array $blocks Array of blocks to check
 * @param string $copyright_prio A setting that sets if the copyright 'field' 
 *                              or the 'iptc' metadata should have priority.
 * @return array Array of copyright information
 */
function fau_elemental_gather_copyright_info_recursive($blocks, $copyright_prio) {
    $copyright_info = array();

    if (empty($blocks)) {
        return $copyright_info;
    }

    foreach ($blocks as $block) {
        $copyright = null;
        if ($copyright_prio === 'field') {
            $copyright ??= fau_elemental_gather_copyright_info_from_attribute($block);
            $copyright ??= fau_elemental_gather_copyright_info_from_metadata($block);
        } else {
            $copyright ??= fau_elemental_gather_copyright_info_from_metadata($block);
            $copyright ??= fau_elemental_gather_copyright_info_from_attribute($block);
        }

        // Only add copyright if it is not null, as it can be if no field or metadata was found
        if (!empty($copyright)) {
            $copyright_info[] = $copyright;
        }

        // Recursively check inner blocks
        if (!empty($block['innerBlocks'])) {
            $copyright_info = array_merge(
                $copyright_info,
                fau_elemental_gather_copyright_info_recursive($block['innerBlocks'], $copyright_prio)
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

        // Get the copyright info priority from the options
        $copyright_prio = get_theme_mod('faue_copyright_info_priority', 'field');
        
        // Recursively gather copyright info from all blocks
        $copyright_info = fau_elemental_gather_copyright_info_recursive($blocks, $copyright_prio);

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