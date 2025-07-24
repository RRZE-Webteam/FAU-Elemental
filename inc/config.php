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
    // Search Results Configuration
    'faue_search_excerpt_length' => 30,
    'faue_search_separator' => '|',
    'faue_search_arrow' => '→',
    // Search API Configuration
    'faue_search_max_length' => 100,
    'faue_search_rate_limit_window' => 10, // 1 minute window
    'faue_search_rate_limit_max_requests' => 20, // Increased from 30 to 60
    'faue_search_debounce_delay' => 300, // 300ms debounce delay
    'faue_search_cache_duration' => 3600, // 1 hour cache duration (much more reasonable)
    'faue_search_browser_cache_duration' => 3600, // 1 hour browser cache (can be much longer)
    'faue_search_recent_searches_duration' => 3600, // 1 hour for recent searches tracking
    'faue_search_rate_limit_violations_duration' => 3600, // 1 hour for rate limit violations
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