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
    
    // Remove block pattern categories UI if no patterns are registered with them,
    // or to ensure a clean slate if we are defining all categories.
    remove_theme_support('block-pattern-categories');
}
add_action('after_setup_theme', 'fau_elemental_remove_default_patterns');

/**
 * Register block pattern categories.
 *
 * @return void
 */
function fau_elemental_register_pattern_categories() {
    register_block_pattern_category(
        'fau-elemental',
        array('label' => esc_html__('FAU Elemental', 'fau-elemental'))
    );
    register_block_pattern_category(
        'page-starters',
        array('label' => __('Page Starters', 'fau-elemental'))
    );
    register_block_pattern_category(
        'hero',
        array('label' => esc_html__('Hero', 'fau-elemental'))
    );
}
add_action('init', 'fau_elemental_register_pattern_categories');

/**
 * Register patterns from the patterns directory
 */
function fau_elemental_register_standard_patterns() {
    $pattern_dir = get_theme_file_path('patterns');
    if (!is_dir($pattern_dir)) {
        return;
    }

    $pattern_files = glob($pattern_dir . '/*.php');
    foreach ($pattern_files as $pattern_file) {
        $pattern_data = get_file_data($pattern_file, array(
            'title' => 'Title',
            'slug' => 'Slug',
            'categories' => 'Categories',
            'blockTypes' => 'Block Types',
            'postTypes' => 'Post Types',
            'description' => 'Description'
        ));

        if (empty($pattern_data['slug'])) {
            continue;
        }

        $categories = array_map('trim', explode(',', $pattern_data['categories']));
        $block_types = array_map('trim', explode(',', $pattern_data['blockTypes']));
        $post_types = array_map('trim', explode(',', $pattern_data['postTypes']));

        register_block_pattern(
            $pattern_data['slug'],
            array(
                'title' => $pattern_data['title'],
                'description' => $pattern_data['description'],
                'source' => 'theme',
                'content' => (function () use ($pattern_file) {
                    ob_start();
                    include $pattern_file;
                    return ob_get_clean();
                })(),
                'categories' => $categories,
                'blockTypes' => $block_types,
                'postTypes' => $post_types,
                'viewportWidth' => 1376,
                'inserter' => true
            )
        );
    }
}
add_action('init', 'fau_elemental_register_standard_patterns');

function fau_elemental_register_patterns() {
    // Get the website type from theme mods
    $website_type = get_theme_mod('faue_website_type', 'fau');
    error_log('FAU Elemental - Registering patterns for website type: ' . $website_type);

    // Map website types to their corresponding pattern files
    $pattern_map = array(
        'fau' => 'hero-fau',
        'faculty' => 'hero-faculty',
        'chair' => 'hero-chair',
        'other' => 'hero-other',
        'cooperation' => 'hero-cooperation'
    );

    // Unregister the pattern if it exists
    if (class_exists('WP_Block_Patterns_Registry') && WP_Block_Patterns_Registry::get_instance()->is_registered('fau-elemental/hero')) {
        unregister_block_pattern('fau-elemental/hero');
    }

    // Register the pattern with content based on website type
    $pattern_name = isset($pattern_map[$website_type]) ? $pattern_map[$website_type] : 'hero-fau';
            $pattern_path = get_theme_file_path("/components/patterns/{$pattern_name}/pattern.php");
    
    if (!file_exists($pattern_path)) {
        error_log('FAU Elemental - Pattern file not found: ' . $pattern_path);
        return;
    }

    register_block_pattern(
        'fau-elemental/hero',
        array(
            'title'         => __('Hero: Front Page', 'fau-elemental'),
            'description'   => __('A dynamic Hero: Portalsection based on the website type, intended as a page starter.', 'fau-elemental'),
            'source'        => 'theme',
            'content'       => (function () use ($pattern_path) {
                ob_start();
                include $pattern_path;
                return ob_get_clean();
            })(),
            'categories'    => array('hero', 'page-starters', 'fau-elemental'),
            'keywords'      => array('hero', 'starter', 'page', $pattern_name),
            'blockTypes'    => array('core/post-content'),
            'postTypes'     => array('page'),
            'viewportWidth' => 1376,
            'inserter'      => true
        )
    );
}
add_action('init', 'fau_elemental_register_patterns');
