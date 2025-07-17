<?php
/**
 * Portal Menu Backwards Compatibility
 *
 * Handles compatibility with FAU-Einrichtungen theme's portal menu settings
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check if old theme options exist and migrate them to new format
 */
function fau_elemental_check_old_portal_menu_settings() {
    // Reset the migration flag to ensure it runs properly
    delete_option('fau_elemental_portal_menu_migrated');
    
    global $wpdb;
    
    // Find pages with old portal menu settings
    $old_menu_pages = $wpdb->get_results(
        "SELECT post_id, meta_key, meta_value 
        FROM {$wpdb->postmeta} 
        WHERE meta_key = 'portalmenu-slug' 
        OR meta_key = 'portalmenu-slug_oben'",
        ARRAY_A
    );
    
    if (!empty($old_menu_pages)) {
        // Create an associative array to track which posts already have a menu
        $processed_posts = array();
        
        foreach ($old_menu_pages as $meta) {
            $post_id = $meta['post_id'];
            $meta_key = $meta['meta_key'];
            $menu_name = $meta['meta_value'];
            
            // Skip if we've already processed this post (prioritize the first found, which is typically the bottom menu)
            if (isset($processed_posts[$post_id])) {
                continue;
            }
            
            // Get the menu ID from the name
            $menu_obj = get_term_by('name', $menu_name, 'nav_menu');
            if (!$menu_obj) {
                // Try by slug if name doesn't work
                $menu_obj = get_term_by('slug', $menu_name, 'nav_menu');
            }
            
            // Try by ID if it's already a number
            if (!$menu_obj && is_numeric($menu_name)) {
                $menu_obj = get_term_by('id', $menu_name, 'nav_menu');
            }
            
            if ($menu_obj) {
                update_post_meta($post_id, 'portal_menu_id', $menu_obj->term_id);
                // Mark this post as processed
                $processed_posts[$post_id] = true;
                
                // Get additional settings
                if ($meta_key === 'portalmenu-slug') {
                    // Bottom menu settings
                    $nosub = get_post_meta($post_id, 'fauval_portalmenu_nosub', true);
                    if ($nosub) {
                        update_post_meta($post_id, 'portal_menu_hide_subs', true);
                    }
                    
                    $nothumbs = get_post_meta($post_id, 'fauval_portalmenu_thumbnailson', true);
                    if ($nothumbs) {
                        update_post_meta($post_id, 'portal_menu_hide_thumbs', true);
                    }
                } else {
                    // Top menu settings
                    $nosub = get_post_meta($post_id, 'fauval_portalmenu_nosub_oben', true);
                    if ($nosub) {
                        update_post_meta($post_id, 'portal_menu_hide_subs', true);
                    }
                    
                    $nothumbs = get_post_meta($post_id, 'fauval_portalmenu_thumbnailson_oben', true);
                    if ($nothumbs) {
                        update_post_meta($post_id, 'portal_menu_hide_thumbs', true);
                    }
                }
                
                // Ensure the page uses the portal page template
                update_post_meta($post_id, '_wp_page_template', FAU_Elemental_Portal_Menu_Config::TEMPLATE);
                
                // Log the migration for debugging
                error_log("Migrated portal menu settings for post $post_id with menu {$menu_obj->name}");
            } else {
                error_log("Could not find menu for post $post_id with menu name/slug '$menu_name'");
            }
        }
        
        update_option('fau_elemental_portal_menu_migrated', true);
    }
}
add_action('after_switch_theme', 'fau_elemental_check_old_portal_menu_settings');

/**
 * Add backwards compatibility for old shortcodes in content
 */
function fau_elemental_check_content_for_old_shortcodes($post_id) {
    // Only check page content
    if (get_post_type($post_id) !== 'page') {
        return;
    }
    
    // Skip if a portal menu is already set for this post
    if (get_post_meta($post_id, 'portal_menu_id', true)) {
        return;
    }
    
    $content = get_post_field('post_content', $post_id);
    
    // Check if content contains old portal menu shortcode
    if (has_shortcode($content, 'portalmenu')) {
        error_log("Found portalmenu shortcode in content for post $post_id");
        
        // Extract shortcode attributes
        $pattern = get_shortcode_regex(array('portalmenu'));
        preg_match_all('/' . $pattern . '/', $content, $matches, PREG_SET_ORDER);
        
        if (!empty($matches)) {
            foreach ($matches as $shortcode) {
                $atts = shortcode_parse_atts($shortcode[3]);
                
                // Check if menu attribute exists
                if (isset($atts['menu'])) {
                    $menu_obj = get_term_by('name', $atts['menu'], 'nav_menu');
                    if (!$menu_obj) {
                        $menu_obj = get_term_by('slug', $atts['menu'], 'nav_menu');
                    }
                    
                    if (!$menu_obj && is_numeric($atts['menu'])) {
                        $menu_obj = get_term_by('id', $atts['menu'], 'nav_menu');
                    }
                    
                    if ($menu_obj) {
                        // Always set portal menu ID regardless of template
                        update_post_meta($post_id, 'portal_menu_id', $menu_obj->term_id);
                        error_log("Set portal_menu_id to {$menu_obj->term_id} for post $post_id from shortcode");
                        
                        // Handle other attributes
                        if (isset($atts['showsubs']) && ($atts['showsubs'] === 'false' || $atts['showsubs'] === false)) {
                            update_post_meta($post_id, 'portal_menu_hide_subs', true);
                        }
                        
                        if (isset($atts['nothumbs']) && ($atts['nothumbs'] === 'true' || $atts['nothumbs'] === true)) {
                            update_post_meta($post_id, 'portal_menu_hide_thumbs', true);
                        }
                    }
                }
            }
        }
    }
}
add_action('save_post', 'fau_elemental_check_content_for_old_shortcodes');

/**
 * Force re-migration of portal menu settings when saving a page
 */
function fau_elemental_handle_portal_page_save($post_id) {
    // Only for pages
    if (get_post_type($post_id) !== 'page') {
        return;
    }
    
    // Check if there's a portal menu ID set
    $menu_id = get_post_meta($post_id, 'portal_menu_id', true);
    
    // If no menu is set, check if there's an old menu slug
    if (empty($menu_id)) {
        $menu_slug = get_post_meta($post_id, 'portalmenu-slug', true);
        if (!empty($menu_slug)) {
            $menu_obj = get_term_by('name', $menu_slug, 'nav_menu');
            if (!$menu_obj) {
                $menu_obj = get_term_by('slug', $menu_slug, 'nav_menu');
            }
            
            if (!$menu_obj && is_numeric($menu_slug)) {
                $menu_obj = get_term_by('id', $menu_slug, 'nav_menu');
            }
            
            if ($menu_obj) {
                update_post_meta($post_id, 'portal_menu_id', $menu_obj->term_id);
                error_log("Portal menu save handler: Set portal_menu_id to {$menu_obj->term_id} for post $post_id from old slug");
            }
        }
    }
    
    // Check if this is a portal page template
    $template = get_post_meta($post_id, '_wp_page_template', true);
    if ($template === FAU_Elemental_Portal_Menu_Config::TEMPLATE || $template === 'portal-page.php') {
        error_log("Post $post_id is using portal page template: $template");
    } else if (!empty($menu_id)) {
        // This page has portal menu settings but is not using the portal template
        error_log("Post $post_id has portal menu ID $menu_id but is not using portal template");
    }
}
add_action('save_post', 'fau_elemental_handle_portal_page_save', 20); // Run after the other save functions

/**
 * Function to manually trigger remigration
 */
function fau_elemental_remigrate_portal_menus() {
    delete_option('fau_elemental_portal_menu_migrated');
    fau_elemental_check_old_portal_menu_settings();
}

/**
 * AJAX handler for remigration button
 */
function fau_elemental_ajax_remigrate_portal_menus() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'fau_elemental_remigrate_nonce')) {
        wp_send_json_error('Invalid nonce');
    }
    
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission denied');
    }
    
    // Trigger remigration
    fau_elemental_remigrate_portal_menus();
    
    // Specific post migration if post_id is provided
    if (isset($_POST['post_id'])) {
        $post_id = intval($_POST['post_id']);
        fau_elemental_migrate_portal_menu_settings($post_id);
    }
    
    wp_send_json_success('Migration complete');
}
add_action('wp_ajax_fau_elemental_remigrate_portal_menus', 'fau_elemental_ajax_remigrate_portal_menus');

/**
 * Handle the [portalmenu] shortcode
 * 
 * @param array $atts Shortcode attributes containing menu settings
 * @return string The rendered portal menu HTML
 */
function fau_elemental_portalmenu_shortcode($atts) {
    // Extract and sanitize attributes with defaults from config
    $defaults = [
        'menu' => '',
        'id' => '',
        'nothumbs' => FAU_Elemental_Portal_Menu_Config::get_default('hide_thumbs'),
        'nothumbnails' => FAU_Elemental_Portal_Menu_Config::get_default('hide_thumbs'),
        'nosub' => !FAU_Elemental_Portal_Menu_Config::get_default('show_subs'),
        'hidesubs' => !FAU_Elemental_Portal_Menu_Config::get_default('show_subs'),
        'is-style-dark' => FAU_Elemental_Portal_Menu_Config::get_default('is_dark'),
        'dark' => FAU_Elemental_Portal_Menu_Config::get_default('is_dark'),
        'theme' => FAU_Elemental_Portal_Menu_Config::get_default('is_dark') ? "dark" : "light",
    ];
    
    $atts = shortcode_atts($defaults, $atts, 'portalmenu');
    
    // Find menu ID - priority: id param, menu param, page setting
    $menu_id = 0;
    
    // If id is provided directly
    if (!empty($atts['id'])) {
        $menu_id = $atts['id'];
        
        // If not numeric, try to resolve to an ID
        if (!is_numeric($menu_id)) {
            $menu_obj = get_term_by('name', $menu_id, 'nav_menu');
            if (!$menu_obj) {
                $menu_obj = get_term_by('slug', $menu_id, 'nav_menu');
            }
            
            if ($menu_obj) {
                $menu_id = $menu_obj->term_id;
            }
        }
    }
    // If menu name/slug is provided
    elseif (!empty($atts['menu'])) {
        $menu_obj = get_term_by('name', $atts['menu'], 'nav_menu');
        if (!$menu_obj) {
            $menu_obj = get_term_by('slug', $atts['menu'], 'nav_menu');
        }
        
        if ($menu_obj) {
            $menu_id = $menu_obj->term_id;
        }
    }
    // Fallback to page setting
    else {
        $post_id = get_the_ID();
        if ($post_id) {
            $menu_id = get_post_meta($post_id, FAU_Elemental_Portal_Menu_Config::get_meta_field('menu_id'), true);
            if (!$menu_id) {
                // Try the old meta field for backwards compatibility
                $old_menu = get_post_meta($post_id, 'portalmenu-slug', true);
                if ($old_menu) {
                    $menu_obj = get_term_by('name', $old_menu, 'nav_menu');
                    if (!$menu_obj) {
                        $menu_obj = get_term_by('slug', $old_menu, 'nav_menu');
                    }
                    
                    if ($menu_obj) {
                        $menu_id = $menu_obj->term_id;
                    }
                }
            }
        }
    }
    
    // If no menu was found, return accessible error message
    if (!$menu_id) {
        return '<div role="alert" aria-live="polite" class="portal-menu-error">' . 
               esc_html__('No menu could be found with the specified identifier.', 'fau-elemental') . 
               '</div>';
    }
    
    // Handle boolean attributes that might have different formats
    $hide_thumbs = filter_var($atts['nothumbs'] ?: $atts['nothumbnails'], FILTER_VALIDATE_BOOLEAN);
    $hide_subs = filter_var($atts['nosub'] ?: $atts['hidesubs'], FILTER_VALIDATE_BOOLEAN);
    $is_dark = filter_var($atts['is-style-dark'] ?: $atts['dark'], FILTER_VALIDATE_BOOLEAN);
    
    // Load our menu walker class
    if (!class_exists('Walker_Content_Menu')) {
        require_once get_template_directory() . '/inc/class-walker-content-menu.php';
    }

    // Set up walker settings
    $walker_settings = array(
        'showsubs' => !$hide_subs,
        'nothumbs' => $hide_thumbs,
        'theme'    => $is_dark ? 'dark' : 'light',
    );

    return Walker_Content_Menu::render_portalmenu($menu_id, $walker_settings);
}
add_shortcode('portalmenu', 'fau_elemental_portalmenu_shortcode');

/**
 * Manual migration function that can be called from other parts of the code
 *
 * @param int $post_id The post ID to migrate
 * @return bool True if migration was performed, false if not needed
 */
function fau_elemental_migrate_portal_menu_settings($post_id) {
    // Skip if post type is not page
    if (get_post_type($post_id) !== 'page') {
        return false;
    }
    
    // Check if we need to migrate
    $old_menu = get_post_meta($post_id, 'portalmenu-slug', true);
    $old_menu_top = get_post_meta($post_id, 'portalmenu-slug_oben', true);
    
    if (empty($old_menu) && empty($old_menu_top)) {
        return false; // Nothing to migrate
    }
    
    // Skip if a portal menu is already set
    if (get_post_meta($post_id, 'portal_menu_id', true)) {
        return false;
    }
    
    // Prioritize bottom menu if both exist
    $menu_to_use = !empty($old_menu) ? $old_menu : $old_menu_top;
    $is_top_menu = empty($old_menu) && !empty($old_menu_top);
    
    // Find menu object
    $menu_obj = get_term_by('name', $menu_to_use, 'nav_menu');
    if (!$menu_obj) {
        $menu_obj = get_term_by('slug', $menu_to_use, 'nav_menu');
    }
    
    if (!$menu_obj && is_numeric($menu_to_use)) {
        $menu_obj = get_term_by('id', $menu_to_use, 'nav_menu');
    }
    
    if ($menu_obj) {
        // Always set the menu ID regardless of template
        update_post_meta($post_id, 'portal_menu_id', $menu_obj->term_id);
        
        // Migrate other settings based on which menu we're using
        $settings_map = array(
            'thumbnails' => $is_top_menu ? 'fauval_portalmenu_thumbnailson_oben' : 'fauval_portalmenu_thumbnailson',
            'nosub' => $is_top_menu ? 'fauval_portalmenu_nosub_oben' : 'fauval_portalmenu_nosub',
        );
        
        // Process each setting
        $thumbnails = get_post_meta($post_id, $settings_map['thumbnails'], true);
        if ($thumbnails) {
            update_post_meta($post_id, 'portal_menu_hide_thumbs', true);
        }
        
        $nosub = get_post_meta($post_id, $settings_map['nosub'], true);
        if ($nosub) {
            update_post_meta($post_id, 'portal_menu_hide_subs', true);
        }
        
        // Check if the page is using a custom template
        $template = get_post_meta($post_id, '_wp_page_template', true);
        
        // Only set template if not already set to something other than default
        if (empty($template) || $template === 'default') {
            update_post_meta($post_id, '_wp_page_template', 'portal-page.php');
            error_log("Portal menu migration: Set template for post $post_id to portal-page.php");
        } else {
            error_log("Portal menu migration: Post $post_id already using template: $template - not changing");
        }
        
        return true;
    }
    
    return false; // Migration didn't happen
} 