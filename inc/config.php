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
    'faue_website_type' => 'chair',
    // Breadcrumb Mode
    'faue_breadcrumb_variant_blue' => true,
    // Search Results Configuration
    'faue_search_excerpt_length' => 30,
    'faue_search_separator' => '|',
    'faue_search_arrow' => '→',
    // Search API Configuration
    'faue_search_max_length' => 100,
    'faue_search_rate_limit_window' => 10, // 10 second window
    'faue_search_rate_limit_max_requests' => 20,
    'faue_search_debounce_delay' => 300, // 300ms debounce delay
    'faue_search_cache_duration' => 3600, // 1 hour cache duration (much more reasonable)
    'faue_search_browser_cache_duration' => 3600, // 1 hour browser cache (can be much longer)
    'faue_search_recent_searches_duration' => 3600, // 1 hour for recent searches tracking
    'faue_search_rate_limit_violations_duration' => 3600, // 1 hour for rate limit violations
    // Display Footer Address
    'display_footer_address' => true,
    // Fallback Image
    'faue_fallback_image' => '',
    // Hide copyright on single posts
    'faue_hide_copyright_on_single' => true,
    // Footer toggle text
    'fau_info_toggle_text' => __('Show more', 'fau-elemental'),
    'fau_info_toggle_text_expanded' => __('Show less', 'fau-elemental'),
    // Footer content defaults
    'fau_footer_title' => __('FAU - Knowledge in Motion', 'fau-elemental'),
    'fau_footer_description' => __('FAU is Germany\'s most innovative university, ranking second in Europe. With 40,000 students, we are one of the largest universities in Germany with outstanding teaching and excellent research.', 'fau-elemental'),
);

// Social Media Platforms Configuration
$faue_social_platforms = array(
    'instagram' => 'Instagram',
    'facebook' => 'Facebook',
    'xing' => 'Xing',
    'linkedin' => 'LinkedIn',
    'mastodon' => 'Mastodon',
    'bluesky' => 'Bluesky',
    'youtube' => 'YouTube',
    'tiktok' => 'TikTok',
    'threads' => 'Threads'
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