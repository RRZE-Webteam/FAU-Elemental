<?php
/**
 * Server-side rendering of the FAU Big Button Teaser Group block.
 *
 * @package FAU-Elemental
 * 
 * @var array    $attributes Block attributes.
 * @var string   $content    Block default content.
 * @var WP_Block $block      Block instance.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Ensure the shared rendering function is available
require_once get_template_directory() . '/components/blocks/fau-big-button/big-button.php';

/**
 * Renders the FAU Big Button Teaser Group block on the server.
 *
 * @param array $attributes Block attributes.
 * @param string $content Block default content.
 * @param WP_Block $block Block instance.
 * @return string Returns the block content.
 */
function render_block_fau_big_button($attributes, $content, $block) {
    // Set default attributes
    $attributes = wp_parse_args($attributes, [
        'teaserSize' => 'small',
        'variant' => 'filled',
        'items' => []
    ]);

    // Sanitize attributes
    $teaser_size = sanitize_text_field($attributes['teaserSize']);
    $variant = sanitize_text_field($attributes['variant']);
    $items = $attributes['items'];

    // Detect if dark style is applied
    $is_dark_style = false;
    if (isset($block) && isset($block->parsed_block['attrs']['className'])) {
        $is_dark_style = strpos($block->parsed_block['attrs']['className'], 'is-style-dark') !== false;
    }

    // Add wrapper classes
    $base_classes = 'fau-big-button-teaser-group fau-big-button-teaser-group--' . $teaser_size . ' fau-big-button-teaser-group--' . $variant;
    $base_classes .= $is_dark_style ? ' fau-big-button-teaser-group--dark' : ' fau-big-button-teaser-group--light';
    $base_classes .= ' fau-big-button-teaser-group--faculty-showcase';
    
    $wrapper_attributes = get_block_wrapper_attributes([
        'class' => $base_classes
    ]);

    // Get items data
    $items_data = [];
    
    if (!empty($items) && is_array($items)) {
        foreach ($items as $item) {
            $items_data[] = [
                'title' => sanitize_text_field($item['title'] ?? ''),
                'excerpt' => sanitize_textarea_field($item['description'] ?? ''),
                'url' => esc_url_raw($item['url'] ?? ''),
                'faculty_color' => sanitize_text_field($item['facultyColor'] ?? 'default')
            ];
        }
    }

    // Prepare options for shared rendering function
    $options = [
        'teaser_size' => $teaser_size,
        'variant' => $variant,
        'is_dark_style' => $is_dark_style,
        'wrapper_attributes' => $wrapper_attributes,
        'faculty_showcase' => true
    ];

    return render_big_button_html($items_data, $options);
}

echo render_block_fau_big_button($attributes, $content, $block);
