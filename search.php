<?php

/**
 * The template for displaying search results
 *
 * @package Fau-Elemental
 */

get_header();
?>

<main>
    <?php if (have_posts()) : ?>
        <h1><?php printf(__('Search Results for: %s', 'fau-elemental'), get_search_query()); ?></h1>

        <?php while (have_posts()) : the_post(); ?>
            <article>
                <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                <div class="date"><?php echo get_the_date(); ?></div>
                <?php the_excerpt(); ?>
            </article>
        <?php endwhile; ?>

        <?php the_posts_pagination(); ?>

    <?php else : ?>
        <p><?php _e('No results found.', 'fau-elemental'); ?></p>
        <?php get_search_form(); ?>
    <?php endif; ?>
</main>

<?php get_footer(); ?>