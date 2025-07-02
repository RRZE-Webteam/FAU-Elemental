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
        'faculty_color' => 'fau.de',        
        'max_items' => count($items)
    ];

    // Use the shared rendering function
    return render_big_button_html($items, $options);
} 