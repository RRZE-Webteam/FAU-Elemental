<?php
/**
 * Portal Page Settings
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

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
    $menu_id = get_post_meta($post->ID, 'portal_menu_id', true);
    $hide_subs = get_post_meta($post->ID, 'portal_menu_hide_subs', true);
    $hide_thumbs = get_post_meta($post->ID, 'portal_menu_hide_thumbs', true);
    $is_dark = get_post_meta($post->ID, 'portal_menu_is_dark', true);

    // Get all menus
    $menus = wp_get_nav_menus();

    ?>
    <div class="fau-portal-menu-settings">
        <p>
            <label for="portal_menu_id"><strong><?php esc_html_e('Menu', 'fau-elemental'); ?>:</strong></label>
            <select name="portal_menu_id" id="portal_menu_id" class="widefat">
                <option value=""><?php esc_html_e('- Select Menu -', 'fau-elemental'); ?></option>
                <?php foreach ($menus as $menu) : ?>
                    <option value="<?php echo esc_attr($menu->term_id); ?>" <?php selected($menu_id, $menu->term_id); ?>>
                        <?php echo esc_html($menu->name); ?>
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
        
        <code>[portalmenu menu="<?php echo ($menu_id ? esc_attr($menu_id) : 'menu-id-or-name'); ?>" showsubs="<?php echo esc_attr($hide_subs ? "false" : "true"); ?>" nothumbs="<?php echo esc_attr($hide_thumbs ? "true" : "false") ?>"]</code>
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
}

add_action('save_post', 'fau_elemental_save_portal_menu_meta_box_data'); 
