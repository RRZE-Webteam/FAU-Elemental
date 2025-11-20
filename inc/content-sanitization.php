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

    $content = preg_replace_callback('/<(img|div|span|p|h1|h2|h3|h4|h5|h6|a|figure|figcaption)\b([^>]*?)\s+style\s*=\s*"([^"]*)"([^>]*)>/i', function ($matches) {
        $tag = $matches[1];
        $before_style = $matches[2];
        $style_content = $matches[3];
        $after_style = $matches[4];

        if ($tag === 'img' || $tag === 'figure') {
            $new_style = preg_replace('/(width|max-width|min-width|height|max-height|min-height)\s*:\s*[^;]+;?\s*/i', '', $style_content);
        } else {
            $new_style = preg_replace('/(width|max-width|min-width)\s*:\s*[^;]+;?\s*/i', '', $style_content);
        }

        if (trim($new_style) === '') {
            return "<{$tag}{$before_style}{$after_style}>";
        } else {
            return "<{$tag}{$before_style} style=\"" . esc_attr(trim($new_style)) . "\"{$after_style}>";
        }
    }, $content);

    $content = preg_replace('/\sstyle="\s*"/i', '', $content);
    
    $content = preg_replace('/<(img|figure)\b([^>]*?)\s+width\s*=\s*"[^"]*"([^>]*)>/i', '<$1$2$3>', $content);
    $content = preg_replace('/<(img|figure)\b([^>]*?)\s+height\s*=\s*"[^"]*"([^>]*)>/i', '<$1$2$3>', $content);
    
    return $content;
}

add_filter('the_content', 'fau_elemental_remove_inline_styles_from_content', 10);
add_filter('content_edit_pre', 'fau_elemental_remove_inline_styles_from_content', 10);
add_filter('the_editor_content', 'fau_elemental_remove_inline_styles_from_content', 10);

