<?php
/**
 * Post Settings and Functionality
 *
 * @package faue
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Calculate reading time for posts
 *
 * @param int $post_id Optional. Post ID. Default is current post.
 * @return string Reading time with proper format
 */
function get_reading_time($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    $content = get_post_field('post_content', $post_id);
    $word_count = str_word_count(strip_tags($content));
    
    // Average reading speed: 200-250 words per minute
    $reading_speed = 225;
    
    $reading_time = ceil($word_count / $reading_speed);
    
    // Minimum reading time of 1 minute
    if ($reading_time < 1) {
        $reading_time = 1;
    }
    
    // Return reading time with label included
    return  $reading_time . ' ' . __('min', 'fau-elemental');
}

/**
 * Get just the numeric value of reading time
 */
function get_reading_time_value($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    $content = get_post_field('post_content', $post_id);
    $word_count = str_word_count(strip_tags($content));
    
    // Average reading speed: 225 words per minute
    $reading_speed = 225;
    
    $reading_time = ceil($word_count / $reading_speed);
    
    // Minimum reading time of 1 minute
    if ($reading_time < 1) {
        $reading_time = 1;
    }
    
    return $reading_time;
}

/**
 * Add Post Header Options meta box
 */
function add_post_header_options_meta_box() {
    add_meta_box(
        'post_header_options',
        __('Post Header Options', 'fau-elemental'),
        'render_post_header_options_meta_box',
        'post',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'add_post_header_options_meta_box');

/**
 * Render Post Header Options meta box
 */
function render_post_header_options_meta_box($post) {
    wp_nonce_field('post_header_options_nonce', 'post_header_options_nonce');
    
    // Get saved values
    $show_reading_time = get_post_meta($post->ID, 'show_reading_time', true);

    $show_categories = get_post_meta($post->ID, 'show_categories', true);
    
    // Set defaults
    if ($show_reading_time === '') {
        $show_reading_time = '1';
    }
    if ($show_categories === '') {
        $show_categories = '1';
    }
    ?>
    <div class="post-header-options">
        <label>
            <input type="checkbox" name="show_reading_time" value="1" <?php checked($show_reading_time, '1'); ?> />
            <strong><?php esc_html_e('Show reading time', 'fau-elemental'); ?></strong>
        </label>
        
        <label>
            <input type="checkbox" name="show_categories" value="1" <?php checked($show_categories, '1'); ?> />
            <strong><?php esc_html_e('Show categories', 'fau-elemental'); ?></strong>
        </label>
        
 
    </div>
    <?php
}

/**
 * Save Post Header Options meta box
 */
function save_post_header_options_meta_box($post_id) {
    // Security checks
    if (!isset($_POST['post_header_options_nonce']) || 
        !wp_verify_nonce($_POST['post_header_options_nonce'], 'post_header_options_nonce')) {
        return;
    }
    
    // Don't save during autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Check permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Save the options
    $show_reading_time = isset($_POST['show_reading_time']) ? '1' : '0';
    update_post_meta($post_id, 'show_reading_time', $show_reading_time);
    
    $show_categories = isset($_POST['show_categories']) ? '1' : '0';
    update_post_meta($post_id, 'show_categories', $show_categories);
    
  
}
add_action('save_post', 'save_post_header_options_meta_box');

// Add JavaScript to handle the listen link toggle
function post_header_options_admin_scripts() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        $('#show-listen-link-toggle').on('change', function() {
            if ($(this).is(':checked')) {
                $('#listen-fields').show();
            } else {
                $('#listen-fields').hide();
            }
        });
    });
    </script>
    <?php
}
add_action('admin_footer', 'post_header_options_admin_scripts');

/**
 * Apply post header settings to HTML output using output buffering
 */
function buffer_start() {
    if (is_singular('post')) {
        ob_start();
    }
}

function buffer_end() {
    if (is_singular('post') && ob_get_length()) {
        $html = ob_get_clean();
        $post_id = get_the_ID();
        
        // Get meta values
        $show_reading_time = get_post_meta($post_id, 'show_reading_time', true);
    
        
        // Calculate reading time
        $reading_time = get_reading_time($post_id);
        
        // Inject CSS to control visibility
        $inject_css = '<style>';
        
        if ($show_reading_time !== '1') {
            $inject_css .= '.reading-time, .post-reading-time { display: none !important; }';
        }
        
     
        
        $inject_css .= '</style>';
        
        // Insert reading time
        $html = preg_replace(
            '/<p class=["\'](?:[^"\']*\s)?reading-time(?:\s[^"\']*)?["\'][^>]*>.*?<\/p>/',
            '<p class="reading-time"><strong>' . esc_html($reading_time) . '</strong></p>',
            $html
        );
        
      
        
        // Add the CSS to the head
        $html = str_replace('</head>', $inject_css . '</head>', $html);
        
        echo $html;
    }
}

add_action('template_redirect', 'buffer_start', 1);
add_action('wp_footer', 'buffer_end', 999);

/**
 * Register and render a dynamic block for post header
 */
function register_post_header_block() {
    register_block_type('fau-elemental/post-header', array(
        'render_callback' => 'render_post_header_block',
    ));
}
add_action('init', 'register_post_header_block');

/**
 * Render the post header block
 */
function render_post_header_block($attributes) {
    ob_start();
    
    $post_id = get_the_ID();
    
    // Get meta values
    $show_reading_time = get_post_meta($post_id, 'show_reading_time', true);

    
    // Get reading time
    $reading_time = get_reading_time($post_id);
    ?>
    
    <div class="post-header wp-block-group alignwide">
        <div class="post-meta-top wp-block-group">
            <?php echo get_the_date(); ?>
            
            <?php if (has_category()): ?>
            <div class="post-categories">
                <?php wp_strip_all_tags(the_category(', ')); ?>
            </div>
            <?php endif; ?>
        </div>
        
        <h1 class="post-title"><?php the_title(); ?></h1>
        
        <div class="post-meta wp-block-group">
            <?php if ($show_reading_time === '1'): ?>
                
            <p class="reading-time"><b><?php echo esc_html($reading_time); ?></b></p>
            <?php endif; ?>
            
 
        </div>
    </div>
    
    <?php
    return ob_get_clean();
}

/**
 * Filter post content to add dynamic elements to the header
 */
function filter_post_header_content($content) {
    if (is_singular('post') && is_main_query()) {
        $post_id = get_the_ID();
        
        // Get the reading time and listening duration
        $reading_time = get_reading_time_value($post_id);
        $listen_duration = get_post_meta($post_id, 'listen_duration', true) ?: '4';
        
        // Replace the placeholders
        $content = str_replace('<!-- READING_TIME -->', esc_html($reading_time), $content);
        $content = str_replace('<!-- LISTEN_DURATION -->', esc_html($listen_duration), $content);
        
        // Get visibility settings
        $show_reading_time = get_post_meta($post_id, 'show_reading_time', true) !== '0';
       
        $show_categories = get_post_meta($post_id, 'show_categories', true) !== '0';
  
        
        // Apply CSS to hide elements if needed
        $inline_style = '<style>';
        
        if (!$show_reading_time) {
            $inline_style .= '.post-reading-time, .reading-time { display: none !important; }';
        }
        
        if (!$show_categories) {
            $inline_style .= '.post-categories, .post-categories-separator { display: none !important; }';
        }
        

        
        $inline_style .= '</style>';
        
        // Add the style to the beginning of the content
        $content = $inline_style . $content;
    }
    
    return $content;
}
add_filter('the_content', 'filter_post_header_content', 1);




/**
 * Add caption to post featured image blocks
 * 
 * This adds the caption from the media library to the featured image
 * when rendered with the post-featured-image block
 *
 * @param string $block_content The block content.
 * @param array  $block         The full block, including name and attributes.
 * @return string Modified block content.
 */
function fau_add_caption_to_featured_image($block_content, $block) {
    // Only modify core/post-featured-image blocks
    if (isset($block['blockName']) && 'core/post-featured-image' === $block['blockName']) {
        // Don't modify if the content is null or empty
        if (empty($block_content)) {
            return $block_content;
        }
        
        // Get the post ID and the attachment ID
        $post_id = get_the_ID();
        $thumbnail_id = get_post_thumbnail_id($post_id);
        
        if (!$thumbnail_id) {
            return $block_content;
        }
        
        // Get the attachment post to retrieve the caption
        $attachment = get_post($thumbnail_id);
        if (!$attachment) {
            return $block_content;
        }
        
        // Get the caption from the attachment's excerpt
        $caption = $attachment->post_excerpt;
        
        // Only add caption if it exists
        if (!empty($caption)) {
            // Check if the block content ends with </figure>
            if (substr(trim($block_content), -9) === '</figure>') {
                // Insert the figcaption before the closing figure tag
                $block_content = str_replace('</figure>', '<figcaption class="wp-element-caption">' . wp_kses_post($caption) . '</figcaption></figure>', $block_content);
            }
        }
    }
    
    return $block_content;
}
add_filter('render_block', 'fau_add_caption_to_featured_image', 10, 2);

/**
 * Modify post-terms block output to display categories without links
 */
function fau_elemental_modify_post_terms_block($block_content, $block) {
    if (!is_string($block_content)) {
        return $block_content;
    }

    // Only modify category terms
    if (isset($block['attrs']['term']) && $block['attrs']['term'] === 'category') {
        $post_id = get_the_ID();
        if (!$post_id) {
            return '';
        }

        $categories = get_the_category($post_id);
        if (empty($categories)) {
            return '';
        }

        $category_names = array();
        foreach ($categories as $category) {
            $category_names[] = $category->name;
        }

        $separator = isset($block['attrs']['separator']) ? $block['attrs']['separator'] : ', ';
        $className = isset($block['attrs']['className']) ? $block['attrs']['className'] : 'post-categories';

        return '<div class="' . esc_attr($className) . '">' . 
               esc_html(implode($separator, $category_names)) . 
               '</div>';
    }

    return $block_content;
}
add_filter('render_block', 'fau_elemental_modify_post_terms_block', 10, 2);