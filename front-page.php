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

        <div class="is-layout-constrained">
            <?php the_content(); ?>
        </div>

    <?php endwhile; ?>
</main>

<?php
get_footer();
