<?php
/**
 * Server-side rendering of the FAU Big Button Teaser Group block.
 *
 * @package FAU-Elemental
 */

/**
 * Shared rendering function for big button teaser groups
 *
 * @param array $items Array of items with 'title', 'excerpt', 'url' keys
 * @param array $options Rendering options
 * @return string HTML output
 */
function render_big_button_teaser_group_html($items, $options = []) {
    // Set default options
    $defaults = [
        'roof_line' => '',
        'show_roof_line' => false,
        'headline' => '',
        'show_headline' => false,
        'teaser_text' => '',
        'show_teaser_text' => false,
        'faculty_color' => 'default',
        'teaser_size' => 'small',
        'variant' => 'filled',
        'is_dark_style' => false,
        'wrapper_attributes' => '',
        'max_items' => 3
    ];
    $options = wp_parse_args($options, $defaults);

    // Generate CSS classes
    $css_classes = [
        'fau-big-button-teaser-group',
        'fau-big-button-teaser-group--' . $options['teaser_size'],
        'fau-big-button-teaser-group--' . $options['variant']
    ];

    if ($options['faculty_color'] !== 'default') {
        $css_classes[] = 'fau-big-button-teaser-group--' . $options['faculty_color'];
    }

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
        <?php if ($options['show_roof_line'] && !empty($options['roof_line'])) : ?>
            <div class="fau-big-button-teaser-group__roof-line">
                <?php echo esc_html($options['roof_line']); ?>
            </div>
        <?php endif; ?>

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
                
                if (!empty($title) && !empty($url)) :
            ?>
                <a href="<?php echo $url; ?>">
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
        'roofLine' => '',
        'showRoofLine' => false,
        'headline' => '',
        'showHeadline' => false,
        'teaserText' => '',
        'showTeaserText' => false,
        'facultyColor' => 'default',
        'teaserSize' => 'small',
        'variant' => 'filled',
        'numberOfButtons' => 3,
        'selectedPages' => [],
        'showAllPages' => false
    ]);

    // Sanitize attributes
    $roof_line = sanitize_text_field($attributes['roofLine']);
    $show_roof_line = (bool) $attributes['showRoofLine'];
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

    // Detect if dark style is applied
    $is_dark_style = false;
    if (isset($block) && isset($block->parsed_block['attrs']['className'])) {
        $is_dark_style = strpos($block->parsed_block['attrs']['className'], 'is-style-dark') !== false;
    }

    // Add wrapper classes if they exist
    if (isset($block->context['postId'])) {
        $wrapper_attributes = get_block_wrapper_attributes([
            'class' => 'fau-big-button-teaser-group fau-big-button-teaser-group--' . $teaser_size . ' fau-big-button-teaser-group--' . $variant . ($faculty_color !== 'default' ? ' fau-big-button-teaser-group--' . $faculty_color : '') . ($is_dark_style ? ' fau-big-button-teaser-group--dark' : ' fau-big-button-teaser-group--light')
        ]);
    } else {
        $wrapper_attributes = '';
    }

    // Get page data for selected pages
    $pages = [];
    if ($show_all_pages) {
        // Get all published pages
        $pages_query = get_posts([
            'post_type' => 'page',
            'post_status' => 'publish',
            'posts_per_page' => $number_of_buttons, // Limit to numberOfButtons
            'orderby' => 'title',
            'order' => 'ASC'
        ]);
        
        foreach ($pages_query as $page) {
            $pages[] = [
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
                $pages[] = [
                    'title' => get_the_title($page->ID),
                    'excerpt' => get_the_excerpt($page->ID),
                    'url' => get_permalink($page->ID)
                ];
            }
        }
    }

    // Prepare options for shared rendering function
    $options = [
        'roof_line' => $roof_line,
        'show_roof_line' => $show_roof_line,
        'headline' => $headline,
        'show_headline' => $show_headline,
        'teaser_text' => $teaser_text,
        'show_teaser_text' => $show_teaser_text,
        'faculty_color' => $faculty_color,
        'teaser_size' => $teaser_size,
        'variant' => $variant,
        'is_dark_style' => $is_dark_style,
        'wrapper_attributes' => $wrapper_attributes,
        'max_items' => $number_of_buttons
    ];

    return render_big_button_teaser_group_html($pages, $options);
} 