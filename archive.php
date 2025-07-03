<?php
/**
 * The template for displaying archive pages
 *
 * @package Fau-Elemental
 */

get_header();
?>

<main class="is-layout-constrained">
    <?php if (have_posts()) : ?>
        <h1><?php the_archive_title(); ?></h1>
        
        <?php while (have_posts()) : the_post(); ?>
            <article>
                <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                <div class="date"><?php echo get_the_date(); ?></div>
                <?php the_excerpt(); ?>
            </article>
        <?php endwhile; ?>

        <?php the_posts_pagination(); ?>

    <?php else : ?>
        <p><?php _e('No posts found.', 'fau-elemental'); ?></p>
    <?php endif; ?>
</main>

<?php get_footer(); ?>
