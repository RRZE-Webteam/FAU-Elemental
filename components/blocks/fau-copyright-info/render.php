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
 * Validate that a post ID belongs to an image attachment.
 *
 * Block markup can contain stale IDs after content is copied or imported. On a
 * different site, such an ID may belong to a page or another post type.
 *
 * @param mixed $image_id Potential attachment ID.
 * @return int|null Valid image attachment ID, or null.
 */
if (!function_exists('fau_elemental_validate_image_attachment_id')) {
    function fau_elemental_validate_image_attachment_id($image_id) {
        $image_id = absint($image_id);

        if (
            !$image_id ||
            get_post_type($image_id) !== 'attachment' ||
            !wp_attachment_is_image($image_id)
        ) {
            return null;
        }

        return $image_id;
    }
}

/**
 * Get and validate the image attachment ID used by a supported block.
 *
 * @param array $block A parsed block.
 * @return int|null Valid image attachment ID, or null.
 */
if (!function_exists('fau_elemental_get_block_image_id')) {
    function fau_elemental_get_block_image_id($block) {
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

        return fau_elemental_validate_image_attachment_id($image_id);
    }
}

/**
 * Get the copyright info from a block attribute 'copyrightInfo' if it is set. 
 * null if not.
 * 
 * @param array $block a single block
 * @return array|null Array with 'text' and 'image_id' keys, or null if not present
 */
if (!function_exists('fau_elemental_gather_copyright_info_from_attribute')) {
    function fau_elemental_gather_copyright_info_from_attribute($block) {
        if (!empty($block['attrs']['copyrightInfo'])) {
            return array(
                'text' => $block['attrs']['copyrightInfo'],
                'image_id' => fau_elemental_get_block_image_id($block)
            );
        }
        return null;
    }
}

/**
 * Get the copyright info from the block or image metadata if it is set. null if not.
 * Works for core/image, core/media-text, core/cover, and featured image blocks.
 * 
 * @param array $block a single block
 * @return array|null Array with 'text' and 'image_id' keys, or null if not present
 */
if (!function_exists('fau_elemental_gather_copyright_info_from_metadata')) {
    function fau_elemental_gather_copyright_info_from_metadata($block) {
        $image_id = fau_elemental_get_block_image_id($block);

        if (!$image_id) {
            return null;
        }

        $copyright_info = fau_elemental_gather_copyright_info_from_image_id($image_id, 'iptc');

        return !empty($copyright_info) ? $copyright_info[0] : null;
    }
}

/**
 * Recursively gather copyright information from blocks and their inner blocks
 *
 * @param array $blocks Array of blocks to check
 * @param string $copyright_prio A setting that sets if the copyright 'field' 
 *                              or the 'iptc' metadata should have priority.
 * @return array Array of copyright information
 */
if (!function_exists('fau_elemental_gather_copyright_info_recursive')) {
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
}

/**
 * Gather copyright information from a specific image ID
 *
 * @param int $image_id The image attachment ID
 * @param string $copyright_prio Copyright priority setting
 * @return array Array of copyright information
 */
if (!function_exists('fau_elemental_gather_copyright_info_from_image_id')) {
    function fau_elemental_gather_copyright_info_from_image_id($image_id, $copyright_prio) {
        $image_id = fau_elemental_validate_image_attachment_id($image_id);

        if (!$image_id) {
            return array();
        }

        $image_metadata = wp_get_attachment_metadata($image_id);
        $metadata_copyright = !empty($image_metadata['image_meta']['copyright'])
            ? $image_metadata['image_meta']['copyright']
            : '';
        $image_description = get_post_field('post_content', $image_id);

        // The attachment description acts as the field value for featured images,
        // which do not have a block-level copyrightInfo attribute.
        $candidates = $copyright_prio === 'field'
            ? array($image_description, $metadata_copyright)
            : array($metadata_copyright, $image_description);

        foreach ($candidates as $copyright_text) {
            if (!empty($copyright_text)) {
                return array(
                    array(
                        'text' => $copyright_text,
                        'image_id' => $image_id
                    )
                );
            }
        }

        return array();
    }
}

/**
 * Remove duplicate copyright entries while preserving their original order.
 *
 * @param array $copyright_info Copyright entries.
 * @return array Deduplicated copyright entries.
 */
if (!function_exists('fau_elemental_deduplicate_copyright_info')) {
    function fau_elemental_deduplicate_copyright_info($copyright_info) {
        $deduplicated = array();
        $seen = array();

        foreach ($copyright_info as $info) {
            if (!is_array($info) || !array_key_exists('text', $info)) {
                continue;
            }

            $image_id = !empty($info['image_id']) ? absint($info['image_id']) : 0;
            $key = $image_id . ':' . md5((string) $info['text']);

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $deduplicated[] = array(
                'text' => $info['text'],
                'image_id' => $image_id ?: null
            );
        }

        return $deduplicated;
    }
}

/**
 * Gather copyright information from all blocks in the content
 *
 * @return array Array of copyright information
 */
if (!function_exists('fau_elemental_gather_copyright_info')) {
    function fau_elemental_gather_copyright_info($post_id = 0) {
        $copyright_info = array();

        $post_id = absint($post_id);
        if (!$post_id && is_singular()) {
            $post_id = absint(get_queried_object_id());
        }

        // Archive and search pages do not have one authoritative source post.
        if (!$post_id) {
            $filtered_info = apply_filters('fau_elemental_copyright_info', $copyright_info, 0);
            return is_array($filtered_info)
                ? fau_elemental_deduplicate_copyright_info($filtered_info)
                : array();
        }

        $source_post = get_post($post_id);
        if (!$source_post) {
            $filtered_info = apply_filters('fau_elemental_copyright_info', $copyright_info, $post_id);
            return is_array($filtered_info)
                ? fau_elemental_deduplicate_copyright_info($filtered_info)
                : array();
        }

        // Parse blocks to find blocks with copyright info
        $blocks = parse_blocks($source_post->post_content);

        // Get the copyright info priority from the options
        $copyright_prio = get_theme_mod('faue_copyright_info_priority', 'field');
        
        // First check for featured image copyright info if the post has a featured image
        if (has_post_thumbnail($post_id)) {
            $featured_image_id = get_post_thumbnail_id($post_id);
            $featured_image_copyright = fau_elemental_gather_copyright_info_from_image_id($featured_image_id, $copyright_prio);
            if (!empty($featured_image_copyright)) {
                $copyright_info = array_merge($copyright_info, $featured_image_copyright);
            }
        }

        // Then recursively gather copyright info from all blocks in the content
        $content_copyright_info = fau_elemental_gather_copyright_info_recursive($blocks, $copyright_prio);
        $copyright_info = array_merge($copyright_info, $content_copyright_info);

        // Allow other plugins to add their copyright information
        $filtered_info = apply_filters('fau_elemental_copyright_info', $copyright_info, $post_id);

        return is_array($filtered_info)
            ? fau_elemental_deduplicate_copyright_info($filtered_info)
            : array();
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
        $post_id = !empty($attributes['postId']) ? absint($attributes['postId']) : 0;
        $copyright_info = fau_elemental_gather_copyright_info($post_id);

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
