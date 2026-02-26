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
    // FAU Logo Color (only applies to fau.de website type)
    'faue_fau_logo_color' => 'white',
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
    'fau_footer_title' => __('Moving knowledge', 'fau-elemental'),
    'fau_footer_description' => __('FAU is the most innovative university in Germany, and second across Europe. Established in 1743, it is one of the largest universities in Germany, with approximately 40,000 students, over 600 professors and around 16,000 members of staff.', 'fau-elemental'),
    // Target groups defaults (German)
    'faue_target_groups_de' => array(
        array(
            'title' => 'Studieninteressierte',
            'description' => 'Studium, Orientierung und Studiengänge',
            'link' => 'https://www.fau.de/fuer-studieninteressierte/'
        ),
        array(
            'title' => 'Studierende',
            'description' => 'Services, Portale und Studieninformationen',
            'link' => 'https://www.fau.de/fuer-studierende/'
        ),
        array(
            'title' => 'Forschende',
            'description' => 'Forschungsprofil, akademische Karriere und Erfolge',
            'link' => 'https://www.fau.de/fuer-forschende-und-lehrende/'
        ),
        array(
            'title' => 'Kooperationspartner',
            'description' => 'Kooperationen, Patente und Transfer',
            'link' => 'https://www.fau.de/services/fuer-unternehmen-und-partner/'
        ),
    ),
    // Target groups defaults (English)
    'faue_target_groups_en' => array(
        array(
            'title' => 'Prospective Students',
            'description' => 'Study, orientation, and degree programs',
            'link' => 'https://www.fau.eu/for-prospective-international-students/'
        ),
        array(
            'title' => 'Students',
            'description' => 'Services, portals, and study information',
            'link' => 'https://www.fau.eu/for-students/'
        ),
        array(
            'title' => 'Researchers',
            'description' => 'Research profile, academic career, and achievements',
            'link' => 'https://www.fau.eu/for-researchers-and-teaching-staff/'
        ),
        array(
            'title' => 'Partners',
            'description' => 'Collaborations, patents, and knowledge transfer',
            'link' => 'https://www.fau.eu/for-companies-and-partners/'
        ),
    ),
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
    'threads' => 'Threads',
    'twitter' => 'Twitter',
    'pinterest' => 'Pinterest',
    'reddit' => 'Reddit',
    'community-fau' => 'FAU Community',
    'indeed' => 'Indeed',
    'whatsapp' => 'WhatsApp',
    'discord' => 'Discord',
    'twitch' => 'Twitch',
    'arxiv' => 'arXiv',
    'academia' => 'Academia.edu',
    'email' => 'E-Mail',
    'rss' => 'RSS Feed',
    'calendar' => 'Calendar'
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

// Helper function to check if the website type is a cooperation type
function faue_is_cooperation_website($website_type = null) {
    if ($website_type === null) {
        $website_type = get_theme_mod('faue_website_type', faue_get_default('faue_website_type'));
    }
    return in_array($website_type, array('cooperation', 'cooperation-external'), true);
}