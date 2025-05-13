<?php
/**
 * Template part for displaying post meta information
 *
 * @package FAU-Elemental
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Check if post meta should be displayed (if function exists)
if (function_exists('faue_show_post_meta') && !faue_show_post_meta()) {
    return; // Don't render anything
}

// Get dark theme setting
$is_dark_theme = get_theme_mod('faue_post_meta_dark_theme', false);
$theme_class = $is_dark_theme ? 'is-style-dark' : '';
?>
<div class="post-meta <?php echo esc_attr($theme_class); ?>">
    <div class="post-last-update">
        <span class="date-label"><?php esc_html_e('Last update:', 'fau-elemental'); ?></span>
        <span class="post-date"><?php echo esc_html(get_the_modified_date('d.m.Y - H:i')); ?></span>
        <span class="post-reading-time">
            <?php 
            // Get estimated reading time
            if (function_exists('get_post_reading_time')) {
                echo get_post_reading_time();
            }
            ?>
        </span>
    </div>
    
    <div class="share-button-container">
        <div class="wp-block-buttons <?php echo esc_attr($theme_class); ?>">
            <div class="wp-block-button">
                <button class="wp-block-button__link share-toggle"><?php esc_html_e('Share Page', 'fau-elemental'); ?></button>
            </div>
        </div>
        
        <div class="share-dropdown <?php echo esc_attr($theme_class); ?>" role="menu" aria-label="<?php esc_attr_e('Share options', 'fau-elemental'); ?>">
            <div class="share-options" role="list">
                <div class="share-option share-option--bluesky" role="listitem">
                    <a href="#" class="share-link" data-share="bluesky" role="menuitem"><?php esc_html_e('Bluesky', 'fau-elemental'); ?></a>
                </div>
                <div class="share-option share-option--signal" role="listitem">
                    <a href="#" class="share-link" data-share="signal" role="menuitem"><?php esc_html_e('Signal', 'fau-elemental'); ?></a>
                </div>
                <div class="share-option share-option--linkedin" role="listitem">
                    <a href="#" class="share-link" data-share="linkedin" role="menuitem"><?php esc_html_e('LinkedIn', 'fau-elemental'); ?></a>
                </div>
                <div class="share-option share-option--facebook" role="listitem">
                    <a href="#" class="share-link" data-share="facebook" role="menuitem"><?php esc_html_e('Facebook', 'fau-elemental'); ?></a>
                </div>
                <div class="share-option share-option--whatsapp" role="listitem">
                    <a href="#" class="share-link" data-share="whatsapp" role="menuitem"><?php esc_html_e('WhatsApp', 'fau-elemental'); ?></a>
                </div>
                <div class="share-option share-option--email" role="listitem">
                    <a href="#" class="share-link" data-share="email" role="menuitem"><?php esc_html_e('E-Mail', 'fau-elemental'); ?></a>
                </div>
                <div class="share-option share-option--print" role="listitem">
                    <div class="wp-block-button">
                        <button class="wp-block-button__link" onclick="window.print();" role="menuitem"><?php esc_html_e('Print', 'fau-elemental'); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

