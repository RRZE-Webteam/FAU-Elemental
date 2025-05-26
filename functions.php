<?php
/**
 * FAU Elemental Theme Functions
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

// Theme setup and core functionality
require_once get_template_directory() . '/inc/theme-setup.php';

// Asset management
require_once get_template_directory() . '/inc/enqueue-assets.php';

// Block functionality
require_once get_template_directory() . '/inc/blocks/loader.php';
require_once get_template_directory() . '/inc/block-patterns.php';

// Post settings and functionality
require_once get_template_directory() . '/inc/posts-settings.php';

// Theme settings
require_once get_template_directory() . '/inc/theme-settings.php';

// Shortcodes functionality
require_once get_template_directory() . '/inc/shortcodes-loader.php';

// Portal menu compatibility with old theme
require_once get_template_directory() . '/inc/portal-menu-compatibility.php';

// Breadcrumb functionality
require_once get_template_directory() . '/src/components/breadcrumbs/breadcrumbs.php';

/**
 * Register custom page templates
 * 
 * IMPORTANT: Portal Page template MUST be registered in the root of the theme,
 * not in templates/ directory for it to work with WordPress template selector
 */
function fau_elemental_register_page_templates($templates) {
    // Root template is the most important one - always register it first
    // This is the one that will be used in the dropdown
    $templates['portal-page.php'] = 'Portal Page';
    
    // Secondary location as fallback (might not appear in dropdown)
    if (file_exists(get_template_directory() . '/templates/portal-page.php')) {
        $templates['templates/portal-page.php'] = 'Portal Page (Templates)';
    }
    
    // Force flush the template cache if we're in admin
    if (is_admin()) {
        $cache_key = 'page_templates-' . md5(get_theme_root() . '/' . get_stylesheet());
        $old_templates = wp_cache_get($cache_key, 'themes');
        if (is_array($old_templates)) {
            wp_cache_delete($cache_key, 'themes');
        }
    }
    
    return $templates;
}
add_filter('theme_page_templates', 'fau_elemental_register_page_templates', 11, 1);



/**
 * Fix portal template includes for different template locations
 */
function fau_elemental_template_include($template) {
    if (is_page()) {
        $template_slug = get_page_template_slug();
        
        // Debug output
        if (defined('FAU_ELEMENTAL_DEBUG') && FAU_ELEMENTAL_DEBUG) {
            error_log('FAU Elemental Debug: Template include requested for: ' . $template_slug);
        }
        
        // Priority 1: Use the root template if explicitly selected
        if ($template_slug === 'portal-page.php') {
            $root_template = locate_template(['portal-page.php']);
            if (!empty($root_template)) {
                return $root_template;
            }
        }
        
        // Priority 2: Use the template in templates/ directory if selected
        if ($template_slug === 'templates/portal-page.php') {
            $nested_template = locate_template(['templates/portal-page.php']);
            if (!empty($nested_template)) {
                return $nested_template;
            }
            
            // If the template in templates/ is selected but doesn't exist,
            // try to use the root template as fallback
            $root_template = locate_template(['portal-page.php']);
            if (!empty($root_template)) {
                error_log('FAU Elemental: Using root portal-page.php as fallback');
                update_post_meta(get_the_ID(), '_wp_page_template', 'portal-page.php');
                return $root_template;
            }
        }
        
        // If the requested template isn't found but the page has a portal menu ID
        // Try to use any available portal template
        if (get_post_meta(get_the_ID(), 'portal_menu_id', true)) {
            $possible_templates = ['portal-page.php', 'templates/portal-page.php'];
            foreach ($possible_templates as $possible) {
                $try_template = locate_template([$possible]);
                if (!empty($try_template)) {
                    error_log('FAU Elemental: Portal menu ID found, using template: ' . $possible);
                    update_post_meta(get_the_ID(), '_wp_page_template', $possible);
                    return $try_template;
                }
            }
        }
    }
    return $template;
}
add_filter('template_include', 'fau_elemental_template_include', 99);

/**
 * Add a filter to post updated messages to help with portal page template
 */
function fau_elemental_post_updated_messages($messages) {
    global $post;
    
    if ($post && get_post_type($post) === 'page') {
        $template = get_post_meta($post->ID, '_wp_page_template', true);
        
        if ($template === 'portal-page.php' || $template === 'templates/portal-page.php') {
            // Add message for portal page template
            $messages['post'][1] .= ' <span style="color:#2271b1;">This page is using the Portal Page template. Make sure to select a menu in the Portal Menu Settings box.</span>';
        }
    }
    
    return $messages;
}
add_filter('post_updated_messages', 'fau_elemental_post_updated_messages');

/**
 * Hook to migrate settings right after theme activation
 */
add_action('after_switch_theme', function() {
    if (function_exists('fau_elemental_check_old_portal_menu_settings')) {
        fau_elemental_check_old_portal_menu_settings();
    }
});
