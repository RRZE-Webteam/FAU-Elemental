<?php
/**
 * Block Functionality Loader
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

// Load block category registration
require_once get_template_directory() . '/inc/blocks/category.php';

// Load block registration functionality
require_once get_template_directory() . '/inc/blocks/registration.php';

// Load block modifications
require_once get_template_directory() . '/inc/blocks/heading.php';
require_once get_template_directory() . '/inc/blocks/image.php';
require_once get_template_directory() . '/inc/blocks/gallery.php';

require_once get_template_directory() . '/inc/blocks/quote.php';