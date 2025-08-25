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
        'hero',
        array('label' => esc_html__('Hero', 'fau-elemental'))
    );
}
add_action('init', 'fau_elemental_register_pattern_categories');

/**
 * Register patterns from the components/patterns directory structure
 */
function fau_elemental_register_component_patterns() {
    $pattern_base_dir = get_theme_file_path('components/patterns');
    
    if (!is_dir($pattern_base_dir)) {
        return;
    }

    // Get all subdirectories in components/patterns/
    $pattern_dirs = glob($pattern_base_dir . '/*', GLOB_ONLYDIR);
    
    foreach ($pattern_dirs as $pattern_dir) {
        $pattern_file = $pattern_dir . '/pattern.php';
        
        if (!file_exists($pattern_file)) {
            continue;
        }

        // Extract pattern data from file headers
        $pattern_data = get_file_data($pattern_file, array(
            'title' => 'Title',
            'slug' => 'Slug',
            'categories' => 'Categories',
            'blockTypes' => 'Block Types',
            'postTypes' => 'Post Types',
            'description' => 'Description',
            'viewportWidth' => 'Viewport Width',
            'inserter' => 'Inserter',
            'keywords' => 'Keywords',
            'templateTypes' => 'Template Types',
            'websiteTypes' => 'Website Types'
        ));

        if (empty($pattern_data['slug'])) {
            continue;
        }

        // Check if pattern should be registered for current website type
        if (!empty($pattern_data['websiteTypes'])) {
            $allowed_website_types = array_map('trim', explode(',', $pattern_data['websiteTypes']));
            $current_website_type = get_theme_mod('faue_website_type', 'fau');
            
            if (!in_array($current_website_type, $allowed_website_types)) {
                continue; // Skip this pattern for current website type
            }
        }

        // Process categories
        $categories = !empty($pattern_data['categories']) 
            ? array_map('trim', explode(',', $pattern_data['categories'])) 
            : array();

        // Process block types
        $block_types = !empty($pattern_data['blockTypes']) 
            ? array_map('trim', explode(',', $pattern_data['blockTypes'])) 
            : array();

        // Process post types
        $post_types = !empty($pattern_data['postTypes']) 
            ? array_map('trim', explode(',', $pattern_data['postTypes'])) 
            : array();

        // Process keywords
        $keywords = !empty($pattern_data['keywords']) 
            ? array_map('trim', explode(',', $pattern_data['keywords'])) 
            : array();

        // Process template types
        $template_types = !empty($pattern_data['templateTypes']) 
            ? array_map('trim', explode(',', $pattern_data['templateTypes'])) 
            : array();

        // Get viewport width or default
        $viewport_width = !empty($pattern_data['viewportWidth']) 
            ? (int) $pattern_data['viewportWidth'] 
            : 1376;

        // Check inserter setting
        $inserter = $pattern_data['inserter'] !== 'false';

        // Dynamic title based on website type for specific patterns
        $dynamic_title = $pattern_data['title'];
        if (strpos($pattern_data['slug'], 'fau-elemental/hero-') === 0 && $pattern_data['slug'] !== 'fau-elemental/hero-portal') {
            $dynamic_title = 'Hero: Front Page';
        }

        // Register the pattern
        register_block_pattern(
            $pattern_data['slug'],
            array(
                'title' => $dynamic_title,
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
                'keywords' => $keywords,
                'templateTypes' => $template_types,
                'viewportWidth' => $viewport_width,
                'inserter' => $inserter
            )
        );
    }
}
add_action('init', 'fau_elemental_register_component_patterns');
