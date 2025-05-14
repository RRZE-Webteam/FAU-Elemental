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
    // Add meta box to all pages - we'll use JS to hide it when not needed
    add_meta_box(
        'fau_elemental_portal_menu_settings',
        __('Portal Menu Settings', 'fau-elemental'),
        'fau_elemental_portal_menu_meta_box_callback',
        'page',
        'side',
        'high' // Changed from 'default' to 'high' for more visibility
    );
}
add_action('add_meta_boxes', 'fau_elemental_add_portal_page_meta_boxes');

/**
 * Always show the meta box in admin, regardless of template
 * We'll use JavaScript to toggle visibility based on template
 */
function fau_elemental_admin_head() {
    global $post;
    if (!$post || get_post_type($post) !== 'page') {
        return;
    }
    
    // Make the metabox more noticeable with styling
    echo '<style>
        #fau_elemental_portal_menu_settings {
            border: 2px solid #2271b1;
            box-shadow: 0 0 5px rgba(34, 113, 177, 0.2);
            margin-top: 10px;
        }
        #fau_elemental_portal_menu_settings .postbox-header {
            background-color: #f0f6fc;
        }
        #fau_elemental_portal_menu_settings h2 {
            font-weight: bold;
        }
        /* Fix metabox in Gutenberg */
        .components-panel__body .fau-portal-menu-settings {
            padding: 16px;
            border: 1px solid #e0e0e0;
            margin-bottom: 16px;
        }
        .fau-portal-template-notice {
            margin: 10px 0;
            padding: 10px;
            background-color: #f0f7fb;
            border-left: 4px solid #2271b1;
        }
    </style>';
}
add_action('admin_head', 'fau_elemental_admin_head');

/**
 * Show the meta box when template is selected via JavaScript
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
        
        // Function to check the template and toggle the meta box
        function checkTemplate() {
            var template = $('#page_template').val();
            console.log('FAU Portal Menu: Detected template: ' + template);
            
            if (template === 'templates/portal-page.php' || template === 'portal-page.php') {
                console.log('FAU Portal Menu: Showing settings');
                $('#fau_elemental_portal_menu_settings').show().css({
                    'border': '2px solid #2271b1',
                    'box-shadow': '0 0 5px rgba(34, 113, 177, 0.2)'
                });
                
                // Add a notification about the template and settings
                if (!$('#portal-template-notice').length) {
                    $('#fau_elemental_portal_menu_settings').after(
                        '<div id="portal-template-notice" class="notice notice-info inline fau-portal-template-notice">' +
                        '<p><strong>Portal Page Template Selected</strong></p>' +
                        '<p>Configure your portal menu using the settings above.</p>' +
                        '</div>'
                    );
                }
            } else {
                console.log('FAU Portal Menu: Hiding settings');
                //$('#fau_elemental_portal_menu_settings').hide();
                $('#portal-template-notice').remove();
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
                
                if (template === 'templates/portal-page.php' || template === 'portal-page.php') {
                    console.log('FAU Portal Menu: Using portal template');
                } else {
                    // Add a note inside the metabox
                    $('#fau_elemental_portal_menu_settings .inside').prepend(
                        '<div class="notice notice-warning" style="margin: 0 0 10px 0; padding: 8px;">' +
                        '<p>To use these settings, select the <strong>Portal Page</strong> template in the Document Settings panel.</p>' +
                        '</div>'
                    );
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
    $columns = get_post_meta($post->ID, 'portal_menu_columns', true) ?: 3;
    $hide_subs = get_post_meta($post->ID, 'portal_menu_hide_subs', true);
    $list_view = get_post_meta($post->ID, 'portal_menu_list_view', true);
    $hide_thumbs = get_post_meta($post->ID, 'portal_menu_hide_thumbs', true);
    $no_fallback = get_post_meta($post->ID, 'portal_menu_no_fallback', true);
    $hover_zoom = get_post_meta($post->ID, 'portal_menu_hover_zoom', true);
    $hover_blur = get_post_meta($post->ID, 'portal_menu_hover_blur', true);

    // Get all menus
    $menus = wp_get_nav_menus();
    
    // Check if we're using the Portal Page template
    $template = get_post_meta($post->ID, '_wp_page_template', true);
    $using_portal_template = ($template === 'templates/portal-page.php' || $template === 'portal-page.php');
    
    echo '<div class="fau-portal-menu-settings">';
    if (!$using_portal_template) {
        echo '<div class="notice notice-info" style="margin: 0 0 10px 0; padding: 8px;">';
        echo '<p>' . esc_html__('To activate these settings, select the "Portal Page" template.', 'fau-elemental') . '</p>';
        echo '<p><a href="#" onclick="jQuery(\'#page_template\').val(\'portal-page.php\').trigger(\'change\'); return false;" class="button button-primary">' . esc_html__('Switch to Portal Page Template', 'fau-elemental') . '</a></p>';
        echo '</div>';
    } else {
        echo '<div class="notice notice-success" style="margin: 0 0 10px 0; padding: 8px;">';
        echo '<p><strong>' . esc_html__('Portal Page Template Active', 'fau-elemental') . '</strong></p>';
        echo '</div>';
    }
    ?>
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
        <label for="portal_menu_columns"><strong><?php esc_html_e('Number of Columns', 'fau-elemental'); ?>:</strong></label>
        <select name="portal_menu_columns" id="portal_menu_columns" class="widefat">
            <option value="1" <?php selected($columns, 1); ?>><?php esc_html_e('1 Column', 'fau-elemental'); ?></option>
            <option value="2" <?php selected($columns, 2); ?>><?php esc_html_e('2 Columns', 'fau-elemental'); ?></option>
            <option value="3" <?php selected($columns, 3); ?>><?php esc_html_e('3 Columns', 'fau-elemental'); ?></option>
            <option value="4" <?php selected($columns, 4); ?>><?php esc_html_e('4 Columns', 'fau-elemental'); ?></option>
        </select>
    </p>
    
    <div style="margin-top: 10px; border-top: 1px solid #eee; padding-top: 10px;">
        <p><strong><?php esc_html_e('Display Options', 'fau-elemental'); ?>:</strong></p>
        
        <p>
            <label for="portal_menu_list_view">
                <input type="checkbox" name="portal_menu_list_view" id="portal_menu_list_view" value="1" <?php checked($list_view, true); ?>>
                <?php esc_html_e('List View', 'fau-elemental'); ?>
            </label>
        </p>
        
        <p>
            <label for="portal_menu_hide_subs">
                <input type="checkbox" name="portal_menu_hide_subs" id="portal_menu_hide_subs" value="1" <?php checked($hide_subs, true); ?>>
                <?php esc_html_e('Hide Submenus', 'fau-elemental'); ?>
            </label>
        </p>
        
        <p>
            <label for="portal_menu_hide_thumbs">
                <input type="checkbox" name="portal_menu_hide_thumbs" id="portal_menu_hide_thumbs" value="1" <?php checked($hide_thumbs, true); ?>>
                <?php esc_html_e('Hide Thumbnails', 'fau-elemental'); ?>
            </label>
        </p>
        
        <p>
            <label for="portal_menu_no_fallback">
                <input type="checkbox" name="portal_menu_no_fallback" id="portal_menu_no_fallback" value="1" <?php checked($no_fallback, true); ?>>
                <?php esc_html_e('No Fallback Images', 'fau-elemental'); ?>
            </label>
        </p>
    </div>
    
    <div style="margin-top: 10px; border-top: 1px solid #eee; padding-top: 10px;">
        <p><strong><?php esc_html_e('Hover Effects', 'fau-elemental'); ?>:</strong></p>
        
        <p>
            <label for="portal_menu_hover_zoom">
                <input type="checkbox" name="portal_menu_hover_zoom" id="portal_menu_hover_zoom" value="1" <?php checked($hover_zoom, true); ?>>
                <?php esc_html_e('Hover Zoom Effect', 'fau-elemental'); ?>
            </label>
        </p>
        
        <p>
            <label for="portal_menu_hover_blur">
                <input type="checkbox" name="portal_menu_hover_blur" id="portal_menu_hover_blur" value="1" <?php checked($hover_blur, true); ?>>
                <?php esc_html_e('Hover Blur Effect', 'fau-elemental'); ?>
            </label>
        </p>
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
    
    // Save columns setting
    if (isset($_POST['portal_menu_columns'])) {
        update_post_meta($post_id, 'portal_menu_columns', intval($_POST['portal_menu_columns']));
    }

    // Save checkboxes
    $checkbox_fields = array(
        'portal_menu_hide_subs',
        'portal_menu_list_view',
        'portal_menu_hide_thumbs',
        'portal_menu_no_fallback',
        'portal_menu_hover_zoom',
        'portal_menu_hover_blur'
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