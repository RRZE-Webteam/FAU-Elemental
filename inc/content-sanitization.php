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

    if (empty($content) || !is_string($content)) {
        return $content;
    }

    $content = preg_replace_callback('/<(img|div|span|p|h1|h2|h3|h4|h5|h6|a|figure|figcaption)\b([^>]*)>/i', function ($matches) {
        $tag = $matches[1];
        $all_attributes = $matches[2];
        
        if (preg_match('/style\s*=\s*["\']([^"\']*)["\']/i', $all_attributes, $style_matches)) {
            $style_content = $style_matches[1];
            $original_style = $style_matches[0];
            
            if ($tag === 'img' || $tag === 'figure') {
                $properties_to_remove = ['width', 'max-width', 'min-width', 'height', 'max-height', 'min-height'];
            } else {
                $properties_to_remove = ['width', 'max-width', 'min-width'];
            }
            
            $new_style = $style_content;
            foreach ($properties_to_remove as $prop) {
                $new_style = preg_replace('/\s*' . preg_quote($prop, '/') . '\s*:\s*[^;]+;?\s*/i', '', $new_style);
                $new_style = preg_replace('/\s*' . preg_quote($prop, '/') . '\s*:\s*[^;]+/i', '', $new_style);
            }
            
            $new_style = trim($new_style);
            $new_style = preg_replace('/;\s*;/', ';', $new_style);
            $new_style = preg_replace('/^\s*;\s*/', '', $new_style);
            $new_style = preg_replace('/\s*;\s*$/', '', $new_style);
            $new_style = trim($new_style);
            
            if (empty($new_style)) {
                $all_attributes = preg_replace('/\s*style\s*=\s*["\'][^"\']*["\']/i', '', $all_attributes);
            } else {
                $all_attributes = preg_replace('/\s*style\s*=\s*["\'][^"\']*["\']/i', ' style="' . esc_attr($new_style) . '"', $all_attributes);
            }
        }
        
        $all_attributes = preg_replace('/\s+/', ' ', $all_attributes);
        $all_attributes = trim($all_attributes);
        
        return "<{$tag}" . ($all_attributes ? " {$all_attributes}" : '') . ">";
    }, $content);

    $content = preg_replace('/\s+style\s*=\s*["\']\s*["\']/i', '', $content);
    $content = preg_replace('/style\s*=\s*["\']\s*["\']/i', '', $content);
    
    $content = preg_replace_callback('/<(img|figure)\b([^>]*)>/i', function ($matches) {
        $tag = $matches[1];
        $attributes = $matches[2];
        
        $attributes = preg_replace('/\s+width\s*=\s*["\'][^"\']*["\']/i', '', $attributes);
        $attributes = preg_replace('/width\s*=\s*["\'][^"\']*["\']/i', '', $attributes);
        $attributes = preg_replace('/\s+height\s*=\s*["\'][^"\']*["\']/i', '', $attributes);
        $attributes = preg_replace('/height\s*=\s*["\'][^"\']*["\']/i', '', $attributes);
        $attributes = preg_replace('/\s+/', ' ', $attributes);
        $attributes = trim($attributes);
        
        return "<{$tag}" . ($attributes ? " {$attributes}" : '') . ">";
    }, $content);

    $content = preg_replace_callback('/\[(caption|wp_caption)\b([^\]]*)\]/i', function ($matches) {
        $tag = $matches[1];
        $attr_string = $matches[2];
        $attrs = shortcode_parse_atts($attr_string);

        if (empty($attrs) || !isset($attrs['width'])) {
            return $matches[0];
        }

        unset($attrs['width']);
        $rebuilt = '';

        foreach ($attrs as $key => $value) {
            if (is_array($value)) {
                continue;
            }
            $rebuilt .= ' ' . $key . '="' . esc_attr($value) . '"';
        }

        return '[' . $tag . ($rebuilt ? $rebuilt : '') . ']';
    }, $content);
    
    return $content;
}

add_filter('the_content', 'fau_elemental_remove_inline_styles_from_content', PHP_INT_MAX);
add_filter('content_save_pre', 'fau_elemental_remove_inline_styles_from_content', 10);
add_filter('content_edit_pre', 'fau_elemental_remove_inline_styles_from_content', 10);
add_filter('the_editor_content', 'fau_elemental_remove_inline_styles_from_content', 10);
add_filter('the_excerpt', 'fau_elemental_remove_inline_styles_from_content', PHP_INT_MAX);

/**
 * Force WordPress caption shortcodes to render without inline width styles.
 *
 * The core caption handler adds `style="width: ...px"` whenever a width
 * attribute is present. Returning zero from the `img_caption_shortcode_width`
 * filter prevents that inline style from being added while keeping the
 * HTML structure intact.
 *
 * @param int    $width   Calculated caption width.
 * @param array  $atts    Caption shortcode attributes.
 * @param string $content Caption inner content.
 * @return int Filtered width value.
 */
function fau_elemental_force_caption_width($width, $atts, $content) {
    $sanitize_inline_styles = get_theme_mod('advanced_sanitize_inlinestyles', true);

    if (!$sanitize_inline_styles) {
        return $width;
    }

    return 0;
}
add_filter('img_caption_shortcode_width', 'fau_elemental_force_caption_width', PHP_INT_MAX, 3);

/**
 * Override the caption shortcode output to ensure no inline width styles are emitted.
 *
 * This mirrors WordPress core's markup but omits the inline width attribute entirely.
 *
 * @param string $output  Existing shortcode output (empty by default).
 * @param array  $attr    Shortcode attributes.
 * @param string $content Inner content containing the image.
 * @return string
 */
function fau_elemental_override_caption_shortcode_output($output, $attr, $content) {
    if (!get_theme_mod('advanced_sanitize_inlinestyles', true)) {
        return $output;
    }

    if (!isset($attr['caption'])) {
        if (preg_match('#((?:<a [^>]+>\s*)?<img [^>]+>(?:\s*</a>)?)(.*)#is', $content, $matches)) {
            $content         = $matches[1];
            $attr['caption'] = trim($matches[2]);
        }
    } elseif (false !== strpos($attr['caption'], '<')) {
        $attr['caption'] = wp_kses($attr['caption'], 'post');
    }

    $atts = shortcode_atts(
        [
            'id'         => '',
            'caption_id' => '',
            'align'      => 'alignnone',
            'width'      => '',
            'caption'    => '',
            'class'      => '',
        ],
        $attr,
        'caption'
    );

    $atts['width'] = (int) $atts['width'];

    if ($atts['width'] < 1 || empty($atts['caption'])) {
        return do_shortcode($content);
    }

    $id          = '';
    $caption_id  = '';
    $describedby = '';

    if ($atts['id']) {
        $atts['id'] = sanitize_html_class($atts['id']);
        $id         = 'id="' . esc_attr($atts['id']) . '" ';
    }

    if ($atts['caption_id']) {
        $atts['caption_id'] = sanitize_html_class($atts['caption_id']);
    } elseif ($atts['id']) {
        $atts['caption_id'] = 'caption-' . str_replace('_', '-', $atts['id']);
    }

    if ($atts['caption_id']) {
        $caption_id  = 'id="' . esc_attr($atts['caption_id']) . '" ';
        $describedby = 'aria-describedby="' . esc_attr($atts['caption_id']) . '" ';
    }

    $class   = trim('wp-caption ' . $atts['align'] . ' ' . $atts['class']);
    $html5   = current_theme_supports('html5', 'caption');
    $inner   = do_shortcode($content);
    $caption = wp_kses_post($atts['caption']);

    if ($html5) {
        $html = sprintf(
            '<figure %s%sclass="%s">%s%s</figure>',
            $id,
            $describedby,
            esc_attr($class),
            $inner,
            sprintf('<figcaption %sclass="wp-caption-text">%s</figcaption>', $caption_id, $caption)
        );
    } else {
        $html = sprintf(
            '<div %sclass="%s">%s%s</div>',
            $id,
            esc_attr($class),
            str_replace('<img ', '<img ' . $describedby, $inner),
            sprintf('<p %sclass="wp-caption-text">%s</p>', $caption_id, $caption)
        );
    }

    return $html;
}
/**
 * Register custom caption shortcodes that never emit inline width styles.
 */
function fau_elemental_register_caption_shortcodes() {
    remove_shortcode('caption');
    remove_shortcode('wp_caption');
    add_shortcode('caption', 'fau_elemental_caption_shortcode');
    add_shortcode('wp_caption', 'fau_elemental_caption_shortcode');
}
add_action('init', 'fau_elemental_register_caption_shortcodes', 20);

/**
 * Render the caption shortcode without inline width styling.
 *
 * @param array  $attr    Shortcode attributes.
 * @param string $content Inner content.
 * @return string
 */
function fau_elemental_caption_shortcode($attr, $content = '') {
    if (!isset($attr['caption'])) {
        if (preg_match('#((?:<a [^>]+>\s*)?<img [^>]+>(?:\s*</a>)?)(.*)#is', $content, $matches)) {
            $content         = $matches[1];
            $attr['caption'] = trim($matches[2]);
        }
    } elseif (false !== strpos($attr['caption'], '<')) {
        $attr['caption'] = wp_kses($attr['caption'], 'post');
    }

    $atts = shortcode_atts(
        [
            'id'         => '',
            'caption_id' => '',
            'align'      => 'alignnone',
            'width'      => '',
            'caption'    => '',
            'class'      => '',
        ],
        $attr,
        'caption'
    );

    $atts['width'] = (int) $atts['width'];

    if ($atts['width'] < 1 || empty($atts['caption'])) {
        return do_shortcode($content);
    }

    $id          = '';
    $caption_id  = '';
    $describedby = '';

    if ($atts['id']) {
        $atts['id'] = sanitize_html_class($atts['id']);
        $id         = 'id="' . esc_attr($atts['id']) . '" ';
    }

    if ($atts['caption_id']) {
        $atts['caption_id'] = sanitize_html_class($atts['caption_id']);
    } elseif ($atts['id']) {
        $atts['caption_id'] = 'caption-' . str_replace('_', '-', $atts['id']);
    }

    if ($atts['caption_id']) {
        $caption_id  = 'id="' . esc_attr($atts['caption_id']) . '" ';
        $describedby = 'aria-describedby="' . esc_attr($atts['caption_id']) . '" ';
    }

    $class   = trim('wp-caption ' . $atts['align'] . ' ' . $atts['class']);
    $html5   = current_theme_supports('html5', 'caption');
    $inner   = do_shortcode($content);
    $caption = wp_kses_post($atts['caption']);

    if ($html5) {
        $html = sprintf(
            '<figure %s%sclass="%s">%s%s</figure>',
            $id,
            $describedby,
            esc_attr($class),
            $inner,
            sprintf('<figcaption %sclass="wp-caption-text">%s</figcaption>', $caption_id, $caption)
        );
    } else {
        $html = sprintf(
            '<div %sclass="%s">%s%s</div>',
            $id,
            esc_attr($class),
            str_replace('<img ', '<img ' . $describedby, $inner),
            sprintf('<p %sclass="wp-caption-text">%s</p>', $caption_id, $caption)
        );
    }

    return $html;
}

/**
 * Sanitize rendered caption shortcode output to strip any remaining inline styles.
 *
 * Even if other plugins/themes modify the caption markup later, running the
 * output through our sanitizer guarantees no width/height styles leak through.
 *
 * @param string $output Rendered shortcode output.
 * @param string $tag    Shortcode tag name.
 * @param array  $attr   Shortcode attributes.
 * @return string Filtered output.
 */
function fau_elemental_sanitize_caption_shortcode_output($output, $tag, $attr, $m) {
    if (empty($output)) {
        return $output;
    }

    if (!in_array($tag, ['caption', 'wp_caption'], true)) {
        return $output;
    }

    $sanitize_inline_styles = get_theme_mod('advanced_sanitize_inlinestyles', true);

    if (!$sanitize_inline_styles) {
        return $output;
    }

    return fau_elemental_remove_inline_styles_from_content($output);
}
add_filter('do_shortcode_tag', 'fau_elemental_sanitize_caption_shortcode_output', PHP_INT_MAX, 4);

