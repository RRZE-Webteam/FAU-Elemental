<?php
/**
 * Template part for displaying post hero section
 *
 * @package FAU-Elemental
 */

// Cache post ID to avoid multiple function calls
$post_id = get_the_ID();
?>

<div class="post-header">
    
    <div class="post-header-content">
        
        <div class="post-meta-top">
            <div class="wp-block-post-date">
                <?php 
                // Check for custom date
                $use_custom_date = get_post_meta($post_id, '_faue_use_custom_last_updated', true);
                $custom_date = get_post_meta($post_id, '_faue_custom_last_updated', true);
                
                if ($use_custom_date === '1' && !empty($custom_date)) {
                    // Use custom date
                    $timestamp = strtotime($custom_date);
                    if ($timestamp !== false) {
                        $date_formatted = date_i18n('j. F Y', $timestamp);
                        $date_iso = date_i18n(DATE_W3C, $timestamp);
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
            $show_categories = get_post_meta($post_id, 'show_categories', true);
            // Default to showing categories if meta doesn't exist (backwards compatibility)
            if ($show_categories === '') {
                $show_categories = '1';
            }
            
            if ($show_categories === '1' && has_category()) : ?>
            
            <?php echo get_the_category_list(); ?>
            <?php endif; ?>
        </div>
        
        <?php
        // Use the_title() instead of render_block() for better performance
        the_title('<h1 class="wp-block-post-title">', '</h1>');
        ?>
        
    </div>
    
    <?php if (has_post_thumbnail()) : 
        echo render_block(array(
            'blockName' => 'core/post-featured-image',
            'attrs' => array(
                'align' => 'full',
                'className' => 'wp-block-image is-style-large'
            )
        ));
    endif; ?>
</div> 