<?php
/**
 * The template for displaying single posts (PHP fallback)
 *
 * @package Fau-Elemental
 */

get_header();
?>

<main id="primary" class="site-main">
    <?php
    while (have_posts()) :
        the_post();
        
        // Load the header template part (PHP fallback)
        get_template_part('template-parts/header/single');
        
        // Content
        ?>
        <div class="entry-content">
            <?php the_content(); ?>
        </div>
        <?php
        
        // Comments
        if (comments_open() || get_comments_number()) :
            comments_template();
        endif;
        
        // Post navigation
        the_post_navigation(
            array(
                'prev_text' => '<span class="nav-subtitle">' . esc_html__('Previous:', 'fau-elemental') . '</span> <span class="nav-title">%title</span>',
                'next_text' => '<span class="nav-subtitle">' . esc_html__('Next:', 'fau-elemental') . '</span> <span class="nav-title">%title</span>',
            )
        );
    endwhile;
    ?>
</main>

<?php
get_footer(); 