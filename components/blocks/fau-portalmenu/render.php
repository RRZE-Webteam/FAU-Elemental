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
$is_dark =   isset($attributes['isDark'])   ? !empty($attributes['isDark'])   : FAU_Elemental_Portal_Menu_Config::get_default('is_dark');

// Create Walker instance with settings
$walker = new Walker_Content_Menu([
    'showsubs' => $show_subs,
    'nothumbs' => $no_thumbs,
]);

// Get menu object for accessibility
$menu_obj = null;
if (is_numeric($menu)) {
    $menu_obj = wp_get_nav_menu_object($menu);
} else {
    $menu_obj = get_term_by('name', $menu, 'nav_menu');
    if (!$menu_obj) {
        $menu_obj = get_term_by('slug', $menu, 'nav_menu');
    }
}

echo '<div class="wp-block-group' . ($is_dark ? ' is-style-dark' : '') . '">' . "\n";
echo '<div class="fau-portal-menu" role="navigation" aria-label="' . __('Portal Menu', 'fau-elemental') . '">' . "\n";

// Render the menu
wp_nav_menu([
    'menu' => $menu,
    'echo' => true,
    'container' => true,
    'link_before' => '',
    'link_after' => '',
    'item_spacing' => 'discard',
    'walker' => $walker
]);

echo '</div></div>';
