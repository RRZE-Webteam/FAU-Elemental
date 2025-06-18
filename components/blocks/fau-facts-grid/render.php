<?php
/**
 * Render callback for the FAU Facts Grid block
 *
 * @param array    $attributes Block attributes
 * @param string   $content    Block content
 * @param WP_Block $block      Block instance
 */

// Get attributes with defaults
$facts = isset( $attributes['facts'] ) ? $attributes['facts'] : array();

// Ensure facts is an array
if (!is_array($facts)) {
    $facts = array();
}

// Get block wrapper attributes
$wrapper_attributes = get_block_wrapper_attributes();
?>

<div <?php echo $wrapper_attributes; ?>>
    <div class="fau-facts-grid">
        <?php if ( ! empty( $facts ) ) : ?>
            <div class="fau-facts-grid-items">
                <?php foreach ( $facts as $index => $fact ) : ?>
                    <?php
                    // Ensure fact is an array
                    if (!is_array($fact)) {
                        continue;
                    }
                    
                    $text = isset( $fact['text'] ) ? $fact['text'] : '';
                    $icon_url = isset( $fact['iconUrl'] ) ? trim( $fact['iconUrl'] ) : '';
                    $icon_id = isset( $fact['iconId'] ) ? $fact['iconId'] : null;
                    $link = isset( $fact['link'] ) ? $fact['link'] : '';
                    $show_link = isset( $fact['showLink'] ) ? $fact['showLink'] : false;
                    
                    $has_link_class = ( ! empty( $link ) && $show_link ) ? ' has-link' : '';
                    ?>
                    <div class="fau-facts-grid-item<?php echo esc_attr( $has_link_class ); ?>">
                        <div class="fau-facts-grid-item-icon">
                            <?php if ( ! empty( $icon_url ) ) : ?>
                                <img 
                                    src="<?php echo esc_url( $icon_url ); ?>" 
                                    alt=""
                                    style="width: 24px; height: 24px; object-fit: contain;"
                                />
                            <?php else : ?>
                                <img 
                                    src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/fact-icon.png' ); ?>" 
                                    alt=""
                                    style="width: 24px; height: 24px; object-fit: contain;"
                                />
                            <?php endif; ?>
                        </div>
                        <div class="fau-facts-grid-item-content">
                            <?php if ( ! empty( $text ) ) : ?>
                                <div class="fau-facts-grid-item-text"><?php echo wp_kses_post( $text ); ?></div>
                            <?php endif; ?>
                            
                            <?php if ( ! empty( $link ) && $show_link ) : ?>
                                <!-- wp:buttons -->
                                <div class="wp-block-buttons">
                                    <!-- wp:button {"className":"is-style-outline"} -->
                                    <div class="wp-block-button is-style-tertiary">
                                        <a class="wp-block-button__link" href="<?php echo esc_url( $link ); ?>">
                                            <?php echo esc_html( __( 'Mehr', 'fau-elemental' ) ); ?>
                                        </a>
                                    </div>
                                    <!-- /wp:button -->
                                </div>
                                <!-- /wp:buttons -->
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>