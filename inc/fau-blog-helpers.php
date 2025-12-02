<?php
/**
 * FAU Blog Helper Functions
 * Utilities for fetching data from the FAU blog in multisite networks
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Find the blog ID with website type 'fau' in a multisite network
 *
 * @return int|false Blog ID if found, false otherwise
 */
function faue_get_fau_blog_id() {
    if (!is_multisite()) {
        return false;
    }

    $main_site_id = get_main_site_id();
    if ($main_site_id) {
        switch_to_blog($main_site_id);
        try {
            $website_type = get_theme_mod('faue_website_type', '');
            if ($website_type === 'fau') {
                $fau_blog_id = $main_site_id;
            }
        } finally {
            restore_current_blog();
        }
        if (isset($fau_blog_id)) {
            return $fau_blog_id;
        }
    }

    return false;
}

/**
 * Get footer title and description from the FAU blog
 *
 * @return array Array with 'title' and 'description' keys
 */
function faue_get_footer_title_description_from_fau_blog() {
    $result = array(
        'title' => '',
        'description' => ''
    );
    
    // If not multisite, get from current blog
    if (!is_multisite()) {
        $result['title'] = get_theme_mod('fau_footer_title', faue_get_default('fau_footer_title'));
        $result['description'] = get_theme_mod('fau_footer_description', faue_get_default('fau_footer_description'));
        return $result;
    }

    // Find the FAU blog
    $fau_blog_id = faue_get_fau_blog_id();
    if (!$fau_blog_id) {
        // If no FAU blog found, use default fallback values
        $result['title'] = faue_get_default('fau_footer_title');
        $result['description'] = faue_get_default('fau_footer_description');
        return $result;
    }

    // Switch to FAU blog and get settings
    switch_to_blog($fau_blog_id);
    try {
        $result['title'] = get_theme_mod('fau_footer_title', faue_get_default('fau_footer_title'));
        $result['description'] = get_theme_mod('fau_footer_description', faue_get_default('fau_footer_description'));
    } finally {
        // Always restore the current blog
        restore_current_blog();
    }

    return $result;
}

