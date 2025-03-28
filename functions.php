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

/**
 * Calculate reading time for posts
 *
 * @param int $post_id Optional. Post ID. Default is current post.
 * @return string Reading time with proper format
 */
function get_reading_time($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    $content = get_post_field('post_content', $post_id);
    $word_count = str_word_count(strip_tags($content));
    
    // Average reading speed: 200-250 words per minute
    $reading_speed = 225;
    
    $reading_time = ceil($word_count / $reading_speed);
    
    // Minimum reading time of 1 minute
    if ($reading_time < 1) {
        $reading_time = 1;
    }
    
    // Change the format from "X min read" to "Lesedauer: X min"
    return 'Lesedauer: ' . $reading_time . ' min';
}

/**
 * Get just the numeric value of reading time
 */
function get_reading_time_value($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    $content = get_post_field('post_content', $post_id);
    $word_count = str_word_count(strip_tags($content));
    
    // Average reading speed: 225 words per minute
    $reading_speed = 225;
    
    $reading_time = ceil($word_count / $reading_speed);
    
    // Minimum reading time of 1 minute
    if ($reading_time < 1) {
        $reading_time = 1;
    }
    
    return $reading_time;
}

/**
 * Add Post Header Options meta box
 */
function add_post_header_options_meta_box() {
    add_meta_box(
        'post_header_options',
        'Post Header Options',
        'render_post_header_options_meta_box',
        'post',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'add_post_header_options_meta_box');

/**
 * Render Post Header Options meta box
 */
function render_post_header_options_meta_box($post) {
    wp_nonce_field('post_header_options_nonce', 'post_header_options_nonce');
    
    // Get saved values
    $show_reading_time = get_post_meta($post->ID, 'show_reading_time', true);
    $show_listen_link = get_post_meta($post->ID, 'show_listen_link', true);
    $listen_url = get_post_meta($post->ID, 'listen_url', true);
    
    // Set defaults
    if ($show_reading_time === '') {
        $show_reading_time = '1';
    }
    ?>
    <div class="post-header-options">
        <label>
            <input type="checkbox" name="show_reading_time" value="1" <?php checked($show_reading_time, '1'); ?> />
            <strong>Show reading time</strong>
        </label>
        
        <label>
            <input type="checkbox" name="show_listen_link" value="1" <?php checked($show_listen_link, '1'); ?> id="show-listen-link-toggle" />
            <strong>Show listen link</strong>
        </label>
        
        <div class="post-header-listen-fields" id="listen-fields" style="<?php echo ($show_listen_link !== '1') ? 'display:none;' : ''; ?>">
            <label for="listen_url">Audio URL:</label>
            <input type="url" id="listen_url" name="listen_url" value="<?php echo esc_url($listen_url); ?>" style="width: 100%;" placeholder="https://..." />
        </div>
    </div>
    <?php
}

/**
 * Save Post Header Options meta box
 */
function save_post_header_options_meta_box($post_id) {
    // Security checks
    if (!isset($_POST['post_header_options_nonce']) || 
        !wp_verify_nonce($_POST['post_header_options_nonce'], 'post_header_options_nonce')) {
        return;
    }
    
    // Don't save during autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Check permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Save the options
    $show_reading_time = isset($_POST['show_reading_time']) ? '1' : '0';
    update_post_meta($post_id, 'show_reading_time', $show_reading_time);
    
    $show_listen_link = isset($_POST['show_listen_link']) ? '1' : '0';
    update_post_meta($post_id, 'show_listen_link', $show_listen_link);
    
    if (isset($_POST['listen_url'])) {
        update_post_meta($post_id, 'listen_url', esc_url_raw($_POST['listen_url']));
    }
}
add_action('save_post', 'save_post_header_options_meta_box');

/**
 * Apply post header settings to HTML output using output buffering
 */
function buffer_start() {
    if (is_singular('post')) {
        ob_start();
    }
}

function buffer_end() {
    if (is_singular('post') && ob_get_length()) {
        $html = ob_get_clean();
        $post_id = get_the_ID();
        
        // Get meta values
        $show_reading_time = get_post_meta($post_id, 'show_reading_time', true);
        $show_listen_link = get_post_meta($post_id, 'show_listen_link', true);
        $listen_url = get_post_meta($post_id, 'listen_url', true);
        
        // Calculate reading time
        $reading_time = get_reading_time($post_id);
        
        // Inject CSS to control visibility
        $inject_css = '<style>';
        
        if ($show_reading_time !== '1') {
            $inject_css .= '.reading-time, .post-reading-time { display: none !important; }';
        }
        
        if ($show_listen_link !== '1' || empty($listen_url)) {
            $inject_css .= '.listen-link, .post-listen-link { display: none !important; }';
        }
        
        $inject_css .= '</style>';
        
        // Insert reading time
        $html = preg_replace(
            '/<p class=["\'](?:[^"\']*\s)?reading-time(?:\s[^"\']*)?["\'][^>]*>.*?<\/p>/',
            '<p class="reading-time">' . esc_html($reading_time) . '</p>',
            $html
        );
        
        // Insert listen URL - FIXED VERSION
        if ($show_listen_link === '1' && !empty($listen_url)) {
            $html = preg_replace(
                '/<p class="[^"]*listen-link[^"]*"><a href=["\'][^"\']*["\'](\s[^>]*)?>[^<]*<\/a><\/p>/',
                '<p class="listen-link"><a href="' . esc_url($listen_url) . '"$1>Beitrag anhören: ' . esc_html($listen_duration) . ' min. abspielen</a></p>',
                $html
            );
        }
        
        // Add the CSS to the head
        $html = str_replace('</head>', $inject_css . '</head>', $html);
        
        echo $html;
    }
}

add_action('template_redirect', 'buffer_start', 1);
add_action('wp_footer', 'buffer_end', 999);

/**
 * Register and render a dynamic block for post header
 */
function register_post_header_block() {
    register_block_type('your-theme/post-header', array(
        'render_callback' => 'render_post_header_block',
    ));
}
add_action('init', 'register_post_header_block');

/**
 * Render the post header block
 */
function render_post_header_block($attributes) {
    ob_start();
    
    $post_id = get_the_ID();
    
    // Get meta values
    $show_reading_time = get_post_meta($post_id, 'show_reading_time', true);
    $show_listen_link = get_post_meta($post_id, 'show_listen_link', true);
    $listen_url = get_post_meta($post_id, 'listen_url', true);
    
    // Get reading time
    $reading_time = get_reading_time($post_id);
    ?>
    
    <div class="post-header wp-block-group alignwide">
        <div class="post-meta-top wp-block-group">
            <?php echo get_the_date(); ?>
            
            <?php if (has_category()): ?>
            <div class="post-categories">
                <?php the_category(', '); ?>
            </div>
            <?php endif; ?>
        </div>
        
        <h1 class="post-title"><?php the_title(); ?></h1>
        
        <div class="post-meta wp-block-group">
            <?php if ($show_reading_time === '1'): ?>
            <p class="reading-time"><?php echo esc_html($reading_time); ?></p>
            <?php endif; ?>
            
            <?php if ($show_listen_link === '1' && !empty($listen_url)): ?>
            <p class="listen-link">
                <a href="<?php echo esc_url($listen_url); ?>">Listen to article</a>
            </p>
            <?php endif; ?>
        </div>
    </div>
    
    <?php
    return ob_get_clean();
}

/**
 * Filter post content to add dynamic elements to the header
 */
function filter_post_header_content($content) {
    if (is_singular('post') && is_main_query()) {
        $post_id = get_the_ID();
        
        // Get the reading time and listening duration
        $reading_time = get_reading_time_value($post_id);
        $listen_duration = get_post_meta($post_id, 'listen_duration', true) ?: '4';
        
        // Replace the placeholders
        $content = str_replace('<!-- READING_TIME -->', esc_html($reading_time), $content);
        $content = str_replace('<!-- LISTEN_DURATION -->', esc_html($listen_duration), $content);
        
        // Get visibility settings
        $show_reading_time = get_post_meta($post_id, 'show_reading_time', true) !== '0';
        $show_listen_link = get_post_meta($post_id, 'show_listen_link', true) === '1';
        $listen_url = get_post_meta($post_id, 'listen_url', true);
        
        // Apply CSS to hide elements if needed
        $inline_style = '<style>';
        
        if (!$show_reading_time) {
            $inline_style .= '.post-reading-time, .reading-time { display: none !important; }';
        }
        
        if (!$show_listen_link || empty($listen_url)) {
            $inline_style .= '.post-listen-link, .listen-link { display: none !important; }';
        } else {
            // Update the listen link URL if it's shown
            $content = preg_replace(
                '/<a href=["\'][^"\']*["\'][^>]*>Beitrag anhören:([^<]*)<\/a>/',
                '<a href="' . esc_url($listen_url) . '">Beitrag anhören:$1</a>',
                $content
            );
        }
        
        // Hide categories separator if no categories
        if (!has_category()) {
            $inline_style .= '.post-categories-separator { display: none !important; }';
        }
        
        $inline_style .= '</style>';
        
        // Add the style to the beginning of the content
        $content = $inline_style . $content;
    }
    
    return $content;
}
add_filter('the_content', 'filter_post_header_content', 1);

/**
 * Get audio file duration
 * 
 * @param string $audio_url URL of the audio file
 * @return int Duration in minutes (rounded up)
 */
function get_audio_duration($audio_url) {
    // Ensure getID3 is available
    if (!function_exists('wp_get_audio_metadata')) {
        require_once(ABSPATH . 'wp-admin/includes/media.php');
    }
    
    // Get file path from URL
    $audio_path = str_replace(get_site_url(), ABSPATH, $audio_url);
    
    // Get audio metadata
    $metadata = wp_get_audio_metadata($audio_path);
    
    if (!empty($metadata['length'])) {
        // Convert seconds to minutes and round up
        return ceil($metadata['length'] / 60);
    }
    
    // Fallback to default duration if unable to detect
    return 4;
}

/**
 * Auto-update audio duration when URL is saved
 */
function update_audio_duration($post_id) {
    // Skip if not saving audio URL
    if (!isset($_POST['listen_url'])) {
        return;
    }
    
    $listen_url = esc_url_raw($_POST['listen_url']);
    if (!empty($listen_url)) {
        $duration = get_audio_duration($listen_url);
        update_post_meta($post_id, 'listen_duration', $duration);
    }
}
add_action('save_post', 'update_audio_duration');
