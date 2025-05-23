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
    
    // Parse attributes with defaults
    $type = isset($attributes['type']) ? intval($attributes['type']) : 1;
    $show_subs = !empty($attributes['showSubs']);
    $is_mega_nav = !empty($attributes['isMegaNav']);
    $list_view = !empty($attributes['listView']);
    $no_thumbs = !empty($attributes['noThumbs']);
    $no_fallback = !empty($attributes['noFallback']);
    $hover_zoom = !empty($attributes['hoverZoom']);
    $hover_blur = !empty($attributes['hoverBlur']);
    
    // Setup CSS classes
    $menu_classes = 'contentmenu';
    
    // Add size class based on type
    if ($type == 1) {
        $menu_classes .= ' size_2-1';
    } elseif ($type == 2) {
        $menu_classes .= ' size_3-2';
    } elseif ($type == 3) {
        $menu_classes .= ' size_3-4';
    }
    
    // Add optional classes
    if ($list_view) {
        $menu_classes .= ' listview';
    }
    if ($no_thumbs) {
        $menu_classes .= ' no-thumb';
    }
    if ($hover_zoom) {
        $menu_classes .= ' hover-zoom';
    }
    if ($hover_blur) {
        $menu_classes .= ' hover-blur';
    }
    
    // Create Walker instance with settings
    $walker = new Walker_Content_Menu([
        'type' => $type,
        'showsubs' => $show_subs,
        'listview' => $list_view,
        'nothumbs' => $no_thumbs,
        'nofallback' => $no_fallback,
        'hoverzoom' => $hover_zoom,
        'hoverblur' => $hover_blur,
        'meganav' => $is_mega_nav,
        'columns' => 3, // Default columns
    ]);
    
    // Buffer the output
    ob_start();
    
    echo '<div class="wp-block-fau-elemental-portalmenu">';
    echo '<div class="' . esc_attr($menu_classes) . '">';
    
    // Render the menu directly
    wp_nav_menu([
        'menu' => $menu,
        'container' => false,
        'menu_class' => 'subpages-menu',
        'walker' => $walker,
        'fallback_cb' => false,
    ]);
    
    echo '</div>';
    echo '</div>';
    
    return ob_get_clean();
} 