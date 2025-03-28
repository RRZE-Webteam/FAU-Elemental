<?php
/**
 * Single Post Header Template Part
 *
 * @package YourThemeName
 */

// Get post data
$post_id = get_the_ID();

// Check if reading time should be displayed
$show_reading_time = get_post_meta($post_id, 'show_reading_time', true);
$reading_time = function_exists('get_reading_time') ? get_reading_time() : '5 min';

// Check if listen link should be displayed
$show_listen_link = get_post_meta($post_id, 'show_listen_link', true);
$listen_url = get_post_meta($post_id, 'listen_url', true);

// Check if featured image should be displayed
$show_featured_image = get_post_meta($post_id, 'show_featured_image', true) || has_post_thumbnail();
?>

<div class="post-header alignwide">
    <div class="post-meta-top">
        <div class="post-date"><?php echo get_the_date(); ?></div>
        
        <?php if (has_category()) : ?>
        <div class="post-categories">
            – <?php echo esc_html(get_the_category_list(', ')); ?>
        </div>
        <?php endif; ?>
    </div>
    
    <h1 class="post-title"><?php the_title(); ?></h1>
    
    <div class="post-meta">
        <?php if ($show_reading_time === '1') : ?>
        <p class="reading-time"><?php echo esc_html(get_reading_time()); ?></p>
        <?php endif; ?>
        
        <?php if ($show_listen_link === '1' && !empty($listen_url)) : ?>
        <p class="listen-link">
            <a href="<?php echo esc_url($listen_url); ?>">
                Beitrag anhören: <?php echo esc_html(get_audio_duration($listen_url)); ?> min. abspielen
            </a>
        </p>
        <?php endif; ?>
    </div>
    
    <?php if (has_post_thumbnail()) : ?>
    <figure class="post-featured-image alignwide">
        <?php the_post_thumbnail('large'); ?>
    </figure>
    <?php endif; ?>
</div> 