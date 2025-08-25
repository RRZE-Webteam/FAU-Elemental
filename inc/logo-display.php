<?php
/**
 * Logo and Title Display Functions
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get SVG file path for use with img tags
 *
 * @param string $name The name of the SVG icon
 * @param bool $echo Whether to echo the output
 * @return string|void The SVG file path if $echo is false
 */
function fau_get_svg_path($name, $echo = true) {
    // Check if it's the FAU logo
    if ($name === 'fau-logo-2021') {
        // Check website type to determine logo color
        $website_type = get_theme_mod('faue_website_type', 'fau');
        
        if (in_array($website_type, array('faculty', 'chair'))) {
            // Use blue logo for faculty and chair websites
            $svg_path = get_template_directory_uri() . '/assets/images/logo.svg';
        } else {
            // Use white logo for all other website types
            $svg_path = get_template_directory_uri() . '/assets/images/Logo-white.svg';
        }
    } else {
        $svg_path = get_template_directory_uri() . '/assets/svg/' . $name . '.svg';
    }
    
    if ($echo) {
        echo $svg_path;
    } else {
        return $svg_path;
    }
}

/**
 * Display an SVG icon with specified attributes
 *
 * @param string $name The name of the SVG icon
 * @param int $width The width of the SVG
 * @param int $height The height of the SVG
 * @param string $class Additional CSS classes
 * @param bool $echo Whether to echo the output
 * @return string|void The SVG markup if $echo is false
 */
function fau_use_svg($name, $width = 0, $height = 0, $class = '', $echo = true) {
    // Check if it's the FAU logo
    if ($name === 'fau-logo-2021') {
        // Check website type to determine logo color
        $website_type = get_theme_mod('faue_website_type', 'fau');
        
        if (in_array($website_type, array('faculty', 'chair'))) {
            // Use blue logo for faculty and chair websites
            $svg_path = get_template_directory() . '/assets/images/logo.svg';
        } else {
            // Use white logo for all other website types
            $svg_path = get_template_directory() . '/assets/images/Logo-white.svg';
        }
    } else {
        $svg_path = get_template_directory() . '/assets/svg/' . $name . '.svg';
    }
    
    if (!file_exists($svg_path)) {
        return '';
    }

    $svg_content = file_get_contents($svg_path);
    if (!$svg_content) {
        return '';
    }

    // Add width and height attributes if specified
    if ($width > 0) {
        $svg_content = str_replace('<svg', '<svg width="' . esc_attr($width) . '"', $svg_content);
    }
    if ($height > 0) {
        $svg_content = str_replace('<svg', '<svg height="' . esc_attr($height) . '"', $svg_content);
    }

    // Add class if specified
    if (!empty($class)) {
        $svg_content = str_replace('<svg', '<svg class="' . esc_attr($class) . '"', $svg_content);
    }

    // Add specific attributes for FAU logo
    if ($name === 'fau-logo-2021') {
        $svg_content = str_replace('<svg', '<svg hidden="true" labelledby="website-title" role="img"', $svg_content);
    }

    if ($echo) {
        echo $svg_content;
    } else {
        return $svg_content;
    }
}

/**
 * Display the FAU logo and title based on website type and faculty
 */
function fau_elemental_display_logo_title() {
    global $defaultoptions;
    global $default_fau_orga_faculty;

    $faculty = '';
    if (isset($defaultoptions) && is_array($defaultoptions) && isset($defaultoptions['website_usefaculty'])) {
        $website_usefaculty = $defaultoptions['website_usefaculty'];
        if (isset($default_fau_orga_faculty) && in_array($website_usefaculty, $default_fau_orga_faculty)) {
            $faculty = $website_usefaculty;
        }
    }

    $website_type = get_theme_mod('faue_website_type', 'fau');
    
    // Handle invalid faculty selection for faculty or chair website type
    if ((empty($faculty)) && in_array($website_type, array('faculty', 'chair'))) {
        $website_type = 'other';
    }

    $faulogo = true;
    $visible_toptitle = 'Friedrich-Alexander-Universität';
    $visible_toptitle_secondline = 'Erlangen-Nürnberg';
    $visible_shortcut = get_theme_mod('website_shorttitle');
    $visible_title = get_bloginfo('title', 'display');

    // Handle different website types
    switch ($website_type) {
        case 'fau': // FAU Portal
            $visible_shortcut = '';
            $visible_title = '';
            break;
        case 'cooperation': // External cooperation
            $visible_toptitle_secondline = '';
            $visible_toptitle = '';
            $visible_shortcut = '';
            $visible_title = '';
            $faulogo = false;
            break;
        default: // faculty, chair, other
            $visible_toptitle_secondline = '';
            break;
    }

    // Output the logo and title
    echo '<div itemscope itemtype="https://schema.org/Organization">';
    if (!is_front_page()) {
        echo '<a itemprop="url" rel="home" class="generated" href="' . esc_url(home_url('/')) . '">';
    }

    echo '<span class="textlogo">';
    
    // Check if custom logo is set and website type is cooperation
    if ($website_type === 'cooperation' && has_custom_logo()) {
        echo '<span class="baselogo">';
        // Add max-height style to the custom logo
        $custom_logo_id = get_theme_mod('custom_logo');
        $custom_logo = wp_get_attachment_image_src($custom_logo_id, 'full');
        if ($custom_logo) {
            echo '<img src="' . esc_url($custom_logo[0]) . '" alt="' . esc_attr(get_bloginfo('name')) . '" class="custom-logo" style="max-height: 60px;">';
        } else {
            the_custom_logo();
        }
        echo '</span>';
    } elseif ($faulogo) {
        echo '<span class="baselogo">';
        echo '<img src="' . fau_get_svg_path("fau-logo-2021", false) . '" alt="' . esc_attr(get_bloginfo('name')) . '" class="faubaselogo" width="150" height="58">';
        echo '</span>';
    }

    // Only show text elements for non-cooperation websites and non-front pages
    if ($website_type !== 'cooperation' && !is_front_page()) {
        echo '<span class="text">';
        if ($visible_toptitle) {
            echo '<span class="fau-title"' . ($visible_title ? ' aria-hidden="true"' : ' id="website-title"') . '>' . esc_html($visible_toptitle) . '</span> ';
            
            if ($visible_toptitle_secondline) {
                echo '<span class="fau-title-place"' . ($visible_title ? ' aria-hidden="true"' : '') . '>' . esc_html($visible_toptitle_secondline) . '</span> ';
            }
        }

        if ($visible_title) {
            echo '<span id="website-title" class="visible-title' . (!empty($faculty) ? ' ' . esc_attr($faculty) : '') . '" itemprop="name">' . esc_html($visible_title) . '</span>';
            
            if ($visible_shortcut) {
                echo ' <span class="separator">|</span> <span class="website-shortcut' . (!empty($faculty) ? ' ' . esc_attr($faculty) : '') . '">' . esc_html($visible_shortcut) . '</span>';
            }
        } elseif ($visible_shortcut) {
            echo '<span id="website-title" class="visible-title' . (!empty($faculty) ? ' ' . esc_attr($faculty) : '') . '" itemprop="name">' . esc_html($visible_shortcut) . '</span>';
        }
        echo '</span>';
    }
    
    echo '</span>';

    if (!is_front_page()) {
        echo '</a>';
    }
    echo '</div>'; // Close microdata Organization context
} 