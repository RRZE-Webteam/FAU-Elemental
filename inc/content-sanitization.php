<?php
/**
 * Content Sanitization Functions
 * Removes problematic inline styles from Classic Editor content
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Remove width-related and other problematic inline styles from content
 * This prevents images and other elements from overflowing on mobile devices
 * 
 * This function selectively removes width/height properties from inline styles,
 * similar to how the old theme removed text-* properties. This is safer than
 * removing all inline styles, as Block Editor blocks may legitimately use them.
 *
 * @param string $content The post content
 * @return string Filtered content
 */
function fau_elemental_remove_inline_styles_from_content($content) {
    $sanitize_inline_styles = get_theme_mod('advanced_sanitize_inlinestyles', true);

    if (!$sanitize_inline_styles) {
        return $content;
    }

    $content = preg_replace_callback('/<(img|div|span|p|h1|h2|h3|h4|h5|h6|a|figure|figcaption)\b([^>]*?)\sstyle="([^"]*)"\s?([^>]*)>/i', function ($matches) {
        $tag = $matches[1];
        $style_content = $matches[3];

        if ($tag === 'img') {
            $new_style = preg_replace('/(width|max-width|min-width|height|max-height|min-height)\s*:\s*[^;]+;?\s*/i', '', $style_content);
        } else {
            $new_style = preg_replace('/(width|max-width|min-width)\s*:\s*[^;]+;?\s*/i', '', $style_content);
        }

        if (trim($new_style) === '') {
            return "<{$matches[1]}{$matches[2]}{$matches[4]}>";
        } else {
            return "<{$matches[1]}{$matches[2]} style=\"" . esc_attr(trim($new_style)) . "\"{$matches[4]}>";
        }
    }, $content);

    $content = preg_replace('/\sstyle="\s*"/i', '', $content);
    return $content;
}

add_filter('the_content', 'fau_elemental_remove_inline_styles_from_content', 10);
add_filter('content_edit_pre', 'fau_elemental_remove_inline_styles_from_content', 10);
add_filter('the_editor_content', 'fau_elemental_remove_inline_styles_from_content', 10);

