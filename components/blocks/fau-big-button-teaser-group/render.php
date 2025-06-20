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

if ( ! function_exists( 'render_big_button_teaser_group_html' ) ) {
    /**
     * Shared rendering function for big button teaser groups
     *
     * @param array $items Array of items with 'title', 'excerpt', 'url' keys, and optionally 'faculty_color' for individual colors
     * @param array $options Rendering options
     * @return string HTML output
     */
    function render_big_button_teaser_group_html($items, $options = []) {
        // Set default options
        $defaults = [
            'headline' => '',
            'show_headline' => false,
            'teaser_text' => '',
            'show_teaser_text' => false,
            'faculty_color' => 'default',
            'teaser_size' => 'small',
            'variant' => 'filled',
            'is_dark_style' => false,
            'wrapper_attributes' => '',
            'max_items' => 3,
            'faculty_showcase' => false // New option for faculty showcase mode
        ];
        $options = wp_parse_args($options, $defaults);

        // Generate CSS classes
        $css_classes = [
            'fau-big-button-teaser-group',
            'fau-big-button-teaser-group--' . $options['teaser_size'],
            'fau-big-button-teaser-group--' . $options['variant']
        ];

        // Only add global faculty color if not in faculty showcase mode
        if (!$options['faculty_showcase'] && $options['faculty_color'] !== 'default') {
            $css_classes[] = 'fau-big-button-teaser-group--' . $options['faculty_color'];
        }

        // Add dark/light mode class
        if ($options['is_dark_style']) {
            $css_classes[] = 'fau-big-button-teaser-group--dark';
        } else {
            $css_classes[] = 'fau-big-button-teaser-group--light';
        }

        // Add faculty showcase class if enabled
        if ($options['faculty_showcase']) {
            $css_classes[] = 'fau-big-button-teaser-group--faculty-showcase';
        }

        // Use provided wrapper attributes or generate default
        $wrapper_attributes = !empty($options['wrapper_attributes']) 
            ? $options['wrapper_attributes']
            : 'class="' . implode(' ', $css_classes) . '"';

        // Start building the output
        ob_start();
        ?>
        <div <?php echo $wrapper_attributes; ?>>
            <?php if ($options['show_headline'] && !empty($options['headline'])) : ?>
                <h2 class="fau-big-button-teaser-group__headline">
                    <?php echo esc_html($options['headline']); ?>
                </h2>
            <?php endif; ?>

            <?php if ($options['show_teaser_text'] && !empty($options['teaser_text'])) : ?>
                <div class="fau-big-button-teaser-group__teaser-text">
                    <?php echo wp_kses_post(wpautop($options['teaser_text'])); ?>
                </div>
            <?php endif; ?>

            <div class="fau-big-button-teaser-group__buttons">
                <?php 
                // Display items as buttons (limit to max_items)
                foreach (array_slice($items, 0, $options['max_items']) as $item) : 
                    $title = esc_html($item['title']);
                    $excerpt = wp_strip_all_tags($item['excerpt']);
                    $url = esc_url($item['url']);
                    
                    // Generate button classes - include individual faculty color if in showcase mode
                    $button_classes = ['fau-big-button-teaser-group__button'];
                    if ($options['faculty_showcase'] && !empty($item['faculty_color']) && $item['faculty_color'] !== 'default') {
                        $button_classes[] = 'fau-big-button-teaser-group__button--' . $item['faculty_color'];
                    }
                    
                    if (!empty($title) && !empty($url)) :
                ?>
                    <a href="<?php echo $url; ?>" class="<?php echo implode(' ', $button_classes); ?>">
                        <h3>
                            <?php echo $title; ?>
                        </h3>
                        <?php if (!empty($excerpt)) : ?>
                            <p>
                                <?php echo esc_html(wp_trim_words($excerpt, 9, '...')); ?>
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

if ( ! function_exists( 'render_block_fau_big_button_teaser_group' ) ) {
    /**
     * Renders the FAU Big Button Teaser Group block on the server.
     *
     * @param array $attributes Block attributes.
     * @param string $content Block default content.
     * @param WP_Block $block Block instance.
     * @return string Returns the block content.
     */
    function render_block_fau_big_button_teaser_group($attributes, $content, $block) {
        // Set default attributes
        $attributes = wp_parse_args($attributes, [
            'headline' => '',
            'showHeadline' => false,
            'teaserText' => '',
            'showTeaserText' => false,
            'facultyColor' => 'default',
            'teaserSize' => 'small',
            'variant' => 'filled',
            'numberOfButtons' => 3,
            'selectedPages' => [],
            'showAllPages' => false,
            'showFaculties' => false, // New attribute for faculty showcase mode
            'facultyItems' => [] // New attribute for individual faculty items
        ]);

        // Sanitize attributes
        $headline = sanitize_text_field($attributes['headline']);
        $show_headline = (bool) $attributes['showHeadline'];
        $teaser_text = sanitize_textarea_field($attributes['teaserText']);
        $show_teaser_text = (bool) $attributes['showTeaserText'];
        $faculty_color = sanitize_text_field($attributes['facultyColor']);
        $teaser_size = sanitize_text_field($attributes['teaserSize']);
        $variant = sanitize_text_field($attributes['variant']);
        $number_of_buttons = absint($attributes['numberOfButtons']);
        $selected_pages = $attributes['selectedPages'];
        $show_all_pages = (bool) $attributes['showAllPages'];
        $show_faculties = (bool) $attributes['showFaculties'];
        $faculty_items = $attributes['facultyItems'];

        // Detect if dark style is applied
        $is_dark_style = false;
        if (isset($block) && isset($block->parsed_block['attrs']['className'])) {
            $is_dark_style = strpos($block->parsed_block['attrs']['className'], 'is-style-dark') !== false;
        }

        // Add wrapper classes if they exist
        $base_classes = 'fau-big-button-teaser-group fau-big-button-teaser-group--' . $teaser_size . ' fau-big-button-teaser-group--' . $variant;
        
        // Only add global faculty color if not in faculty showcase mode
        if (!$show_faculties && $faculty_color !== 'default') {
            $base_classes .= ' fau-big-button-teaser-group--' . $faculty_color;
        }
        
        $base_classes .= $is_dark_style ? ' fau-big-button-teaser-group--dark' : ' fau-big-button-teaser-group--light';
        
        // Add faculty showcase class if enabled
        if ($show_faculties) {
            $base_classes .= ' fau-big-button-teaser-group--faculty-showcase';
        }
        
        $wrapper_attributes = get_block_wrapper_attributes([
            'class' => $base_classes
        ]);

        // Get data based on mode
        $items = [];
        
        if ($show_faculties && !empty($faculty_items) && is_array($faculty_items)) {
            // Faculty showcase mode - use individual faculty items
            foreach (array_slice($faculty_items, 0, $number_of_buttons) as $faculty_item) {
                $items[] = [
                    'title' => sanitize_text_field($faculty_item['title'] ?? ''),
                    'excerpt' => sanitize_textarea_field($faculty_item['description'] ?? ''),
                    'url' => esc_url_raw($faculty_item['url'] ?? ''),
                    'faculty_color' => sanitize_text_field($faculty_item['facultyColor'] ?? 'default')
                ];
            }
        } elseif ($show_all_pages) {
            // Get all published pages
            $pages_query = get_posts([
                'post_type' => 'page',
                'post_status' => 'publish',
                'posts_per_page' => $number_of_buttons, // Limit to numberOfButtons
                'orderby' => 'title',
                'order' => 'ASC'
            ]);
            
            foreach ($pages_query as $page) {
                $items[] = [
                    'title' => get_the_title($page->ID),
                    'excerpt' => get_the_excerpt($page->ID),
                    'url' => get_permalink($page->ID)
                ];
            }
        } elseif (!empty($selected_pages) && is_array($selected_pages)) {
            // Use manually selected pages
            $page_ids = array_slice(array_column($selected_pages, 'id'), 0, $number_of_buttons);
            
            if (!empty($page_ids)) {
                $pages_query = get_posts([
                    'post_type' => 'page',
                    'post_status' => 'publish',
                    'include' => $page_ids,
                    'orderby' => 'post__in',
                    'numberposts' => $number_of_buttons
                ]);
                
                foreach ($pages_query as $page) {
                    $items[] = [
                        'title' => get_the_title($page->ID),
                        'excerpt' => get_the_excerpt($page->ID),
                        'url' => get_permalink($page->ID)
                    ];
                }
            }
        }

        // Prepare options for shared rendering function
        $options = [
            'headline' => $headline,
            'show_headline' => $show_headline,
            'teaser_text' => $teaser_text,
            'show_teaser_text' => $show_teaser_text,
            'faculty_color' => $faculty_color,
            'teaser_size' => $teaser_size,
            'variant' => $variant,
            'is_dark_style' => $is_dark_style,
            'wrapper_attributes' => $wrapper_attributes,
            'max_items' => $number_of_buttons,
            'faculty_showcase' => $show_faculties
        ];

        return render_big_button_teaser_group_html($items, $options);
    }
}

echo render_block_fau_big_button_teaser_group($attributes, $content, $block);