<?php
/**
 * Render callback for the FAU Facts Grid block
 *
 * @param array    $attributes Block attributes
 * @param string   $content    Block content
 * @param WP_Block $block      Block instance
 * @return string  Rendered block HTML
 */
function render_block_fau_facts_grid( $attributes, $content, $block ) {
    error_log('FAU Facts Grid attributes: ' . print_r($attributes, true));
    
    // Get attributes with defaults
    $roof_line = isset( $attributes['roofLine'] ) ? $attributes['roofLine'] : '';
    $show_roof_line = isset( $attributes['showRoofLine'] ) ? $attributes['showRoofLine'] : false;
    $facts = isset( $attributes['facts'] ) ? $attributes['facts'] : array();
    
    error_log('FAU Facts Grid processed facts: ' . print_r($facts, true));
    error_log('FAU Facts Grid facts count: ' . count($facts));
    
    // Ensure facts is an array
    if (!is_array($facts)) {
        error_log('FAU Facts Grid: facts is not an array');
        $facts = array();
    }
    
    // Get block wrapper attributes
    $wrapper_attributes = get_block_wrapper_attributes();
    
    // Start output buffering
    ob_start();
    ?>
    
    <div <?php echo $wrapper_attributes; ?>>
        <div class="fau-facts-grid">
            <?php if ( $show_roof_line && ! empty( $roof_line ) ) : ?>
                <h3 class="fau-facts-grid-roof-line"><?php echo wp_kses_post( $roof_line ); ?></h3>
            <?php endif; ?>
            
            <?php if ( ! empty( $facts ) ) : ?>
                <div class="fau-facts-grid-items">
                    <?php foreach ( $facts as $index => $fact ) : ?>
                        <?php
                        error_log('Processing fact ' . $index . ': ' . print_r($fact, true));
                        
                        // Ensure fact is an array
                        if (!is_array($fact)) {
                            error_log('FAU Facts Grid: fact ' . $index . ' is not an array');
                            continue;
                        }
                        
                        $text = isset( $fact['text'] ) ? $fact['text'] : '';
                        $icon_url = isset( $fact['iconUrl'] ) ? trim( $fact['iconUrl'] ) : '';
                        $icon_id = isset( $fact['iconId'] ) ? $fact['iconId'] : null;
                        $link = isset( $fact['link'] ) ? $fact['link'] : '';
                        $show_link = isset( $fact['showLink'] ) ? $fact['showLink'] : false;
                        
                        error_log('Processed fact ' . $index . ' values: text=' . $text . ', icon_url=' . $icon_url . ', link=' . $link);
                        
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
    
    <?php
    return ob_get_clean();
} 