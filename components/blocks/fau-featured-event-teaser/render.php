<?php
/**
 * Server-side rendering of the `fau-elemental/featured-event-teaser` block.
 *
 * @package FAU_Elemental
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}



/**
 * Renders the `fau-elemental/featured-event-teaser` block on the server.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block default content.
 * @param WP_Block $block      Block instance.
 * @return string Returns the block content.
 */
function render_block_fau_featured_event_teaser( $attributes, $content, $block ) {
    // Extract attributes with defaults
    $event_title = $attributes['eventTitle'] ?? '';
    $event_description = $attributes['eventDescription'] ?? '';
    $event_date = $attributes['eventDate'] ?? '';
    $button_text = $attributes['buttonText'] ?? 'Mehr erfahren';
    $button_url = $attributes['buttonUrl'] ?? '#';
    $image_url = $attributes['imageUrl'] ?? '';
    $image_alt = $attributes['imageAlt'] ?? '';

    // Simple date processing for testing
    $day = '01';
    $month_year = 'Jan 2024';
    $datetime_attr = '2024-01-01';
    
    // If no date provided, use current date
    if ( empty( $event_date ) ) {
        $today = new DateTime();
        $day = $today->format( 'j' );
        $month = $today->format( 'n' );
        $year = $today->format( 'Y' );
        $month_year = 'Jan ' . $year; // Simplified for testing
        $datetime_attr = $year . '-' . str_pad( $month, 2, '0', STR_PAD_LEFT ) . '-' . str_pad( $day, 2, '0', STR_PAD_LEFT );
    } else {
        // Simple processing for saved dates
        $date_parts = explode( ' ', $event_date );
        if ( count( $date_parts ) >= 3 ) {
            $day = $date_parts[0];
            $month = $date_parts[1];
            $year = $date_parts[2];
            $month_year = 'Jan ' . $year; // Simplified for testing
            $datetime_attr = $year . '-' . str_pad( $month, 2, '0', STR_PAD_LEFT ) . '-' . str_pad( $day, 2, '0', STR_PAD_LEFT );
        }
    }

    // Get block wrapper attributes
    $wrapper_attributes = get_block_wrapper_attributes([
        'class' => 'wp-block-fau-elemental-featured-event-teaser'
    ]);

    // Start building the output
    $output = '<div ' . $wrapper_attributes . '>';
    $output .= '<div class="featured-event-content">';
    
    // Left content
    $output .= '<div class="content-left">';
    if ( $event_title ) {
        $output .= '<h2>' . esc_html( $event_title ) . '</h2>';
    }
    if ( $event_description ) {
        $output .= '<p>' . esc_html( $event_description ) . '</p>';
    }
    $output .= '<div class="wp-block-buttons">';
    $output .= '<div class="wp-block-button">';
    $output .= '<a class="wp-block-button__link" href="' . esc_url( $button_url ) . '">';
    $output .= esc_html( $button_text );
    $output .= '</a>';
    $output .= '</div>';
    $output .= '</div>';
    $output .= '</div>';
    
    // Right content
    $output .= '<div class="content-right">';
    $output .= '<time datetime="' . esc_attr( $datetime_attr ) . '">';
    $output .= '<span class="date-day">' . esc_html( $day ) . '</span>';
    $output .= '<span class="date-month-year">' . esc_html( $month_year ) . '</span>';
    $output .= '</time>';
    
    if ( $image_url ) {
        $output .= '<div class="featured-event-image">';
        $output .= '<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $image_alt ) . '" />';
        $output .= '</div>';
    }
    $output .= '</div>';
    
    $output .= '</div>';
    $output .= '</div>';

    return $output;
}

// Render the block
echo render_block_fau_featured_event_teaser( $attributes, $content, $block ); 