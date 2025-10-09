<?php
/**
 * Heading Block Modifications
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Modify heading block options to remove H1 and set available levels
 *
 * @param array  $args       Block registration arguments.
 * @param string $block_type Block type name.
 * @return array Modified arguments.
 */
function fau_elemental_modify_heading_levels($args, $block_type) {
    if ('core/heading' !== $block_type) {
        return $args;
    }
    
    // Remove align support
    $args['supports']['align'] = [];

    // Remove H1, only allow H2-H6
    $args['attributes']['levelOptions']['default'] = [2, 3, 4, 5, 6];

    return $args;
}
add_filter('register_block_type_args', 'fau_elemental_modify_heading_levels', 10, 2); 