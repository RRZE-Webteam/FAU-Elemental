<?php
/**
 * The template for displaying category archive pages
 * @package Fau-Elemental
 */

get_header(); ?>

<main id="main" class="wp-block-group archive-page">
    <header class="blog-header is-layout-flow">
        <h1 class="blog-title">
            <?php echo esc_html(single_cat_title('', false)); ?>
        </h1>
        <div class="archive-description">
            <?php 
            $description = category_description();
            if (empty($description)) {
                // translators: name of the wordpress category
                $description = sprintf(__('Browse all posts in the %s category.', 'fau-elemental'), single_cat_title('', false));
            }
            echo wp_kses_post($description);
            ?>
        </div>
    </header>

    <?php
        $category_id = get_queried_object_id();

        require_once get_template_directory() . '/components/templates/pages/archive-grid-util.php';
        fau_render_archive_grid(
            [
                'variant' => 'post',
                'selectedCategory' => $category_id,
            ],
            __('Category posts listing', 'fau-elemental'),
        );
    ?>

</main>

<?php get_footer(); ?> 