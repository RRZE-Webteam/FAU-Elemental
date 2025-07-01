<?php
/**
 * FAU Elemental Theme Functions
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}


// Configuration
require_once get_template_directory() . '/inc/config.php';

// Theme setup and core functionality
require_once get_template_directory() . '/inc/theme-setup.php';

// Asset management
require_once get_template_directory() . '/inc/enqueue-assets.php';

// Customizer
require_once get_template_directory() . '/inc/customizer.php';

// Block functionality
require_once get_template_directory() . '/inc/blocks/loader.php';
require_once get_template_directory() . '/inc/block-patterns.php';

// Post settings and functionality
require_once get_template_directory() . '/inc/posts-settings.php';

// Theme settings
require_once get_template_directory() . '/inc/theme-settings.php';

// Include post meta functionality
require_once get_template_directory() . '/inc/post-meta.php';

// Menu registration
require_once get_template_directory() . '/inc/menu-registration.php';

// Shortcodes functionality
require_once get_template_directory() . '/inc/shortcodes-loader.php';

// Portal menu compatibility with old theme
require_once get_template_directory() . '/inc/portal-menu-compatibility.php';

// Breadcrumb functionality
require_once get_template_directory() . '/components/template-parts/breadcrumbs/breadcrumbs.php';

// Navigation components
require_once get_template_directory() . '/components/ui/navigation/index.php';

/**
 * Register custom page templates
 * 
 * IMPORTANT: Portal Page template MUST be registered in the root of the theme,
 * not in templates/ directory for it to work with WordPress template selector
 */
function fau_elemental_register_page_templates($templates) {
    // Register the portal page template
    $templates['portal-page.php'] = 'Portal Page';
    
    // Force flush the template cache if we're in admin
    if (is_admin()) {
        $cache_key = 'page_templates-' . md5(get_theme_root() . '/' . get_stylesheet());
        $old_templates = wp_cache_get($cache_key, 'themes');
        if (is_array($old_templates)) {
            wp_cache_delete($cache_key, 'themes');
        }
    }
    
    return $templates;
}
add_filter('theme_page_templates', 'fau_elemental_register_page_templates', 11, 1);



/**
 * Fix portal template includes for different template locations
 */
function fau_elemental_template_include($template) {
    if (is_page()) {
        $template_slug = get_page_template_slug();
        
        // Debug output
        if (defined('FAU_ELEMENTAL_DEBUG') && FAU_ELEMENTAL_DEBUG) {
            error_log('FAU Elemental Debug: Template include requested for: ' . $template_slug);
        }
        
        // Priority 1: Use the root template if explicitly selected
        if ($template_slug === 'portal-page.php') {
            $root_template = locate_template(['portal-page.php']);
            if (!empty($root_template)) {
                return $root_template;
            }
        }
        
        // If the requested template isn't found but the page has a portal menu ID
        // Try to use the portal template
        if (get_post_meta(get_the_ID(), 'portal_menu_id', true)) {
            $portal_template = locate_template(['portal-page.php']);
            if (!empty($portal_template)) {
                error_log('FAU Elemental: Portal menu ID found, using template: portal-page.php');
                update_post_meta(get_the_ID(), '_wp_page_template', 'portal-page.php');
                return $portal_template;
            }
        }
    }
    return $template;
}
add_filter('template_include', 'fau_elemental_template_include', 99);

/**
 * Main theme setup function for FAU-Elemental
 */
 function fau_elemental_theme_setup() {
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
    
 
    
    // Add custom image sizes if needed
    // add_image_size('featured-large', 1600, 900, true);
}
add_action('after_setup_theme', 'fau_elemental_theme_setup');

/**
 * Add a filter to post updated messages to help with portal page template
 */
function fau_elemental_post_updated_messages($messages) {
    global $post;
    
    if ($post && get_post_type($post) === 'page') {
        $template = get_post_meta($post->ID, '_wp_page_template', true);
        
        if ($template === 'portal-page.php') {
            // Add message for portal page template
            $messages['post'][1] .= ' <span style="color:#2271b1;">This page is using the Portal Page template. Make sure to select a menu in the Portal Menu Settings box.</span>';
        }
    }
    
    return $messages;
}
add_filter('post_updated_messages', 'fau_elemental_post_updated_messages');

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
    
    return $classes;
}
add_filter('body_class', 'fau_elemental_body_classes');

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
}

/**
 * Hook to migrate settings right after theme activation
 */
add_action('after_switch_theme', function() {
    if (function_exists('fau_elemental_check_old_portal_menu_settings')) {
        fau_elemental_check_old_portal_menu_settings();
    }
    
    // Also trigger address migration
    if (function_exists('fau_elemental_migrate_address_information')) {
        fau_elemental_migrate_address_information();
    }
});

/**
 * Sanitize and format telephone number
 * Follows international standards as required by FAU
 *
 * @param string $phone The phone number to format
 * @return string Formatted phone number
 */
function fau_elemental_format_phone_number($phone) {
    if (empty($phone)) {
        return '';
    }
    
    // Remove all characters except numbers, "+", "(", ")", "-" and spaces
    $phone = preg_replace('/[^\d\+\-\(\) ]/', '', $phone);
    $phone = preg_replace('/\s+/', ' ', trim($phone));
    
    // Convert "+49(0)" to "+49"
    $phone = preg_replace('/^\+49\s*\(0\)/', '+49', $phone);
    $phone = preg_replace('/^0049/', '+49', $phone);
    
    // If number starts with "0" (German number without country code)
    if (preg_match('/^0[1-9]/', $phone)) {
        $phone = preg_replace('/^0/', '+49 ', $phone);
    }
    
    // Standardize format with spaces between groups
    $phone = preg_replace('/(\+?\d{1,3})\s*(\d{3,4})\s*(\d{3,4})\s*(\d{0,4})/', '$1 $2 $3 $4', $phone);
    
    return trim($phone); // Remove excess spaces at the end
}

/**
 * Enqueue footer scripts and localize strings
 */
function fau_elemental_enqueue_footer_scripts() {
    // Only enqueue on pages that have footers
    if (is_admin()) {
        return;
    }
    
    $faue_website_type = get_theme_mod('faue_website_type');
    
    // Enqueue footer toggle script for instance sites (where the toggle is used)
    if ($faue_website_type !== 'fau') {
        wp_enqueue_script(
            'fau-footer-toggle',
            get_theme_file_uri('components/template-parts/footer-main/footer-toggle.js'),
            [],
            wp_get_theme()->get('Version'),
            true
        );
        
        // Localize strings for the footer toggle functionality
        wp_localize_script('fau-footer-toggle', 'fauFooterStrings', [
            'showMore' => __('Show more', 'fau-elemental'),
            'showLess' => __('Show less', 'fau-elemental')
        ]);
    }
}
add_action('wp_enqueue_scripts', 'fau_elemental_enqueue_footer_scripts');


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
    if ($website_type === 'cooperation') {
        // Add custom logo setting
        $wp_customize->add_setting('fau_elemental_custom_logo', array(
            'default'           => '',
            'sanitize_callback' => 'absint',
        ));

        // Add custom logo control with cropping
        $wp_customize->add_control(new WP_Customize_Cropped_Image_Control($wp_customize, 'fau_elemental_custom_logo', array(
            'label'    => __('Custom Logo', 'fau-elemental'),
            'section'  => 'title_tagline',
            'settings' => 'fau_elemental_custom_logo',
            'width'    => 400,
            'height'   => 112,
            'flex_width'  => true,
            'flex_height' => true,
        )));
    }
}
add_action('customize_register', 'fau_elemental_customize_register');
// ============================================================================
// FAU TEASER GRID AJAX HANDLERS
// ============================================================================

/**
 * Include and register AJAX handlers for FAU Teaser Grid
 */
function fau_elemental_register_teaser_grid_ajax() {
    // Include the AJAX handler file
    require_once get_template_directory() . '/components/blocks/fau-teaser-grid/ajax.php';
}
add_action('init', 'fau_elemental_register_teaser_grid_ajax');
