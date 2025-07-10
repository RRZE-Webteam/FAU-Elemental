<?php
/**
 * Render callback for the FAU Portal Menu block.
 * WCAG 2.2 Level II compliant with semantic HTML and proper ARIA support
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
$type = isset($attributes['type']) ? intval($attributes['type']) : FAU_Elemental_Portal_Menu_Config::get_default('type');
$show_subs = isset($attributes['showSubs']) ? !empty($attributes['showSubs']) : FAU_Elemental_Portal_Menu_Config::get_default('show_subs');
$is_mega_nav = isset($attributes['isMegaNav']) ? !empty($attributes['isMegaNav']) : FAU_Elemental_Portal_Menu_Config::get_default('is_mega_nav');
$list_view = isset($attributes['listView']) ? !empty($attributes['listView']) : FAU_Elemental_Portal_Menu_Config::get_default('list_view');
$no_thumbs = isset($attributes['noThumbs']) ? !empty($attributes['noThumbs']) : FAU_Elemental_Portal_Menu_Config::get_default('hide_thumbs');
$no_fallback = isset($attributes['noFallback']) ? !empty($attributes['noFallback']) : FAU_Elemental_Portal_Menu_Config::get_default('no_fallback');
$hover_zoom = isset($attributes['hoverZoom']) ? !empty($attributes['hoverZoom']) : FAU_Elemental_Portal_Menu_Config::get_default('hover_zoom');
$hover_blur = isset($attributes['hoverBlur']) ? !empty($attributes['hoverBlur']) : FAU_Elemental_Portal_Menu_Config::get_default('hover_blur');
$is_dark = isset($attributes['isDark']) ? !empty($attributes['isDark']) : FAU_Elemental_Portal_Menu_Config::get_default('is_dark');

// Setup CSS classes using configuration
$menu_classes = FAU_Elemental_Portal_Menu_Config::get_css_class('container');

// Add type-specific class
$type_config = FAU_Elemental_Portal_Menu_Config::get_type($type);
$menu_classes .= ' ' . $type_config['css_class'];

// Add optional classes
if ($list_view) {
    $menu_classes .= ' ' . FAU_Elemental_Portal_Menu_Config::get_css_class('list_view');
}
if ($no_thumbs) {
    $menu_classes .= ' ' . FAU_Elemental_Portal_Menu_Config::get_css_class('no_thumb');
}
if ($hover_zoom) {
    $menu_classes .= ' ' . FAU_Elemental_Portal_Menu_Config::get_css_class('hover_zoom');
}
if ($hover_blur) {
    $menu_classes .= ' ' . FAU_Elemental_Portal_Menu_Config::get_css_class('hover_blur');
}
if ($is_dark) {
    $menu_classes .= ' ' . FAU_Elemental_Portal_Menu_Config::get_css_class('dark_style');
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
    'columns' => FAU_Elemental_Portal_Menu_Config::get_default('columns'),
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

// Semantic HTML with proper ARIA attributes
echo '<section class="wp-block-fau-elemental-portalmenu" aria-labelledby="portal-menu-heading">';

// Hidden heading for screen readers
if ($menu_obj) {
    echo '<h2 id="portal-menu-heading" class="' . esc_attr(FAU_Elemental_Portal_Menu_Config::get_css_class('screen_reader_text')) . '">';
    echo esc_html(sprintf(
        /* translators: %s: Menu name */
        __('Portal Menu: %s', 'fau-elemental'),
        $menu_obj->name
    ));
    echo '</h2>';
}

echo '<nav class="' . esc_attr($menu_classes) . '" aria-label="' . esc_attr__('Portal Menu', 'fau-elemental') . '">';

// Render the menu
wp_nav_menu([
    'menu' => $menu,
    'container' => false,
    'menu_class' => FAU_Elemental_Portal_Menu_Config::get_css_class('menu_list'),
    'walker' => $walker,
    'fallback_cb' => false,
]);

echo '</nav>';
echo '</section>';