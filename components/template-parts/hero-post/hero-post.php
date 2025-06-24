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
                <time datetime="<?php echo get_the_date('c'); ?>"><?php echo get_the_date(); ?></time>
            </div>
            
            <?php if (has_category()) : ?>
            <p class="post-categories-separator">–</p>
            
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
        // Render the post-featured-image block
        echo render_block(array(
            'blockName' => 'core/post-featured-image',
            'attrs' => array(
                'className' => 'wp-block-image is-style-large'
            ),
        ));
    endif; ?>
</div> 