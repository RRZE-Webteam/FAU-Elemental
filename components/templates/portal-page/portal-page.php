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
    <div class="is-layout-flow faue-content-wrapper">
        <?php 
        // Display the page content if any
        while (have_posts()) : the_post(); 
            the_content();
        endwhile;
        
        // Display the portal menu if a menu is selected
        if ($menu_name) {

            if ($is_dark) {
                echo '<div class="wp-block-group is-style-dark">';
            }

            $shortcode = '[portalmenu';
            $shortcode .= ' menu="' . esc_attr($menu_name) . '"';
            $shortcode .= ' showsubs="' . ($show_subs ? 'true' : 'false') . '"';
            $shortcode .= ' nothumbs="' . ($no_thumbs ? 'true' : 'false') . '"';
            $shortcode .= ']';
            
            // Output the shortcode
            echo do_shortcode($shortcode);

            if ($is_dark) {
                echo '</div>';
            }
        }
        ?>
    </div>
</main>

<?php
get_footer(); 