<?php
/**
 * Portal Page Settings
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

define('FAUE_NAV_MENUS_CACHE_KEY', 'fau_elemental_nav_menus');

/**
 * Returns cached menus
 */
function fau_elemental_get_nav_menus() {
    $menus = wp_cache_get( FAUE_NAV_MENUS_CACHE_KEY, 'theme' );
    if ( false === $menus ) {
        $menus = get_terms(
            array(
                'taxonomy'   => 'nav_menu',
                'fields'     => 'id=>name', // ID -> Name-Map
                'hide_empty' => false,
                'orderby'    => 'name',
            )
        );
        wp_cache_set( FAUE_NAV_MENUS_CACHE_KEY, $menus, 'theme', 3 * HOUR_IN_SECONDS );
    }
    return $menus;
}

/**
 * Clear cache on navigation menu change
 */
add_action( 'wp_update_nav_menu', fn() => wp_cache_delete( FAUE_NAV_MENUS_CACHE_KEY, 'theme' ) );

/**
 * Add meta boxes for portal page settings
 */
function fau_elemental_add_portal_page_meta_boxes() {
    // Add meta box to all pages - ALWAYS show it regardless of template
    add_meta_box(
        'fau_elemental_portal_menu_settings',
        __('Portal Menu Settings', 'fau-elemental'),
        'fau_elemental_portal_menu_meta_box_callback',
        'page',
        'side',
        'high' // High priority for more visibility
    );
}
add_action('add_meta_boxes', 'fau_elemental_add_portal_page_meta_boxes');

/**
 * Meta box callback
 */
function fau_elemental_portal_menu_meta_box_callback($post) {
    // Add a nonce field for security
    wp_nonce_field('fau_elemental_portal_menu_meta_box', 'fau_elemental_portal_menu_meta_box_nonce');

    // Get the saved values
    $selected_menu_id = get_post_meta($post->ID, 'portal_menu_id', true);
    $hide_subs = (bool) get_post_meta($post->ID, 'portal_menu_hide_subs', true);
    $hide_thumbs = (bool) get_post_meta($post->ID, 'portal_menu_hide_thumbs', true);
    $is_dark = (bool) get_post_meta($post->ID, 'portal_menu_is_dark', true);

    $hide_title_meta_key = FAU_Elemental_Portal_Menu_Config::get_meta_field('hide_title');
    $hide_title_raw = $hide_title_meta_key ? get_post_meta($post->ID, $hide_title_meta_key, true) : '';
    $hide_title = ($hide_title_raw === '' && $hide_title_meta_key)
        ? (bool) FAU_Elemental_Portal_Menu_Config::get_default('hide_title')
        : (bool) $hide_title_raw;

    // Get all menus
    $menus = fau_elemental_get_nav_menus();

    ?>
    <div class="fau-portal-menu-settings">
        <p>
            <label for="portal_menu_id"><strong><?php esc_html_e('Menu', 'fau-elemental'); ?>:</strong></label>
            <select name="portal_menu_id" id="portal_menu_id" class="widefat">
                <option value=""><?php esc_html_e('- Select Menu -', 'fau-elemental'); ?></option>
                <?php foreach ($menus as $menu_id => $menu_name) : ?>
                    <option value="<?php echo esc_attr($menu_id); ?>" <?php selected($selected_menu_id, $menu_id); ?>>
                        <?php echo esc_html($menu_name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        
        <p>
            <label><input type="checkbox" name="portal_menu_hide_subs" id="portal_menu_hide_subs" value="1" <?php checked($hide_subs, true); ?>>
            <?php esc_html_e('Hide Submenus', 'fau-elemental'); ?></label>
        </p>
        
        <p>
            <label><input type="checkbox" name="portal_menu_hide_thumbs" id="portal_menu_hide_thumbs" value="1" <?php checked($hide_thumbs, true); ?>>
            <?php esc_html_e('Hide Thumbnails', 'fau-elemental'); ?></label>
        </p>
        
        <p>
            <label><input type="checkbox" name="portal_menu_is_dark" id="portal_menu_is_dark" value="1" <?php checked($is_dark, true); ?>>
            <?php esc_html_e('Dark Style', 'fau-elemental'); ?></label>
        </p>

        <p>
            <label><input type="checkbox" name="portal_menu_hide_title_meta" id="portal_menu_hide_title_meta" value="1" <?php checked($hide_title, true); ?>>
            <?php esc_html_e('Hide Page Title (use portal hero)', 'fau-elemental'); ?></label>
        </p>
        
        <code>[portalmenu menu="<?php echo ($selected_menu_id ? esc_attr($selected_menu_id) : 'menu-id-or-name'); ?>" showsubs="<?php echo esc_attr($hide_subs ? "false" : "true"); ?>" nothumbs="<?php echo esc_attr($hide_thumbs ? "true" : "false") ?>"]</code>
    </div>
    <?php
}

/**
 * Save meta box data
 */
function fau_elemental_save_portal_menu_meta_box_data($post_id) {
    // Check if nonce is set
    if (!isset($_POST['fau_elemental_portal_menu_meta_box_nonce'])) {
        return;
    }

    // Verify the nonce
    if (!wp_verify_nonce($_POST['fau_elemental_portal_menu_meta_box_nonce'], 'fau_elemental_portal_menu_meta_box')) {
        return;
    }

    // Check if this is an autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check user permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save the menu ID regardless of template - it helps if they switch templates later
    if (isset($_POST['portal_menu_id'])) {
        update_post_meta($post_id, 'portal_menu_id', sanitize_text_field($_POST['portal_menu_id']));
    }

    // Save checkboxes
    $checkbox_fields = array(
        'portal_menu_hide_subs',
        'portal_menu_hide_thumbs',
        'portal_menu_is_dark'
    );

    foreach ($checkbox_fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $field, true);
        } else {
            delete_post_meta($post_id, $field);
        }
    }

    // Handle hide title separately so unchecked state overrides the default
    $hide_title_field = FAU_Elemental_Portal_Menu_Config::get_meta_field('hide_title');
    if (!empty($hide_title_field)) {
        $hide_title_checked = isset($_POST[$hide_title_field]);
        update_post_meta($post_id, $hide_title_field, $hide_title_checked);
    }
}

add_action('save_post', 'fau_elemental_save_portal_menu_meta_box_data'); 
