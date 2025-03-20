<?php

// Allow SVG Upload - TODO: Remove this after testing

function allow_svg_upload($mimes)
{
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter('upload_mimes', 'allow_svg_upload');

// Enqueue Styles

function fau_elemental_enqueue_styles()
{
    $theme_asset = include get_theme_file_path('build/css/theme.asset.php');

    wp_enqueue_style(
        'fau-elemental-style',
        get_stylesheet_uri()
    );

    wp_enqueue_style(
        'fau-elemental-theme',
        get_theme_file_uri('build/css/theme.css'),
        $theme_asset['dependencies'],
        $theme_asset['version']
    );

    // wp_add_inline_style(
    //     'fau-elemental-primary',
    //     'body { background: green; }'
    // );
}
add_action('wp_enqueue_scripts', 'fau_elemental_enqueue_styles');

// Enqueue Editor Styles
function fau_elemental_setup()
{
    add_editor_style(array(
        'style.css',
        'build/css/editor.css'
    ));
}
add_action('after_setup_theme', 'fau_elemental_setup');

// Enqueue Editor Wrapper Styles
function fau_elemental_enqueue_editor_assets() {
    wp_enqueue_style(
        'fau-elemental-editor-wrapper',
        get_theme_file_uri('build/css/editor-wrapper.css'),
        array(),
        wp_get_theme()->get('Version')
    );
}
add_action('enqueue_block_editor_assets', 'fau_elemental_enqueue_editor_assets');

// Enqueue Scripts
function fau_elemental_enqueue_scripts()
{
    wp_enqueue_script(
        'fau-elemental-example',
        get_parent_theme_file_uri('assets/js/example.js'),
        array(),
        wp_get_theme()->get('Version'),
        true
    );

    // wp_add_inline_script(
    //     'fau-elemental-example',
    //     'console.log( "Inline script" );',
    // );
}
add_action('wp_enqueue_scripts', 'fau_elemental_enqueue_scripts');

// Enqueue Editor Scripts
function fau_elemental_enqueue_block_editor_script()
{
    wp_enqueue_script(
        'fau-elemental-block-editor-script',
        get_parent_theme_file_uri('build/js/editor.js'),
        array('wp-dom-ready', 'wp-blocks', 'wp-hooks', 'wp-edit-post', 'wp-element', 'wp-components'),
        wp_get_theme()->get('Version'),
        true
    );
}
add_action('enqueue_block_editor_assets', 'fau_elemental_enqueue_block_editor_script');


// Register Block Category
function fau_elemental_register_block_categories($categories)
{
    return array_merge(
        array(
            array(
                'slug'  => 'fau-elemental/FAU',
                'title' => __('FAU Elemental', 'fau-elemental'),
            ),
        ),
        $categories
    );
}
add_filter('block_categories_all', 'fau_elemental_register_block_categories');

// Register Blocks
function fau_elemental_register_blocks()
{
    // Get all directories in the build folder that start with 'fau-'
    $block_folders = glob(get_theme_file_path('build/fau-*'), GLOB_ONLYDIR);

    // Register each block
    foreach ($block_folders as $block_folder) {
        if (file_exists($block_folder . '/block.json')) {
            register_block_type($block_folder);
        }
    }
}
add_action('init', 'fau_elemental_register_blocks');

// Theme Settings

function fau_elemental_settings_page()
{
    add_menu_page(
        'FAU Elemental Settings',
        'FAU Elemental',
        'manage_options',
        'fau-elemental-settings',
        'fau_elemental_settings_callback'
    );
}
add_action('admin_menu', 'fau_elemental_settings_page');

function fau_elemental_settings_callback()
{
    echo '<h1>FAU Elemental Settings</h1>';

    // Add settings update message
    if (isset($_GET['settings-updated'])) {
        if (get_settings_errors()) {
            foreach (get_settings_errors() as $error) {
                echo '<div class="notice notice-' . esc_attr($error['type']) . ' is-dismissible"><p>' . esc_html($error['message']) . '</p></div>';
            }
        } else {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Settings saved successfully.', 'fau-elemental') . '</p></div>';
        }
    }

    echo '<form method="post" action="options.php">';
    settings_fields('fau-elemental-settings-group');
    do_settings_sections('fau-elemental-settings');
    submit_button();
    echo '</form>';
}

function fau_elemental_register_settings()
{
    register_setting(
        'fau-elemental-settings-group',
        'fau_elemental_website_type',
        array(
            'sanitize_callback' => 'fau_elemental_sanitize_website_type'
        )
    );

    register_setting(
        'fau-elemental-settings-group',
        'fau_elemental_faculty',
        array(
            'sanitize_callback' => 'fau_elemental_sanitize_faculty'
        )
    );

    add_settings_section('fau-elemental-section', 'Custom Options', null, 'fau-elemental-settings');

    // Add new website type field
    add_settings_field(
        'fau_elemental_website_type',
        'Website Type',
        'fau_elemental_website_type_callback',
        'fau-elemental-settings',
        'fau-elemental-section'
    );

    // Add faculty field
    add_settings_field(
        'fau_elemental_faculty',
        'Faculty',
        'fau_elemental_faculty_callback',
        'fau-elemental-settings',
        'fau-elemental-section'
    );
}
add_action('admin_init', 'fau_elemental_register_settings');

// Add the callback function for the website type dropdown
function fau_elemental_website_type_callback()
{
    $website_type = get_option('fau_elemental_website_type', 'fau');
    $options = array(
        'fau' => __('FAU.de', 'fau-elemental'),
        'faculty' => __('Fakultät', 'fau-elemental'),
        'chair' => __('Lehrstuhl', 'fau-elemental'),
        'other' => __('Sonstige', 'fau-elemental'),
        'cooperation' => __('Kooperation', 'fau-elemental')
    );

    echo '<select name="fau_elemental_website_type">';
    foreach ($options as $value => $label) {
        echo '<option value="' . esc_attr($value) . '" ' . selected($website_type, $value, false) . '>';
        echo esc_html($label);
        echo '</option>';
    }
    echo '</select>';
}

// Add the callback function for the faculty dropdown
function fau_elemental_faculty_callback()
{
    $faculty = get_option('fau_elemental_faculty', '');
    $options = array(
        'phil' => __('Philosophische Fakultät', 'fau-elemental'),
        'nat' => __('Naturwissenschaftliche Fakultät', 'fau-elemental'),
        'med' => __('Medizinische Fakultät', 'fau-elemental'),
        'rw' => __('Rechtswissenschaftliche Fakultät', 'fau-elemental'),
        'tf' => __('Technische Fakultät', 'fau-elemental')
    );

    echo '<select name="fau_elemental_faculty">';
    echo '<option value="">' . esc_html__('Select Faculty', 'fau-elemental') . '</option>';
    foreach ($options as $value => $label) {
        echo '<option value="' . esc_attr($value) . '" ' . selected($faculty, $value, false) . '>';
        echo esc_html($label);
        echo '</option>';
    }
    echo '</select>';
}

// Add sanitization callback
function fau_elemental_sanitize_website_type($input)
{
    $valid_types = array('fau', 'faculty', 'chair', 'other', 'cooperation');

    if (!in_array($input, $valid_types)) {
        add_settings_error(
            'fau_elemental_website_type',
            'invalid_website_type',
            __('Invalid website type selected.', 'fau-elemental'),
            'error'
        );
        return get_option('fau_elemental_website_type', 'fau');
    }

    return $input;
}

// Add sanitization callback for faculty
function fau_elemental_sanitize_faculty($input)
{
    $valid_faculties = array('phil', 'nat', 'med', 'rw', 'tf', '');

    if (!in_array($input, $valid_faculties)) {
        add_settings_error(
            'fau_elemental_faculty',
            'invalid_faculty',
            __('Invalid faculty selected.', 'fau-elemental'),
            'error'
        );
        return get_option('fau_elemental_faculty', '');
    }

    return $input;
}

function fau_elemental_get_org_classes()
{
    $classes = array('fau-theme', 'fau-elemental');

    // Get website type from options
    $website_type = get_option('fau_elemental_website_type', 'fau');

    // Add website type specific classes
    switch ($website_type) {
        case 'fau':
            $classes[] = 'fauorg-home';
            break;
        case 'faculty':
            $classes[] = 'fauorg-fakultaet';
            break;
        case 'chair':
            $classes[] = 'fauorg-unterorg';
            break;
        case 'cooperation':
            $classes[] = 'fauorg-kooperation';
            break;
    }

    // Add faculty-specific class if set
    $faculty = get_option('fau_elemental_faculty', '');
    if ($faculty) {
        $classes[] = 'faculty-' . sanitize_html_class($faculty);
    }

    return $classes;
}

// Frontend body classes
function fau_elemental_body_class($classes)
{
    return array_merge($classes, fau_elemental_get_org_classes());
}
add_filter('body_class', 'fau_elemental_body_class');

// Conditional Patterns
function fau_elemental_register_patterns()
{
    // Get the website type from options
    $website_type = get_option('fau_elemental_website_type', 'fau');

    // Map website types to their corresponding pattern files
    $pattern_map = array(
        'fau' => 'hero-fau',
        'faculty' => 'hero-faculty',
        'chair' => 'hero-chair',
        'other' => 'hero-other',
        'cooperation' => 'hero-cooperation'
    );

    // Unregister the pattern if it exists
    unregister_block_pattern('fau-elemental/hero');

    // Register the pattern with content based on website type
    $pattern_name = isset($pattern_map[$website_type]) ? $pattern_map[$website_type] : 'hero';
    register_block_pattern(
        'fau-elemental/hero',
        array(
            'title' => __('Hero Pattern', 'fau-elemental'),
            'source' => 'theme',
            'content' => (function () use ($pattern_name) {
                ob_start();
                include get_theme_file_path("/conditional-patterns/{$pattern_name}.php");
                return ob_get_clean();
            })()
        )
    );
}
add_action('init', 'fau_elemental_register_patterns');

/**
 * Add a fullscreen button to core/image blocks
 * 
 * @param string $block_content The block content about to be rendered
 * @param array $block The full block, including name and attributes
 * @return string Modified block content
 */
function add_fullscreen_button_to_images( $block_content, $block ) {
    // Only modify core/image blocks
    if ( isset( $block['blockName'] ) && 'core/image' === $block['blockName'] ) {
        // Don't modify if the content is null
        if ( is_null( $block_content ) ) {
            return $block_content;
        }
        
        // Enqueue the fullscreen script only once per page
        static $script_enqueued = false;
        if (!$script_enqueued) {
            $asset_file = include get_theme_file_path('build/js/image-fullscreen.asset.php');
            wp_enqueue_script(
                'image-fullscreen',
                get_theme_file_uri('build/js/image-fullscreen.js'),
                array_merge($asset_file['dependencies'], ['jquery']),
                $asset_file['version'],
                true
            );
            $script_enqueued = true;
        }
        
        // Extract the image source from the block content
        preg_match('/src="([^"]+)"/', $block_content, $matches);
        $img_src = isset($matches[1]) ? $matches[1] : '';
        
        // Create the fullscreen button
        $fullscreen_button = '<button class="image-fullscreen-btn" onclick="openImageFullscreen(\'' . esc_attr($img_src) . '\')">⛶</button>';
        
        // Insert the button into the block content
        // Find the closing tag of the figure or the first div
        $pos = strpos($block_content, '</figure>');
        if ($pos === false) {
            $pos = strpos($block_content, '</div>');
        }
        
        if ($pos !== false) {
            // Insert the button before the closing tag
            $block_content = substr_replace($block_content, $fullscreen_button, $pos, 0);
        }
    }
    
    return $block_content;
}
add_filter( 'render_block', 'add_fullscreen_button_to_images', 10, 2 );
function enqueue_quote_block_scripts() {
    // Frontend only
    if (!is_admin()) {
        wp_enqueue_script(
            'quote-carousel-frontend',
            get_template_directory_uri() . '/src/js/quote-carousel-frontend.js',
            array(),
            '1.0.0',
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'enqueue_quote_block_scripts');