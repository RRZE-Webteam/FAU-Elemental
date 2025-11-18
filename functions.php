<?php
/**
 * FAU Elemental Theme Functions
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

// Configuration
require_once get_template_directory() . '/inc/config.php';

// Theme setup and core functionality
require_once get_template_directory() . '/inc/theme-setup.php';

// Asset management
require_once get_template_directory() . '/inc/enqueue-assets.php';

// Customizer
require_once get_template_directory() . '/inc/customizer.php';

// Block functionality
require_once get_template_directory() . '/inc/blocks/loader.php';
require_once get_template_directory() . '/inc/block-patterns.php';

// Post settings and functionality
require_once get_template_directory() . '/inc/posts-settings.php';

// Theme settings
require_once get_template_directory() . '/inc/theme-settings.php';

// Include post meta functionality
require_once get_template_directory() . '/inc/post-meta.php';

// Image helper functions
require_once get_template_directory() . '/inc/image-helpers.php';

// Menu registration
require_once get_template_directory() . '/inc/menu-registration.php';

// Shortcodes functionality
require_once get_template_directory() . '/inc/shortcodes-loader.php';

// Portal menu compatibility with old theme
require_once get_template_directory() . '/inc/portal-menu-compatibility.php';

// Image links migration from old themes
require_once get_template_directory() . '/inc/image-links-migration.php';
// Portal menu configuration
require_once get_template_directory() . '/inc/portal-menu-config.php';

// Portal page settings
require_once get_template_directory() . '/inc/portal-page-settings.php';

// Social media management
require_once get_template_directory() . '/inc/social-media.php';
require_once get_template_directory() . '/inc/social-media-admin.php';

// Breadcrumb functionality
require_once get_template_directory() . '/components/template-parts/breadcrumbs/breadcrumbs.php';

// Navigation components
require_once get_template_directory() . '/components/template-parts/navigation/index.php';

// Page meta fields
require_once get_template_directory() . '/inc/page-meta-fields.php';

require_once get_template_directory() . '/components/template-parts/pagination/pagination.php';

// Widgets
require_once get_template_directory() . '/inc/widgets.php';

/**
 * Register custom page templates
 */
function fau_elemental_register_page_templates($templates) {
    // Register the portal page template
    $templates[FAU_Elemental_Portal_Menu_Config::TEMPLATE] = __('Portal Page', 'fau-elemental');
    
    // Manually register specific page templates
    $templates['components/templates/pages/page-all-posts.php'] = __('All Posts', 'fau-elemental');
    $templates['components/templates/pages/page-all-pages.php'] = __('All Pages', 'fau-elemental');
   
    
    return $templates;
}
add_filter('theme_page_templates', 'fau_elemental_register_page_templates', 11, 1);



/**
 * Fix portal template includes for different template locations
 */
function fau_elemental_template_include($template) {
    if (is_page()) {
        $template_slug = get_page_template_slug();
        
        // Priority 1: Use the root template if explicitly selected
        if ($template_slug === FAU_Elemental_Portal_Menu_Config::TEMPLATE) {
            $root_template = locate_template([FAU_Elemental_Portal_Menu_Config::TEMPLATE]);
            if (!empty($root_template)) {
                return $root_template;
            }
        }
        
        // Only force portal template if:
        // 1. User has portal_menu_id set
        // 2. User hasn't explicitly cleared it
        // 3. Current template is default or empty (user hasn't explicitly chosen another template)
        $menu_id = get_post_meta(get_the_ID(), 'portal_menu_id', true);
        $explicitly_cleared = get_post_meta(get_the_ID(), 'portal_menu_explicitly_cleared', true);
        
        if ($menu_id && !$explicitly_cleared && (empty($template_slug) || $template_slug === 'default')) {
            $portal_template = locate_template([FAU_Elemental_Portal_Menu_Config::TEMPLATE]);
            if (!empty($portal_template)) {
                error_log('FAU Elemental: Portal menu ID found, using template: ' . FAU_Elemental_Portal_Menu_Config::TEMPLATE);
                update_post_meta(get_the_ID(), '_wp_page_template', FAU_Elemental_Portal_Menu_Config::TEMPLATE);
                return $portal_template;
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
        
        if ($template === FAU_Elemental_Portal_Menu_Config::TEMPLATE) {
            // Add message for portal page template
            $messages['post'][1] .= ' <span style="color:#2271b1;">This page is using the Portal Page template. Make sure to select a menu in the Portal Menu Settings box.</span>';
        }
    }
    
    return $messages;
}
add_filter('post_updated_messages', 'fau_elemental_post_updated_messages');

/**
 * Ensure plugin hooks are available in the block theme
 */
function fau_elemental_add_plugin_compatibility_hooks() {
    // Common hooks that plugins often use
    add_action('wp_head', function() {
        do_action('fau_elemental_header');
    });
    
    add_action('wp_footer', function() {
        do_action('fau_elemental_footer');
    });
    
    // Hook before and after content
    add_filter('the_content', function($content) {
        $before = apply_filters('fau_elemental_before_content', '');
        $after = apply_filters('fau_elemental_after_content', '');
        return $before . $content . $after;
    });
}
add_action('init', 'fau_elemental_add_plugin_compatibility_hooks');

/**
 * Enqueue styles for PHP templates
 */
function fau_elemental_enqueue_php_template_styles() {
    // Ensure block styles are loaded even in PHP templates
    wp_enqueue_style('wp-block-library');
    wp_enqueue_style('global-styles');
}
add_action('wp_enqueue_scripts', 'fau_elemental_enqueue_php_template_styles');

/**
 * Add custom classes to body for PHP templates
 */
function fau_elemental_body_classes($classes) {
    // Add these classes to ensure PHP templates look like block templates
    $classes[] = 'wp-theme';
    
    return $classes;
}
add_filter('body_class', 'fau_elemental_body_classes');

/**
 * Function to load template parts for both block and PHP templates
 *
 * @param string $slug Template slug
 * @param string $name Template name (optional)
 * @param array $args Additional arguments to pass to the template (optional)
 */
function fau_elemental_load_template_part($slug, $name = null, $args = array()) {
    // First check if block template part exists
    $part_name = $name ? "{$slug}-{$name}" : $slug;
    $block_part_file = get_theme_file_path("/parts/{$part_name}.html");
    
    if (file_exists($block_part_file) && filesize($block_part_file) > 0) {
        // Block template exists, use it
        echo do_blocks(file_get_contents($block_part_file));
    } else {
        // Fall back to PHP template part
        // Use WordPress's standard structure for template-parts
        $directory = '';
        
        // Organize by type if slug has a recognizable prefix
        if (strpos($slug, 'header') === 0) {
            $directory = 'header';
        } elseif (strpos($slug, 'footer') === 0) {
            $directory = 'footer';
        } elseif (strpos($slug, 'content') === 0) {
            $directory = 'content';
        }
        
        if ($directory) {
            get_template_part("template-parts/{$directory}/{$slug}", $name, $args);
        } else {
            get_template_part("template-parts/{$slug}", $name, $args);
        }
    }
}

/**
 * Hook to migrate settings right after theme activation
 * This is now handled by the consolidated migration function in customizer.php
 * Individual migration functions are called from there
 */
add_action('after_switch_theme', function() {
    // Portal menu migration is still handled separately as it's specific to portal functionality
    if (function_exists('fau_elemental_check_old_portal_menu_settings')) {
        fau_elemental_check_old_portal_menu_settings();
    }
    
    // All other migrations are now handled by fau_elemental_migrate_all_settings()
    // which is called via the action hook in customizer.php
    
    // Schedule image links migration to run after WordPress is fully loaded
    // This prevents critical errors during theme activation
    update_option('fau_elemental_schedule_image_links_migration', true);
});

/**
 * Sanitize and format telephone number
 * Follows international standards as required by FAU
 *
 * @param string $phone The phone number to format
 * @return string Formatted phone number
 */
function fau_elemental_format_phone_number($phone) {
    if (empty($phone)) {
        return '';
    }
    
    // Remove all characters except numbers, "+", "(", ")", "-" and spaces
    $phone = preg_replace('/[^\d\+\-\(\) ]/', '', $phone);
    $phone = preg_replace('/\s+/', ' ', trim($phone));
    
    // Convert "+49(0)" to "+49"
    $phone = preg_replace('/^\+49\s*\(0\)/', '+49', $phone);
    $phone = preg_replace('/^0049/', '+49', $phone);
    
    // If number starts with "0" (German number without country code)
    if (preg_match('/^0[1-9]/', $phone)) {
        $phone = preg_replace('/^0/', '+49 ', $phone);
    }
    
    // Standardize format with spaces between groups
    $phone = preg_replace('/(\+?\d{1,3})\s*(\d{3,4})\s*(\d{3,4})\s*(\d{0,4})/', '$1 $2 $3 $4', $phone);
    
    return trim($phone); // Remove excess spaces at the end
}

// ============================================================================
// FAU TEASER GRID AJAX HANDLERS
// ============================================================================

/**
 * Include and register AJAX handlers for FAU Teaser Grid
 */
function fau_elemental_register_teaser_grid_ajax() {
    // Include the AJAX handler file
    require_once get_template_directory() . '/components/blocks/fau-teaser-grid/ajax.php';
}
add_action('init', 'fau_elemental_register_teaser_grid_ajax');

/**
 * TBD
 * Control block locking permissions
 * 
 * This function restricts who can lock and unlock blocks in the editor.
 * By default, only users with 'edit_theme_options' capability can lock/unlock blocks.
 * You can modify this logic based on your needs.
 */
function fau_elemental_control_block_locking_permissions($settings, $context) {
    
    $settings['canLockBlocks'] = current_user_can('manage_options');
    
    return $settings;
}
add_filter('block_editor_settings_all', 'fau_elemental_control_block_locking_permissions', 10, 2);

/**
 * As this theme provides its own translations, we need to remap from the WP_LANG_DIR to our /languages dir.
 * This is currently a wierd WordPress behaviour that might change in the future, rendering this function
 * useless or even harmful, but until then its the best we can do.
 */
function fau_script_translation_location( string $file, string $handle, string $domain ) {
    if ($domain === 'fau-elemental') {
        $file = str_replace( WP_LANG_DIR . '/themes', get_template_directory() . '/languages', $file );
    }
    return $file;
}
add_filter( 'load_script_translation_file', 'fau_script_translation_location', 10, 3 );

/**
 * Register custom taxonomy for pages (separate from post categories)
 * This creates an independent category system for pages
 */
function fau_elemental_register_page_categories() {
    register_taxonomy(
        'page_category',
        'page',
        array(
            'labels' => array(
                'name' => __('Page Categories', 'fau-elemental'),
                'singular_name' => __('Page Category', 'fau-elemental'),
                'menu_name' => __('Page Categories', 'fau-elemental'),
                'all_items' => __('All Page Categories', 'fau-elemental'),
                'edit_item' => __('Edit Page Category', 'fau-elemental'),
                'view_item' => __('View Page Category', 'fau-elemental'),
                'update_item' => __('Update Page Category', 'fau-elemental'),
                'add_new_item' => __('Add New Page Category', 'fau-elemental'),
                'new_item_name' => __('New Page Category Name', 'fau-elemental'),
                'parent_item' => __('Parent Page Category', 'fau-elemental'),
                'parent_item_colon' => __('Parent Page Category:', 'fau-elemental'),
                'search_items' => __('Search Page Categories', 'fau-elemental'),
                'not_found' => __('No page categories found', 'fau-elemental'),
            ),
            'hierarchical' => true,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_in_rest' => true,
            'show_tagcloud' => false,
            'query_var' => true,
            'rewrite' => array(
                'slug' => 'page-category',
                'with_front' => false,
                'hierarchical' => true
            ),
        )
    );
}
add_action('init', 'fau_elemental_register_page_categories');
