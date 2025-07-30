<?php
/**
 * Server-side rendering of the FAU Logo Grid block.
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
    'logos' => []
]);

// Sanitize attributes
$logos = $attributes['logos'];

// Add wrapper classes
$base_classes = 'fau-logo-grid';

$wrapper_attributes = get_block_wrapper_attributes([
    'class' => $base_classes
]);

// Build the output HTML
$output = '<div ' . $wrapper_attributes . '>';
$output .= '<div class="fau-logo-grid__container">';

if ( ! empty( $logos ) && is_array( $logos ) ) {
    foreach ( $logos as $index => $logo ) {
        // Skip invalid logo entries
        if ( ! $logo || ! is_array( $logo ) ) {
            continue;
        }

        // Check if logo has an image URL
        if ( empty( $logo['imageUrl'] ) ) {
            continue;
        }

        // Sanitize logo data
        $image_url = esc_url( $logo['imageUrl'] );
        $link_url = ! empty( $logo['link'] ) ? esc_url( $logo['link'] ) : '';
        
        // Determine if this logo should be marked as selected
        // You can modify this logic based on your needs:
        // - Mark first logo as selected: $index === 0
        // - Mark logos with specific category: $logo['category'] === 'featured'
        // - Mark logos with specific link: strpos($logo['link'], 'current') !== false
        $is_selected = $index === 0; // Example: mark first logo as selected
        
        $item_class = 'fau-logo-grid__item';
        if ( $is_selected ) {
            $item_class .= ' fau-logo-grid__item--selected';
        }
        
        $output .= '<div class="' . $item_class . '">';
        
        if ( $link_url ) {
            $output .= '<a href="' . $link_url . '" class="fau-logo-grid__link">';
            $output .= '<img src="' . $image_url . '" alt="" class="fau-logo-grid__image" loading="lazy" />';
            $output .= '</a>';
        } else {
            $output .= '<img src="' . $image_url . '" alt="" class="fau-logo-grid__image" loading="lazy" />';
        }
        
        $output .= '</div>';
    }
}

$output .= '</div>';
$output .= '</div>';

// Echo the output
echo $output; 