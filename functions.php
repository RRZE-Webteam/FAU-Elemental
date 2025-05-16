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

// Logo display functionality
require_once get_template_directory() . '/inc/logo-display.php';

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
    register_block_type('fau-elemental/post-header', array(
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

/**
 * Completely lock all theme templates from FSE editing
 */
function lock_theme_templates_from_fse() {
    // 1. Get list of theme templates to protect
    $theme_templates = array();
    
    // Get all template files from the theme
    $template_files = glob(get_template_directory() . '/templates/*.html');
    foreach ($template_files as $template_file) {
        $template_slug = basename($template_file, '.html');
        $theme_templates[] = $template_slug;
    }
    
    // Get all template part files
    $template_part_files = glob(get_template_directory() . '/parts/*.html');
    foreach ($template_part_files as $template_file) {
        $template_slug = basename($template_file, '.html');
        $theme_templates[] = $template_slug;
    }
    
    // Always protect these core templates
    $core_templates = array(
        'single', 'single-post', 'page', 'home', 'front-page', 
        'archive', 'category', 'tag', 'index', '404', 'search'
    );
    
    $protected_templates = array_merge($theme_templates, $core_templates);
    $protected_templates = array_unique($protected_templates);
    
    // 2. Filter templates out of the FSE editor
    add_filter('get_block_templates', function($templates, $query, $template_type) use ($protected_templates) {
        // Only filter in admin context
        if (!is_admin()) {
            return $templates;
        }
        
        return array_filter($templates, function($template) use ($protected_templates) {
            // Keep templates not in our protected list
            return !in_array($template->slug, $protected_templates);
        });
    }, 20, 3);
    
    // 3. Block REST API access for protected templates
    add_filter('rest_request_before_callbacks', function($response, $handler, $request) use ($protected_templates) {
        $route = $request->get_route();
        $method = $request->get_method();
        
        // Only block write operations
        if (!in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return $response;
        }
        
        // Check if this is a template or template part endpoint
        if (strpos($route, '/wp/v2/templates') === 0 || 
            strpos($route, '/wp/v2/template-parts') === 0) {
            
            // For template creation/update
            if ($method === 'POST' || $method === 'PUT' || $method === 'PATCH') {
                $slug = $request->get_param('slug');
                
                if ($slug && in_array($slug, $protected_templates)) {
                    return new WP_Error(
                        'template_locked',
                        'This template is locked and cannot be modified.',
                        ['status' => 403]
                    );
                }
            }
            
            // For template deletion
            if ($method === 'DELETE') {
                $path_parts = explode('/', trim($route, '/'));
                $template_id = end($path_parts);
                
                foreach ($protected_templates as $protected) {
                    if ($template_id === $protected || strpos($template_id, "wp_template//$protected") === 0 || 
                        strpos($template_id, "wp_template_part//$protected") === 0) {
                        return new WP_Error(
                            'template_locked',
                            'This template is locked and cannot be deleted.',
                            ['status' => 403]
                        );
                    }
                }
            }
        }
        
        return $response;
    }, 10, 3);
    
    // 4. Prevent direct access to template editing screens
    add_action('admin_init', function() use ($protected_templates) {
        global $pagenow;
        
        if ($pagenow === 'site-editor.php' || 
            (isset($_GET['page']) && $_GET['page'] === 'gutenberg-edit-site')) {
            
            if (isset($_GET['postId'])) {
                $template_id = $_GET['postId'];
                
                foreach ($protected_templates as $protected) {
                    if (strpos($template_id, $protected) !== false) {
                        // Redirect to site editor home
                        wp_safe_redirect(admin_url('site-editor.php'));
                        exit;
                    }
                }
            }
        }
    });
    
    // 5. Hide protected templates from the Site Editor UI
    add_action('admin_head', function() use ($protected_templates) {
        $selectors = array();
        
        foreach ($protected_templates as $template) {
            $selectors[] = ".edit-site-template-card[data-slug=\"{$template}\"]";
            $selectors[] = ".edit-site-template-card[data-title*=\"{$template}\"]";
        }
        
        if (!empty($selectors)) {
            echo '<style>' . implode(', ', $selectors) . ' { display: none !important; }</style>';
        }
        
        // Also hide template editing options
        echo '<style>
            /* Hide template switching in post editor */
            .edit-post-header__settings .components-dropdown-menu__toggle[aria-label*="Template"],
            .edit-post-header-toolbar__document-overview-toggle[aria-label*="Template"],
            
            /* Hide template editing panels */
            .edit-post-template-panel,
            .edit-post-template__actions
            {
                display: none !important;
            }
        </style>';
    });
    
    // 6. Override user capabilities to edit templates
    add_filter('map_meta_cap', function($caps, $cap, $user_id, $args) use ($protected_templates) {
        // Check if this is a template capability check
        if (in_array($cap, array('edit_theme', 'edit_themes', 'edit_theme_options'))) {
            
            // If checking for a specific template
            if (isset($args[0]) && is_string($args[0])) {
                foreach ($protected_templates as $protected) {
                    if (strpos($args[0], $protected) !== false) {
                        return array('do_not_allow');
                    }
                }
            }
        }
        
        return $caps;
    }, 10, 4);
    
    // 7. Disable template editing mode in the post editor
    add_filter('block_editor_settings_all', function($settings) {
        if (get_post_type() === 'post' || get_post_type() === 'page') {
            $settings['supportsTemplateMode'] = false;
            
            // Disable editing existing templates
            if (isset($settings['defaultTemplateTypes'])) {
                foreach ($settings['defaultTemplateTypes'] as $key => &$template) {
                    $template['isLocked'] = true;
                }
            }
        }
        
        return $settings;
    }, 999);
    
    // 8. Add admin notice about templates being locked
    add_action('admin_notices', function() {
        $screen = get_current_screen();
        
        if ($screen && $screen->id === 'appearance_page_gutenberg-edit-site') {
            echo '<div class="notice notice-info is-dismissible">';
            echo '<p><strong>Note:</strong> Some templates are locked and cannot be edited in the Site Editor. These templates are defined in the theme files.</p>';
            echo '</div>';
        }
    });
}

// Initialize template protection
add_action('init', 'lock_theme_templates_from_fse', 999);
add_action('admin_init', 'lock_theme_templates_from_fse', 999);
add_action('rest_api_init', 'lock_theme_templates_from_fse', 999);

/**
 * Main theme setup function for FAU-Elemental
 */
function fau_elemental_theme_setup() {
    // Add theme support for block templates and FSE
    add_theme_support('block-templates');
    
    // Ensure PHP templates are available as fallbacks
    add_theme_support('template-hierarchy');
    
    // Basic theme features support
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');
    add_theme_support('automatic-feed-links');
    add_theme_support('html5', array(
        'comment-list', 
        'comment-form', 
        'search-form', 
        'gallery', 
        'caption',
        'style',
        'script'
    ));
    add_theme_support('title-tag');
    
    // Register core menu locations used by classic templates
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'fau-elemental'),
        'footer' => __('Footer Menu', 'fau-elemental'),
        'footer-instance-menu' => __('Footer Instance Menu', 'fau-elemental')
    ));
    register_nav_menus(array(
        'footer-wichtige-links' => __('Footer Wichtige Links', 'fau-elemental'),
        'footer-instance-menu' => __('Footer Instance Menu', 'fau-elemental')
    ));
    register_nav_menus( array(
        'footer-instance' => esc_html__( 'Footer Instance Menu', 'fau-elemental' ),
    ) );

    // Add custom image sizes if needed
    // add_image_size('featured-large', 1600, 900, true);
}
add_action('after_setup_theme', 'fau_elemental_theme_setup');

/**
 * Ensure plugin hooks are available in the block theme
 */
function fau_elemental_add_plugin_compatibility_hooks() {
    // Common hooks that plugins often use
    add_action('wp_head', function() {
        do_action('fau_elemental_header');
    });
    
    add_action('wp_footer', function() {
        do_action('fau_elemental_footer');
    });
    
    // Hook before and after content
    add_filter('the_content', function($content) {
        $before = apply_filters('fau_elemental_before_content', '');
        $after = apply_filters('fau_elemental_after_content', '');
        return $before . $content . $after;
    });
}
add_action('init', 'fau_elemental_add_plugin_compatibility_hooks');

/**
 * Enqueue styles for PHP templates
 */
function fau_elemental_enqueue_php_template_styles() {
    // Ensure block styles are loaded even in PHP templates
    wp_enqueue_style('wp-block-library');
    wp_enqueue_style('global-styles');
}
add_action('wp_enqueue_scripts', 'fau_elemental_enqueue_php_template_styles');

/**
 * Add custom classes to body for PHP templates
 */
function fau_elemental_body_classes($classes) {
    // Add these classes to ensure PHP templates look like block templates
    $classes[] = 'wp-theme';
    $classes[] = 'is-layout-flow';
    
    return $classes;
}
add_filter('body_class', 'fau_elemental_body_classes');

/**
 * Register template parts for block templates
 */
function fau_elemental_register_template_parts() {
    // Get all template parts from the parts directory
    $block_parts = glob(get_template_directory() . '/parts/*.html');
    
    foreach ($block_parts as $part_file) {
        $slug = basename($part_file, '.html');
        
        // Only register if file exists and has content
        if (file_exists($part_file) && filesize($part_file) > 0) {
            // Determine category based on slug prefix
            $category = 'uncategorized';
            if (strpos($slug, 'header-') === 0) {
                $category = 'header';
            } elseif (strpos($slug, 'footer-') === 0) {
                $category = 'footer';
            } elseif (strpos($slug, 'sidebar-') === 0) {
                $category = 'sidebar';
            }
            
            // Create title from slug
            $title = str_replace('-', ' ', $slug);
            $title = ucwords($title);
            
            register_block_pattern(
                'fau-elemental/' . $slug,
                array(
                    'title'       => $title,
                    'description' => sprintf(__('%s template part', 'fau-elemental'), $title),
                    'content'     => file_get_contents($part_file),
                    'categories'  => array($category),
                )
            );
        }
    }
}
add_action('init', 'fau_elemental_register_template_parts');







 // This will load the main footer.php dont remove this
function render_footer_template() {
    ob_start();
    get_footer(); 
    return ob_get_clean();
}

register_block_type('fau-elemental/footer', array(
    'render_callback' => 'render_footer_template'
));

/**
 * Footer Customizer Settings
 * Reorganized for better user experience
 */
function fau_footer_customizer_settings($wp_customize) {
    // Get the website type from theme settings
    $website_type = get_option('faue_website_type', 'fau');
    $faculty = get_option('faue_faculty', 'phil');
    
    // 1. MAIN FOOTER SECTIONS - organized by website type
    
    // Main section header
    $wp_customize->add_panel('fau_footer_panel', [
        'title' => __('Footer Settings', 'fau-elemental'),
        'priority' => 130,
        'description' => __('Customize all footer elements for your site', 'fau-elemental'),
    ]);
    
    // ======= COMMON SETTINGS FOR ALL WEBSITE TYPES =======
    
    // Social Media Section
    $wp_customize->add_section('footer_social_media', [
        'title' => __('Social Media Links', 'fau-elemental'),
        'panel' => 'fau_footer_panel',
        'priority' => 10,
    ]);
    
    $social_platforms = [
        'instagram' => 'Instagram',
        'facebook' => 'Facebook',
        'xing' => 'Xing',
        'linkedin' => 'LinkedIn',
        'twitter' => 'X/Twitter',
        'mastodon' => 'Mastodon',
        'bluesky' => 'BlueSky',
        'youtube' => 'YouTube',
        'tiktok' => 'TikTok'
    ];

    foreach ($social_platforms as $key => $label) {
        $wp_customize->add_setting('social_' . $key);
        $wp_customize->add_control('social_' . $key, [
            'label' => $label . ' URL',
            'section' => 'footer_social_media',
            'type' => 'url'
        ]);
    }
    
    // Image Credits Section
    $wp_customize->add_section('footer_image_credits', [
        'title' => __('Image Credits', 'fau-elemental'),
        'panel' => 'fau_footer_panel',
        'priority' => 20,
    ]);
    
    $wp_customize->add_setting('image_credits');
    $wp_customize->add_control('image_credits', [
        'label' => __('Image Credits Text', 'fau-elemental'),
        'description' => __('Copyright information for images used on the site', 'fau-elemental'),
        'section' => 'footer_image_credits',
        'type' => 'textarea'
    ]);
    
    // ======= WEBSITE TYPE SPECIFIC SECTIONS =======
    
    if ($website_type === 'fau') {
        // ======= MAIN FAU WEBSITE SETTINGS =======
        
        // FAU Main Claim
        $wp_customize->add_section('footer_fau_claim', [
            'title' => __('FAU Claim', 'fau-elemental'),
            'panel' => 'fau_footer_panel',
            'priority' => 30,
        ]);
        
        $wp_customize->add_setting('fau_footer_title', [
            'default' => 'FAU - Wissen in Bewegung'
        ]);
        $wp_customize->add_control('fau_footer_title', [
            'label' => __('FAU Claim Title', 'fau-elemental'),
            'section' => 'footer_fau_claim',
            'type' => 'text'
        ]);
        
        $wp_customize->add_setting('fau_footer_description', [
            'default' => 'Die FAU ist die innovativste Universität Deutschlands, europaweit auf dem zweiten Platz. Mit 40.000 Studierenden gehören wir zu den größten Hochschulen in Deutschland mit herausragender Lehre und exzellenter Forschung.'
        ]);
        $wp_customize->add_control('fau_footer_description', [
            'label' => __('FAU Claim Text', 'fau-elemental'),
            'section' => 'footer_fau_claim',
            'type' => 'textarea'
        ]);
        
        // Target Groups
        $wp_customize->add_section('footer_target_groups', [
            'title' => __('Target Groups', 'fau-elemental'),
            'panel' => 'fau_footer_panel',
            'priority' => 40,
            'description' => __('Configure the four target group sections in the footer', 'fau-elemental')
        ]);
        
        $target_groups = [
            'zur_fau' => __('Zur FAU', 'fau-elemental'),
            'forschung' => __('Forschung', 'fau-elemental'),
            'studierende' => __('Studierende', 'fau-elemental'),
            'studieninteressierte' => __('Studieninteressierte', 'fau-elemental')
        ];
        
        foreach ($target_groups as $key => $label) {
            // Group heading
            $wp_customize->add_setting('target_group_heading_' . $key, [
                'default' => $label
            ]);
            $wp_customize->add_control(new WP_Customize_Control($wp_customize, 'target_group_heading_' . $key, [
                'label' => sprintf(__('--- %s ---', 'fau-elemental'), $label),
                'section' => 'footer_target_groups',
                'settings' => 'target_group_heading_' . $key,
                'type' => 'hidden'
            ]));
            
            // Title
            $wp_customize->add_setting('target_' . $key . '_title', [
                'default' => $label
            ]);
            $wp_customize->add_control('target_' . $key . '_title', [
                'label' => __('Title', 'fau-elemental'),
                'section' => 'footer_target_groups',
                'type' => 'text'
            ]);
            
            // Description
            $default_desc = __('Schwerpunkte, Leitbild, Reputation, Erfolge u.v.m.', 'fau-elemental');
            if ($key === 'zur_fau') {
                $default_desc = __('Geschichte, Besonderheiten Daten, Struktur u.v.m', 'fau-elemental');
            }
            
            $wp_customize->add_setting('target_' . $key . '_description', [
                'default' => $default_desc
            ]);
            $wp_customize->add_control('target_' . $key . '_description', [
                'label' => __('Description', 'fau-elemental'),
                'section' => 'footer_target_groups',
                'type' => 'textarea'
            ]);
            
            // Link
            $wp_customize->add_setting('target_' . $key . '_link', [
                'default' => '#'
            ]);
            $wp_customize->add_control('target_' . $key . '_link', [
                'label' => __('Link URL', 'fau-elemental'),
                'section' => 'footer_target_groups',
                'type' => 'url'
            ]);
            
            // Separator
            if ($key != 'studieninteressierte') {
                $wp_customize->add_setting('target_separator_' . $key);
                $wp_customize->add_control(new WP_Customize_Control($wp_customize, 'target_separator_' . $key, [
                    'label' => '',
                    'section' => 'footer_target_groups',
                    'settings' => 'target_separator_' . $key,
                    'type' => 'hidden'
                ]));
            }
        }
        
    } else {
        // ======= FACULTY/INSTANCE SETTINGS =======
        
        // Faculty Header
        $wp_customize->add_section('faculty_header', [
            'title' => __('Faculty Information', 'fau-elemental'),
            'panel' => 'fau_footer_panel',
            'priority' => 30,
        ]);
        
        $wp_customize->add_setting('instance_title', [
            'default' => get_bloginfo('name')
        ]);
        $wp_customize->add_control('instance_title', [
            'label' => __('Faculty Title', 'fau-elemental'),
            'section' => 'faculty_header',
            'type' => 'text'
        ]);
        
        $wp_customize->add_setting('instance_description', [
            'default' => get_bloginfo('description')
        ]);
        $wp_customize->add_control('instance_description', [
            'label' => __('Faculty Description', 'fau-elemental'),
            'section' => 'faculty_header',
            'type' => 'textarea'
        ]);
        
        // Contact Information
        $wp_customize->add_section('faculty_contact', [
            'title' => __('Contact Information', 'fau-elemental'),
            'panel' => 'fau_footer_panel',
            'priority' => 40,
        ]);
        
        // Get faculty-specific default values
        $defaults = [
            'phil' => [
                'name' => 'Philosophische Fakultät',
                'street' => 'Bismarckstraße 1',
                'city' => '91054 Erlangen',
                'phone' => '+49 9131 85-22345',
                'email' => 'dekanat-phil@fau.de'
            ],
            'nat' => [
                'name' => 'Naturwissenschaftliche Fakultät',
                'street' => 'Naturwissenschaftliche Fakultät',
                'city' => '91058 Erlangen',
                'phone' => '+49 9131 85-27032',
                'email' => 'dekanat-nat@fau.de'
            ],
            'med' => [
                'name' => 'Medizinische Fakultät',
                'street' => 'Krankenhausstraße 12',
                'city' => '91054 Erlangen',
                'phone' => '+49 9131 85-26730',
                'email' => 'med-dekanat@fau.de'
            ],
            'rw' => [
                'name' => 'Rechtswissenschaftliche Fakultät',
                'street' => 'Schillerstraße 1',
                'city' => '91054 Erlangen',
                'phone' => '+49 9131 85-22260',
                'email' => 'dekanat-rw@fau.de'
            ],
            'tf' => [
                'name' => 'Technische Fakultät',
                'street' => 'Martensstraße 5a',
                'city' => '91058 Erlangen',
                'phone' => '+49 9131 85-27130',
                'email' => 'tf-dekanat@fau.de'
            ]
        ];
        
        // Set defaults based on selected faculty
        $faculty_defaults = isset($defaults[$faculty]) ? $defaults[$faculty] : $defaults['phil'];
        
        $contact_fields = [
            'instance_university_name' => [
                'label' => 'University Name',
                'default' => 'Friedrich-Alexander-Universität Erlangen-Nürnberg'
            ],
            'instance_faculty_name' => [
                'label' => 'Faculty Name',
                'default' => $faculty_defaults['name']
            ],
            'instance_street' => [
                'label' => 'Street Address',
                'default' => $faculty_defaults['street']
            ],
            'instance_city' => [
                'label' => 'City',
                'default' => $faculty_defaults['city']
            ],
            'instance_phone' => [
                'label' => 'Phone Number',
                'default' => $faculty_defaults['phone']
            ],
            'instance_email' => [
                'label' => 'Email Address',
                'default' => $faculty_defaults['email']
            ],
            'instance_directions_link' => [
                'label' => 'Directions Link',
                'default' => 'https://www.fau.de/anfahrt/'
            ]
        ];

        foreach ($contact_fields as $setting => $config) {
            $wp_customize->add_setting($setting, [
                'default' => $config['default'],
                'sanitize_callback' => 'sanitize_text_field',
            ]);

            $wp_customize->add_control($setting, [
                'label' => __($config['label'], 'fau-elemental'),
                'section' => 'faculty_contact',
                'type' => 'text',
            ]);
        }
        
        // FAU Footer Info for Faculty sites
        $wp_customize->add_section('faculty_fau_info', [
            'title' => __('FAU Information', 'fau-elemental'),
            'panel' => 'fau_footer_panel',
            'priority' => 50,
            'description' => __('Configure the collapsible FAU section in the footer', 'fau-elemental')
        ]);
        
        $wp_customize->add_setting('fau_footer_title', [
            'default' => 'FAU - Wissen in Bewegung'
        ]);
        $wp_customize->add_control('fau_footer_title', [
            'label' => __('FAU Claim Title', 'fau-elemental'),
            'section' => 'faculty_fau_info',
            'type' => 'text'
        ]);
        
        $wp_customize->add_setting('fau_footer_description', [
            'default' => 'Die FAU ist die innovativste Universität Deutschlands, europaweit auf dem zweiten Platz. Mit 40.000 Studierenden gehören wir zu den größten Hochschulen in Deutschland mit herausragender Lehre und exzellenter Forschung.'
        ]);
        $wp_customize->add_control('fau_footer_description', [
            'label' => __('FAU Claim Text', 'fau-elemental'),
            'section' => 'faculty_fau_info',
            'type' => 'textarea'
        ]);
        
        // Add the same target groups as main FAU site
        $target_groups = [
            'zur_fau' => __('Zur FAU', 'fau-elemental'),
            'forschung' => __('Forschung', 'fau-elemental'),
            'studierende' => __('Studierende', 'fau-elemental'),
            'studieninteressierte' => __('Studieninteressierte', 'fau-elemental')
        ];
        
        foreach ($target_groups as $key => $label) {
            // Group heading
            $wp_customize->add_setting('target_group_heading_' . $key, [
                'default' => $label
            ]);
            $wp_customize->add_control(new WP_Customize_Control($wp_customize, 'target_group_heading_' . $key, [
                'label' => sprintf(__('--- %s ---', 'fau-elemental'), $label),
                'section' => 'faculty_fau_info',
                'settings' => 'target_group_heading_' . $key,
                'type' => 'hidden'
            ]));
            
            // Title
            $wp_customize->add_setting('target_' . $key . '_title', [
                'default' => $label
            ]);
            $wp_customize->add_control('target_' . $key . '_title', [
                'label' => __('Title', 'fau-elemental'),
                'section' => 'faculty_fau_info',
                'type' => 'text'
            ]);
            
            // Description
            $default_desc = __('Schwerpunkte, Leitbild, Reputation, Erfolge u.v.m.', 'fau-elemental');
            if ($key === 'zur_fau') {
                $default_desc = __('Geschichte, Besonderheiten Daten, Struktur u.v.m', 'fau-elemental');
            }
            
            $wp_customize->add_setting('target_' . $key . '_description', [
                'default' => $default_desc
            ]);
            $wp_customize->add_control('target_' . $key . '_description', [
                'label' => __('Description', 'fau-elemental'),
                'section' => 'faculty_fau_info',
                'type' => 'textarea'
            ]);
            
            // Link
            $wp_customize->add_setting('target_' . $key . '_link', [
                'default' => '#'
            ]);
            $wp_customize->add_control('target_' . $key . '_link', [
                'label' => __('Link URL', 'fau-elemental'),
                'section' => 'faculty_fau_info',
                'type' => 'url'
            ]);
            
            // Separator
            if ($key != 'studieninteressierte') {
                $wp_customize->add_setting('target_separator_' . $key);
                $wp_customize->add_control(new WP_Customize_Control($wp_customize, 'target_separator_' . $key, [
                    'label' => '',
                    'section' => 'faculty_fau_info',
                    'settings' => 'target_separator_' . $key,
                    'type' => 'hidden'
                ]));
            }
        }
    }
    
    // ======= FOOTER APPEARANCE =======
    $wp_customize->add_section('footer_appearance', [
        'title' => __('Footer Appearance', 'fau-elemental'),
        'panel' => 'fau_footer_panel',
        'priority' => 90,
    ]);
    
    // Logo
    $wp_customize->add_setting('fau_footer_logo');
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'fau_footer_logo', [
        'label' => __('Footer Logo', 'fau-elemental'),
        'description' => __('Upload a custom logo for the footer (white version recommended)', 'fau-elemental'),
        'section' => 'footer_appearance',
        'settings' => 'fau_footer_logo'
    ]));
    
    // Toggle text
    $wp_customize->add_setting('fau_info_toggle_text', [
        'default' => __('FAU Informationen anzeigen', 'fau-elemental')
    ]);
    $wp_customize->add_control('fau_info_toggle_text', [
        'label' => __('Toggle Button Text (Show)', 'fau-elemental'),
        'section' => 'footer_appearance',
        'type' => 'text'
    ]);
    
    $wp_customize->add_setting('fau_info_toggle_text_hide', [
        'default' => __('FAU Informationen ausblenden', 'fau-elemental')
    ]);
    $wp_customize->add_control('fau_info_toggle_text_hide', [
        'label' => __('Toggle Button Text (Hide)', 'fau-elemental'),
        'section' => 'footer_appearance',
        'type' => 'text'
    ]);
    $wp_customize->add_setting('footer_logo_tagline', [
        'default' => "Friedrich-Alexander-Universität\nErlangen-Nürnberg"
    ]);
    $wp_customize->add_control('footer_logo_tagline', [
        'label' => __('Logo Tagline', 'fau-elemental'),
        'description' => __('Text displayed next to the logo. Use \\n for line break.', 'fau-elemental'),
        'section' => 'footer_appearance',
        'type' => 'textarea'
    ]);
}

add_action('customize_register', 'fau_footer_customizer_settings');

/**
 * Add SVG support to WordPress media uploader
 */
function fau_elemental_add_svg_support($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    $mimes['svgz'] = 'image/svg+xml';
    return $mimes;
}
add_filter('upload_mimes', 'fau_elemental_add_svg_support');

/**
 * Sanitize SVG uploads
 */
function fau_elemental_sanitize_svg($file) {
    if ($file['type'] === 'image/svg+xml') {
        $svg_content = file_get_contents($file['tmp_name']);
        
        // Basic SVG sanitization
        if (strpos($svg_content, '<script') !== false || 
            strpos($svg_content, 'javascript:') !== false || 
            strpos($svg_content, 'onload=') !== false) {
            $file['error'] = __('SVG files containing scripts are not allowed for security reasons.', 'fau-elemental');
        }
    }
    return $file;
}
add_filter('wp_handle_upload_prefilter', 'fau_elemental_sanitize_svg');

/**
 * Fix SVG display in media library
 */
function fau_elemental_fix_svg_thumb_display() {
    echo '<style>
        .attachment-266x266, .thumbnail img {
            width: 100% !important;
            height: auto !important;
        }
    </style>';
}
add_action('admin_head', 'fau_elemental_fix_svg_thumb_display');

/**
 * Add logo settings to customizer
 */
function fau_elemental_customize_register($wp_customize) {
    // Add website shorttitle setting
    $wp_customize->add_setting('website_shorttitle', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    // Add website shorttitle control to Site Identity section
    $wp_customize->add_control('website_shorttitle', array(
        'label'    => __('Website Short Title', 'fau-elemental'),
        'section'  => 'title_tagline',
        'type'     => 'text',
    ));

    // Get current website type
    $website_type = get_option('faue_website_type', 'fau');

    // Only add custom logo control if website type is not cooperation
    // (Removed custom logo for cooperation, will use WordPress custom logo instead)
    // if ($website_type === 'cooperation') {
    //     // Add custom logo setting
    //     $wp_customize->add_setting('fau_elemental_custom_logo', array(
    //         'default'           => '',
    //         'sanitize_callback' => 'absint',
    //     ));

    //     // Add custom logo control with cropping
    //     $wp_customize->add_control(new WP_Customize_Cropped_Image_Control($wp_customize, 'fau_elemental_custom_logo', array(
    //         'label'    => __('Custom Logo', 'fau-elemental'),
    //         'section'  => 'title_tagline',
    //         'settings' => 'fau_elemental_custom_logo',
    //         'width'    => 400,
    //         'height'   => 112,
    //         'flex_width'  => true,
    //         'flex_height' => true,
    //     )));
    // }

    if ($website_type !== 'cooperation') {
        // Remove the default custom logo control from Site Identity
        $wp_customize->remove_control('custom_logo');
    }
}
add_action('customize_register', 'fau_elemental_customize_register');

/* Commenting out old logo settings but keeping them for reference
function fau_elemental_logo_customizer_settings($wp_customize) {
    // Move logo settings to Site Identity section
    $wp_customize->get_section('title_tagline')->title = __('Site Identity & Logo', 'fau-elemental');
    $wp_customize->get_section('title_tagline')->priority = 20;

    // Regular Logo
    $wp_customize->add_setting('fau_regular_logo', array(
        'default' => '',
        'sanitize_callback' => 'esc_url_raw',
    ));

    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'fau_regular_logo', array(
        'label' => __('Regular Logo', 'fau-elemental'),
        'description' => __('Upload your regular logo (recommended size: 300x100px)', 'fau-elemental'),
        'section' => 'title_tagline',
        'settings' => 'fau_regular_logo',
        'priority' => 8,
    )));

    // White Logo
    $wp_customize->add_setting('fau_white_logo', array(
        'default' => '',
        'sanitize_callback' => 'esc_url_raw',
    ));

    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'fau_white_logo', array(
        'label' => __('White Logo', 'fau-elemental'),
        'description' => __('Upload your white logo for dark backgrounds (recommended size: 300x100px)', 'fau-elemental'),
        'section' => 'title_tagline',
        'settings' => 'fau_white_logo',
        'priority' => 9,
    )));

    // Logo Height
    $wp_customize->add_setting('fau_logo_height', array(
        'default' => '50',
        'sanitize_callback' => 'absint',
    ));

    $wp_customize->add_control('fau_logo_height', array(
        'label' => __('Logo Height (px)', 'fau-elemental'),
        'description' => __('Set the height of your logo in pixels', 'fau-elemental'),
        'section' => 'title_tagline',
        'type' => 'number',
        'priority' => 10,
        'input_attrs' => array(
            'min' => 20,
            'max' => 200,
            'step' => 5,
        ),
    ));

    // University Name (Multi-line)
    $wp_customize->add_setting('fau_university_name', array(
        'default' => "Friedrich-Alexander-Universität\nErlangen-Nürnberg",
        'sanitize_callback' => 'wp_kses_post',
    ));

    $wp_customize->add_control('fau_university_name', array(
        'label' => __('University Name', 'fau-elemental'),
        'description' => __('Enter the full university name. Use line breaks (press Enter) for multiple lines.', 'fau-elemental'),
        'section' => 'title_tagline',
        'type' => 'textarea',
        'priority' => 11,
    ));
}

add_action('customize_register', 'fau_elemental_logo_customizer_settings');

// Commenting out old logo helper functions but keeping them for reference
function fau_elemental_get_logo($type = 'regular') {
    $logo_url = '';
    
    if ($type === 'white') {
        $logo_url = get_theme_mod('fau_white_logo', '');
    } else {
        $logo_url = get_theme_mod('fau_regular_logo', '');
    }
    
    return $logo_url;
}

function fau_elemental_display_logo($type = 'regular', $class = '') {
    $logo_url = fau_elemental_get_logo($type);
    $logo_height = get_theme_mod('fau_logo_height', 50);
    
    if (!empty($logo_url)) {
        $class = !empty($class) ? 'class="' . esc_attr($class) . '"' : '';
        echo '<img src="' . esc_url($logo_url) . '" alt="' . esc_attr(get_bloginfo('name')) . '" ' . $class . ' style="height: ' . esc_attr($logo_height) . 'px; width: auto;">';
    }
}

function fau_elemental_get_university_name() {
    return get_theme_mod('fau_university_name', "Friedrich-Alexander-Universität\nErlangen-Nürnberg");
}

function fau_elemental_display_university_name($class = '') {
    $university_name = fau_elemental_get_university_name();
    $class = !empty($class) ? 'class="' . esc_attr($class) . '"' : '';
    
    echo '<div ' . $class . '>' . nl2br(esc_html($university_name)) . '</div>';
}

*/