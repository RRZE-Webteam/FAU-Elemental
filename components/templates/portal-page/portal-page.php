<?php
/**
 * Template Name: Portal Page
 * Template Post Type: page
 *
 * A template for portal pages that displays a menu as a portal menu.
 *
 * @package FAU-Elemental
 */

get_header();

// Get the selected menu for this page
$menu_id = get_post_meta(get_the_ID(), 'portal_menu_id', true);
$menu_name = '';
if ($menu_id) {
    $menu_obj = wp_get_nav_menu_object($menu_id);
    if ($menu_obj) {
        $menu_name = $menu_obj->name;
    }
}

// If no menu is set, try to find an old menu slug
if (!$menu_name) {
    $old_menu_slug = get_post_meta(get_the_ID(), 'portalmenu-slug', true);
    if ($old_menu_slug) {
        $menu_name = $old_menu_slug;
        $menu_obj = get_term_by('name', $old_menu_slug, 'nav_menu');
        if ($menu_obj) {
            // Save the menu ID for future use
            update_post_meta(get_the_ID(), 'portal_menu_id', $menu_obj->term_id);
        }
    }
}

// Get display options
$show_subs = !get_post_meta(get_the_ID(), 'portal_menu_hide_subs', true);
$no_thumbs = get_post_meta(get_the_ID(), 'portal_menu_hide_thumbs', true) ?: false;
$is_dark = get_post_meta(get_the_ID(), 'portal_menu_is_dark', true) ?: false;
?>

<main id="primary" class="site-main">
    <div class="is-layout-flow">
        <?php 
        // Display the page content if any
        while (have_posts()) : the_post(); 
            the_content();
        endwhile;
        
        // Display admin notice if no menu is selected
        if (!$menu_name && current_user_can('edit_pages')) {
            echo '<div class="notice notice-warning" style="padding: 15px; background-color: #fff8e5; border-left: 4px solid #ffb900; margin: 20px 0; box-shadow: 0 1px 1px rgba(0,0,0,.04);">';
            echo '<p><strong>' . esc_html__('Portal Page Notice', 'fau-elemental') . ':</strong> ' . esc_html__('No menu is selected for this portal page. Please edit this page and select a menu in the Portal Menu Settings meta box.', 'fau-elemental') . '</p>';
            echo '<p><a href="' . esc_url(get_edit_post_link()) . '" class="button button-secondary">' . esc_html__('Edit Page', 'fau-elemental') . '</a></p>';
            echo '</div>';
        }
        
        // Display the portal menu if a menu is selected
        if ($menu_name) {
            $shortcode = '[portalmenu';
            $shortcode .= ' menu="' . esc_attr($menu_name) . '"';
            $shortcode .= ' showsubs="' . ($show_subs ? 'true' : 'false') . '"';
            $shortcode .= ' nothumbs="' . ($no_thumbs ? 'true' : 'false') . '"';
            $shortcode .= ' theme="' . ($is_dark ? 'dark' : 'light') . '"';
            $shortcode .= ']';
            
            // Output the shortcode
            echo do_shortcode($shortcode);
        }
        ?>
    </div>
</main>

<?php
get_footer(); 