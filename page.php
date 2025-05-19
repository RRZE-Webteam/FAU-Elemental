<?php
/**
 * The template for displaying pages
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
    <?php while (have_posts()) : the_post(); ?>
    
    <div class="wp-block-group alignwide">
        <h1 class="wp-block-post-title alignwide">
            <?php the_title(); ?>
        </h1>
        
        <?php if (has_post_thumbnail()) : ?>
        <figure class="wp-block-post-featured-image alignwide post-featured-image">
            <?php the_post_thumbnail('full', ['class' => 'wp-block-post-featured-image__image']); ?>
        </figure>
        <?php endif; ?>
    </div>

    <div class="wp-block-post-content alignwide">
        <?php the_content(); ?>
    </div>
    
    <?php
    // If comments are open or we have at least one comment, load up the comment template.
    if (comments_open() || get_comments_number()) :
        comments_template();
    endif;
    ?>
    
    <?php endwhile; ?>
</main>

<?php
get_footer(); 