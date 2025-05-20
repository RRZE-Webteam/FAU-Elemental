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
    
    // Return reading time with strong tags
    return $reading_time . ' min';
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
    $show_categories = get_post_meta($post->ID, 'show_categories', true);
    
    // Set defaults
    if ($show_reading_time === '') {
        $show_reading_time = '1';
    }
    if ($show_categories === '') {
        $show_categories = '1';
    }
    ?>
    <div class="post-header-options">
        <label>
            <input type="checkbox" name="show_reading_time" value="1" <?php checked($show_reading_time, '1'); ?> />
            <strong>Show reading time</strong>
        </label>
        
        <label>
            <input type="checkbox" name="show_categories" value="1" <?php checked($show_categories, '1'); ?> />
            <strong>Show categories</strong>
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
    
    $show_categories = isset($_POST['show_categories']) ? '1' : '0';
    update_post_meta($post_id, 'show_categories', $show_categories);
    
    $show_listen_link = isset($_POST['show_listen_link']) ? '1' : '0';
    update_post_meta($post_id, 'show_listen_link', $show_listen_link);
    
    if (isset($_POST['listen_url'])) {
        update_post_meta($post_id, 'listen_url', esc_url_raw($_POST['listen_url']));
    }
}
add_action('save_post', 'save_post_header_options_meta_box');

// Add JavaScript to handle the listen link toggle
function post_header_options_admin_scripts() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        $('#show-listen-link-toggle').on('change', function() {
            if ($(this).is(':checked')) {
                $('#listen-fields').show();
            } else {
                $('#listen-fields').hide();
            }
        });
    });
    </script>
    <?php
}
add_action('admin_footer', 'post_header_options_admin_scripts');

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
            '<p class="reading-time"><strong>' . esc_html($reading_time) . '</strong></p>',
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
                <?php wp_strip_all_tags(the_category(', ')); ?>
            </div>
            <?php endif; ?>
        </div>
        
        <h1 class="post-title"><?php the_title(); ?></h1>
        
        <div class="post-meta wp-block-group">
            <?php if ($show_reading_time === '1'): ?>
            <p class="reading-time"><b><?php echo esc_html($reading_time); ?></b></p>
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
        $show_categories = get_post_meta($post_id, 'show_categories', true) !== '0';
        $listen_url = get_post_meta($post_id, 'listen_url', true);
        
        // Apply CSS to hide elements if needed
        $inline_style = '<style>';
        
        if (!$show_reading_time) {
            $inline_style .= '.post-reading-time, .reading-time { display: none !important; }';
        }
        
        if (!$show_categories) {
            $inline_style .= '.post-categories, .post-categories-separator { display: none !important; }';
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
    ));
    
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
    // $classes[] = 'is-layout-flow';
    
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

/**
 * Function to load template parts for both block and PHP templates
 *
 * @param string $slug Template slug
 * @param string $name Template name (optional)
 * @param array $args Additional arguments to pass to the template (optional)
 */
function fau_elemental_load_template_part($slug, $name = null, $args = array()) {
    // First check if block template part exists
    $part_name = $name ? "{$slug}-{$name}" : $slug;
    $block_part_file = get_theme_file_path("/parts/{$part_name}.html");
    
    if (file_exists($block_part_file) && filesize($block_part_file) > 0) {
        // Block template exists, use it
        echo do_blocks(file_get_contents($block_part_file));
    } else {
        // Fall back to PHP template part
        // Use WordPress's standard structure for template-parts
        $directory = '';
        
        // Organize by type if slug has a recognizable prefix
        if (strpos($slug, 'header') === 0) {
            $directory = 'header';
        } elseif (strpos($slug, 'footer') === 0) {
            $directory = 'footer';
        } elseif (strpos($slug, 'content') === 0) {
            $directory = 'content';
        }
        
        if ($directory) {
            get_template_part("template-parts/{$directory}/{$slug}", $name, $args);
        } else {
            get_template_part("template-parts/{$slug}", $name, $args);
        }
    }
}

// Additional theme functionality

/**
 * Add caption to post featured image blocks
 * 
 * This adds the caption from the media library to the featured image
 * when rendered with the post-featured-image block
 *
 * @param string $block_content The block content.
 * @param array  $block         The full block, including name and attributes.
 * @return string Modified block content.
 */
function fau_add_caption_to_featured_image($block_content, $block) {
    // Only modify core/post-featured-image blocks
    if (isset($block['blockName']) && 'core/post-featured-image' === $block['blockName']) {
        // Don't modify if the content is null or empty
        if (empty($block_content)) {
            return $block_content;
        }
        
        // Get the post ID and the attachment ID
        $post_id = get_the_ID();
        $thumbnail_id = get_post_thumbnail_id($post_id);
        
        if (!$thumbnail_id) {
            return $block_content;
        }
        
        // Get the attachment post to retrieve the caption
        $attachment = get_post($thumbnail_id);
        if (!$attachment) {
            return $block_content;
        }
        
        // Get the caption from the attachment's excerpt
        $caption = $attachment->post_excerpt;
        
        // Only add caption if it exists
        if (!empty($caption)) {
            // Check if the block content ends with </figure>
            if (substr(trim($block_content), -9) === '</figure>') {
                // Insert the figcaption before the closing figure tag
                $block_content = str_replace('</figure>', '<figcaption class="wp-element-caption">' . wp_kses_post($caption) . '</figcaption></figure>', $block_content);
            }
        }
    }
    
    return $block_content;
}
add_filter('render_block', 'fau_add_caption_to_featured_image', 10, 2);

/**
 * Modify post-terms block output to display categories without links
 */
function fau_elemental_modify_post_terms_block($block_content, $block) {
    if (!is_string($block_content)) {
        return $block_content;
    }

    // Only modify category terms
    if (isset($block['attrs']['term']) && $block['attrs']['term'] === 'category') {
        $post_id = get_the_ID();
        if (!$post_id) {
            return '';
        }

        $categories = get_the_category($post_id);
        if (empty($categories)) {
            return '';
        }

        $category_names = array();
        foreach ($categories as $category) {
            $category_names[] = $category->name;
        }

        $separator = isset($block['attrs']['separator']) ? $block['attrs']['separator'] : ', ';
        $className = isset($block['attrs']['className']) ? $block['attrs']['className'] : 'post-categories';

        return '<div class="' . esc_attr($className) . '">' . 
               esc_html(implode($separator, $category_names)) . 
               '</div>';
    }

    return $block_content;
}
add_filter('render_block', 'fau_elemental_modify_post_terms_block', 10, 2);