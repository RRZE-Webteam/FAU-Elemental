<?php

/**
 * The template for displaying portal pages
 *
 * @package Fau-Elemental
 */

get_header();
?>

<main>
    <?php while (have_posts()) : the_post(); ?>

        <div class="is-layout-flow">
            <?php the_content(); ?>
        </div>

    <?php endwhile; ?>
</main>

<?php
get_footer();
