<?php
/**
 * Shared big button functions for the `fau-elemental/fau-big-button` block.
 *
 * @package FAU_Elemental
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Character limits for the big button block (matching frontend behavior)
define('FAU_BIG_BUTTON_DESCRIPTION_MAX_LENGTH', 80); // Only descriptions are trimmed in frontend

/**
 * Trim text by characters while respecting word boundaries
 *
 * @param string $text The text to trim
 * @param int $max_chars Maximum number of characters
 * @param string $more Trailing text
 * @return string Trimmed text
 */
function fau_trim_text_big_button($text, $max_chars = 80, $more = '…') {
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

/**
 * Shared rendering function for big button teaser groups
 *
 * @param array $items Array of items with 'title', 'excerpt', 'url' keys, and optionally 'faculty_color' for individual colors
 * @param array $options {
 *     Rendering options for the big button group.
 *
 *     @type string $teaser_size       Size of the buttons. Accepts 'small' or 'large'. Default 'small'.
 *     @type string $variant           Button style variant. Accepts 'filled' or 'outline'. Default 'filled'.
 *     @type bool   $is_dark_style     Whether to apply dark style. Default false.
 *     @type string $wrapper_attributes HTML attributes for the wrapper element. Default empty.
 *     @type bool   $faculty_showcase  Whether to display as faculty showcase. Default true.
 *     @type bool   $force_fau_colors  Whether to force default FAU colors, ignoring faculty-specific colors. Default false.
 * }
 * @return string HTML output
 */
function render_big_button_html($items, $options = []) {
    // Set default options
    $defaults = [
        'teaser_size'        => 'small',
        'variant'            => 'filled',
        'is_dark_style'      => false,
        'wrapper_attributes' => '',
        'faculty_showcase'   => true,
        'force_fau_colors'   => false
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
                
                if ($options['force_fau_colors']) {
                    // When force_fau_colors is true, always use FAU colors (no faculty-specific colors)
                    // This effectively means we don't add any faculty color class
                    $effective_faculty_color = null;
                } elseif ($website_type === 'fau') {
                    // For fau.de websites, use individual item color if set
                    if (!empty($item['faculty_color']) && $item['faculty_color'] !== 'default') {
                        $effective_faculty_color = $item['faculty_color'];
                    }
                } elseif ($website_type === 'faculty' || $website_type === 'chair') {
                    // For faculty websites, use the website's faculty type
                    $effective_faculty_color = $faculty_type;
                }
                // For other website types (chair, other, cooperation), no faculty color
                
                if ($effective_faculty_color) {
                    $button_classes[] = 'fau-big-button-teaser-group__button--' . $effective_faculty_color;
                }
                
                if (!empty($title) && !empty($url)) :
            ?>
                <a href="<?php echo esc_url($url); ?>" class="<?php echo implode(' ', $button_classes); ?>" role="button">
                    <p class="big-button-title">
                        <?php echo $title; ?>
                    </p>
                    <?php if (!empty($excerpt)) : ?>
                        <p>
                            <?php echo esc_html(fau_trim_text_big_button($excerpt, FAU_BIG_BUTTON_DESCRIPTION_MAX_LENGTH, '…')); ?>
                        </p>
                    <?php endif; ?>
                    <span class="arrow-link" aria-hidden="true"></span>
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
