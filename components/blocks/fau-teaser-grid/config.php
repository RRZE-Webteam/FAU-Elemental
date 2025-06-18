<?php
/**
 * FAU Teaser Grid Block Configuration
 *
 * @package FAU_Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * FAU Teaser Grid Block Configuration Constants
 */
class FAU_Teaser_Grid_Config {
    
    /**
     * Default configuration values
     */
    const DEFAULTS = [
        'posts_per_page' => 15,
        'current_page' => 1,
        'display_style' => 'teaser-grid',
        'teaser_layout' => '3m',
        'variant' => 'post',
        'selection_mode' => 'auto',
        'order_by' => 'date',
        'order' => 'DESC',
        'heading_level' => 'h4',
        'show_load_more' => false,
        'selected_category' => 0,
        'total_posts' => 0,
    ];
    
    /**
     * CSS class names
     */
    const CSS_CLASSES = [
        'wrapper' => 'fau-list-item',
        'wrapper_with_load_more' => 'has-load-more',
        'grid' => 'fau-teaser-grid',
        'teaser_item' => 'teaser-item',
        'load_more_wrapper' => 'fau-teaser-grid__load-more-wrapper',
        'load_more_button' => 'fau-teaser-grid__load-more-button',
        'load_more_spinner' => 'load-more-spinner',
        'loading_text' => 'loading-text',
    ];
    
    /**
     * ARIA labels and accessibility texts
     */
    const ARIA_LABELS = [
        'content_grid' => 'Content grid',
        'content_items' => 'Content items',
        'load_more_posts' => 'Load more posts',
        'pagination' => 'Pagination',
    ];
    
    /**
     * Translatable text strings
     */
    const TEXT_STRINGS = [
        'no_items_found' => 'No items found',
        'load_more' => 'Load More',
        'loading' => 'Loading...',
        'read_more_about' => 'Read more about',
        'security_check_failed' => 'Security check failed',
        'no_posts_selected' => 'No posts selected',
    ];
    
    /**
     * Allowed heading levels
     */
    const ALLOWED_HEADING_LEVELS = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
    
    /**
     * Layout configurations
     */
    const LAYOUTS = [
        '1xl' => ['columns' => 1, 'special' => false],
        '2l' => ['columns' => 2, 'special' => false],
        '3m' => ['columns' => 3, 'special' => false],
        'l2s' => ['columns' => 3, 'special' => true, 'wrapping' => true],
        '2sl' => ['columns' => 3, 'special' => true, 'wrapping' => true],
        '2s-left' => ['columns' => 3, 'special' => true],
        '2s-right' => ['columns' => 3, 'special' => true],
    ];
    
    /**
     * AJAX configuration
     */
    const AJAX = [
        'action' => 'fau_load_more_posts',
        'nonce_name' => 'fau_load_more_nonce',
    ];
    
    /**
     * Image configuration
     */
    const IMAGES = [
        'fallback_image' => '/assets/images/logo.svg',
        'aspect_ratio' => '3/2',
        'loading' => 'lazy',
    ];
    
    /**
     * Get default value
     *
     * @param string $key The configuration key
     * @return mixed The default value
     */
    public static function get_default($key) {
        return self::DEFAULTS[$key] ?? null;
    }
    
    /**
     * Get CSS class name
     *
     * @param string $key The class key
     * @return string The CSS class name
     */
    public static function get_css_class($key) {
        return self::CSS_CLASSES[$key] ?? '';
    }
    
    /**
     * Get ARIA label
     *
     * @param string $key The label key
     * @return string The ARIA label
     */
    public static function get_aria_label($key) {
        return __(self::ARIA_LABELS[$key] ?? '', 'fau-elemental');
    }
    
    /**
     * Get translatable text
     *
     * @param string $key The text key
     * @return string The translated text
     */
    public static function get_text($key) {
        return __(self::TEXT_STRINGS[$key] ?? '', 'fau-elemental');
    }
    
    /**
     * Check if heading level is valid
     *
     * @param string $level The heading level
     * @return bool Whether the level is valid
     */
    public static function is_valid_heading_level($level) {
        return in_array($level, self::ALLOWED_HEADING_LEVELS);
    }
    
    /**
     * Get layout configuration
     *
     * @param string $layout The layout key
     * @return array The layout configuration
     */
    public static function get_layout_config($layout) {
        return self::LAYOUTS[$layout] ?? self::LAYOUTS['3m'];
    }
    
    /**
     * Check if layout requires wrapping
     *
     * @param string $layout The layout key
     * @return bool Whether the layout requires wrapping
     */
    public static function layout_requires_wrapping($layout) {
        $config = self::get_layout_config($layout);
        return isset($config['wrapping']) && $config['wrapping'];
    }
} 