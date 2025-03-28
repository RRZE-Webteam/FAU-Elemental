<?php
/**
 * The template for displaying single posts
 *
 * @package YourThemeName
 */

get_header();
?>

<main class="site-main" style="
    margin-top: var(--wp--preset--spacing--50);
    margin-bottom: var(--wp--preset--spacing--50);
    padding-top: 0;
    padding-bottom: 0;
    padding-left: var(--wp--preset--spacing--40);
    padding-right: var(--wp--preset--spacing--40);
  ">
    <div class="content-area">
        <?php
        // Include the single post header template part
        get_template_part('template-parts/header', 'single');
        ?>

        <div class="post-content alignwide">
            <?php
            while (have_posts()) :
                the_post();
                the_content();
            endwhile;
            ?>
        </div>
    </div>
</main>

<?php
get_footer(); 