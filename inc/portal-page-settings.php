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
 * Show the meta box with improved template selection visibility
 */
function fau_elemental_admin_footer() {
    global $post;
    if (!$post || get_post_type($post) !== 'page') {
        return;
    }
    
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        console.log('FAU Portal Menu: Script loaded');
        
        // Function to check the template and update the meta box UI
        function checkTemplate() {
            var template = $('#page_template').val();
            console.log('FAU Portal Menu: Detected template: ' + template);
            
            if (template === 'components/templates/portal-page/portal-page.php' || template === 'portal-page.php') {
                console.log('FAU Portal Menu: Using portal template');
                $('#fau_elemental_portal_menu_settings').addClass('fau-portal-active').css({
                    'border': '2px solid #2271b1',
                    'box-shadow': '0 0 5px rgba(34, 113, 177, 0.2)'
                });
            } else {
                console.log('FAU Portal Menu: Not using portal template');
                $('#fau_elemental_portal_menu_settings').removeClass('fau-portal-active').css({
                    'border': '1px solid #ccd0d4',
                    'box-shadow': 'none'
                });
            }
        }
        
        // Check if we're in the block editor
        if ($('.block-editor').length || $('.edit-post-layout').length) {
            console.log('FAU Portal Menu: Block editor detected');
            
            // For Gutenberg/Block Editor - we'll show the panel always
            // and add a note inside it when template isn't selected
            
            // Check if the post is already using a portal template
            setTimeout(function() {
                var template = wp.data.select('core/editor').getEditedPostAttribute('template');
                console.log('FAU Portal Menu: Block editor template: ' + template);
                
                if (template === 'components/templates/portal-page/portal-page.php' || template === 'portal-page.php') {
                    console.log('FAU Portal Menu: Using portal template');
                }
            }, 1000);
        } else {
            console.log('FAU Portal Menu: Classic editor detected');
            // For Classic Editor
            checkTemplate();
            $('#page_template').change(function() {
                console.log('FAU Portal Menu: Template changed to ' + $(this).val());
                checkTemplate();
            });
        }
    });
    </script>
    <?php
}
add_action('admin_footer', 'fau_elemental_admin_footer');

/**
 * Meta box callback
 */
function fau_elemental_portal_menu_meta_box_callback($post) {
    // Add a nonce field for security
    wp_nonce_field('fau_elemental_portal_menu_meta_box', 'fau_elemental_portal_menu_meta_box_nonce');

    // Get the saved values
    $menu_id = get_post_meta($post->ID, 'portal_menu_id', true);
    $display_type = get_post_meta($post->ID, 'portal_menu_type', true) ?: 1;
    $columns = 3; // Always use 3 columns
    $hide_subs = get_post_meta($post->ID, 'portal_menu_hide_subs', true);
    $list_view = get_post_meta($post->ID, 'portal_menu_list_view', true);
    $hide_thumbs = get_post_meta($post->ID, 'portal_menu_hide_thumbs', true);
    $no_fallback = get_post_meta($post->ID, 'portal_menu_no_fallback', true);
    $hover_zoom = get_post_meta($post->ID, 'portal_menu_hover_zoom', true);
    $hover_blur = get_post_meta($post->ID, 'portal_menu_hover_blur', true);
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
            <label for="portal_menu_type"><strong><?php esc_html_e('Display Type', 'fau-elemental'); ?>:</strong></label>
            <select name="portal_menu_type" id="portal_menu_type" class="widefat">
                <option value="1" <?php selected($display_type, 1); ?>><?php esc_html_e('Type 1 (2:1 Ratio)', 'fau-elemental'); ?></option>
                <option value="2" <?php selected($display_type, 2); ?>><?php esc_html_e('Type 2 (3:2 Ratio)', 'fau-elemental'); ?></option>
                <option value="3" <?php selected($display_type, 3); ?>><?php esc_html_e('Type 3 (3:4 Ratio)', 'fau-elemental'); ?></option>
            </select>
        </p>
        
        <p>
            <label><input type="checkbox" name="portal_menu_hide_subs" id="portal_menu_hide_subs" value="1" <?php checked($hide_subs, true); ?> />
            <?php esc_html_e('Hide Submenus', 'fau-elemental'); ?></label>
        </p>
        
        <p>
            <label><input type="checkbox" name="portal_menu_list_view" id="portal_menu_list_view" value="1" <?php checked($list_view, true); ?> />
            <?php esc_html_e('List View', 'fau-elemental'); ?></label>
        </p>
        
        <p>
            <label><input type="checkbox" name="portal_menu_hide_thumbs" id="portal_menu_hide_thumbs" value="1" <?php checked($hide_thumbs, true); ?> />
            <?php esc_html_e('Hide Thumbnails', 'fau-elemental'); ?></label>
        </p>
        
        <p>
            <label><input type="checkbox" name="portal_menu_no_fallback" id="portal_menu_no_fallback" value="1" <?php checked($no_fallback, true); ?> />
            <?php esc_html_e('No Fallback Image', 'fau-elemental'); ?></label>
        </p>
        
        <h4><?php esc_html_e('Hover Effects', 'fau-elemental'); ?></h4>
        <p>
            <label><input type="checkbox" name="portal_menu_hover_zoom" id="portal_menu_hover_zoom" value="1" <?php checked($hover_zoom, true); ?> />
            <?php esc_html_e('Zoom', 'fau-elemental'); ?></label>
        </p>
        
        <p>
            <label><input type="checkbox" name="portal_menu_hover_blur" id="portal_menu_hover_blur" value="1" <?php checked($hover_blur, true); ?> />
            <?php esc_html_e('Blur', 'fau-elemental'); ?></label>
        </p>
        
        <h4><?php esc_html_e('Appearance', 'fau-elemental'); ?></h4>
        <p>
            <label><input type="checkbox" name="portal_menu_is_dark" id="portal_menu_is_dark" value="1" <?php checked($is_dark, true); ?> />
            <?php esc_html_e('Dark Style', 'fau-elemental'); ?></label>
        </p>
        
        <div class="portal-menu-shortcode-example" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
            <h4><?php esc_html_e('Shortcode Usage', 'fau-elemental'); ?></h4>
            <code>[portalmenu menu="<?php echo ($menu_id ? esc_attr($menu_id) : 'menu-id-or-name'); ?>" type="<?php echo esc_attr($display_type); ?>" columns="<?php echo esc_attr($columns) ?>"]</code>
        </div>
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

    // Save display type
    if (isset($_POST['portal_menu_type'])) {
        update_post_meta($post_id, 'portal_menu_type', intval($_POST['portal_menu_type']));
    }
    
    // Always save 3 columns
    update_post_meta($post_id, 'portal_menu_columns', 3);

    // Save checkboxes
    $checkbox_fields = array(
        'portal_menu_hide_subs',
        'portal_menu_list_view',
        'portal_menu_hide_thumbs',
        'portal_menu_no_fallback',
        'portal_menu_hover_zoom',
        'portal_menu_hover_blur',
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