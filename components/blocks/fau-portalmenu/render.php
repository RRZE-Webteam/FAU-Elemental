<?php
/**
 * Render callback for the FAU Portal Menu block.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block content.
 * @param WP_Block $block      Block instance.
 * @return string Rendered block HTML.
 */
function render_block_fau_portalmenu($attributes, $content, $block) {
    // Include shortcodes file if not already included
    
    // Ensure Walker_Content_Menu class is loaded
    if (!class_exists('Walker_Content_Menu')) {
        require_once get_template_directory() . '/inc/class-walker-content-menu.php';
    }
    
    // Get menu ID or name
    $menu = '';
    if (!empty($attributes['menuId'])) {
        $menu = $attributes['menuId'];
    } elseif (!empty($attributes['menuName'])) {
        $menu = $attributes['menuName'];
    }
    
    if (empty($menu)) {
        return '<div class="wp-block-fau-elemental-portalmenu-error">' . 
               __('Please select a menu to display.', 'fau-elemental') . 
               '</div>';
    }
    
    // Build shortcode attributes
    $shortcode_atts = array(
        'menu' => $menu,
        'meganav' => !empty($attributes['isMegaNav']) ? 'true' : 'false',
        'showsubs' => !empty($attributes['showSubs']) ? 'true' : 'false',
        'nothumbs' => !empty($attributes['noThumbs']) ? 'true' : 'false',
        'nofallback' => !empty($attributes['noFallback']) ? 'true' : 'false',
        'type' => isset($attributes['type']) ? $attributes['type'] : 1,
        'listview' => !empty($attributes['listView']) ? 'true' : 'false',
        'hoverzoom' => !empty($attributes['hoverZoom']) ? 'true' : 'false',
        'hoverblur' => !empty($attributes['hoverBlur']) ? 'true' : 'false',
    );
    
    // Build the shortcode string
    $shortcode = '[portalmenu';
    foreach ($shortcode_atts as $key => $value) {
        $shortcode .= ' ' . $key . '="' . $value . '"';
    }
    $shortcode .= ']';
    
    // Execute the shortcode
    $output = do_shortcode($shortcode);
    
    // Add wrapper div with class names
    return '<div class="wp-block-fau-elemental-portalmenu">' . $output . '</div>';
}

echo render_block_fau_portalmenu($attributes, $content, $block);