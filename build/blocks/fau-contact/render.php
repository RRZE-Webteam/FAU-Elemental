<?php
/**
 * Server-side rendering of the `fau-elemental/fau-contact` block.
 *
 * @package FAU_Elemental
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'render_block_fau_contact' ) ) {
    /**
     * Renders the `fau-elemental/fau-contact` block on the server.
     *
     * @param array    $attributes Block attributes.
     * @param string   $content    Block default content.
     * @param WP_Block $block      Block instance.
     * @return string Returns the contact block HTML.
     */
    function render_block_fau_contact( $attributes, $content, $block ) {
        // Ensure we have attributes
        if ( ! isset( $attributes ) ) {
            return;
        }

        // Extract attributes
        $show_top_line = $attributes['showTopLine'] ?? false;
        $top_line = $attributes['topLine'] ?? '';
        $headline = $attributes['headline'] ?? '';
        $show_address = $attributes['showAddress'] ?? false;
        $address = $attributes['address'] ?? '';
        $show_opening_hours = $attributes['showOpeningHours'] ?? false;
        $opening_hours = $attributes['openingHours'] ?? '';
        $contact_links = $attributes['contactLinks'] ?? [];
        $social_media = $attributes['socialMedia'] ?? [];

        // Build output
        $output = '';

        // Start contact block with semantic HTML
        $output .= '<section class="fau-contact-block" role="region" aria-labelledby="contact-heading">';

        // Top line
        if ( $show_top_line && ! empty( $top_line ) ) {
            $output .= '<div class="contact-topline">' . esc_html( $top_line ) . '</div>';
        }

        // Main layout
        $output .= '<div class="contact-layout">';

        // Content section
        $output .= '<div class="contact-content">';

        // Headline with proper ID for aria-labelledby
        if ( ! empty( $headline ) ) {
            $output .= '<h2 id="contact-heading" class="contact-headline">' . wp_kses_post( $headline ) . '</h2>';
        }

        // Address section
        if ( $show_address && ! empty( $address ) ) {
            $output .= '<div class="contact-section">';
            $output .= '<h3>' . esc_html__( 'Adresse', 'fau-elemental' ) . '</h3>';
            $output .= '<div class="contact-text">' . nl2br( esc_html( $address ) ) . '</div>';
            $output .= '</div>';
        }

        // Opening hours section
        if ( $show_opening_hours && ! empty( $opening_hours ) ) {
            $output .= '<div class="contact-section">';
            $output .= '<h3>' . esc_html__( 'Sprechzeiten', 'fau-elemental' ) . '</h3>';
            $output .= '<div class="contact-text">' . nl2br( esc_html( $opening_hours ) ) . '</div>';
            $output .= '</div>';
        }

        // Contact links section
        if ( ! empty( $contact_links ) ) {
            $output .= '<div class="contact-section">';
            $output .= '<h3>' . esc_html__( 'Kontakt', 'fau-elemental' ) . '</h3>';
            
            foreach ( $contact_links as $link ) {
                if ( ! empty( $link['value'] ) ) {
                    $icon_class = fau_elemental_get_contact_icon_class( $link['type'] );
                    $formatted_link = fau_elemental_format_contact_link( $link['type'], $link['value'] );
                    $display_text = ! empty( $link['label'] ) ? $link['label'] : $link['value'];
                    
                    $output .= '<div class="contact-link contact-link-' . esc_attr( $link['type'] ) . '">';
                    $output .= '<i class="' . esc_attr( $icon_class ) . '" aria-hidden="true"></i>';
                    if ( in_array( $link['type'], [ 'phone', 'email' ] ) || filter_var( $link['value'], FILTER_VALIDATE_URL ) ) {
                        $output .= '<a href="' . esc_url( $formatted_link ) . '">' . esc_html( $display_text ) . '</a>';
                    } else {
                        $output .= '<span>' . esc_html( $display_text ) . '</span>';
                    }
                    $output .= '</div>';
                }
            }
            
            $output .= '</div>';
        }

        // Social media links section (using footer styling approach)
        if ( ! empty( $social_media ) ) {
            $output .= '<div class="contact-section">';
            $output .= '<div class="social-links">';
            
            // Used the same order and platform names as the footer
            $social_platforms = [
                'instagram' => 'Instagram',
                'facebook' => 'Facebook',
                'xing' => 'Xing',
                'linkedin' => 'LinkedIn',
                'twitter' => 'Twitter/X',
                'mastodon' => 'Mastodon',
                'bluesky' => 'Bluesky',
                'youtube' => 'YouTube',
                'tiktok' => 'TikTok'
            ];
            
            foreach ( $social_platforms as $platform => $label ) {
                if ( ! empty( $social_media[ $platform ] ) ) {
                    $output .= '<a href="' . esc_url( $social_media[ $platform ] ) . '" class="social-link ' . esc_attr( $platform ) . '" aria-label="' . esc_attr( $label ) . '" rel="noopener noreferrer">';
                    $output .= '<!-- ' . esc_html( $label ) . ' -->';
                    $output .= '</a>';
                }
            }
            
            $output .= '</div>';
            $output .= '</div>';
        }

        $output .= '</div>'; // End contact-content

        // Image section - using InnerBlocks content (core image block)
        $output .= '<div class="contact-image-section">';
        $output .= $content; // This will contain the core image block HTML
        $output .= '</div>';

        $output .= '</div>'; // End contact-layout
        $output .= '</section>'; // End fau-contact-block

        return $output;
    }
}

if ( ! function_exists( 'fau_elemental_get_contact_icon_class' ) ) {
    /**
     * Get the appropriate icon class for contact types.
     *
     * @param string $type The contact type.
     * @return string The icon class.
     */
    function fau_elemental_get_contact_icon_class( $type ) {
        $icon_classes = [
            'phone' => 'fas fa-phone',
            'email' => 'fas fa-envelope',
            'messenger' => 'fas fa-comments',
            'website' => 'fas fa-globe',
            'matrix' => 'fas fa-matrix-org',
        ];

        return $icon_classes[ $type ] ?? 'fas fa-link';
    }
}

if ( ! function_exists( 'fau_elemental_format_contact_link' ) ) {
    /**
     * Format contact links based on their type.
     *
     * @param string $type The contact type.
     * @param string $value The contact value.
     * @return string The formatted link.
     */
    function fau_elemental_format_contact_link( $type, $value ) {
        switch ( $type ) {
            case 'phone':
                // Remove spaces and format for tel: link
                $clean_phone = preg_replace( '/[^+\d]/', '', $value );
                return 'tel:' . $clean_phone;
                
            case 'email':
                return 'mailto:' . $value;
                
            case 'messenger':
            case 'matrix':
            case 'website':
            default:
                // For messenger, matrix, and website, assume it's already a proper URL
                // If it doesn't start with http, add https://
                if ( ! preg_match( '/^https?:\/\//', $value ) ) {
                    return 'https://' . $value;
                }
                return $value;
        }
    }
} 