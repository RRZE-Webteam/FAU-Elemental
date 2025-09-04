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
function fau_elemental_add_image_fullscreen($block_content, $block)
{
    // Only modify core/image blocks
    if (isset($block['blockName']) && 'core/image' === $block['blockName']) {
        // Don't modify if the content is null
        if (is_null($block_content)) {
            return $block_content;
        }

        // Get image data directly from block attributes
        $attachment_id = isset($block['attrs']['id']) ? (int) $block['attrs']['id'] : 0;
        if (!$attachment_id) {
            return $block_content;
        }
        $img_src = wp_get_attachment_url($attachment_id) ?: '';

        // Determine natural image dimensions from attachment metadata
        $img_width = 0;
        $img_height = 0;
        $meta = wp_get_attachment_metadata($attachment_id);
        if (is_array($meta)) {
            if (isset($meta['width'])) {
                $img_width = (int) $meta['width'];
            }
            if (isset($meta['height'])) {
                $img_height = (int) $meta['height'];
            }
        }

        // If we have dimensions, decide whether to add the class
        if ($img_width > 0 && $img_height > 0) {
            $ASPECT_RATIO_TOLERANCE = 0.01; // same as frontend/editor
            $TARGET_ASPECT_RATIO = 1.5;     // 3:2
            $MIN_ASPECT_RATIO = $TARGET_ASPECT_RATIO - $ASPECT_RATIO_TOLERANCE; // 1.49

            $natural_aspect_ratio = $img_width / $img_height;
            $should_add_class = ($natural_aspect_ratio < $MIN_ASPECT_RATIO);

            if ($should_add_class) {
                // Inject 'tall-image' class into the <img> tag (no callback to avoid accidental numeric output)
                if (preg_match('/<img\b[^>]*\bclass=([\'\"])(.*?)\1/i', $block_content)) {
                    // Append class to existing class attribute
                    $block_content = preg_replace(
                        '/(<img\b[^>]*\bclass=)([\'\"])([^\'\"]*)(\2)/i',
                        '$1$2$3 tall-image$2',
                        $block_content,
                        1
                    );
                } else {
                    // Add new class attribute
                    $block_content = preg_replace(
                        '/(<img\b)([^>]*>)/i',
                        '$1 class="tall-image"$2',
                        $block_content,
                        1
                    );
                }
            }
        }

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

        // Insert the button inside the image wrapper
        $block_content = preg_replace('/(<div class="image-wrapper">.*?)(<\/div>)/s', '$1' . $fullscreen_button . '$2', $block_content);
    }

    return $block_content;
}
add_filter('render_block', 'fau_elemental_add_image_fullscreen', 10, 2);

/**
 * Register the image scripts
 */
function faue_register_image_scripts()
{
    // Register fullscreen script
    $asset_file = include get_theme_file_path('build/js/image-fullscreen.asset.php');
    wp_register_script(
        'image-fullscreen',
        get_theme_file_uri('build/js/image-fullscreen.js'),
        array_merge($asset_file['dependencies'], ['jquery']),
        $asset_file['version'],
        true
    );
}
add_action('init', 'faue_register_image_scripts', 5);

/**
 * Extend core image block metadata to include our view scripts
 */
function faue_extend_core_image($metadata)
{
    if (!empty($metadata['name']) && 'core/image' === $metadata['name']) {
        $metadata['viewScript'] = array_merge(
            (array) ($metadata['viewScript'] ?? []),
            array('image-fullscreen')
        );
    }
    return $metadata;
}
add_filter('block_type_metadata', 'faue_extend_core_image');
