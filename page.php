<?php
/**
 * The template for displaying pages
 *
 * @package Fau-Elemental
 */

get_header();
?>

<main>
<?php while (have_posts()) : the_post(); ?>
    
    <h1>Page</h1>

    <div>
        <?php the_content(); ?>
    </div>
    
    <?php endwhile; ?>
</main>

<?php
get_footer(); 