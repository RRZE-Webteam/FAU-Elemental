<?php
/**
 * The template for displaying archive pages with filtering and pagination
 * @package Fau-Elemental
 */

get_header(); ?>

<main class="wp-block-group archive-page">
    <header class="blog-header is-layout-flow">
        <h1 class="blog-title">
            <?php 
            if (is_category()) {
                echo esc_html(single_cat_title('', false));
            } elseif (is_tag()) {
                echo esc_html(single_tag_title('', false));
            } elseif (is_author()) {
                echo esc_html(get_the_author());
            } elseif (is_date()) {
                if (is_year()) {
                    echo esc_html(get_the_date('Y'));
                } elseif (is_month()) {
                    echo esc_html(get_the_date('F Y'));
                } elseif (is_day()) {
                    echo esc_html(get_the_date());
                }
            } else {
                the_archive_title();
            }
            ?>
        </h1>
    </header>

    <?php if (is_category() && category_description()) : ?>
        <div class="is-layout-flow">
            <div class="category-description">
                <?php echo wp_kses_post(category_description()); ?>
            </div>
        </div>
    <?php elseif (is_tag() && tag_description()) : ?>
        <div class="is-layout-flow">
            <div class="tag-description">
                <?php echo wp_kses_post(tag_description()); ?>
            </div>
        </div>
    <?php endif; ?>

    <?php
        // Prepare block attributes
        $block_args = [
            'variant' => 'post',
        ];

        // Get total post count for display
        $post_count = 0;
        if (is_category()) {
            $post_count = get_queried_object()->count;
            $block_args['selectedCategory'] = get_queried_object_id();
        } else if (is_tag()) {
            $post_count = get_queried_object()->count;
            $block_args['selectedTags'] = [get_queried_object_id()];
        } elseif (is_author()) {
            $author = get_queried_object();
            $post_count = count_user_posts($author->ID);
            $block_args['selectedAuthor'] = get_queried_object_id();
        } elseif (is_date()) {
            // For date archives, we need to query to get the count
            $date_query_args = [
                'post_type' => 'post',
                'post_status' => 'publish',
                'posts_per_page' => 1,  // Only header query
                'no_found_rows' => false  // We need found_posts
            ];
            
            if (is_year() || is_month() || is_day()) {
                $block_args['selectedYear'] = $date_query_args['year'] = get_query_var('year');
            }
            if (is_month() || is_day()) {
                $block_args['selectedMonth'] = $date_query_args['monthnum'] = get_query_var('monthnum');
            }
            if (is_day()) {
                $block_args['selectedDay'] = $date_query_args['day'] = get_query_var('day');
            }
            
            $date_query = new WP_Query($date_query_args);
            $post_count = $date_query->found_posts;
            wp_reset_postdata();
        } else {
            // Fallback for other archive types
            global $wp_query;
            $post_count = $wp_query->found_posts;
        }

        require_once get_template_directory() . '/components/templates/pages/archive-grid-util.php';
        fau_render_archive_grid(
            $block_args,
            __('Posts listing', 'fau-elemental'),
            $post_count,
        );
    ?>

</main>

<?php get_footer(); ?>
