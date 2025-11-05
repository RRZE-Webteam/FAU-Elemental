<?php

/**
 * The template for displaying single posts
 *
 * @package FAU-Elemental
 */

get_header();

if (locate_template('template-parts/hero-post.php')) {
    get_template_part('template-parts/hero', 'post');
} else {
    get_template_part('components/template-parts/hero-post/hero-post');
}
?>

<main id="main" class="site-main" role="main">
    <?php
    if (have_posts()) :
        while (have_posts()) :
            the_post();
            ?>
            <article id="post-<?php echo esc_attr(get_the_ID()); ?>" <?php post_class(); ?>>
                <div class="faue-content-wrapper">
                    <?php the_content(); ?>
                </div>

                <?php
                if (locate_template('template-parts/post-meta.php')) {
                    get_template_part('template-parts/post', 'meta');
                } else {
                    get_template_part('components/template-parts/post-meta/post-meta');
                }
                ?>
            </article>
            <?php
        endwhile;
    endif;
    ?>
</main>

<?php
get_footer();
