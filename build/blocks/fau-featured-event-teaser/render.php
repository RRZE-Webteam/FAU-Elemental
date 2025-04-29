<?php
/**
 * Server-side rendering of the `fau-elemental/featured-event-teaser` block.
 *
 * @package FAU_Elemental
 */

/**
 * Renders the `fau-elemental/featured-event-teaser` block on the server.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block default content.
 * @param WP_Block $block      Block instance.
 *
 * @return string Returns the markup for the featured event teaser.
 */
function render_block_fau_featured_event_teaser($attributes, $content, $block) {
    // Debug log
    error_log('render_block_fau_featured_event_teaser called with attributes: ' . print_r($attributes, true));
    
    // Extract attributes
    $subtitle = isset($attributes['subtitle']) ? $attributes['subtitle'] : '';
    $showSubtitle = isset($attributes['showSubtitle']) ? $attributes['showSubtitle'] : false;
    $eventTitle = isset($attributes['eventTitle']) ? $attributes['eventTitle'] : '';
    $eventDescription = isset($attributes['eventDescription']) ? $attributes['eventDescription'] : '';
    $eventDate = isset($attributes['eventDate']) ? $attributes['eventDate'] : '';
    $buttonText = isset($attributes['buttonText']) ? $attributes['buttonText'] : 'Mehr erfahren';
    $buttonUrl = isset($attributes['buttonUrl']) ? $attributes['buttonUrl'] : '#';
    $showImage = isset($attributes['showImage']) ? $attributes['showImage'] : false;
    $imageUrl = isset($attributes['imageUrl']) ? $attributes['imageUrl'] : '';
    $imageAlt = isset($attributes['imageAlt']) ? $attributes['imageAlt'] : '';

    // Split the date into day and month/year
    $dateParts = $eventDate ? explode(' ', $eventDate) : ['01', 'Okt 2024'];
    $day = $dateParts[0];
    $monthYear = implode(' ', array_slice($dateParts, 1));

    // Helper function to escape HTML
    function escape_html($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
    
    // Start output buffering
    ob_start();
    ?>
    <div class="wp-block-fau-elemental-featured-event-teaser">
        <div class="featured-event-content">
            <div class="content-left">
                <?php if ($showSubtitle && $subtitle) : ?>
                    <p class="event-subtitle"><?php echo $subtitle; ?></p>
                <?php endif; ?>
                <h2 class="event-title"><?php echo $eventTitle; ?></h2>
                <p class="event-description"><?php echo $eventDescription; ?></p>
                <div class="wp-block-button">
                    <a class="wp-block-button__link" href="<?php echo escape_html($buttonUrl); ?>">
                        <?php echo escape_html($buttonText); ?>
                        <span class="button-arrow">→</span>
                    </a>
                </div>
            </div>
            <div class="content-right">
                <div class="event-date">
                    <div class="date-day"><?php echo escape_html($day); ?></div>
                    <div class="date-month-year"><?php echo escape_html($monthYear); ?></div>
                </div>
                <?php if ($showImage && $imageUrl) : ?>
                    <div class="featured-event-image">
                        <img src="<?php echo escape_html($imageUrl); ?>" alt="<?php echo escape_html($imageAlt); ?>" />
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
    $output = ob_get_clean();
    error_log('render_block_fau_featured_event_teaser output: ' . substr($output, 0, 100) . '...');
    return $output;
}