<?php
/**
 * Theme Settings Functions
 *
 * @package faue
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add theme settings to the WordPress Customizer
 */
function faue_customize_register($wp_customize) {
    // Add FAU Elemental section
    $wp_customize->add_section('faue_theme_settings', array(
        'title'    => __('Theme Settings', 'fau-elemental'),
        'priority' => 120,
    ));

    // Add Header Settings section
    $wp_customize->add_section('faue_header_settings', array(
        'title'    => __('Header Settings', 'fau-elemental'),
        'priority' => 125,
    ));

    // Breadcrumb Mode Setting (stores boolean, convert to 'dark'/'light' when using)
    $wp_customize->add_setting('faue_breadcrumb_variant_blue', [
        'default' => faue_get_default('faue_breadcrumb_variant_blue'),
        'transport' => 'refresh',
        'sanitize_callback' => 'faue_sanitize_breadcrumb_mode',
    ]);
    
    $wp_customize->add_control('faue_breadcrumb_variant_blue', [
        'label' => __('Breadcrumb variant: Blue', 'fau-elemental'),
        'description' => __('Setting for the blue breadcrumb variant', 'fau-elemental'),
        'section' => 'faue_header_settings',
        'type' => 'checkbox',
        'priority' => 15,
    ]);

    // Website Type Setting
    $wp_customize->add_setting('faue_website_type', array(
        'default'           => faue_get_default('faue_website_type'),
        'transport'         => 'refresh',
        'sanitize_callback' => 'faue_sanitize_website_type',
    ));

    $wp_customize->add_control('faue_website_type', array(
        'label'    => __('Website Type', 'fau-elemental'),
        'section'  => 'faue_theme_settings',
        'type'     => 'select',
        'choices'  => array(
            'fau'          => __('FAU.de', 'fau-elemental'),
            'faculty'      => __('Faculty', 'fau-elemental'),
            'chair'        => __('Chair', 'fau-elemental'),
            'other'        => __('Other', 'fau-elemental'),
            'cooperation'  => __('Cooperation', 'fau-elemental'),
        ),
    ));

    // Faculty Setting
    $wp_customize->add_setting('faue_faculty', array(
        'default'           => 'phil',
        'transport'         => 'refresh',
        'sanitize_callback' => 'faue_sanitize_faculty',
    ));

    $wp_customize->add_control('faue_faculty', array(
        'label'           => __('Faculty', 'fau-elemental'),
        'section'         => 'faue_theme_settings',
        'type'            => 'select',
        'choices'         => array(
            'phil' => __('Philosophical Faculty', 'fau-elemental'),
            'nat'  => __('Natural Sciences Faculty', 'fau-elemental'),
            'med'  => __('Medical Faculty', 'fau-elemental'),
            'rw'   => __('Law Faculty', 'fau-elemental'),
            'tf'   => __('Technical Faculty', 'fau-elemental'),
        ),
        'active_callback' => 'faue_is_faculty_website',
    ));

    // Copyright Info Priority
    $wp_customize->add_setting('faue_copyright_info_priority', array(
        'default'           => 'field',
        'sanitize_callback' => 'faue_sanitize_copyright_info_priority',
    ));

    $wp_customize->add_control('faue_copyright_info_priority', array(
        'label'    => __('Copyright Info Priority', 'fau-elemental'),
        'section'  => 'faue_theme_settings',
        'type'     => 'select',
        'choices'  => array(
            'field' => __('Copyright Field', 'fau-elemental'),
            'iptc'  => __('IPTC Image Metadata', 'fau-elemental'),
        ),
    ));

    // Fallback Image Setting
    $wp_customize->add_setting('faue_fallback_image', array(
        'default'           => faue_get_default('faue_fallback_image'),
        'transport'         => 'refresh',
        'sanitize_callback' => 'esc_url_raw',
    ));

    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'faue_fallback_image', array(
        'label'       => __('Fallback Image', 'fau-elemental'),
        'description' => __('Choose a default image to use when no featured image is available for posts or pages.', 'fau-elemental'),
        'section'     => 'faue_theme_settings',
        'priority'    => 30,
    )));
}
add_action('customize_register', 'faue_customize_register');

/**
 * Check if the website type is set to faculty
 */
function faue_is_faculty_website($control) {
    $setting = $control->manager->get_setting('faue_website_type');
    if (!$setting) {
        return false;
    }
    return 'faculty' === $setting->value();
}

/**
 * Sanitize website type input
 */
function faue_sanitize_website_type($input) {
    $valid_types = array('fau', 'faculty', 'chair', 'other', 'cooperation');

    if (!in_array($input, $valid_types)) {
        return 'fau';
    }

    return $input;
}
/**
 * Sanitize copyright info priority input
 */
function faue_sanitize_copyright_info_priority($input) {
    $valid_prios = array('field', 'iptc');

    if (!in_array($input, $valid_prios)) {
        return 'field';
    }

    return $input;
}



/**
 * Restrict specific blocks to certain post types
 * 
 * This function restricts the FAU Teaser Grid block to pages only
 */
function restrict_blocks_by_post_type($allowed_blocks, $editor_context) {
    if (empty($editor_context->post)) {
        return $allowed_blocks;
    }

    $post_type = $editor_context->post->post_type;
    $block_to_remove = 'fau-elemental/fau-teaser-grid';

    if ($post_type === 'post') {
        // If $allowed_blocks is true or null, we need to get all registered blocks
        if ($allowed_blocks === true || is_null($allowed_blocks)) {
            // Make sure WP_Block_Type_Registry class exists
            if (class_exists('WP_Block_Type_Registry')) {
                $registry = WP_Block_Type_Registry::get_instance();
                $allowed_blocks = array_keys($registry->get_all_registered());
            } else {
                // If the registry class doesn't exist, we can't reliably filter blocks
                return $allowed_blocks;
            }
        }

        // Now that we've ensured $allowed_blocks is an array, we can safely filter it
        if (is_array($allowed_blocks)) {
            $allowed_blocks = array_diff($allowed_blocks, [$block_to_remove]);
        }
    }

    return $allowed_blocks;
}
add_filter('allowed_block_types_all', 'restrict_blocks_by_post_type', 10, 2);

function hide_teaser_grid_block_for_posts() {
    global $post;

    if (!is_admin() || get_post_type($post) !== 'post') {
        return;
    }

    ?>
    <script type="text/javascript">
        wp.domReady(() => {
            wp.blocks.unregisterBlockType('fau-elemental/fau-teaser-grid');
        });
    </script>
    <?php
}
add_action('admin_footer', 'hide_teaser_grid_block_for_posts');


/**
 * Sanitize faculty input
 */
function faue_sanitize_faculty($input) {
    $valid_faculties = array('phil', 'nat', 'med', 'rw', 'tf');

    if (!in_array($input, $valid_faculties)) {
        return 'phil';
    }

    return $input;
}

/**
 * Sanitize breadcrumb mode input
 */
function faue_sanitize_breadcrumb_mode($input) {
    return (bool) $input;
}