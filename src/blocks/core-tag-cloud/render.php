<?php
/**
 * Server-side rendering of the tag cloud block.
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Renders the tag cloud block with a heading.
 *
 * @param string $block_content The block content.
 * @param array  $block The block.
 * @return string Modified block content.
 */
function fau_elemental_render_tag_cloud($block_content, $block) {
    if ($block['blockName'] !== 'core/tag-cloud') {
        return $block_content;
    }

    // Remove inline font-size styles
    $block_content = preg_replace('/style="[^"]*font-size[^"]*"/', '', $block_content);

    $wrapper_attributes = get_block_wrapper_attributes([
        'class' => 'wp-block-tag-cloud-wrapper'
    ]);

    $output = '<div ' . $wrapper_attributes . '>';
    $output .= '<div class="wp-block-tag-cloud__heading">' . esc_html__('Keywords', 'fau-elemental') . '</div>';
    $output .= $block_content;
    $output .= '</div>';

    return $output;
}

add_filter('render_block', 'fau_elemental_render_tag_cloud', 10, 2);