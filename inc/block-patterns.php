<?php
/**
 * Pattern Registration Functions
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register block pattern category
 */
function fau_elemental_register_pattern_category() {
    register_block_pattern_category(
        'fau-elemental',
        array('label' => __('FAU Elemental', 'fau-elemental'))
    );
}
add_action('init', 'fau_elemental_register_pattern_category');

/**
 * Remove default patterns and block patterns
 */
function fau_elemental_remove_default_patterns() {
    // Remove core block patterns
    remove_theme_support('core-block-patterns');
    
    // Remove block pattern directory
    remove_theme_support('block-pattern-directory');
    
    // Remove block pattern categories
    remove_theme_support('block-pattern-categories');
}
add_action('after_setup_theme', 'fau_elemental_remove_default_patterns');


function fau_elemental_register_patterns() {
    // Get the website type from options
    $website_type = get_option('faue_website_type', 'fau');

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