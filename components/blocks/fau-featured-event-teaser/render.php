<?php
/**
 * Server-side rendering of the `fau-elemental/featured-event-teaser` block.
 *
 * @package FAU_Elemental
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Extract attributes with defaults
$event_title = $attributes['eventTitle'] ?? '';
$event_description = $attributes['eventDescription'] ?? '';
$event_date = $attributes['eventDate'] ?? '';
$button_text = $attributes['buttonText'] ?? 'Mehr erfahren';
$button_url = $attributes['buttonUrl'] ?? '#';
$image_url = $attributes['imageUrl'] ?? '';
$image_alt = $attributes['imageAlt'] ?? '';

// If no date provided, use current date
if ( empty( $event_date ) ) {
    $today = new DateTime();
    $day = $today->format( 'j' );
    $month_name = $today->format( 'M' );
    $year = $today->format( 'Y' );
    $month_year = $month_name . ' ' . $year;
    $datetime_attr = $today->format( 'Y-m-d' );
} else {
    // Process saved dates - format is "DD MM YYYY" where MM is numeric
    $date_parts = explode( ' ', trim( $event_date ) );
    if ( count( $date_parts ) >= 3 ) {
        $day = $date_parts[0];
        $month_num = $date_parts[1];
        $year = $date_parts[2];
        
        // Convert numeric month to month abbreviation
        $month_names = [
            '1' => 'Jan', '01' => 'Jan',
            '2' => 'Feb', '02' => 'Feb', 
            '3' => 'Mar', '03' => 'Mar',
            '4' => 'Apr', '04' => 'Apr',
            '5' => 'May', '05' => 'May',
            '6' => 'Jun', '06' => 'Jun',
            '7' => 'Jul', '07' => 'Jul',
            '8' => 'Aug', '08' => 'Aug',
            '9' => 'Sep', '09' => 'Sep',
            '10' => 'Oct',
            '11' => 'Nov',
            '12' => 'Dec'
        ];
        
        $month_name = $month_names[$month_num] ?? '';
        $month_year = $month_name . ' ' . $year;
        $datetime_attr = $year . '-' . str_pad( $month_num, 2, '0', STR_PAD_LEFT ) . '-' . str_pad( $day, 2, '0', STR_PAD_LEFT );
    }
}

// Get block wrapper attributes
$wrapper_attributes = get_block_wrapper_attributes([
    'class' => 'wp-block-fau-elemental-featured-event-teaser'
]);
?>

<div <?php echo $wrapper_attributes; ?>>
    <div class="featured-event-content">
        
        <!-- Left content -->
        <div class="content-left">
            <?php if ( $event_title ) : ?>
                <h2><?php echo esc_html( $event_title ); ?></h2>
            <?php endif; ?>
            
            <?php if ( $event_description ) : ?>
                <p><?php echo esc_html( $event_description ); ?></p>
            <?php endif; ?>
            
            <div class="wp-block-buttons">
                <div class="wp-block-button">
                    <a class="wp-block-button__link" href="<?php echo esc_url( $button_url ); ?>">
                        <?php echo esc_html( $button_text ); ?>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Right content -->
        <div class="content-right">
            <time datetime="<?php echo esc_attr( $datetime_attr ); ?>">
                <span class="date-day"><?php echo esc_html( $day ); ?></span>
                <span class="date-month-year"><?php echo esc_html( $month_year ); ?></span>
            </time>
            
            <?php if ( $image_url ) : ?>
                <div class="featured-event-image">
                    <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>" />
                </div>
            <?php endif; ?>
        </div>
        
    </div>
</div> 