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

// Ensure we have a post object
global $post;
if (!$post instanceof WP_Post) {
    return;
}

// Check if dark theme should be used (from customizer setting)
$use_dark_theme = function_exists('faue_post_meta_dark_theme') && faue_post_meta_dark_theme();
$theme_class = $use_dark_theme ? 'is-style-dark' : '';

// Only fetch needed post meta keys
$use_custom_date = get_post_meta($post->ID, '_faue_use_custom_last_updated', true);
$custom_date = get_post_meta($post->ID, '_faue_custom_last_updated', true);

// Determine the date to display and get timestamp
$timestamp = false;
$display_date = '';

if ($use_custom_date === '1' && !empty($custom_date)) {
    // Use custom date
    $timestamp = strtotime($custom_date);
    if ($timestamp !== false) {
        $display_date = wp_date(get_option('date_format') . ' - ' . get_option('time_format'), $timestamp, wp_timezone());
    }
}

// Fall back to WordPress modified date if no custom date or invalid custom date
if (empty($display_date) || $timestamp === false) {
    $timestamp = get_the_modified_time('U', $post);
    $display_date = wp_date(get_option('date_format') . ' - ' . get_option('time_format'), $timestamp, wp_timezone());
}

// Use date_i18n(DATE_W3C, $timestamp) for datetime attribute (site timezone)
$datetime_iso = date_i18n(DATE_W3C, $timestamp);
?>
<div class="post-meta <?php echo esc_attr($theme_class); ?>">
    <div class="post-meta-wrapper">
        <div class="post-meta-inner">
            <div class="post-last-update">
                <span class="date-label"><?php esc_html_e('Last update:', 'fau-elemental'); ?></span>
                <time datetime="<?php echo esc_attr($datetime_iso); ?>" class="post-date">
                    <?php echo esc_html($display_date); ?>
                </time>
            </div>
        </div>
    </div>
</div>