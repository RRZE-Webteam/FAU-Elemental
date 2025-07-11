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

// Menu registration
require_once get_template_directory() . '/inc/menu-registration.php';

// Shortcodes functionality
require_once get_template_directory() . '/inc/shortcodes-loader.php';

// Portal menu compatibility with old theme
require_once get_template_directory() . '/inc/portal-menu-compatibility.php';

// Portal menu configuration
require_once get_template_directory() . '/inc/portal-menu-config.php';

// Portal page settings
require_once get_template_directory() . '/inc/portal-page-settings.php';

// Breadcrumb functionality
require_once get_template_directory() . '/components/template-parts/breadcrumbs/breadcrumbs.php';

// Navigation components
require_once get_template_directory() . '/components/template-parts/navigation/index.php';

// Page meta fields
require_once get_template_directory() . '/inc/page-meta-fields.php';

// AJAX handlers
require_once get_template_directory() . '/inc/ajax-handlers.php';

/**
 * Register custom page templates
 */
function fau_elemental_register_page_templates($templates) {
    // Register the portal page template
    $templates[FAU_Elemental_Portal_Menu_Config::TEMPLATE] = __('Portal Page', 'fau-elemental');
    
    // Dynamically register all templates from components/templates/
    $template_base_dir = get_theme_file_path('components/templates');
    
    if (is_dir($template_base_dir)) {
        // Get all PHP files in components/templates/
        $template_files = glob($template_base_dir . '/*.php');
        
        foreach ($template_files as $template_file) {
            $template_path = 'components/templates/' . basename($template_file);
            
            // Extract template data from file headers
            $template_data = get_file_data($template_file, array(
                'template_name' => 'Template Name',
                'description' => 'Description',
            ));
            
            // Use template name from header or generate from filename
            $template_name = !empty($template_data['template_name']) 
                ? $template_data['template_name'] 
                : ucwords(str_replace(['-', '_'], ' ', basename($template_file, '.php')));
            
            $templates[$template_path] = $template_name;
        }
        
        // Also check subdirectories (like portal-page/)
        $template_dirs = glob($template_base_dir . '/*', GLOB_ONLYDIR);
        foreach ($template_dirs as $template_dir) {
            $template_files = glob($template_dir . '/*.php');
            foreach ($template_files as $template_file) {
                $template_path = 'components/templates/' . basename($template_dir) . '/' . basename($template_file);
                
                // Extract template data from file headers
                $template_data = get_file_data($template_file, array(
                    'template_name' => 'Template Name',
                    'description' => 'Description',
                ));
                
                // Use template name from header or generate from filename
                $template_name = !empty($template_data['template_name']) 
                    ? $template_data['template_name'] 
                    : ucwords(str_replace(['-', '_'], ' ', basename($template_file, '.php')));
                
                $templates[$template_path] = $template_name;
            }
        }
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
        if ($template_slug === FAU_Elemental_Portal_Menu_Config::TEMPLATE) {
            $root_template = locate_template([FAU_Elemental_Portal_Menu_Config::TEMPLATE]);
            if (!empty($root_template)) {
                return $root_template;
            }
        }
        
        // If the requested template isn't found but the page has a portal menu ID
        // Try to use the portal template
        if (get_post_meta(get_the_ID(), 'portal_menu_id', true)) {
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
 * Main theme setup function for FAU-Elemental
 */
 function fau_elemental_theme_setup() {
    // Basic theme features support
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');
    add_theme_support('automatic-feed-links');
    add_theme_support('html5', array(
        'comment-list', 
        'comment-form', 
        'search-form', 
        'gallery', 
        'caption',
        'style',
        'script'
    ));
    add_theme_support('title-tag');
    
 
    
    // Add custom image sizes if needed
    // add_image_size('featured-large', 1600, 900, true);
}
add_action('after_setup_theme', 'fau_elemental_theme_setup');

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
 */
add_action('after_switch_theme', function() {
    if (function_exists('fau_elemental_check_old_portal_menu_settings')) {
        fau_elemental_check_old_portal_menu_settings();
    }
    
    // Also trigger address migration
    if (function_exists('fau_elemental_migrate_address_information')) {
        fau_elemental_migrate_address_information();
    }
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

/**
 * Enqueue footer scripts and localize strings
 */
function fau_elemental_enqueue_footer_scripts() {
    // Only enqueue on pages that have footers
    if (is_admin()) {
        return;
    }
    
    $faue_website_type = get_theme_mod('faue_website_type');
    
    // Enqueue footer toggle script for instance sites (where the toggle is used)
    if ($faue_website_type !== 'fau') {
        wp_enqueue_script(
            'fau-footer-toggle',
            get_theme_file_uri('components/template-parts/footer-main/footer-toggle.js'),
            [],
            wp_get_theme()->get('Version'),
            true
        );
        
        // Localize strings for the footer toggle functionality
        wp_localize_script('fau-footer-toggle', 'fauFooterStrings', [
            'showMore' => __('Show more', 'fau-elemental'),
            'showLess' => __('Show less', 'fau-elemental')
        ]);
    }
}
add_action('wp_enqueue_scripts', 'fau_elemental_enqueue_footer_scripts');

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