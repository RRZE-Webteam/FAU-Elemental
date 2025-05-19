<?php
/**
 * The template for displaying search results
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
                    <?php printf(__('Search Results for: %s', 'fau-elemental'), '<span>' . get_search_query() . '</span>'); ?>
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
                            <?php if (get_post_type() === 'post') : ?>
                                <span class="post-type"> | <?php _e('Post', 'fau-elemental'); ?></span>
                            <?php else : ?>
                                <span class="post-type"> | <?php echo get_post_type_object(get_post_type())->labels->singular_name; ?></span>
                            <?php endif; ?>
                        </div>
                    </header>

                    <div class="entry-content">
                        <?php the_excerpt(); ?>
                        <a href="<?php the_permalink(); ?>" class="read-more"><?php _e('Read More', 'fau-elemental'); ?> →</a>
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
            <p><?php _e('No results found for your search.', 'fau-elemental'); ?></p>
            
            <div class="search-again">
                <p><?php _e('Please try another search:', 'fau-elemental'); ?></p>
                <?php get_search_form(); ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php
get_footer(); 