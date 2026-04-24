<?php
/**
 * Social Media Admin Interface for FAU-Elemental
 * 
 * Allows users to add custom social media platforms through WordPress admin.
 * 
 * @package FAU_Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add custom social media platforms admin page
 */
function faue_add_social_media_admin_page() {
    add_theme_page(
        __('Custom Social Media Platforms', 'fau-elemental'),
        __('Custom Social Media', 'fau-elemental'),
        'manage_options',
        'faue-custom-social',
        'faue_custom_social_admin_page'
    );
}
add_action('admin_menu', 'faue_add_social_media_admin_page');

/**
 * Enqueue admin styles for social media page
 */
function faue_enqueue_social_media_admin_styles($hook) {
    // Only load on our custom social media admin page
    if ($hook !== 'appearance_page_faue-custom-social') {
        return;
    }
    
    wp_enqueue_style(
        'faue-social-media-admin',
        get_template_directory_uri() . '/build/css/admin-social-media.css',
        array(),
        wp_get_theme()->get('Version')
    );
}
add_action('admin_enqueue_scripts', 'faue_enqueue_social_media_admin_styles');

/**
 * Custom social media admin page
 */
function faue_custom_social_admin_page() {
    // Handle form submission
    if (isset($_POST['submit']) && wp_verify_nonce($_POST['faue_social_nonce'], 'faue_social_action')) {
        faue_save_custom_social_platforms();
    }
    
    $custom_platforms = get_option('faue_custom_social_platforms', array());
    ?>
    <div class="wrap">
        <h1><?php _e('Custom Social Media Platforms', 'fau-elemental'); ?></h1>
        
        <div class="notice notice-info">
            <p><?php _e('Add custom social media platforms that will appear in the customizer. Platforms will only show in the footer when you add a URL for them.', 'fau-elemental'); ?></p>
        </div>
        
        <form method="post" action="" enctype="multipart/form-data">
            <?php wp_nonce_field('faue_social_action', 'faue_social_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="platform_name"><?php _e('Platform Name', 'fau-elemental'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="platform_name" name="platform_name" class="regular-text" 
                               placeholder="<?php esc_attr_e('e.g., Discord', 'fau-elemental'); ?>" required />
                        <p class="description"><?php _e('The display name for this platform (e.g., Discord, Telegram)', 'fau-elemental'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="platform_key"><?php _e('Platform Key', 'fau-elemental'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="platform_key" name="platform_key" class="regular-text" 
                               placeholder="<?php esc_attr_e('e.g., discord', 'fau-elemental'); ?>" required />
                        <p class="description"><?php _e('A unique identifier (lowercase, no spaces, e.g., discord, telegram)', 'fau-elemental'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="platform_icon"><?php _e('Platform Icon', 'fau-elemental'); ?></label>
                    </th>
                    <td>
                        <input type="file" id="platform_icon" name="platform_icon" accept=".svg" />
                        <p class="description"><?php _e('Upload an SVG icon file (recommended size: 24x24px)', 'fau-elemental'); ?></p>
                        
                        <div>
                            <strong><?php _e('OR', 'fau-elemental'); ?></strong>
                        </div>
                        
                        <input type="url" id="platform_icon_url" name="platform_icon_url" class="regular-text"
                               placeholder="<?php echo esc_attr( trailingslashit( home_url() ) . 'wp-content/uploads/icon.svg' ); ?>" />
                        <p class="description"><?php _e('Enter a direct URL to an SVG icon already hosted on this site (e.g. from the Media Library). Remote URLs are rejected to protect visitor privacy.', 'fau-elemental'); ?></p>
                    </td>
                </tr>
            </table>
            
            <?php submit_button(__('Add Platform', 'fau-elemental')); ?>
        </form>
        
        <?php if (!empty($custom_platforms)): ?>
        <hr>
        <h2><?php _e('Current Custom Platforms', 'fau-elemental'); ?></h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Platform', 'fau-elemental'); ?></th>
                    <th><?php _e('Key', 'fau-elemental'); ?></th>
                    <th><?php _e('Icon Preview', 'fau-elemental'); ?></th>
                    <th><?php _e('Actions', 'fau-elemental'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($custom_platforms as $key => $platform): ?>
                <tr>
                    <td><strong><?php echo esc_html($platform['name']); ?></strong></td>
                    <td><code><?php echo esc_html($key); ?></code></td>
                    <td>
                        <?php if (!empty($platform['icon_url']) && faue_is_local_icon_url($platform['icon_url'])): ?>
                            <img src="<?php echo esc_url($platform['icon_url']); ?>"
                                 class="faue-social-icon-preview" alt="<?php echo esc_attr($platform['name']); ?>" />
                            <br><small><?php echo esc_html($platform['icon_url']); ?></small>
                        <?php elseif (!empty($platform['icon_url'])): ?>
                            <span class="dashicons dashicons-warning"></span> <?php esc_html_e('External icon blocked (remote URLs are not allowed).', 'fau-elemental'); ?>
                            <br><small><code><?php echo esc_html($platform['icon_url']); ?></code></small>
                        <?php else: ?>
                            <span class="dashicons dashicons-warning"></span> <?php _e('No icon', 'fau-elemental'); ?>
                            <?php if (isset($platform['upload_error'])): ?>
                                <br><small class="faue-error-message"><?php echo esc_html($platform['upload_error']); ?></small>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="post" action="" class="faue-inline-form">
                            <?php wp_nonce_field('faue_social_action', 'faue_social_nonce'); ?>
                            <input type="hidden" name="remove_platform" value="<?php echo esc_attr($key); ?>" />
                            <?php submit_button(__('Remove', 'fau-elemental'), 'small', 'submit', false, array('onclick' => 'return confirm("' . esc_js(__('Are you sure you want to remove this platform?', 'fau-elemental')) . '");')); ?>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
        
        <div class="notice notice-warning">
            <p><strong><?php _e('Note:', 'fau-elemental'); ?></strong> <?php _e('After adding or removing platforms, you may need to refresh the customizer to see the changes.', 'fau-elemental'); ?></p>
        </div>
        
    </div>
    <?php
}

/**
 * Save custom social media platforms
 */
function faue_save_custom_social_platforms() {
    // Check if this is a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }
    
    // Check nonce
    if (!wp_verify_nonce($_POST['faue_social_nonce'], 'faue_social_action')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error is-dismissible"><p>' . __('Security check failed. Please try again.', 'fau-elemental') . '</p></div>';
        });
        return;
    }
    
    if (isset($_POST['remove_platform'])) {
        $platform_key = sanitize_text_field($_POST['remove_platform']);
        $custom_platforms = get_option('faue_custom_social_platforms', array());
        unset($custom_platforms[$platform_key]);
        update_option('faue_custom_social_platforms', $custom_platforms);
        
        // Clear any cached data
        delete_transient('faue_social_platforms_combined');
        
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible"><p>' . __('Platform removed successfully!', 'fau-elemental') . '</p></div>';
        });
        return;
    }
    
    // Check if this is actually a form submission
    if (!isset($_POST['platform_name']) || !isset($_POST['platform_key'])) {
        return;
    }
    
    $platform_name = sanitize_text_field($_POST['platform_name']);
    $platform_key = sanitize_text_field($_POST['platform_key']);
    $platform_key = strtolower(preg_replace('/[^a-z0-9_-]/', '', $platform_key));
    
    if (empty($platform_name) || empty($platform_key)) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error is-dismissible"><p>' . __('Platform name and key are required.', 'fau-elemental') . '</p></div>';
        });
        return;
    }
    
    
    $custom_platforms = get_option('faue_custom_social_platforms', array());
    
    // Handle file upload
    $icon_url = '';
    if (!empty($_FILES['platform_icon']['name'])) {
        $upload = faue_handle_icon_upload($_FILES['platform_icon']);
        if ($upload && isset($upload['url'])) {
            $icon_url = $upload['url'];
        } else {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p>' . __('Icon upload failed. Please ensure the file is a valid SVG and under 1MB.', 'fau-elemental') . '</p></div>';
            });
        }
    } elseif (!empty($_POST['platform_icon_url'])) {
        $candidate_url = esc_url_raw(wp_unslash($_POST['platform_icon_url']));
        if (faue_is_local_icon_url($candidate_url)) {
            $icon_url = $candidate_url;
        } else {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Icon URL must reference a file hosted on this site. Upload an SVG above, or paste a URL from your Media Library.', 'fau-elemental') . '</p></div>';
            });
        }
    }
    
    $custom_platforms[$platform_key] = array(
        'name' => $platform_name,
        'icon_url' => $icon_url,
        'added_date' => current_time('mysql')
    );
    
    $result = update_option('faue_custom_social_platforms', $custom_platforms);
    
    // Clear any cached data
    delete_transient('faue_social_platforms_combined');
    
    add_action('admin_notices', function() use ($icon_url) {
        if (!empty($icon_url)) {
            echo '<div class="notice notice-success is-dismissible"><p>' . __('Platform added successfully with icon!', 'fau-elemental') . '</p></div>';
        } else {
            echo '<div class="notice notice-warning is-dismissible"><p>' . __('Platform added successfully, but no icon was uploaded.', 'fau-elemental') . '</p></div>';
        }
    });
}

/**
 * Handle icon file upload
 */
function faue_handle_icon_upload($file) {
    if (!function_exists('wp_handle_upload')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
    }
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error_messages = array(
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        );
        $error_message = isset($error_messages[$file['error']]) ? $error_messages[$file['error']] : 'Unknown upload error';
        return false;
    }
    
    // Check file size (max 1MB)
    if ($file['size'] > 1024 * 1024) {
        return false;
    }
    
    // Check file extension manually since wp_check_filetype doesn't recognize SVG by default
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($file_extension !== 'svg') {
        return false;
    }
    
    // Additional check: verify it's actually an SVG by checking the content
    $file_content = file_get_contents($file['tmp_name']);
    if (strpos($file_content, '<svg') === false) {
        return false;
    }
    
    
    $upload_overrides = array(
        'test_form' => false,
        'mimes' => array('svg' => 'image/svg+xml'),
        'upload_error_handler' => 'faue_upload_error_handler'
    );
    
    return wp_handle_upload($file, $upload_overrides);
}

/**
 * Custom upload error handler
 */
function faue_upload_error_handler($file, $message) {
    return new WP_Error('upload_error', $message);
}

/**
 * Get combined social platforms (built-in + custom)
 */
function faue_get_combined_social_platforms() {
    $transient_key = 'faue_social_platforms_combined';
    $combined = get_transient($transient_key);
    
    if ($combined === false) {
        $built_in = faue_get_social_platforms();
        $custom = get_option('faue_custom_social_platforms', array());
        
        $combined = $built_in;
        foreach ($custom as $key => $platform) {
            $combined[$key] = $platform['name'];
        }
        
        set_transient($transient_key, $combined, HOUR_IN_SECONDS);
    }
    
    return $combined;
}

/**
 * Get custom social platform icon
 */
function faue_get_custom_social_icon($platform_key) {
    $custom_platforms = get_option('faue_custom_social_platforms', array());
    
    if (isset($custom_platforms[$platform_key])) {
        return $custom_platforms[$platform_key]['icon_url'];
    }
    
    return false;
}

/**
 * Add admin notice for custom social media
 */
function faue_custom_social_admin_notice() {
    $screen = get_current_screen();
    if ($screen && $screen->id === 'appearance_page_faue-custom-social') {
        return;
    }
    
    $custom_platforms = get_option('faue_custom_social_platforms', array());
    if (!empty($custom_platforms)) {
        echo '<div class="notice notice-info is-dismissible">';
        echo '<p>' . sprintf(
            /* translators: 1: Number of custom platforms, 2: Admin URL for managing platforms */
            __('You have %1$d custom social media platform(s). <a href="%2$s">Manage them here</a>.', 'fau-elemental'),
            count($custom_platforms),
            admin_url('themes.php?page=faue-custom-social')
        ) . '</p>';
        echo '</div>';
    }
}
add_action('admin_notices', 'faue_custom_social_admin_notice');

/**
 * Allow SVG uploads
 */
function faue_allow_svg_uploads($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter('upload_mimes', 'faue_allow_svg_uploads');

/**
 * Fix SVG file type detection
 */
function faue_fix_svg_filetype($data, $file, $filename, $mimes) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if ($ext === 'svg') {
        $data['ext'] = 'svg';
        $data['type'] = 'image/svg+xml';
    }
    return $data;
}
add_filter('wp_check_filetype_and_ext', 'faue_fix_svg_filetype', 10, 4);
