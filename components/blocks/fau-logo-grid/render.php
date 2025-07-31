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

$wrapper_attributes = get_block_wrapper_attributes([
    'class' => 'fau-logo-grid'
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
        
        // Get image dimensions - use stored values or fetch from media library
        $image_width = null;
        $image_height = null;
        
        if ( ! empty( $logo['width'] ) && ! empty( $logo['height'] ) ) {
            // Use stored dimensions
            $image_width = intval( $logo['width'] );
            $image_height = intval( $logo['height'] );
        } elseif ( ! empty( $logo['imageId'] ) ) {
            // Fetch dimensions from media library
            $image_data = wp_get_attachment_image_src( $logo['imageId'], 'full' );
            if ( $image_data ) {
                $image_width = $image_data[1];
                $image_height = $image_data[2];
            }
        }
        
        // Get alt text from logo data or media attachment
        $alt_text = '';
        if ( ! empty( $logo['alt'] ) ) {
            $alt_text = $logo['alt'];
        } elseif ( ! empty( $logo['imageId'] ) ) {
            // Get alt text from media attachment
            $attachment = get_post( $logo['imageId'] );
            if ( $attachment ) {
                $alt_text = get_post_meta( $logo['imageId'], '_wp_attachment_image_alt', true );
                if ( empty( $alt_text ) ) {
                    $alt_text = $attachment->post_title;
                }
            }
        }
        
        // Fallback to generic alt text
        if ( empty( $alt_text ) ) {
            $alt_text = __( 'Logo', 'fau-elemental' );
        }
        
        $item_class = 'fau-logo-grid__item';
        
        $output .= '<div class="' . $item_class . '">';
        
        if ( $link_url ) {
            // Check if this is an external link
            $parsed_url = parse_url( $link_url );
            $parsed_site_url = parse_url( get_site_url() );
            $is_external = $parsed_url && $parsed_site_url && 
                          ( $parsed_url['host'] !== $parsed_site_url['host'] );
            
            // Build link attributes
            $link_attributes = 'href="' . $link_url . '" class="fau-logo-grid__link"';
            
            // Add rel attributes for external links
            if ( $is_external ) {
                $link_attributes .= ' rel="noopener noreferrer"';
            }
            
            $output .= '<a ' . $link_attributes . '>';
            $output .= '<img src="' . $image_url . '" alt="' . esc_attr( $alt_text ) . '" class="fau-logo-grid__image" loading="lazy"' . 
                      ( $image_width ? ' width="' . esc_attr( $image_width ) . '"' : '' ) .
                      ( $image_height ? ' height="' . esc_attr( $image_height ) . '"' : '' ) . ' />';
            $output .= '</a>';
        } else {
            $output .= '<img src="' . $image_url . '" alt="' . esc_attr( $alt_text ) . '" class="fau-logo-grid__image" loading="lazy"' . 
                      ( $image_width ? ' width="' . esc_attr( $image_width ) . '"' : '' ) .
                      ( $image_height ? ' height="' . esc_attr( $image_height ) . '"' : '' ) . ' />';
        }
        
        $output .= '</div>'; // Close fau-logo-grid__item
    }
}

$output .= '</div>'; // Close fau-logo-grid__container
$output .= '</div>'; // Close wrapper

// Echo the output
echo $output; 