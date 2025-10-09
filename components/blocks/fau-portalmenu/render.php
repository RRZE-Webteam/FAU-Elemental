<?php
/**
 * Render callback for the FAU Portal Menu block.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block content.
 * @param WP_Block $block      Block instance.
 */

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
    echo '<div class="wp-block-fau-elemental-portalmenu-error" role="alert" aria-live="polite">' . 
           esc_html__('Please select a menu to display.', 'fau-elemental') . 
           '</div>';
    return;
}

// Parse attributes with defaults from config
$show_subs = isset($attributes['showSubs']) ? !empty($attributes['showSubs']) : FAU_Elemental_Portal_Menu_Config::get_default('show_subs');
$no_thumbs = isset($attributes['noThumbs']) ? !empty($attributes['noThumbs']) : FAU_Elemental_Portal_Menu_Config::get_default('hide_thumbs');

// Set up walker settings
$walker_settings = array(
    'showsubs' => $show_subs,
    'nothumbs' => $no_thumbs,
);

echo Walker_Content_Menu::render_portalmenu($menu, $walker_settings);
