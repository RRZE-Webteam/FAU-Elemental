<?php
/**
 * Server-side rendering for the FAU Big Teaser block.
 *
 * @package FAU-Elemental
 * 
 * @var array    $attributes Block attributes.
 * @var string   $content    Block default content.
 * @var WP_Block $block      Block instance.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Set default attributes
$attributes = wp_parse_args($attributes, [
    'headlineLevel' => 'h3',
    'headline' => '',
    'teaserText' => '',
    'linkText' => '',
    'linkUrl' => '',
    'image' => null
]);

// Sanitize attributes
$headline_level = sanitize_text_field($attributes['headlineLevel']);
// Ensure headline level is valid (h2-h6)
$valid_levels = ['h2', 'h3', 'h4', 'h5', 'h6'];
$headline_level = in_array($headline_level, $valid_levels) ? $headline_level : 'h3';

$headline     = sanitize_text_field($attributes['headline']);
$teaser_text  = sanitize_textarea_field($attributes['teaserText']);
$link_text    = sanitize_text_field($attributes['linkText']);
$link_url     = esc_url_raw($attributes['linkUrl']);
$image        = $attributes['image'];

/**
 * Helper function to truncate text
 *
 * @param string $text   Text to truncate.
 * @param int    $length Maximum length.
 * @return string Truncated text.
 */
$truncate_text = function( $text, $length ) {
    if ( empty( $text ) || mb_strlen( $text ) <= $length ) {
        return $text;
    }

    $truncated = mb_substr( $text, 0, $length );
    $last_space = mb_strrpos( $truncated, ' ' );

    if ( $last_space !== false && $last_space > $length * 0.8 ) {
        return mb_substr( $truncated, 0, $last_space ) . '…';
    }

    return $truncated . '…';
};

// Apply character limits
$truncated_headline    = $truncate_text( $headline, 100 );
$truncated_teaser_text = $truncate_text( $teaser_text, 200 );
$truncated_link_text   = $truncate_text( $link_text, 40 );

// Get wrapper attributes
$wrapper_attributes = get_block_wrapper_attributes([
    'class' => 'fau-big-teaser'
]);

// Build the HTML output
$output = '<section ' . $wrapper_attributes . '>';
$output .= '<div class="fau-big-teaser__content">';

// Headline
if ( ! empty( $truncated_headline ) ) {
    $output .= '<' . $headline_level . ' class="fau-big-teaser__headline">' . esc_html( $truncated_headline ) . '</' . $headline_level . '>';
}

// Teaser text
if ( ! empty( $truncated_teaser_text ) ) {
    $output .= '<p class="fau-big-teaser__teaser-text">' . esc_html( $truncated_teaser_text ) . '</p>';
}

// Link button
if ( ! empty( $truncated_link_text ) && ! empty( $link_url ) ) {
    $output .= '<div class="wp-block-buttons is-layout-flex wp-block-buttons-is-layout-flex">';
    $output .= '<div class="wp-block-button is-style-tertiary">';
    $output .= '<a href="' . esc_url( $link_url ) . '" class="wp-block-button__link wp-element-button">';
    $output .= esc_html( $truncated_link_text );
    $output .= '</a>';
    $output .= '</div>';
    $output .= '</div>';
}

$output .= '</div>';

// Image
if ( ! empty( $image ) && ! empty( $image['url'] ) ) {
    $image_alt = ! empty( $image['alt'] ) ? $image['alt'] : ( ! empty( $truncated_headline ) ? $truncated_headline : '' );
    $output .= '<div class="fau-big-teaser__image">';
    $output .= '<img src="' . esc_url( $image['url'] ) . '" alt="' . esc_attr( $image_alt ) . '" loading="lazy" />';
    $output .= '</div>';
}

$output .= '</section>';

echo $output; 