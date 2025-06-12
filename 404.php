<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @package Fau-Elemental
 */

get_header();
?>

<main class="wp-block-group alignwide" style="padding: 2rem;">
    <div class="error-404">
        <h1><?php _e('404 - Page Not Found', 'fau-elemental'); ?></h1>
        <p><?php _e('The page you were looking for could not be found.', 'fau-elemental'); ?></p>
        <a href="<?php echo esc_url(home_url('/')); ?>" class="button"><?php _e('Back to Homepage', 'fau-elemental'); ?></a>
    </div>
</main>

<?php
get_footer();
