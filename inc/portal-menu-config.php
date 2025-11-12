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
     * Slug of the portal page template.
     * Note: If the template gets moved this must be changed BUT (!)
     *       as the slug and the file path of the template are the same
     *       for Wordpress, all pages using this template WILL BREAK without
     *       some code that automatically migrates from the old path.
     */
    const TEMPLATE = "components/templates/portal-page/portal-page.php";
    
    /**
     * Default portal menu settings
     */
    const DEFAULTS = [
        'show_subs' => true,
        'hide_thumbs' => false,
        'is_dark' => false,
        'hide_title' => true,
    ];
    
    /**
     * Meta field names
     */
    const META_FIELDS = [
        'menu_id' => 'portal_menu_id',
        'hide_subs' => 'portal_menu_hide_subs',
        'hide_thumbs' => 'portal_menu_hide_thumbs',
        'is_dark' => 'portal_menu_is_dark',
        'hide_title' => 'portal_menu_hide_title'
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
     * Get meta field name
     *
     * @param string $field The field name
     * @return string The meta field name
     */
    public static function get_meta_field($field) {
        return self::META_FIELDS[$field] ?? '';
    }
} 