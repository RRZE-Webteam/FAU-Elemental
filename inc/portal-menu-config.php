<?php
/**
 * Portal Menu Configuration
 * Central configuration file for all portal menu constants and default values
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Portal Menu Configuration Class
 */
class FAU_Elemental_Portal_Menu_Config {
    
    /**
     * Default portal menu settings
     */
    const DEFAULTS = [
        'type' => 1,
        'columns' => 3,
        'show_subs' => true,
        'hide_thumbs' => false,
        'no_fallback' => false,
        'list_view' => false,
        'hover_zoom' => false,
        'hover_blur' => false,
        'is_dark' => false,
        'is_mega_nav' => false,
    ];
    
    /**
     * Portal menu display types
     */
    const TYPES = [
        1 => [
            'name' => 'type_1_2_1_ratio',
            'label' => 'Type 1 (2:1 Ratio)',
            'aspect_ratio' => '2/1',
            'css_class' => 'size_2-1'
        ],
        2 => [
            'name' => 'type_2_3_2_ratio',
            'label' => 'Type 2 (3:2 Ratio)',
            'aspect_ratio' => '3/2',
            'css_class' => 'size_3-2'
        ],
        3 => [
            'name' => 'type_3_3_4_ratio',
            'label' => 'Type 3 (3:4 Ratio)',
            'aspect_ratio' => '3/4',
            'css_class' => 'size_3-4'
        ]
    ];
    
    /**
     * Column options
     */
    const COLUMNS = [
        1 => [
            'label' => '1 Column',
            'css_class' => 'portal-column-1'
        ],
        2 => [
            'label' => '2 Columns',
            'css_class' => 'portal-column-2'
        ],
        3 => [
            'label' => '3 Columns',
            'css_class' => 'portal-column-3'
        ],
        4 => [
            'label' => '4 Columns',
            'css_class' => 'portal-column-4'
        ]
    ];
    
    /**
     * CSS class names
     */
    const CSS_CLASSES = [
        'container' => 'contentmenu',
        'menu_list' => 'subpages-menu',
        'portal_item' => 'portal-item',
        'portal_thumbnail' => 'portal-thumbnail',
        'portal_content' => 'portal-content',
        'portal_title' => 'portal-title',
        'portal_main_link' => 'portal-main-link',
        'portal_button_arrow' => 'portal-button-arrow',
        'portal_submenu' => 'portal-submenu',
        'portal_subitem' => 'portal-subitem',
        'portal_sublink' => 'portal-sublink',
        'screen_reader_text' => 'screen-reader-text',
        'image_link' => 'image-link',
        'list_view' => 'listview',
        'no_thumb' => 'no-thumb',
        'hover_zoom' => 'hover-zoom',
        'hover_blur' => 'hover-blur',
        'dark_style' => 'is-style-dark'
    ];
    
    /**
     * Meta field names
     */
    const META_FIELDS = [
        'menu_id' => 'portal_menu_id',
        'type' => 'portal_menu_type',
        'columns' => 'portal_menu_columns',
        'hide_subs' => 'portal_menu_hide_subs',
        'list_view' => 'portal_menu_list_view',
        'hide_thumbs' => 'portal_menu_hide_thumbs',
        'no_fallback' => 'portal_menu_no_fallback',
        'hover_zoom' => 'portal_menu_hover_zoom',
        'hover_blur' => 'portal_menu_hover_blur',
        'is_dark' => 'portal_menu_is_dark'
    ];
    
    /**
     * Get default value
     *
     * @param string $key The setting key
     * @return mixed The default value
     */
    public static function get_default($key) {
        return self::DEFAULTS[$key] ?? null;
    }
    
    /**
     * Get type configuration
     *
     * @param int $type The type number
     * @return array The type configuration
     */
    public static function get_type($type) {
        return self::TYPES[$type] ?? self::TYPES[1];
    }
    
    /**
     * Get column configuration
     *
     * @param int $columns The column number
     * @return array The column configuration
     */
    public static function get_column($columns) {
        return self::COLUMNS[$columns] ?? self::COLUMNS[3];
    }
    
    /**
     * Get CSS class
     *
     * @param string $element The element name
     * @return string The CSS class
     */
    public static function get_css_class($element) {
        return self::CSS_CLASSES[$element] ?? '';
    }
    
    /**
     * Get meta field name
     *
     * @param string $field The field name
     * @return string The meta field name
     */
    public static function get_meta_field($field) {
        return self::META_FIELDS[$field] ?? '';
    }
} 