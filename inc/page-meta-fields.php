<?php
/**
 * Page Meta Fields
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Page Meta Fields Class
 */
class FAU_Page_Meta_Fields {
    
    /**
     * Initialize the meta fields
     */
    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_fields'));
    }
    
    /**
     * Add meta boxes to page edit screen
     */
    public function add_meta_boxes() {
        add_meta_box(
            'fau_page_navigation_settings',
            __('Navigation Settings', 'fau-elemental'),
            array($this, 'render_navigation_meta_box'),
            'page',
            'side',
            'default'
        );
    }
    
    /**
     * Render the navigation settings meta box
     */
    public function render_navigation_meta_box($post) {
        // Add nonce field for security
        wp_nonce_field('fau_page_meta_nonce', 'fau_page_meta_nonce_field');
        
        // Get current value
        $hide_from_menu = get_post_meta($post->ID, '_fau_hide_from_menu', true);
        ?>
        <table class="form-table">
            <tr>
                <td>
                    <label for="fau_hide_from_menu">
                        <input type="checkbox" 
                               id="fau_hide_from_menu" 
                               name="fau_hide_from_menu" 
                               value="1" 
                               <?php checked($hide_from_menu, '1'); ?>>
                        <?php _e('Hide this page from navigation menus', 'fau-elemental'); ?>
                    </label>
                    <p class="description">
                        <?php _e('When checked, this page will not appear as a child page in navigation menus, even if it has a parent page.', 'fau-elemental'); ?>
                    </p>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Save meta field values
     */
    public function save_meta_fields($post_id) {
        // Check if nonce is valid
        if (!isset($_POST['fau_page_meta_nonce_field']) || 
            !wp_verify_nonce($_POST['fau_page_meta_nonce_field'], 'fau_page_meta_nonce')) {
            return;
        }
        
        // Check if user has permission to edit the post
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Check if this is an autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Save the hide from menu setting
        if (isset($_POST['fau_hide_from_menu'])) {
            update_post_meta($post_id, '_fau_hide_from_menu', '1');
        } else {
            delete_post_meta($post_id, '_fau_hide_from_menu');
        }
    }
    
    /**
     * Check if a page should be hidden from menus
     */
    public static function should_hide_from_menu($page_id) {
        return get_post_meta($page_id, '_fau_hide_from_menu', true) === '1';
    }
}

// Initialize the meta fields
new FAU_Page_Meta_Fields(); 