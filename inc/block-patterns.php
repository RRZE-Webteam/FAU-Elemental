<?php
/**
 * Pattern Registration Functions
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

function fau_elemental_register_patterns() {
    // Get the website type from options
    $website_type = get_option('fau_elemental_website_type', 'fau');

    // Map website types to their corresponding pattern files
    $pattern_map = array(
        'fau' => 'hero-fau',
        'faculty' => 'hero-faculty',
        'chair' => 'hero-chair',
        'other' => 'hero-other',
        'cooperation' => 'hero-cooperation'
    );

    // Unregister the pattern if it exists
    unregister_block_pattern('fau-elemental/hero');

    // Register the pattern with content based on website type
    $pattern_name = isset($pattern_map[$website_type]) ? $pattern_map[$website_type] : 'hero';
    register_block_pattern(
        'fau-elemental/hero',
        array(
            'title' => __('Hero Pattern', 'fau-elemental'),
            'source' => 'theme',
            'content' => (function () use ($pattern_name) {
                ob_start();
                include get_theme_file_path("/conditional-patterns/{$pattern_name}.php");
                return ob_get_clean();
            })()
        )
    );
}
add_action('init', 'fau_elemental_register_patterns'); 