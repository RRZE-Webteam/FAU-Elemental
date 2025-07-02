<?php
/**
 * Template part for displaying post hero section
 *
 * @package Fau-Elemental
 */
?>

<div class="post-header">
    
    <div class="post-header-content">
        
        <div class="post-meta-top">
            <div class="wp-block-post-date">
                <?php 
                // Check for custom date
                $use_custom_date = get_post_meta(get_the_ID(), '_faue_use_custom_last_updated', true);
                $custom_date = get_post_meta(get_the_ID(), '_faue_custom_last_updated', true);
                
                if ($use_custom_date === '1' && !empty($custom_date)) {
                    // Use custom date
                    $timestamp = strtotime($custom_date);
                    if ($timestamp !== false) {
                        $date_formatted = date_i18n('j. F Y', $timestamp);
                        $date_iso = date('c', $timestamp);
                    } else {
                        // Fallback to post date if custom date is invalid
                        $date_formatted = get_the_date('j. F Y');
                        $date_iso = get_the_date('c');
                    }
                } else {
                    // Use regular post date
                    $date_formatted = get_the_date('j. F Y');
                    $date_iso = get_the_date('c');
                }
                ?>
                <time datetime="<?php echo esc_attr($date_iso); ?>"><?php echo esc_html($date_formatted); ?></time>
            </div>
            
            <?php 
            // Check if categories should be shown
            $show_categories = get_post_meta(get_the_ID(), 'show_categories', true);
            // Default to showing categories if meta doesn't exist (backwards compatibility)
            if ($show_categories === '') {
                $show_categories = '1';
            }
            
            if ($show_categories === '1' && has_category()) : ?>
            <span class="post-categories-separator"></span>
            
            <div class="post-categories"><?php 
                $categories = get_the_category();
                $category_names = array();
                foreach ($categories as $category) {
                    $category_names[] = $category->name;
                }
                echo esc_html(implode(', ', $category_names));
            ?></div>
            <?php endif; ?>
        </div>
        
        <?php
        // Render the post-title block
        echo render_block(array(
            'blockName' => 'core/post-title',
            'attrs' => array(
                'level' => 1,
            ),
        ));
        ?>
        
    </div>
    
    <?php if (has_post_thumbnail()) : 
        // Get featured image data
        $thumbnail_id = get_post_thumbnail_id();
        $image_src = wp_get_attachment_image_src($thumbnail_id, 'large');
        $image_alt = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true);
        
        // Get image caption from attachment
        $attachment = get_post($thumbnail_id);
        $caption = $attachment->post_excerpt;
        
        // Create the inner HTML for the image
        $inner_html = sprintf(
            '<figure class="wp-block-image size-large is-style-large has-overlay"><img src="%s" alt="%s" class="wp-image-%d"/>',
            esc_url($image_src[0]),
            esc_attr($image_alt),
            $thumbnail_id
        );
        
        // Add caption if it exists
        if (!empty($caption)) {
            $inner_html .= sprintf('<figcaption class="wp-element-caption">%s</figcaption>', esc_html($caption));
        }
        
        $inner_html .= '</figure>';
        
        // Render the core/image block
        echo render_block(array(
            'blockName' => 'core/image',
            'attrs' => array(
                'id' => $thumbnail_id,
                'sizeSlug' => 'large',
                'linkDestination' => 'none',
                'align' => 'full',
                'className' => 'is-style-large has-overlay'
            ),
            'innerBlocks' => array(),
            'innerHTML' => $inner_html,
            'innerContent' => array($inner_html)
        ));
    endif; ?>
</div> 