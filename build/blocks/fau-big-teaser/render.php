<?php
/**
 * Server-side rendering of the `fau-elemental/fau-big-teaser` block.
 *
 * @package FAU_Elemental
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'render_block_fau_big_teaser' ) ) {
    /**
     * Renders the `fau-elemental/fau-big-teaser` block on the server.
     *
     * @param array    $attributes Block attributes.
     * @param string   $content    Block default content.
     * @param WP_Block $block      Block instance.
     * @return string Returns the rendered big teaser HTML.
     */
    function render_block_fau_big_teaser( $attributes, $content, $block ) {
        // Extract and sanitize attributes
        $roof_line = !empty($attributes['roofLine']) ? $attributes['roofLine'] : '';
        $headline = !empty($attributes['headline']) ? $attributes['headline'] : '';
        $teaser_text = !empty($attributes['teaserText']) ? $attributes['teaserText'] : '';
        $link_text = !empty($attributes['linkText']) ? $attributes['linkText'] : '';
        $link_url = !empty($attributes['linkUrl']) ? $attributes['linkUrl'] : '';
        $link_target = !empty($attributes['linkTarget']) ? $attributes['linkTarget'] : '_self';
        $image = !empty($attributes['image']) ? $attributes['image'] : null;
        $show_roof_line = !empty($attributes['showRoofLine']) ? $attributes['showRoofLine'] : false;

        // Apply character limits (truncate if necessary)
        $roof_line = fau_truncate_text($roof_line, 50); // Reasonable limit for roof line
        $headline = fau_truncate_text($headline, 100);
        $teaser_text = fau_truncate_text($teaser_text, 200);
        $link_text = fau_truncate_text($link_text, 40);

        // Build wrapper classes
        $wrapper_classes = ['fau-big-teaser'];

        $wrapper_attributes = get_block_wrapper_attributes([
            'class' => implode(' ', $wrapper_classes)
        ]);

        // Start building the output
        $output = sprintf('<section %s>', $wrapper_attributes);
        
        // Add image if provided
        if ($image && isset($image['url'])) {
            $image_alt = isset($image['alt']) ? $image['alt'] : $headline;
            $output .= '<div class="fau-big-teaser__image">';
            $output .= sprintf(
                '<img src="%s" alt="%s" loading="lazy" />',
                esc_url($image['url']),
                esc_attr($image_alt)
            );
            $output .= '</div>';
        }

        // Content wrapper
        $output .= '<div class="fau-big-teaser__content">';

        // Roof line (optional)
        if ($show_roof_line && !empty($roof_line)) {
            $output .= sprintf(
                '<div class="fau-big-teaser__roof-line">%s</div>',
                esc_html($roof_line)
            );
        }

        // Headline
        if (!empty($headline)) {
            $output .= sprintf(
                '<h3 class="fau-big-teaser__headline">%s</h3>',
                esc_html($headline)
            );
        }

        // Teaser text
        if (!empty($teaser_text)) {
            $output .= sprintf(
                '<p class="fau-big-teaser__teaser-text">%s</p>',
                esc_html($teaser_text)
            );
        }

        // Link
        if (!empty($link_text) && !empty($link_url)) {
            $output .= sprintf(
                '<a href="%s" target="%s" class="fau-big-teaser__link" rel="%s">%s</a>',
                esc_url($link_url),
                esc_attr($link_target),
                $link_target === '_blank' ? 'noopener noreferrer' : '',
                esc_html($link_text)
            );
        }

        $output .= '</div>'; // Close content wrapper
        $output .= '</section>'; // Close main wrapper

        return $output;
    }
}

if ( ! function_exists( 'fau_truncate_text' ) ) {
    /**
     * Truncate text to a specified length while preserving word boundaries
     *
     * @param string $text The text to truncate
     * @param int    $length Maximum length
     * @return string Truncated text
     */
    function fau_truncate_text( $text, $length ) {
        if ( strlen( $text ) <= $length ) {
            return $text;
        }
        
        $truncated = substr( $text, 0, $length );
        $last_space = strrpos( $truncated, ' ' );
        
        if ( $last_space !== false && $last_space > $length * 0.8 ) {
            $truncated = substr( $truncated, 0, $last_space );
        }
        
        return $truncated . '...';
    }
} 