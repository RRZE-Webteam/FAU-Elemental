<?php
/**
 * Post Settings and Functionality
 *
 * @package FAU-Elemental
 */

 if (!defined('ABSPATH')) {
    exit;
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
    $show_categories = get_post_meta($post->ID, 'show_categories', true);
    
    // Set defaults
    if ($show_categories === '') {
        $show_categories = '1'; // Default to true
    }

    ?>
    <div class="post-header-options">        
        <label>
            <input type="checkbox" name="show_categories" value="1" <?php checked($show_categories, '1'); ?>>
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
    
    $show_categories = isset($_POST['show_categories']) ? '1' : '0';
    update_post_meta($post_id, 'show_categories', $show_categories);
}
add_action('save_post', 'save_post_header_options_meta_box');

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
