<?php
/**
 * Footer Target Groups Component
 * Uses the same styling as the big button block but with custom target group data
 *
 * @package FAU-Elemental
 */

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

    // Generate CSS classes to match the big button block
    $css_classes = [
        'fau-big-button-teaser-group',
        'fau-big-button-teaser-group--' . $size,
        'fau-big-button-teaser-group--' . $variant,
        'fau-big-button-teaser-group--light' // Default to light mode
    ];

    ob_start();
    ?>
    <div class="<?php echo implode(' ', $css_classes); ?>">
        <div class="fau-big-button-teaser-group__buttons">
            <?php 
            foreach ($target_groups as $group) : 
                if (!empty($group['title'])) :
                    $title = esc_html($group['title']);
                    $description = esc_html($group['description']);
                    $link = !empty($group['link']) ? esc_url($group['link']) : '#';
            ?>
                <a href="<?php echo $link; ?>">
                    <h3><?php echo $title; ?></h3>
                    <?php if (!empty($description)) : ?>
                        <p><?php echo wp_trim_words($description, 9, '...'); ?></p>
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