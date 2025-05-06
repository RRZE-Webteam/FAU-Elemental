<?php
/**
 * Single Post Header Template Part (PHP fallback version)
 *
 * @package Fau-Elemental
 */

// Get post data
$post_id = get_the_ID();

// Check if reading time should be displayed
$show_reading_time = get_post_meta($post_id, 'show_reading_time', true);
$reading_time = function_exists('get_reading_time') ? get_reading_time() : '5 min';

// Check if listen link should be displayed
$show_listen_link = get_post_meta($post_id, 'show_listen_link', true);
$listen_url = get_post_meta($post_id, 'listen_url', true);

// Check if featured image should be displayed
$show_featured_image = get_post_meta($post_id, 'show_featured_image', true) || has_post_thumbnail();
?>

<div class="post-header alignwide">
    <div class="post-meta-top">
        <div class="post-date"><?php echo get_the_date(); ?></div>
        
        <?php if (has_category()) : ?>
        <div class="post-categories">
            – <?php echo wp_kses_post(get_the_category_list(', ')); ?>
        </div>
        <?php endif; ?>
    </div>
    
    <h1 class="post-title"><?php the_title(); ?></h1>
    
    <div class="post-meta">
        <?php if ($show_reading_time === '1') : ?>
        <p class="reading-time"><?php echo esc_html($reading_time); ?></p>
        <?php endif; ?>
        
        <?php if ($show_listen_link === '1' && !empty($listen_url)) : ?>
        <p class="listen-link">
            <a href="<?php echo esc_url($listen_url); ?>">
                Beitrag anhören: <?php echo function_exists('get_audio_duration') ? esc_html(get_audio_duration($listen_url)) : ''; ?> min. abspielen
            </a>
        </p>
        <?php endif; ?>
    </div>
    
    <?php if (has_post_thumbnail()) : 
        // Get featured image ID
        $thumbnail_id = get_post_thumbnail_id();
        
        // Get image data
        $image_src = wp_get_attachment_image_src($thumbnail_id, 'large');
        $image_alt = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true);
        
        // Get image caption from attachment
        $attachment = get_post($thumbnail_id);
        $caption = $attachment->post_excerpt;
        
        // Create image block attributes
        $image_attributes = array(
            'id' => $thumbnail_id,
            'url' => $image_src[0],
            'alt' => $image_alt,
            'caption' => $caption,
            'sizeSlug' => 'large',
            'className' => 'wp-block-image',
            'align' => 'wide'
        );
        
        // Serialize the attributes for the render function
        $serialized_attributes = json_encode($image_attributes);
        
        // Render the image block
        echo render_block(array(
            'blockName' => 'core/image',
            'attrs' => $image_attributes,
            'innerBlocks' => array(),
            'innerHTML' => '',
            'innerContent' => array()
        ));
    ?>
    <?php endif; ?>
</div> 