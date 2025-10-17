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
 * @return array|null Array with 'text' and 'image_id' keys, or null if not present
 */
function fau_elemental_gather_copyright_info_from_attribute($block) {
    if (!empty($block['attrs']['copyrightInfo'])) {
        // Extract image ID from the same block using the same logic as metadata function
        $image_id = null;
        if ($block['blockName'] === 'core/image' && !empty($block['attrs']['id'])) {
            $image_id = $block['attrs']['id'];
        } else if ($block['blockName'] === 'core/media-text' && !empty($block['attrs']['mediaId'])) {
            $image_id = $block['attrs']['mediaId'];
        } else if ($block['blockName'] === 'core/cover' && !empty($block['attrs']['id'])) {
            $image_id = $block['attrs']['id'];
        } else if ($block['blockName'] === 'fau-elemental/fau-big-teaser' && !empty($block['attrs']['image']['id'])) {
            $image_id = $block['attrs']['image']['id'];
        }
        
        return array(
            'text' => $block['attrs']['copyrightInfo'],
            'image_id' => $image_id
        );
    }
    return null;
}

/**
 * Get the copyright info from the block or image metadata if it is set. null if not.
 * Works for core/image, core/media-text, core/cover, and featured image blocks.
 * 
 * @param array $block a single block
 * @return array|null Array with 'text' and 'image_id' keys, or null if not present
 */
function fau_elemental_gather_copyright_info_from_metadata($block) {
    $image_id = null;
    if ($block['blockName'] === 'core/image' && !empty($block['attrs']['id'])) {
        $image_id = $block['attrs']['id'];
    } else if ($block['blockName'] === 'core/media-text' && !empty($block['attrs']['mediaId'])) {
        $image_id = $block['attrs']['mediaId'];
    } else if ($block['blockName'] === 'core/cover' && !empty($block['attrs']['id'])) {
        $image_id = $block['attrs']['id'];
    } else if ($block['blockName'] === 'fau-elemental/fau-big-teaser' && !empty($block['attrs']['image']['id'])) {
        $image_id = $block['attrs']['image']['id'];
    } else {
        return null;
    }

    $image_metadata = wp_get_attachment_metadata($image_id);
    
    // Check for copyright info in image metadata
    if (!empty($image_metadata['image_meta']['copyright'])) {
        return array(
            'text' => $image_metadata['image_meta']['copyright'],
            'image_id' => $image_id
        );
    }
    
    // Check for copyright info in image description
    $image_description = get_post_field('post_content', $image_id);
    if (!empty($image_description)) {
        return array(
            'text' => $image_description,
            'image_id' => $image_id
        );
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
 * Gather copyright information from a specific image ID
 *
 * @param int $image_id The image attachment ID
 * @param string $copyright_prio Copyright priority setting
 * @return array Array of copyright information
 */
function fau_elemental_gather_copyright_info_from_image_id($image_id, $copyright_prio) {
    $copyright_info = array();
    
    if (!$image_id) {
        return $copyright_info;
    }

    $image_metadata = wp_get_attachment_metadata($image_id);
    
    // Check for copyright info in image metadata
    if (!empty($image_metadata['image_meta']['copyright'])) {
        $copyright_info[] = array(
            'text' => $image_metadata['image_meta']['copyright'],
            'image_id' => $image_id
        );
    }
    
    // Check for copyright info in image description
    $image_description = get_post_field('post_content', $image_id);
    if (!empty($image_description)) {
        $copyright_info[] = array(
            'text' => $image_description,
            'image_id' => $image_id
        );
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
        
        // First check for featured image copyright info if the post has a featured image
        $copyright_info = array();
        if (has_post_thumbnail()) {
            $featured_image_id = get_post_thumbnail_id();
            $featured_image_copyright = fau_elemental_gather_copyright_info_from_image_id($featured_image_id, $copyright_prio);
            if (!empty($featured_image_copyright)) {
                $copyright_info = array_merge($copyright_info, $featured_image_copyright);
            }
        }

        // Then recursively gather copyright info from all blocks in the content
        $content_copyright_info = fau_elemental_gather_copyright_info_recursive($blocks, $copyright_prio);
        $copyright_info = array_merge($copyright_info, $content_copyright_info);

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
        $output .= '<span class="copyright-info-label">' . esc_html__('Image sources:', 'fau-elemental') . '</span>';
        $output .= '<ul class="copyright-info-list">';
        
        foreach ($copyright_info as $info) {
            $text = $info['text'];
            $image_id = $info['image_id'];
            
            if (!empty($image_id)) {
                // Create link to the image
                $image_url = wp_get_attachment_url($image_id);
                if ($image_url) {
                    $output .= '<li><a href="' . esc_url($image_url) . '">' . esc_html($text) . '</a></li>';
                } else {
                    $output .= '<li>' . esc_html($text) . '</li>';
                }
            } else {
                // No image ID, just display as text
                $output .= '<li>' . esc_html($text) . '</li>';
            }
        }
        
        $output .= '</ul>';
        $output .= '</div>';

        return $output;
    }
} 

echo render_block_fau_copyright_info($attributes, $content);