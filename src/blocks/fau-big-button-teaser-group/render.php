<?php
/**
 * Server-side rendering of the FAU Big Button Teaser Group block.
 *
 * @package FAU-Elemental
 */

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

    // Generate CSS classes
    $css_classes = [
        'fau-big-button-teaser-group',
        'fau-big-button-teaser-group--' . $teaser_size,
        'fau-big-button-teaser-group--' . $variant
    ];

    if ($faculty_color !== 'default') {
        $css_classes[] = 'fau-big-button-teaser-group--' . $faculty_color;
    }

    // Add dark mode class if dark style is applied
    if ($is_dark_style) {
        $css_classes[] = 'fau-big-button-teaser-group--dark';
    } else {
        // Add light mode class as default
        $css_classes[] = 'fau-big-button-teaser-group--light';
    }

    // Add wrapper classes if they exist
    if (isset($block->context['postId'])) {
        $wrapper_attributes = get_block_wrapper_attributes([
            'class' => implode(' ', $css_classes)
        ]);
    } else {
        $wrapper_attributes = 'class="' . implode(' ', $css_classes) . '"';
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
                'id' => $page->ID,
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
                    'id' => $page->ID,
                    'title' => get_the_title($page->ID),
                    'excerpt' => get_the_excerpt($page->ID),
                    'url' => get_permalink($page->ID)
                ];
            }
        }
    }

    // Start building the output
    ob_start();
    ?>
    <div <?php echo $wrapper_attributes; ?>>
        <?php if ($show_roof_line && !empty($roof_line)) : ?>
            <div class="fau-big-button-teaser-group__roof-line">
                <?php echo esc_html($roof_line); ?>
            </div>
        <?php endif; ?>

        <?php if ($show_headline && !empty($headline)) : ?>
            <h2 class="fau-big-button-teaser-group__headline">
                <?php echo esc_html($headline); ?>
            </h2>
        <?php endif; ?>

        <?php if ($show_teaser_text && !empty($teaser_text)) : ?>
            <div class="fau-big-button-teaser-group__teaser-text">
                <?php echo wp_kses_post(wpautop($teaser_text)); ?>
            </div>
        <?php endif; ?>

        <div class="fau-big-button-teaser-group__buttons">
            <?php 
            // Display selected pages as buttons
            foreach (array_slice($pages, 0, $number_of_buttons) as $page) : 
                $page_title = esc_html($page['title']);
                $page_excerpt = wp_strip_all_tags($page['excerpt']);
                $page_url = esc_url($page['url']);
                
                if (!empty($page_title) && !empty($page_url)) :
            ?>
                <div class="fau-big-button-teaser-group__button">
                    <a href="<?php echo $page_url; ?>" class="fau-big-button-teaser-group__button-link">
                        <div class="fau-big-button-teaser-group__button-content">
                            <h3 class="fau-big-button-teaser-group__button-title">
                                <?php echo $page_title; ?>
                            </h3>
                            
                            <?php if (!empty($page_excerpt)) : ?>
                                <div class="fau-big-button-teaser-group__button-text">
                                    <?php echo esc_html(wp_trim_words($page_excerpt, 20, '...')); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </a>
                </div>
            <?php 
                endif;
            endforeach; 
            ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
} 