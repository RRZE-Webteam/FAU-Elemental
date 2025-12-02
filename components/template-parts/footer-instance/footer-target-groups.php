<?php
/**
 * Footer Target Groups Component
 * Uses the shared big button rendering function with custom target group data
 *
 * @package FAU-Elemental
 */

// Ensure the shared rendering function is available
require_once get_template_directory() . '/components/blocks/fau-big-button/big-button.php';

/**
 * Get target group settings from the FAU blog
 *
 * @return array Array of target group data
 */
function faue_get_target_groups_from_fau_blog() {
    $target_groups = array();
    
    // If not multisite, get from current blog
    if (!is_multisite()) {
        for ($i = 1; $i <= 4; $i++) {
            $target_groups[] = array(
                'title' => get_theme_mod('target_section' . $i . '_title', ''),
                'description' => get_theme_mod('target_section' . $i . '_description', ''),
                'link' => get_theme_mod('target_section' . $i . '_link', '')
            );
        }
        return $target_groups;
    }

    // Find the FAU blog
    $fau_blog_id = faue_get_fau_blog_id();
    if (!$fau_blog_id) {
        // If no FAU blog found, use default fallback values based on locale
        $locale = get_locale();
        $is_german = (strpos($locale, 'de') === 0);
        $default_key = $is_german ? 'faue_target_groups_de' : 'faue_target_groups_en';
        $default_groups = faue_get_default($default_key);
        
        if ($default_groups && is_array($default_groups)) {
            return $default_groups;
        }
        
        return $target_groups;
    }

    // Switch to FAU blog and get settings
    switch_to_blog($fau_blog_id);
    try {
        for ($i = 1; $i <= 4; $i++) {
            $target_groups[] = array(
                'title' => get_theme_mod('target_section' . $i . '_title', ''),
                'description' => get_theme_mod('target_section' . $i . '_description', ''),
                'link' => get_theme_mod('target_section' . $i . '_link', '')
            );
        }
    } finally {
        // Always restore the current blog
        restore_current_blog();
    }

    return $target_groups;
}

/**
 * Render footer target groups with big button styling
 *
 * @param array $target_groups Array of target group data
 * @param string $variant Button variant (filled, outline)
 * @param string $size Button size (small, large)
 * @return string HTML output
 */
function render_footer_target_groups($target_groups = [], $variant = 'outline', $size = 'small') {
    if (empty($target_groups)) {
        return '';
    }

    $items = [];
    foreach ($target_groups as $group) {
        if (!empty($group['title'])) {
            $items[] = [
                'title' => $group['title'],
                'excerpt' => $group['description'],
                'url' => !empty($group['link']) ? $group['link'] : '#'
                // No faculty_color set - will always use FAU blue colors
            ];
        }
    }

    $options = [
        'variant' => $variant,
        'teaser_size' => $size,
        'force_fau_colors' => true,       
        'max_items' => count($items)
    ];

    // Use the shared rendering function
    return render_big_button_html($items, $options);
} 