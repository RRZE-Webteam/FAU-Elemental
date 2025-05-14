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
        foreach ($old_menu_pages as $meta) {
            $post_id = $meta['post_id'];
            $meta_key = $meta['meta_key'];
            $menu_name = $meta['meta_value'];
            
            // Get the menu ID from the name
            $menu_obj = get_term_by('name', $menu_name, 'nav_menu');
            if ($menu_obj) {
                update_post_meta($post_id, 'portal_menu_id', $menu_obj->term_id);
                
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
                }
                
                // Ensure the page uses the portal page template
                update_post_meta($post_id, '_wp_page_template', 'templates/portal-page.php');
                
                // Log the migration for debugging
                error_log("Migrated portal menu settings for post $post_id with menu {$menu_obj->name}");
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
    
    $content = get_post_field('post_content', $post_id);
    
    // Check if content contains old portal menu shortcode
    if (has_shortcode($content, 'portalmenu')) {
        // Extract shortcode attributes
        $pattern = get_shortcode_regex(array('portalmenu'));
        preg_match_all('/' . $pattern . '/', $content, $matches, PREG_SET_ORDER);
        
        if (!empty($matches)) {
            foreach ($matches as $shortcode) {
                $atts = shortcode_parse_atts($shortcode[3]);
                
                // Check if menu attribute exists
                if (isset($atts['menu'])) {
                    $menu_obj = get_term_by('name', $atts['menu'], 'nav_menu');
                    if ($menu_obj) {
                        update_post_meta($post_id, 'portal_menu_id', $menu_obj->term_id);
                        
                        // Handle other attributes
                        if (isset($atts['type'])) {
                            update_post_meta($post_id, 'portal_menu_type', intval($atts['type']));
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
                        
                        // Ensure the page uses the portal page template
                        update_post_meta($post_id, '_wp_page_template', 'templates/portal-page.php');
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
    
    // Check if this is a portal page template
    $template = get_post_meta($post_id, '_wp_page_template', true);
    if ($template === 'templates/portal-page.php') {
        // Make sure the menu is set
        $menu_id = get_post_meta($post_id, 'portal_menu_id', true);
        if (empty($menu_id)) {
            // Check if there's an old menu slug
            $menu_slug = get_post_meta($post_id, 'portalmenu-slug', true);
            if (!empty($menu_slug)) {
                $menu_obj = get_term_by('name', $menu_slug, 'nav_menu');
                if ($menu_obj) {
                    update_post_meta($post_id, 'portal_menu_id', $menu_obj->term_id);
                }
            }
        }
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
        $menu_name = '';
        if ($menu_id) {
            $menu_obj = wp_get_nav_menu_object($menu_id);
            if ($menu_obj) {
                $menu_name = $menu_obj->name;
            }
        }
        
        echo '<div style="background: #f1f1f1; padding: 10px; margin: 20px 0; border: 1px solid #ddd;">';
        echo '<h3>Portal Menu Debug Info (Only visible to admins)</h3>';
        echo '<p>Post ID: ' . $post_id . '</p>';
        echo '<p>Menu ID: ' . $menu_id . '</p>';
        echo '<p>Menu Name: ' . $menu_name . '</p>';
        echo '<p>Template: ' . get_page_template_slug() . '</p>';
        echo '<p>Display Type: ' . get_post_meta($post_id, 'portal_menu_type', true) . '</p>';
        echo '<p>Hide Subs: ' . (get_post_meta($post_id, 'portal_menu_hide_subs', true) ? 'Yes' : 'No') . '</p>';
        echo '<p>List View: ' . (get_post_meta($post_id, 'portal_menu_list_view', true) ? 'Yes' : 'No') . '</p>';
        echo '<p>Hide Thumbs: ' . (get_post_meta($post_id, 'portal_menu_hide_thumbs', true) ? 'Yes' : 'No') . '</p>';
        echo '<p>No Fallback: ' . (get_post_meta($post_id, 'portal_menu_no_fallback', true) ? 'Yes' : 'No') . '</p>';
        echo '<p>Hover Zoom: ' . (get_post_meta($post_id, 'portal_menu_hover_zoom', true) ? 'Yes' : 'No') . '</p>';
        echo '<p>Hover Blur: ' . (get_post_meta($post_id, 'portal_menu_hover_blur', true) ? 'Yes' : 'No') . '</p>';
        echo '<p><a href="#" onclick="jQuery.post(ajaxurl, {action: \'fau_elemental_remigrate_portal_menus\'}); alert(\'Migration triggered\'); return false;">Re-run migration</a></p>';
        echo '</div>';
    }
}
add_action('wp_footer', 'fau_elemental_portal_page_debug_info');

/**
 * Add AJAX handler for remigration
 */
function fau_elemental_ajax_remigrate_portal_menus() {
    if (current_user_can('manage_options')) {
        fau_elemental_remigrate_portal_menus();
    }
    wp_die();
}
add_action('wp_ajax_fau_elemental_remigrate_portal_menus', 'fau_elemental_ajax_remigrate_portal_menus'); 