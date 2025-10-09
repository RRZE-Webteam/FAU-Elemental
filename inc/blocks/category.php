<?php
/**
 * Block Category Registration
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register custom block category for FAU Elemental blocks
 *
 * @param array $categories Existing block categories.
 * @return array Modified block categories.
 */
function fau_elemental_register_block_categories($categories) {
    return array_merge(
        array(
            array(
                'slug'  => 'fau-elemental/FAU',
                'title' => __('FAU Elemental', 'fau-elemental'),
            ),
        ),
        $categories
    );
}
add_filter('block_categories_all', 'fau_elemental_register_block_categories'); 