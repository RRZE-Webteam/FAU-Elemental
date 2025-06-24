<?php
/**
 * FAU Elemental Theme Configuration
 *
 * @package FAU_Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}
// Default Values
$faue_defaults = array(
    // Website Type
    'faue_website_type' => 'fau',
    // Breadcrumb Mode
    'faue_breadcrumb_variant_blue' => true,
    // Display Footer Address
    'display_footer_address' => true,
);

// Social Media Platforms Configuration
$faue_social_platforms = array(
    'instagram' => 'Instagram',
    'facebook' => 'Facebook',
    'xing' => 'Xing',
    'linkedin' => 'LinkedIn',
    'x' => 'X',
    'mastodon' => 'Mastodon',
    'bluesky' => 'Bluesky',
    'youtube' => 'YouTube',
    'tiktok' => 'TikTok'
);

// Helper function to get default values
function faue_get_default($key, $subkey = null) {
    global $faue_defaults;
    
    if ($subkey !== null) {
        return isset($faue_defaults[$key][$subkey]) ? $faue_defaults[$key][$subkey] : null;
    }
    
    return isset($faue_defaults[$key]) ? $faue_defaults[$key] : null;
}

// Helper function to get social platforms
function faue_get_social_platforms() {
    global $faue_social_platforms;
    return $faue_social_platforms;
} 