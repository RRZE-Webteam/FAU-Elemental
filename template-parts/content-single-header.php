<?php
/**
 * Template part for displaying single post headers
 *
 * @package YourTheme
 */

// Calculate reading time
$content = get_the_content();
$word_count = str_word_count(strip_tags($content));
$reading_time = ceil($word_count / 200);
$listen_duration = ceil($reading_time * 1.2);
?>

<div class="wp-block-group alignwide post-header">
    <div class="wp-block-group post-meta-top">
        <time datetime="<?php echo get_the_date('c'); ?>" class="wp-block-post-date has-small-font-size">
            <?php echo get_the_date(); ?>
        </time>
        
        <p class="has-small-font-size post-categories-separator">–</p>
        
        <div class="wp-block-post-terms has-small-font-size post-categories">
            <?php the_category(', '); ?>
        </div>
    </div>
    
    <h1 class="wp-block-post-title alignwide">
        <?php the_title(); ?>
    </h1>
    
    <div class="wp-block-group post-meta">
        <p class="reading-time post-reading-time">
            Lesedauer: <?php echo $reading_time; ?> min
        </p>
        
        <p class="listen-link post-listen-link">
            <a href="#">Beitrag anhören: <?php echo $listen_duration; ?> min. abspielen</a>
        </p>
    </div>
    
    <?php if (has_post_thumbnail()) : ?>
    <figure class="wp-block-post-featured-image alignwide post-featured-image">
        <?php the_post_thumbnail('full', ['class' => 'wp-block-post-featured-image__image']); ?>
    </figure>
    <?php endif; ?>
</div> 