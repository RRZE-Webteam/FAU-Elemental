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
?>
<div class="post-meta">
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
        <div class="share-button">
            <button class="share-button__link share-toggle"><?php esc_html_e('Share Page', 'fau-elemental'); ?></button>
        </div>
        
        <div class="share-dropdown" role="menu" aria-label="<?php esc_attr_e('Share options', 'fau-elemental'); ?>">
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
                    <button class="print-button" onclick="window.print();" role="menuitem"><?php esc_html_e('Print', 'fau-elemental'); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Basic two-column layout styles */
.post-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 0;
}

.post-last-update {
    display: flex;
    align-items: center;
    gap: 8px;
}

.post-reading-time {
    margin-left: 15px;
}
</style>

