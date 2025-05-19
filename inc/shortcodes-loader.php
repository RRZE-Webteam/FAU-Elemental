<?php
/**
 * Shortcodes Loader
 *
 * Loads shortcode implementations for backward compatibility
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

// Load the Walker_Content_Menu class
require_once get_template_directory() . '/inc/class-walker-content-menu.php';

// Load shortcode implementations
require_once get_template_directory() . '/inc/shortcodes.php';

/**
 * Initialize all shortcodes
 */
function fau_elemental_init_shortcodes() {
    // Register any additional shortcodes here
}
add_action('init', 'fau_elemental_init_shortcodes'); 