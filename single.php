<?php

/**
 * The template for displaying single posts
 *
 * @package FAU-Elemental
 */

get_header();
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <?php
    // Include the post header template part
    get_template_part('template-parts/header/post');
    ?>

    <div class="entry-content">
        <?php the_content(); ?>
    </div>

    <?php
    // Include post meta (conditionally displayed)
    get_template_part('template-parts/post-meta');
    ?>
</article>

<?php
get_footer();
