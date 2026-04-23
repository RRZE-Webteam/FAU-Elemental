<?php
/**
 * Navigation utility functions
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    return;
}

// Prevent multiple inclusions
if (defined('FAUE_NAVIGATION_UTILS_LOADED')) {
    return;
}

// Mark as loaded
define('FAUE_NAVIGATION_UTILS_LOADED', true);

// Prevent function redeclaration
if (!function_exists('faue_has_hero_block')) {
    /**
     * Check if the current page has a hero block as the first block
     * 
     * This ensures hero styling only applies when the hero is the first
     * thing visitors see on the homepage, not when it's buried in content.
     * 
     * @return bool
     */
    function faue_has_hero_block() {
        // Only check on front page
        if (!is_front_page()) {
            return false;
        }

        // Get the front page content
        $front_page_id = get_option('page_on_front');
        if (!$front_page_id) {
            return false;
        }
        
        $front_page = get_post($front_page_id);
        if (!$front_page || empty($front_page->post_content)) {
            return false;
        }
        
        // Parse blocks to check if hero is first
        $blocks = parse_blocks($front_page->post_content);
        
        // Check if first block is a hero block
        if (!empty($blocks) && isset($blocks[0]['blockName'])) {
            $first_block = $blocks[0];
            
            // Check if first block is a group block with hero class
            if ($first_block['blockName'] === 'core/group' && 
                isset($first_block['attrs']['className'])) {
                
                $hero_blocks = [
                    'hero-fau',
                    'hero-portal', 
                    'hero-faculty-other',
                    'hero-chair-cooperation',
                    'hero-cooperation',
                    'hero-other'
                ];
                
                foreach ($hero_blocks as $hero_block) {
                    if (strpos($first_block['attrs']['className'], $hero_block) !== false) {
                        return true;
                    }
                }
            }
            
            // Also check for columns block (used by hero-portal)
            if ($first_block['blockName'] === 'core/columns' && 
                isset($first_block['attrs']['className'])) {
                
                $hero_blocks = [
                    'hero-fau',
                    'hero-portal', 
                    'hero-faculty-other',
                    'hero-chair-cooperation',
                    'hero-cooperation',
                    'hero-other'
                ];
                
                foreach ($hero_blocks as $hero_block) {
                    if (strpos($first_block['attrs']['className'], $hero_block) !== false) {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
}

if (!function_exists('faue_get_navigation_body_classes')) {
    /**
     * Get navigation-specific body classes
     * 
     * @param array $classes Existing body classes
     * @return array Modified body classes
     */
    function faue_get_navigation_body_classes($classes = []) {
        // Add hero detection class
        if (faue_has_hero_block()) {
            $classes[] = 'has-hero-block';
        }
        
        return $classes;
    }
}

if (!function_exists('faue_get_header_navigation_classes')) {
    /**
     * Get header navigation classes based on current page state
     * 
     * @return string CSS classes for header navigation
     */
    function faue_get_header_navigation_classes() {
        $classes = ['main-navigation'];
        
        if (faue_has_hero_block()) {
            $classes[] = 'main-navigation--hero';
        } else {
            $classes[] = 'main-navigation--standard';
        }
        
        return implode(' ', $classes);
    }
}
