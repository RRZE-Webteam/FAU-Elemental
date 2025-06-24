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

// Ensure WordPress functions are available
if ( ! function_exists( 'wp_strip_all_tags' ) ) {
    function wp_strip_all_tags( $string ) {
        return strip_tags( $string );
    }
}

if ( ! function_exists( 'wp_parse_args' ) ) {
    function wp_parse_args( $args, $defaults = array() ) {
        return is_array( $args ) ? array_merge( $defaults, $args ) : $defaults;
    }
}

if ( ! function_exists( 'get_theme_mod' ) ) {
    function get_theme_mod( $name, $default = false ) {
        return $default;
    }
}

if ( ! function_exists( 'esc_html' ) ) {
    function esc_html( $text ) {
        return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
    }
}

if ( ! function_exists( 'esc_url' ) ) {
    function esc_url( $url ) {
        return filter_var( $url, FILTER_VALIDATE_URL ) ? $url : '';
    }
}

if ( ! function_exists( 'esc_url_raw' ) ) {
    function esc_url_raw( $url ) {
        return filter_var( $url, FILTER_VALIDATE_URL ) ? $url : '';
    }
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
    function sanitize_text_field( $str ) {
        return trim( strip_tags( $str ) );
    }
}

if ( ! function_exists( 'sanitize_textarea_field' ) ) {
    function sanitize_textarea_field( $str ) {
        return trim( strip_tags( $str ) );
    }
}

if ( ! function_exists( 'get_block_wrapper_attributes' ) ) {
    function get_block_wrapper_attributes( $extra_attributes = array() ) {
        $attributes = '';
        foreach ( $extra_attributes as $key => $value ) {
            $attributes .= sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( $value ) );
        }
        return trim( $attributes );
    }
}

if ( ! function_exists( 'esc_attr' ) ) {
    function esc_attr( $text ) {
        return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
    }
}

if ( ! function_exists( 'trim_text_smart' ) ) {
    /**
     * Trim text by characters while respecting word boundaries
     *
     * @param string $text The text to trim
     * @param int $max_chars Maximum number of characters
     * @param string $more Trailing text
     * @return string Trimmed text
     */
    function trim_text_smart($text, $max_chars = 80, $more = '...') {
        if (empty($text) || !is_string($text)) {
            return '';
        }
        
        // Remove HTML tags and trim
        $clean_text = trim(wp_strip_all_tags($text));
        
        // If text is already short enough, return as-is
        if (strlen($clean_text) <= $max_chars) {
            return $clean_text;
        }
        
        // Find the last space before the character limit
        $trimmed = substr($clean_text, 0, $max_chars);
        $last_space_pos = strrpos($trimmed, ' ');
        
        // If there's a space, cut at word boundary; otherwise cut at character limit
        $result = $last_space_pos > 0 ? substr($trimmed, 0, $last_space_pos) : $trimmed;
        
        return $result . $more;
    }
}

if ( ! function_exists( 'render_big_button_html' ) ) {
    /**
     * Shared rendering function for big button teaser groups
     *
     * @param array $items Array of items with 'title', 'excerpt', 'url' keys, and optionally 'faculty_color' for individual colors
     * @param array $options Rendering options
     * @return string HTML output
     */
    function render_big_button_html($items, $options = []) {
        // Set default options
        $defaults = [
            'teaser_size' => 'small',
            'variant' => 'filled',
            'is_dark_style' => false,
            'wrapper_attributes' => '',
            'faculty_showcase' => true
        ];
        $options = wp_parse_args($options, $defaults);

        // Generate CSS classes
        $css_classes = [
            'fau-big-button-teaser-group',
            'fau-big-button-teaser-group--' . $options['teaser_size'],
            'fau-big-button-teaser-group--' . $options['variant'],
            'fau-big-button-teaser-group--faculty-showcase'
        ];

        // Add dark/light mode class
        if ($options['is_dark_style']) {
            $css_classes[] = 'fau-big-button-teaser-group--dark';
        } else {
            $css_classes[] = 'fau-big-button-teaser-group--light';
        }

        // Use provided wrapper attributes or generate default
        $wrapper_attributes = !empty($options['wrapper_attributes']) 
            ? $options['wrapper_attributes']
            : 'class="' . implode(' ', $css_classes) . '"';

        // Start building the output
        ob_start();
        ?>
        <div <?php echo $wrapper_attributes; ?>>
            <div class="fau-big-button-teaser-group__buttons">
                <?php 
                // Display items as buttons
                $website_type = get_theme_mod('faue_website_type', 'fau');
                $faculty_type = get_theme_mod('faue_faculty', 'phil');
                
                foreach ($items as $item) : 
                    $title = esc_html($item['title']);
                    $excerpt = wp_strip_all_tags($item['excerpt']);
                    $url = esc_url($item['url']);
                    
                    // Generate button classes - determine faculty color based on website type
                    $button_classes = ['fau-big-button-teaser-group__button'];
                    
                    // Determine the effective faculty color for this item
                    $effective_faculty_color = null;
                    
                    if ($website_type === 'fau') {
                        // For fau.de websites, use individual item color if set
                        if (!empty($item['faculty_color']) && $item['faculty_color'] !== 'default') {
                            $effective_faculty_color = $item['faculty_color'];
                        }
                    } elseif ($website_type === 'faculty') {
                        // For faculty websites, use the website's faculty type
                        $effective_faculty_color = $faculty_type;
                    }
                    // For other website types (chair, other, cooperation), no faculty color
                    
                    if ($effective_faculty_color) {
                        $button_classes[] = 'fau-big-button-teaser-group__button--' . $effective_faculty_color;
                    }
                    
                    if (!empty($title) && !empty($url)) :
                ?>
                    <a href="<?php echo $url; ?>" class="<?php echo implode(' ', $button_classes); ?>">
                        <h3>
                            <?php echo $title; ?>
                        </h3>
                        <?php if (!empty($excerpt)) : ?>
                            <p>
                                <?php echo esc_html(trim_text_smart($excerpt, 80, '...')); ?>
                            </p>
                        <?php endif; ?>
                        <span class="arrow-link"></span>
                    </a>
                <?php 
                    endif;
                endforeach; 
                ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

if ( ! function_exists( 'render_block_fau_big_button' ) ) {
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
}

echo render_block_fau_big_button($attributes, $content, $block);