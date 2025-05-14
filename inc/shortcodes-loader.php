<?php
/**
 * Shortcodes Loader
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

// Load the Walker_Content_Menu class
require_once get_template_directory() . '/inc/class-walker-content-menu.php';

// Load shortcodes functionality
require_once get_template_directory() . '/inc/shortcodes.php'; 