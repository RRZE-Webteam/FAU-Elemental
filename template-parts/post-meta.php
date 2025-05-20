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

// Check if dark theme should be used (from customizer setting)
$use_dark_theme = function_exists('faue_post_meta_dark_theme') && faue_post_meta_dark_theme();
$theme_class = $use_dark_theme ? 'is-style-dark' : '';

// Get custom last updated date if set
$use_custom_date = get_post_meta(get_the_ID(), '_faue_use_custom_last_updated', true);
$last_updated_date = $use_custom_date === '1' 
    ? get_post_meta(get_the_ID(), '_faue_custom_last_updated', true)
    : get_the_modified_date('d.m.Y - H:i');

// Format the date if it's a custom date
if ($use_custom_date === '1' && !empty($last_updated_date)) {
    $last_updated_date = date('d.m.Y - H:i', strtotime($last_updated_date));
}
?>
<div class="post-meta <?php echo esc_attr($theme_class); ?>">
    <div class="post-meta-wrapper">
        <div class="post-meta-inner">
            <div class="post-last-update">
                <span class="date-label"><?php esc_html_e('Last update:', 'fau-elemental'); ?></span>
                <span class="post-date"><?php echo esc_html($last_updated_date); ?></span>
                <span class="post-reading-time">
                    <?php 
                    // Get estimated reading time
                    if (function_exists('get_post_reading_time')) {
                        echo get_post_reading_time();
                    }
                    ?>
                </span>
            </div>
            

        </div>
    </div>
</div>

