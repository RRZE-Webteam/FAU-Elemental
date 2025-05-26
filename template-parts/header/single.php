<?php
/**
 * Template part for displaying the single post header
 *
 * @package FAU-Elemental
 */

// Get post meta options
$post_id = get_the_ID();
$show_reading_time = get_post_meta($post_id, 'show_reading_time', true);
$show_categories = get_post_meta($post_id, 'show_categories', true);

// Get reading time
$reading_time = get_reading_time($post_id);

// Include the default header
get_template_part('template-parts/header/default');
?>

<main class="wp-block-group post-main" style="margin-top:var(--wp--preset--spacing--50);margin-bottom:var(--wp--preset--spacing--50);padding-top:0;padding-bottom:0;padding-left:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--40)">
    <div class="wp-block-group alignwide post-header">
        <div class="wp-block-group post-header-content">
            <div class="wp-block-group post-meta-top">
                <?php if (get_the_date()): ?>
                    <div class="wp-block-post-date"><?php echo get_the_date(); ?></div>
                <?php endif; ?>

                <?php if ($show_categories !== '0' && has_category()): ?>
                    <p class="post-categories-separator">–</p>
                    <div class="post-categories">
                        <?php echo strip_tags(get_the_category_list(', ')); ?>
                    </div>
                <?php endif; ?>
            </div>

            <h1 class="wp-block-post-title alignwide"><?php the_title(); ?></h1>

            <?php if ($show_reading_time !== '0'): ?>
                <div class="wp-block-group post-meta">
                    <p class="reading-time"><?php 
                        echo esc_html__('Reading time:', 'fau-elemental') . ' ';
                        echo '<strong>' . esc_html(get_reading_time_value()) . ' ' . esc_html__('min', 'fau-elemental') . '</strong>';
                    ?></p>
                </div>
            <?php endif; ?>
        </div>

        <?php 
        $show_featured_image = get_post_meta($post_id, 'show_featured_image', true);
        if ($show_featured_image !== '0' && has_post_thumbnail()): 
        ?>
            <figure class="wp-block-image size-large is-style-large wp-block-post-featured-image">
                <?php 
                the_post_thumbnail('large', array(
                    'class' => 'attachment-large size-large wp-post-image',
                    'style' => 'object-fit:cover;',
                    'loading' => 'eager',
                    'fetchpriority' => 'high'
                ));
                
                $thumbnail_id = get_post_thumbnail_id();
                $caption = wp_get_attachment_caption($thumbnail_id);
                if ($caption) {
                    echo '<figcaption class="wp-element-caption">' . esc_html($caption) . '</figcaption>';
                }
                ?>
            </figure>
        <?php endif; ?>
    </div>

    <div class="entry-content alignwide wp-block-post-content">
        <?php the_content(); ?>
    </div>
</main> 