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

// Customizer
require_once get_template_directory() . '/inc/customizer.php';

// Block functionality
require_once get_template_directory() . '/inc/blocks/loader.php';
require_once get_template_directory() . '/inc/block-patterns.php';

// Post settings and functionality
require_once get_template_directory() . '/inc/posts-settings.php';

// Theme settings
require_once get_template_directory() . '/inc/theme-settings.php';

// Menu registration
require_once get_template_directory() . '/inc/menu-registration.php';

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
/**
 * Main theme setup function for FAU-Elemental
 */
 function fau_elemental_theme_setup() {
    // Add theme support for block templates and FSE
    add_theme_support('block-templates');
    
    // Ensure PHP templates are available as fallbacks
    add_theme_support('template-hierarchy');
    
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
    
    // Register core menu locations used by classic templates
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'fau-elemental'),
        'footer' => __('Footer Menu', 'fau-elemental'),
    ));
    
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
        
        if ($template === 'portal-page.php' || $template === 'templates/portal-page.php') {
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
    // $classes[] = 'is-layout-flow';
    
    return $classes;
}
add_filter('body_class', 'fau_elemental_body_classes');

/**
 * Register template parts for block templates
 */
function fau_elemental_register_template_parts() {
    // Get all template parts from the parts directory
    $block_parts = glob(get_template_directory() . '/parts/*.html');
    
    foreach ($block_parts as $part_file) {
        $slug = basename($part_file, '.html');
        
        // Only register if file exists and has content
        if (file_exists($part_file) && filesize($part_file) > 0) {
            // Determine category based on slug prefix
            $category = 'uncategorized';
            if (strpos($slug, 'header-') === 0) {
                $category = 'header';
            } elseif (strpos($slug, 'footer-') === 0) {
                $category = 'footer';
            } elseif (strpos($slug, 'sidebar-') === 0) {
                $category = 'sidebar';
            }
            
            // Create title from slug
            $title = str_replace('-', ' ', $slug);
            $title = ucwords($title);
            
            register_block_pattern(
                'fau-elemental/' . $slug,
                array(
                    'title'       => $title,
                    'description' => sprintf(__('%s template part', 'fau-elemental'), $title),
                    'content'     => file_get_contents($part_file),
                    'categories'  => array($category),
                )
            );
        }
    }
}
add_action('init', 'fau_elemental_register_template_parts');

// This will load the main footer.php dont remove this
function render_footer_template() {
    ob_start();
    get_footer(); 
    return ob_get_clean();
}

register_block_type('fau-elemental/footer', array(
    'render_callback' => 'render_footer_template'
));

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
});