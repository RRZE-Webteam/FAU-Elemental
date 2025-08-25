<?php
/**
* @package WordPress
* @subpackage FAU
* @since FAU 1.2
*/


/* Tagcloud Menu Widget */
global $wp_embed, $options;

function faue_sidebars_init() {	
    if (apply_filters('rrze_multilang_widget_enabled', false)) {
        register_sidebar( array(
            'name' => __( 'Language switcher', 'fau-elemental' ),
            'id' => 'language-switcher',
            'description' => __( 'Language switcher in the page header', 'fau-elemental' ),
            'before_widget' => '<div class="meta-widget rrze-multilang-widget">',
            'after_widget' => '</div>',

        ) );
    }
}
add_action( 'widgets_init', 'faue_sidebars_init' );

/*
 * Format Widgets
 */
add_filter( 'widget_text', array( $wp_embed, 'run_shortcode' ), 8 );
add_filter( 'widget_text', array( $wp_embed, 'autoembed'), 8 );
add_filter('widget_text','do_shortcode');


