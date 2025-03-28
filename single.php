<?php
/**
 * The template for displaying single posts
 * 
 * This is a PHP fallback template that ensures compatibility with 
 * older plugins while still supporting block content.
 *
 * @package Fau-Elemental
 */

get_header();
?>

<main class="wp-block-group" style="
    margin-top: var(--wp--preset--spacing--50);
    margin-bottom: var(--wp--preset--spacing--50);
    padding-top: 0;
    padding-bottom: 0;
    padding-left: var(--wp--preset--spacing--40);
    padding-right: var(--wp--preset--spacing--40);
">
    <?php
    while (have_posts()) :
        the_post();
        
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

    <div class="wp-block-post-content alignwide">
        <?php the_content(); ?>
    </div>
    
    <div class="post-navigation-container alignwide">
        <div class="post-navigation">
            <?php
            // Previous/next post navigation
            the_post_navigation(array(
                'prev_text' => '<span class="nav-subtitle">' . __('Previous', 'fau-elemental') . '</span> <span class="nav-title">%title</span>',
                'next_text' => '<span class="nav-subtitle">' . __('Next', 'fau-elemental') . '</span> <span class="nav-title">%title</span>',
            ));
            ?>
        </div>
        
        <?php
        // If comments are open or we have at least one comment, load up the comment template.
        if (comments_open() || get_comments_number()) :
            comments_template();
        endif;
        ?>
    </div>
    
    <?php endwhile; ?>
</main>

<?php
get_footer(); 