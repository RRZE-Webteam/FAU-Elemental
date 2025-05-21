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

// Theme settings
require_once get_template_directory() . '/inc/theme-settings.php';

// Post settings
require_once get_template_directory() . '/inc/posts-settings.php';

/**
 * Completely lock all theme templates from FSE editing
 */
function lock_theme_templates_from_fse() {
    // 1. Get list of theme templates to protect
    $theme_templates = array();
    
    // Get all template files from the theme
    $template_files = glob(get_template_directory() . '/templates/*.html');
    foreach ($template_files as $template_file) {
        $template_slug = basename($template_file, '.html');
        $theme_templates[] = $template_slug;
    }
    
    // Get all template part files
    $template_part_files = glob(get_template_directory() . '/parts/*.html');
    foreach ($template_part_files as $template_file) {
        $template_slug = basename($template_file, '.html');
        $theme_templates[] = $template_slug;
    }
    
    // Always protect these core templates
    $core_templates = array(
        'single', 'single-post', 'page', 'home', 'front-page', 
        'archive', 'category', 'tag', 'index', '404', 'search'
    );
    
    $protected_templates = array_merge($theme_templates, $core_templates);
    $protected_templates = array_unique($protected_templates);
    
    // 2. Filter templates out of the FSE editor
    add_filter('get_block_templates', function($templates, $query, $template_type) use ($protected_templates) {
        // Only filter in admin context
        if (!is_admin()) {
            return $templates;
        }
        
        return array_filter($templates, function($template) use ($protected_templates) {
            // Keep templates not in our protected list
            return !in_array($template->slug, $protected_templates);
        });
    }, 20, 3);
    
    // 3. Block REST API access for protected templates
    add_filter('rest_request_before_callbacks', function($response, $handler, $request) use ($protected_templates) {
        $route = $request->get_route();
        $method = $request->get_method();
        
        // Only block write operations
        if (!in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return $response;
        }
        
        // Check if this is a template or template part endpoint
        if (strpos($route, '/wp/v2/templates') === 0 || 
            strpos($route, '/wp/v2/template-parts') === 0) {
            
            // For template creation/update
            if ($method === 'POST' || $method === 'PUT' || $method === 'PATCH') {
                $slug = $request->get_param('slug');
                
                if ($slug && in_array($slug, $protected_templates)) {
                    return new WP_Error(
                        'template_locked',
                        'This template is locked and cannot be modified.',
                        ['status' => 403]
                    );
                }
            }
            
            // For template deletion
            if ($method === 'DELETE') {
                $path_parts = explode('/', trim($route, '/'));
                $template_id = end($path_parts);
                
                foreach ($protected_templates as $protected) {
                    if ($template_id === $protected || strpos($template_id, "wp_template//$protected") === 0 || 
                        strpos($template_id, "wp_template_part//$protected") === 0) {
                        return new WP_Error(
                            'template_locked',
                            'This template is locked and cannot be deleted.',
                            ['status' => 403]
                        );
                    }
                }
            }
        }
        
        return $response;
    }, 10, 3);
    
    // 4. Prevent direct access to template editing screens
    add_action('admin_init', function() use ($protected_templates) {
        global $pagenow;
        
        if ($pagenow === 'site-editor.php' || 
            (isset($_GET['page']) && $_GET['page'] === 'gutenberg-edit-site')) {
            
            if (isset($_GET['postId'])) {
                $template_id = $_GET['postId'];
                
                foreach ($protected_templates as $protected) {
                    if (strpos($template_id, $protected) !== false) {
                        // Redirect to site editor home
                        wp_safe_redirect(admin_url('site-editor.php'));
                        exit;
                    }
                }
            }
        }
    });
    
    // 5. Hide protected templates from the Site Editor UI
    add_action('admin_head', function() use ($protected_templates) {
        $selectors = array();
        
        foreach ($protected_templates as $template) {
            $selectors[] = ".edit-site-template-card[data-slug=\"{$template}\"]";
            $selectors[] = ".edit-site-template-card[data-title*=\"{$template}\"]";
        }
        
        if (!empty($selectors)) {
            echo '<style>' . implode(', ', $selectors) . ' { display: none !important; }</style>';
        }
        
        // Also hide template editing options
        echo '<style>
            /* Hide template switching in post editor */
            .edit-post-header__settings .components-dropdown-menu__toggle[aria-label*="Template"],
            .edit-post-header-toolbar__document-overview-toggle[aria-label*="Template"],
            
            /* Hide template editing panels */
            .edit-post-template-panel,
            .edit-post-template__actions
            {
                display: none !important;
            }
        </style>';
    });
    
    // 6. Override user capabilities to edit templates
    add_filter('map_meta_cap', function($caps, $cap, $user_id, $args) use ($protected_templates) {
        // Check if this is a template capability check
        if (in_array($cap, array('edit_theme', 'edit_themes', 'edit_theme_options'))) {
            
            // If checking for a specific template
            if (isset($args[0]) && is_string($args[0])) {
                foreach ($protected_templates as $protected) {
                    if (strpos($args[0], $protected) !== false) {
                        return array('do_not_allow');
                    }
                }
            }
        }
        
        return $caps;
    }, 10, 4);
    
    // 7. Disable template editing mode in the post editor
    add_filter('block_editor_settings_all', function($settings) {
        if (get_post_type() === 'post' || get_post_type() === 'page') {
            $settings['supportsTemplateMode'] = false;
            
            // Disable editing existing templates
            if (isset($settings['defaultTemplateTypes'])) {
                foreach ($settings['defaultTemplateTypes'] as $key => &$template) {
                    $template['isLocked'] = true;
                }
            }
        }
        
        return $settings;
    }, 999);
    
    // 8. Add admin notice about templates being locked
    add_action('admin_notices', function() {
        $screen = get_current_screen();
        
        if ($screen && $screen->id === 'appearance_page_gutenberg-edit-site') {
            echo '<div class="notice notice-info is-dismissible">';
            echo '<p><strong>Note:</strong> Some templates are locked and cannot be edited in the Site Editor. These templates are defined in the theme files.</p>';
            echo '</div>';
        }
    });
}

// Initialize template protection
add_action('init', 'lock_theme_templates_from_fse', 999);
add_action('admin_init', 'lock_theme_templates_from_fse', 999);
add_action('rest_api_init', 'lock_theme_templates_from_fse', 999);

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
    $classes[] = 'is-layout-flow';
    
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