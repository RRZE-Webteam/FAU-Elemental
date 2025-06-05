<?php
/**
 * The template for displaying the front page
 *
 * @package Fau-Elemental
 */

get_header();
?>

<main>
    <?php while (have_posts()) : the_post(); ?>
    
    <div>
        <h1>
            <?php the_title(); ?>
        </h1>
        
        <?php if (has_post_thumbnail()) : ?>
        <figure class="wp-block-post-featured-image post-featured-image">
            <?php the_post_thumbnail('full', ['class' => 'wp-block-post-featured-image__image']); ?>
        </figure>
        <?php endif; ?>
    </div>

    <div>
        <?php the_content(); ?>
    </div>
    
    <?php endwhile; ?>
</main>

<?php
get_footer(); 