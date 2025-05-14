<?php
/**
 * Server-side rendering of the `fau-elemental/fau-info-box` block.
 *
 * @package FAU_Elemental
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'render_block_fau_info_box' ) ) {
    /**
     * Renders the `fau-elemental/fau-info-box` block on the server.
     *
     * @param array    $attributes Block attributes.
     * @param string   $content    Block default content.
     * @param WP_Block $block      Block instance.
     * @return string Returns the post content with the teaser grid.
     */
    function render_block_fau_info_box( $attributes, $content, $block ) {
        $variant = $attributes['variant'] ?? 'post';
        $selection_mode = $attributes['selectionMode'] ?? 'auto';
        $selected_posts = $attributes['selectedPosts'] ?? [];

        // Start building the output
        $wrapper_attributes = get_block_wrapper_attributes([
            'class' => 'fau-info-box',
            'role' => 'region',
            'aria-label' => __('Content grid', 'fau-elemental')
        ]);
        $output = sprintf('<div %s>', $wrapper_attributes);

        $output .= "Rendered by PHP";

        $output .= '</div>'; // Close fau-info-box

        return $output;
    }
}
