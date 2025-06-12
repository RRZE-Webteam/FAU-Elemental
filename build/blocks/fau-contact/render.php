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
        // Extract attributes with defaults
        $show_top_line = $attributes['showTopLine'] ?? false;
        $top_line = $attributes['topLine'] ?? '';
        $image_id = $attributes['imageId'] ?? 0;
        $image_url = $attributes['imageUrl'] ?? '';
        $image_alt = $attributes['imageAlt'] ?? '';
        $headline = $attributes['headline'] ?? '';
        $show_address = $attributes['showAddress'] ?? false;
        $address = $attributes['address'] ?? '';
        $show_opening_hours = $attributes['showOpeningHours'] ?? false;
        $opening_hours = $attributes['openingHours'] ?? '';
        $contact_links = $attributes['contactLinks'] ?? [];
        $social_links = $attributes['socialLinks'] ?? [];

        // If no headline, don't render the block
        if ( empty( $headline ) ) {
            return '';
        }

        // Start building the output
        $wrapper_attributes = get_block_wrapper_attributes([
            'class' => 'fau-contact-block',
        ]);

        $output = sprintf('<div %s>', $wrapper_attributes);

        // Top line (optional)
        if ( $show_top_line && ! empty( $top_line ) ) {
            $output .= sprintf(
                '<div class="contact-topline"><small>%s</small></div>',
                esc_html( $top_line )
            );
        }

        $output .= '<div class="contact-main">';

        // Image section
        if ( $image_id && $image_url ) {
            $output .= '<div class="contact-image">';
            $output .= sprintf(
                '<img src="%s" alt="%s" loading="lazy" />',
                esc_url( $image_url ),
                esc_attr( $image_alt )
            );
            $output .= '</div>';
        }

        // Content section
        $output .= '<div class="contact-content">';

        // Headline (required)
        $output .= sprintf(
            '<h3 class="contact-headline">%s</h3>',
            wp_kses_post( $headline )
        );

        // Address (optional)
        if ( $show_address && ! empty( $address ) ) {
            $output .= '<div class="contact-address">';
            $output .= sprintf('<strong>%s</strong>', esc_html__( 'Address:', 'fau-elemental' ));
            $output .= sprintf('<div class="address-content">%s</div>', nl2br( esc_html( $address ) ));
            $output .= '</div>';
        }

        // Opening hours (optional)
        if ( $show_opening_hours && ! empty( $opening_hours ) ) {
            $output .= '<div class="contact-hours">';
            $output .= sprintf('<strong>%s</strong>', esc_html__( 'Opening Hours:', 'fau-elemental' ));
            $output .= sprintf('<div class="hours-content">%s</div>', nl2br( esc_html( $opening_hours ) ));
            $output .= '</div>';
        }

        // Contact links (optional)
        if ( ! empty( $contact_links ) ) {
            $output .= '<div class="contact-links">';
            $output .= sprintf('<strong>%s</strong>', esc_html__( 'Contact:', 'fau-elemental' ));
            $output .= '<ul class="contact-links-list">';
            
            foreach ( $contact_links as $link ) {
                if ( ! empty( $link['value'] ) ) {
                    $icon_class = fau_elemental_get_contact_icon_class( $link['type'] );
                    $formatted_link = fau_elemental_format_contact_link( $link['type'], $link['value'] );
                    $label = ! empty( $link['label'] ) ? $link['label'] : $link['value'];
                    
                    $output .= sprintf(
                        '<li class="contact-link contact-link-%s"><i class="%s" aria-hidden="true"></i><a href="%s">%s</a></li>',
                        esc_attr( $link['type'] ),
                        esc_attr( $icon_class ),
                        esc_url( $formatted_link ),
                        esc_html( $label )
                    );
                }
            }
            
            $output .= '</ul>';
            $output .= '</div>';
        }

        // Social media links (optional, max 8)
        if ( ! empty( $social_links ) ) {
            $social_links = array_slice( $social_links, 0, 8 ); // Ensure max 8 links
            $output .= '<div class="social-links">';
            $output .= sprintf('<strong>%s</strong>', esc_html__( 'Social Media:', 'fau-elemental' ));
            $output .= '<ul class="social-links-list">';
            
            foreach ( $social_links as $link ) {
                if ( ! empty( $link['url'] ) ) {
                    $icon_class = fau_elemental_get_social_icon_class( $link['type'] );
                    $label = ! empty( $link['label'] ) ? $link['label'] : ucfirst( $link['type'] );
                    
                    $output .= sprintf(
                        '<li class="social-link social-link-%s"><a href="%s" target="_blank" rel="noopener noreferrer"><i class="%s" aria-hidden="true"></i><span class="screen-reader-text">%s</span><span aria-hidden="true">%s</span></a></li>',
                        esc_attr( $link['type'] ),
                        esc_url( $link['url'] ),
                        esc_attr( $icon_class ),
                        esc_html( sprintf( __( 'Visit %s profile', 'fau-elemental' ), $label ) ),
                        esc_html( $label )
                    );
                }
            }
            
            $output .= '</ul>';
            $output .= '</div>';
        }

        $output .= '</div>'; // Close contact-content
        $output .= '</div>'; // Close contact-main
        $output .= '</div>'; // Close wrapper

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

if ( ! function_exists( 'fau_elemental_get_social_icon_class' ) ) {
    /**
     * Get the appropriate icon class for social media types.
     *
     * @param string $type The social media type.
     * @return string The icon class.
     */
    function fau_elemental_get_social_icon_class( $type ) {
        $icon_classes = [
            'facebook' => 'fab fa-facebook-f',
            'twitter' => 'fab fa-twitter',
            'instagram' => 'fab fa-instagram',
            'linkedin' => 'fab fa-linkedin-in',
            'xing' => 'fab fa-xing',
            'youtube' => 'fab fa-youtube',
            'github' => 'fab fa-github',
            'researchgate' => 'fab fa-researchgate',
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