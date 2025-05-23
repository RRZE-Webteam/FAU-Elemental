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
                    $meganav = get_post_meta($post_id, 'fauval_portalmenu_meganav', true);
                    if ($meganav) {
                        update_post_meta($post_id, 'portal_menu_mega_nav', true);
                    }
                    
                    $nosub = get_post_meta($post_id, 'fauval_portalmenu_nosub', true);
                    if ($nosub) {
                        update_post_meta($post_id, 'portal_menu_hide_subs', true);
                    }
                    
                    $nothumbs = get_post_meta($post_id, 'fauval_portalmenu_thumbnailson', true);
                    if ($nothumbs) {
                        update_post_meta($post_id, 'portal_menu_hide_thumbs', true);
                    }
                    
                    $nofallback = get_post_meta($post_id, 'fauval_portalmenu_nofallbackthumb', true);
                    if ($nofallback) {
                        update_post_meta($post_id, 'portal_menu_no_fallback', true);
                    }
                    
                    $type = get_post_meta($post_id, 'fauval_portalmenu_type', true);
                    if ($type) {
                        update_post_meta($post_id, 'portal_menu_type', intval($type));
                    }
                    
                    $listview = get_post_meta($post_id, 'fauval_portalmenu_listview', true);
                    if ($listview) {
                        update_post_meta($post_id, 'portal_menu_list_view', true);
                    }
                    
                    $hoverzoom = get_post_meta($post_id, 'fauval_portalmenu_hoverZoom', true);
                    if ($hoverzoom) {
                        update_post_meta($post_id, 'portal_menu_hover_zoom', true);
                    }
                    
                    $hoverblur = get_post_meta($post_id, 'fauval_portalmenu_hoverBlur', true);
                    if ($hoverblur) {
                        update_post_meta($post_id, 'portal_menu_hover_blur', true);
                    }
                    
                    // Set default columns if not set
                    if (!get_post_meta($post_id, 'portal_menu_columns', true)) {
                        update_post_meta($post_id, 'portal_menu_columns', 3); // Default to 3 columns
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
                    
                    $nofallback = get_post_meta($post_id, 'fauval_portalmenu_nofallbackthumb_oben', true);
                    if ($nofallback) {
                        update_post_meta($post_id, 'portal_menu_no_fallback', true);
                    }
                    
                    $type = get_post_meta($post_id, 'fauval_portalmenu_type_oben', true);
                    if ($type) {
                        update_post_meta($post_id, 'portal_menu_type', intval($type));
                    }
                    
                    $listview = get_post_meta($post_id, 'fauval_portalmenu_listview_oben', true);
                    if ($listview) {
                        update_post_meta($post_id, 'portal_menu_list_view', true);
                    }
                    
                    $hoverzoom = get_post_meta($post_id, 'fauval_portalmenu_hoverZoom_oben', true);
                    if ($hoverzoom) {
                        update_post_meta($post_id, 'portal_menu_hover_zoom', true);
                    }
                    
                    $hoverblur = get_post_meta($post_id, 'fauval_portalmenu_hoverBlur_oben', true);
                    if ($hoverblur) {
                        update_post_meta($post_id, 'portal_menu_hover_blur', true);
                    }
                    
                    // Set default columns if not set
                    if (!get_post_meta($post_id, 'portal_menu_columns', true)) {
                        update_post_meta($post_id, 'portal_menu_columns', 3); // Default to 3 columns
                    }
                }
                
                // Ensure the page uses the portal page template
                update_post_meta($post_id, '_wp_page_template', 'templates/portal-page.php');
                
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
                        if (isset($atts['type'])) {
                            update_post_meta($post_id, 'portal_menu_type', intval($atts['type']));
                        }
                        
                        if (isset($atts['columns'])) {
                            update_post_meta($post_id, 'portal_menu_columns', intval($atts['columns']));
                        }
                        
                        if (isset($atts['showsubs']) && ($atts['showsubs'] === 'false' || $atts['showsubs'] === false)) {
                            update_post_meta($post_id, 'portal_menu_hide_subs', true);
                        }
                        
                        if (isset($atts['listview']) && ($atts['listview'] === 'true' || $atts['listview'] === true)) {
                            update_post_meta($post_id, 'portal_menu_list_view', true);
                        }
                        
                        if (isset($atts['nothumbs']) && ($atts['nothumbs'] === 'true' || $atts['nothumbs'] === true)) {
                            update_post_meta($post_id, 'portal_menu_hide_thumbs', true);
                        }
                        
                        if (isset($atts['nofallback']) && ($atts['nofallback'] === 'true' || $atts['nofallback'] === true)) {
                            update_post_meta($post_id, 'portal_menu_no_fallback', true);
                        }
                        
                        if (isset($atts['hoverzoom']) && ($atts['hoverzoom'] === 'true' || $atts['hoverzoom'] === true)) {
                            update_post_meta($post_id, 'portal_menu_hover_zoom', true);
                        }
                        
                        if (isset($atts['hoverblur']) && ($atts['hoverblur'] === 'true' || $atts['hoverblur'] === true)) {
                            update_post_meta($post_id, 'portal_menu_hover_blur', true);
                        }
                        
                        // Set default columns if not already set
                        if (!get_post_meta($post_id, 'portal_menu_columns', true)) {
                            update_post_meta($post_id, 'portal_menu_columns', 3);
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
    if ($template === 'templates/portal-page.php' || $template === 'portal-page.php') {
        error_log("Post $post_id is using portal page template: $template");
        
        // Ensure default settings are set if not already
        if (!get_post_meta($post_id, 'portal_menu_type', true)) {
            update_post_meta($post_id, 'portal_menu_type', 1); // Default to Type 1
        }
        if (!get_post_meta($post_id, 'portal_menu_columns', true)) {
            update_post_meta($post_id, 'portal_menu_columns', 3); // Default to 3 columns
        }
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
 * Add debugging information to the portal page
 */
function fau_elemental_portal_page_debug_info() {
    // Only show for admins
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Only on portal pages
    if (is_page() && get_page_template_slug() === 'templates/portal-page.php') {
        $post_id = get_the_ID();
        $menu_id = get_post_meta($post_id, 'portal_menu_id', true);
        $old_menu = get_post_meta($post_id, 'portalmenu-slug', true);
        
        echo '<div class="portal-debug-info" style="background: #f8f9fa; border: 1px solid #ddd; padding: 15px; margin: 20px 0; font-family: monospace;">';
        echo '<h4>Portal Menu Debug Info</h4>';
        echo '<ul>';
        echo '<li>Post ID: ' . $post_id . '</li>';
        echo '<li>New menu ID: ' . $menu_id . '</li>';
        echo '<li>Old menu slug: ' . $old_menu . '</li>';
        echo '<li>Display type: ' . get_post_meta($post_id, 'portal_menu_type', true) . '</li>';
        echo '<li>Columns: ' . get_post_meta($post_id, 'portal_menu_columns', true) . '</li>';
        echo '<li>Hide subs: ' . (get_post_meta($post_id, 'portal_menu_hide_subs', true) ? 'Yes' : 'No') . '</li>';
        echo '<li>List view: ' . (get_post_meta($post_id, 'portal_menu_list_view', true) ? 'Yes' : 'No') . '</li>';
        echo '<li>Hide thumbs: ' . (get_post_meta($post_id, 'portal_menu_hide_thumbs', true) ? 'Yes' : 'No') . '</li>';
        echo '<li>No fallback: ' . (get_post_meta($post_id, 'portal_menu_no_fallback', true) ? 'Yes' : 'No') . '</li>';
        echo '<li>Hover zoom: ' . (get_post_meta($post_id, 'portal_menu_hover_zoom', true) ? 'Yes' : 'No') . '</li>';
        echo '<li>Hover blur: ' . (get_post_meta($post_id, 'portal_menu_hover_blur', true) ? 'Yes' : 'No') . '</li>';
        echo '</ul>';
        
        echo '<p><button id="remigrate-portal-menu" class="button">Re-migrate Portal Menu Settings</button></p>';
        echo '<script>
            jQuery(document).ready(function($) {
                $("#remigrate-portal-menu").on("click", function(e) {
                    e.preventDefault();
                    $.ajax({
                        url: ajaxurl,
                        method: "POST",
                        data: {
                            action: "fau_elemental_remigrate_portal_menus",
                            post_id: ' . $post_id . ',
                            nonce: "' . wp_create_nonce('fau_elemental_remigrate_nonce') . '"
                        },
                        success: function(response) {
                            alert("Migration complete. Refreshing page...");
                            location.reload();
                        }
                    });
                });
            });
        </script>';
        echo '</div>';
    }
}
add_action('wp_footer', 'fau_elemental_portal_page_debug_info');

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
 * Improved shortcode compatibility for [portalmenu]
 */
function fau_elemental_portalmenu_shortcode($atts) {
    // Extract and sanitize attributes
    $atts = shortcode_atts([
        'menu' => '',
        'id' => '',
        'type' => 1,
        'columns' => 3,
        'nothumbs' => false,
        'nothumbnails' => false,
        'nofallback' => false,
        'nofallbackthumb' => false,
        'nosub' => false,
        'hidesubs' => false, 
        'listview' => false,
        'hoverzoom' => false,
        'hoverZoom' => false,
        'hoverblur' => false,
        'hoverBlur' => false,
    ], $atts, 'portalmenu');
    
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
            $menu_id = get_post_meta($post_id, 'portal_menu_id', true);
            if (!$menu_id) {
                // Try the old meta field
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
    
    // If no menu was found, return empty
    if (!$menu_id) {
        return '';
    }
    
    // Handle boolean attributes that might have different formats
    $hide_thumbs = filter_var($atts['nothumbs'] ?: $atts['nothumbnails'], FILTER_VALIDATE_BOOLEAN);
    $no_fallback = filter_var($atts['nofallback'] ?: $atts['nofallbackthumb'], FILTER_VALIDATE_BOOLEAN);
    $hide_subs = filter_var($atts['nosub'] ?: $atts['hidesubs'], FILTER_VALIDATE_BOOLEAN);
    $list_view = filter_var($atts['listview'], FILTER_VALIDATE_BOOLEAN);
    $hover_zoom = filter_var($atts['hoverzoom'] ?: $atts['hoverZoom'], FILTER_VALIDATE_BOOLEAN);
    $hover_blur = filter_var($atts['hoverblur'] ?: $atts['hoverBlur'], FILTER_VALIDATE_BOOLEAN);
    
    // Setup CSS classes
    $menu_classes = 'contentmenu';
    
    // Add size class based on type
    $type = intval($atts['type']);
    if ($type == 1) {
        $menu_classes .= ' size_2-1';
    } elseif ($type == 2) {
        $menu_classes .= ' size_3-2';
    } elseif ($type == 3) {
        $menu_classes .= ' size_3-4';
    }
    
    // Add optional classes
    if ($list_view) {
        $menu_classes .= ' listview';
    }
    if ($hide_thumbs) {
        $menu_classes .= ' no-thumb';
    }
    if ($hover_zoom) {
        $menu_classes .= ' hover-zoom';
    }
    if ($hover_blur) {
        $menu_classes .= ' hover-blur';
    }
    
    // Load our menu walker class
    if (!class_exists('FAU_Elemental\\Walker_Content_Menu')) {
        require_once get_template_directory() . '/inc/class-walker-content-menu.php';
    }
    
    // Buffer the output and return it
    ob_start();
    
    echo '<div class="' . esc_attr($menu_classes) . '">';
    
    wp_nav_menu([
        'menu' => $menu_id,
        'container' => false,
        'menu_class' => 'subpages-menu',
        'walker' => new FAU_Elemental\Walker_Content_Menu([
            'columns' => intval($atts['columns']),
            'hide_subs' => $hide_subs,
            'hide_thumbs' => $hide_thumbs,
            'no_fallback' => $no_fallback,
        ]),
    ]);
    
    echo '</div>';
    
    return ob_get_clean();
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
            'type' => $is_top_menu ? 'fauval_portalmenu_type_oben' : 'fauval_portalmenu_type',
            'thumbnails' => $is_top_menu ? 'fauval_portalmenu_thumbnailson_oben' : 'fauval_portalmenu_thumbnailson',
            'nofallback' => $is_top_menu ? 'fauval_portalmenu_nofallbackthumb_oben' : 'fauval_portalmenu_nofallbackthumb',
            'nosub' => $is_top_menu ? 'fauval_portalmenu_nosub_oben' : 'fauval_portalmenu_nosub',
            'listview' => $is_top_menu ? 'fauval_portalmenu_listview_oben' : 'fauval_portalmenu_listview',
            'hoverzoom' => $is_top_menu ? 'fauval_portalmenu_hoverZoom_oben' : 'fauval_portalmenu_hoverZoom',
            'hoverblur' => $is_top_menu ? 'fauval_portalmenu_hoverBlur_oben' : 'fauval_portalmenu_hoverBlur'
        );
        
        // Process each setting
        $type = get_post_meta($post_id, $settings_map['type'], true);
        if ($type) {
            update_post_meta($post_id, 'portal_menu_type', intval($type));
        }
        
        $thumbnails = get_post_meta($post_id, $settings_map['thumbnails'], true);
        if ($thumbnails) {
            update_post_meta($post_id, 'portal_menu_hide_thumbs', true);
        }
        
        $nofallback = get_post_meta($post_id, $settings_map['nofallback'], true);
        if ($nofallback) {
            update_post_meta($post_id, 'portal_menu_no_fallback', true);
        }
        
        $nosub = get_post_meta($post_id, $settings_map['nosub'], true);
        if ($nosub) {
            update_post_meta($post_id, 'portal_menu_hide_subs', true);
        }
        
        $listview = get_post_meta($post_id, $settings_map['listview'], true);
        if ($listview) {
            update_post_meta($post_id, 'portal_menu_list_view', true);
        }
        
        $hoverzoom = get_post_meta($post_id, $settings_map['hoverzoom'], true);
        if ($hoverzoom) {
            update_post_meta($post_id, 'portal_menu_hover_zoom', true);
        }
        
        $hoverblur = get_post_meta($post_id, $settings_map['hoverblur'], true);
        if ($hoverblur) {
            update_post_meta($post_id, 'portal_menu_hover_blur', true);
        }
        
        // Set default columns if not set
        if (!get_post_meta($post_id, 'portal_menu_columns', true)) {
            update_post_meta($post_id, 'portal_menu_columns', 3);
        }
        
        // Check if the page is using a custom template
        $template = get_post_meta($post_id, '_wp_page_template', true);
        
        // Only set template if not already set to something other than default
        if (empty($template) || $template === 'default') {
            update_post_meta($post_id, '_wp_page_template', 'templates/portal-page.php');
            error_log("Portal menu migration: Set template for post $post_id to templates/portal-page.php");
        } else {
            error_log("Portal menu migration: Post $post_id already using template: $template - not changing");
        }
        
        return true;
    }
    
    return false; // Migration didn't happen
} 