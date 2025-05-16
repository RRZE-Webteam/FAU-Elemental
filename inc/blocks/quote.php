<?php
/**
 * Quote Block Functionality
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

function faue_register_quote_scripts() {
    // Register quote carousel script
    $asset_file = include get_theme_file_path('build/js/quote-carousel.asset.php');
    wp_register_script(
        'quote-carousel',
        get_theme_file_uri('build/js/quote-carousel.js'),
        array_merge($asset_file['dependencies'], ['jquery']),
        $asset_file['version'],
        true
    );
}
add_action('init', 'faue_register_quote_scripts', 5);

function faue_enqueue_quote_block_scripts() {
    // Frontend only
    if (!is_admin()) {
        // Check if the current post contains the core/quote block
        global $post;
        if (is_singular() && has_blocks($post) && has_block('core/quote', $post)) {
            wp_enqueue_script('quote-carousel');
        }
    }
}
add_action('wp_enqueue_scripts', 'faue_enqueue_quote_block_scripts');
