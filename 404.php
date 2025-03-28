<?php
/**
 * The template for displaying 404 pages (not found)
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
    <div class="wp-block-group alignwide error-404 not-found">
        <header class="page-header">
            <h1 class="wp-block-post-title alignwide">
                <?php _e('404 - Page Not Found', 'fau-elemental'); ?>
            </h1>
        </header>

        <div class="page-content">
            <p><?php _e('The page you were looking for could not be found. It might have been removed, renamed, or did not exist in the first place.', 'fau-elemental'); ?></p>
            
            <div class="error-actions">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="button"><?php _e('Back to Homepage', 'fau-elemental'); ?></a>
            </div>
            
            <div class="search-404">
                <h2><?php _e('Search for something else:', 'fau-elemental'); ?></h2>
                <?php get_search_form(); ?>
            </div>
            
            <div class="recent-posts">
                <h2><?php _e('Recent Posts', 'fau-elemental'); ?></h2>
                <ul>
                    <?php
                    $recent_posts = wp_get_recent_posts(array(
                        'numberposts' => 5,
                        'post_status' => 'publish'
                    ));
                    
                    foreach ($recent_posts as $post) :
                        ?>
                        <li>
                            <a href="<?php echo get_permalink($post['ID']); ?>">
                                <?php echo $post['post_title']; ?>
                            </a>
                        </li>
                        <?php
                    endforeach;
                    wp_reset_postdata();
                    ?>
                </ul>
            </div>
        </div>
    </div>
</main>

<?php
get_footer(); 