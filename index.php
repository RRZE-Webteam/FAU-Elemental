<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the required files for a theme.
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
    <div class="wp-block-group alignwide">
        <?php if (have_posts()) : ?>
            <header class="page-header">
                <h1 class="wp-block-post-title alignwide">
                    <?php single_post_title(); ?>
                </h1>
            </header>

            <?php
            // Start the Loop
            while (have_posts()) :
                the_post();
                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('wp-block-post'); ?>>
                    <header class="entry-header">
                        <?php the_title('<h2 class="entry-title"><a href="' . esc_url(get_permalink()) . '">', '</a></h2>'); ?>
                        
                        <div class="entry-meta">
                            <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo get_the_date(); ?></time>
                        </div>
                    </header>

                    <div class="entry-content">
                        <?php the_excerpt(); ?>
                    </div>
                </article>
            <?php endwhile;

            // Pagination
            the_posts_pagination(array(
                'prev_text' => '&larr; ' . __('Previous', 'fau-elemental'),
                'next_text' => __('Next', 'fau-elemental') . ' &rarr;',
            ));

        else :
            ?>
            <p><?php _e('No posts found.', 'fau-elemental'); ?></p>
        <?php endif; ?>
    </div>
</main>

<?php
get_footer(); 
