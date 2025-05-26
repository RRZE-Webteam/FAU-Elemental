<?php
/**
 * Template part for displaying post header
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

$post_id = get_the_ID();
$show_reading_time = get_post_meta($post_id, 'show_reading_time', true);
$show_categories = get_post_meta($post_id, 'show_categories', true);
$reading_time = get_reading_time_value($post_id);
?>

<div class="post-header alignwide" style="margin-top:var(--wp--preset--spacing--50);margin-bottom:var(--wp--preset--spacing--50);padding:0 var(--wp--preset--spacing--40);">
    <div class="post-header-content">
        <div class="post-meta-top" style="display:flex;gap:0.5em;align-items:center;">
            <?php if (get_the_date()): ?>
                <div class="post-date"><?php echo get_the_date(); ?></div>
            <?php endif; ?>

            <?php if ($show_categories !== '0' && has_category()): ?>
                <span class="post-categories-separator">–</span>
                <div class="post-categories">
                    <?php echo strip_tags(get_the_category_list(', ')); ?>
                </div>
            <?php endif; ?>
        </div>

        <h1 class="post-title alignwide" style="margin:1em 0;"><?php the_title(); ?></h1>

        <?php if ($show_reading_time !== '0'): ?>
            <div class="post-meta" style="margin-bottom:2em;">
                <p class="reading-time">
                    <?php echo esc_html__('Reading time:', 'fau-elemental'); ?> 
                    <strong><?php echo esc_html($reading_time); ?> <?php echo esc_html__('min', 'fau-elemental'); ?></strong>
                </p>
            </div>
        <?php endif; ?>
    </div>

    <?php if (has_post_thumbnail()): ?>
        <figure class="post-featured-image" style="margin:0 calc(var(--wp--preset--spacing--40) * -1);">
            <?php 
            the_post_thumbnail('large', array(
                'class' => 'attachment-large size-large',
                'style' => 'width:100%;height:auto;object-fit:cover;',
                'loading' => 'eager',
                'fetchpriority' => 'high'
            ));
            
            $caption = wp_get_attachment_caption(get_post_thumbnail_id());
            if ($caption) {
                echo '<figcaption class="wp-element-caption">' . esc_html($caption) . '</figcaption>';
            }
            ?>
        </figure>
    <?php endif; ?>
</div> 