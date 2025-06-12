<?php
/**
 * Portal Menu Block Registration
 *
 * @package FAU-Elemental
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Portal Menu block.
 */
function fau_elemental_register_portal_menu_block() {
    // Skip if Gutenberg is not available.
    if ( ! function_exists( 'register_block_type' ) ) {
        return;
    }

    // Register the block.
    register_block_type(
        'fau/portal-menu',
        array(
            'title'           => __( 'FAU Portal Menu', 'fau-elemental' ),
            'description'     => __( 'Display a portal menu with optional thumbnails and layout options.', 'fau-elemental' ),
            'category'        => 'fau-blocks',
            'icon'            => 'grid-view',
            'keywords'        => array( 'portal', 'menu', 'fau' ),
            'attributes'      => array(
                'menu'        => array(
                    'type'    => 'string',
                    'default' => '',
                ),
                'type'        => array(
                    'type'    => 'string',
                    'default' => 'grid',
                ),
                'ratio'       => array(
                    'type'    => 'string',
                    'default' => '33-33-33',
                ),
                'columns'     => array(
                    'type'    => 'number',
                    'default' => 3,
                ),
                'thumbnails'  => array(
                    'type'    => 'boolean',
                    'default' => true,
                ),
                'displaySubmenus' => array(
                    'type'    => 'boolean',
                    'default' => true,
                ),
                'listview'    => array(
                    'type'    => 'boolean',
                    'default' => false,
                ),
                'hovertitle'  => array(
                    'type'    => 'boolean',
                    'default' => false,
                ),
                'megamenu'    => array(
                    'type'    => 'boolean',
                    'default' => false,
                ),
            ),
            'render_callback' => 'fau_elemental_render_portal_menu_block',
        )
    );
}
add_action( 'init', 'fau_elemental_register_portal_menu_block' );

/**
 * Renders the Portal Menu block on the server.
 *
 * @param array $attributes The block attributes.
 * @return string The HTML markup for the block.
 */
function fau_elemental_render_portal_menu_block( $attributes ) {
    // Default attributes
    $defaults = array(
        'menu'            => '',
        'type'            => 'grid',
        'ratio'           => '33-33-33',
        'columns'         => 3,
        'thumbnails'      => true,
        'displaySubmenus' => true,
        'listview'        => false,
        'hovertitle'      => false,
        'megamenu'        => false,
    );

    // Merge with defaults
    $attributes = wp_parse_args( $attributes, $defaults );

    // Convert boolean strings to actual booleans
    foreach ( array( 'thumbnails', 'displaySubmenus', 'listview', 'hovertitle', 'megamenu' ) as $bool_attr ) {
        if ( isset( $attributes[$bool_attr] ) && is_string( $attributes[$bool_attr] ) ) {
            $attributes[$bool_attr] = $attributes[$bool_attr] === 'true';
        }
    }

    // Generate the shortcode from block attributes
    $shortcode = '[portalmenu';
    
    if ( ! empty( $attributes['menu'] ) ) {
        $shortcode .= ' menu="' . esc_attr( $attributes['menu'] ) . '"';
    }
    
    if ( ! empty( $attributes['type'] ) ) {
        $shortcode .= ' type="' . esc_attr( $attributes['type'] ) . '"';
    }
    
    if ( ! empty( $attributes['ratio'] ) ) {
        $shortcode .= ' ratio="' . esc_attr( $attributes['ratio'] ) . '"';
    }
    
    if ( isset( $attributes['columns'] ) ) {
        $shortcode .= ' columns="' . intval( $attributes['columns'] ) . '"';
    }
    
    $shortcode .= ' thumbnails="' . ( $attributes['thumbnails'] ? 'true' : 'false' ) . '"';
    $shortcode .= ' display_submenus="' . ( $attributes['displaySubmenus'] ? 'true' : 'false' ) . '"';
    $shortcode .= ' listview="' . ( $attributes['listview'] ? 'true' : 'false' ) . '"';
    $shortcode .= ' hovertitle="' . ( $attributes['hovertitle'] ? 'true' : 'false' ) . '"';
    $shortcode .= ' megamenu="' . ( $attributes['megamenu'] ? 'true' : 'false' ) . '"';
    
    $shortcode .= ']';

    // Process the shortcode and return the output
    return do_shortcode( $shortcode );
}

/**
 * Add editor script for the Portal Menu block.
 */
function fau_elemental_portal_menu_block_editor_assets() {
    // Enqueue editor script for the block


    // Get all menus for the select control
    $menus = wp_get_nav_menus();
    $menu_options = array(
        array(
            'value' => '',
            'label' => __( 'Select a menu', 'fau-elemental' )
        )
    );

    foreach ( $menus as $menu ) {
        $menu_options[] = array(
            'value' => $menu->name,
            'label' => $menu->name,
        );
    }

    // Pass menu options to the editor script
    wp_localize_script(
        'fau-portal-menu-block-editor',
        'fauPortalMenuOptions',
        array(
            'menus' => $menu_options,
        )
    );
}
add_action( 'enqueue_block_editor_assets', 'fau_elemental_portal_menu_block_editor_assets' ); 