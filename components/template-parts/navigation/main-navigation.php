<?php
/**
 * Template part for displaying the main navigation
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

// Ensure the logo display function exists
if (!function_exists('fau_elemental_display_logo_title')) {
    require_once get_template_directory() . '/inc/logo-display.php';
}

// Check if website menu exists using unified system
$has_website_menu = fau_elemental_has_website_menu();
?>

<nav class="main-navigation" aria-label="<?php esc_attr_e('Main Navigation', 'fau-elemental'); ?>">
    <div class="main-navigation__container">
        <div class="main-navigation__logo">
            <?php fau_elemental_display_logo_title(); ?>
        </div>

        <div class="main-navigation__direct-links">
            <?php
            // Check if primary menu exists
            if (has_nav_menu('header_direct_links_menu')) {
                wp_nav_menu(array(
                    'theme_location' => 'header_direct_links_menu',
                    'menu_id'        => 'main-direct-links',
                    'menu_class'     => 'main-navigation__direct-menu',
                    'container'      => false,
                    'fallback_cb'    => false,
                    'depth'          => 1, // Only show top-level items in direct links
                ));
            }
            ?>
        </div>

        <div class="main-navigation__menu-container">
            <?php if ($has_website_menu): ?>
                <button class="main-navigation__toggle menu-modal__open-btn" aria-expanded="false" aria-controls="menu-website-modal" data-modal-target="menu-website-modal">
                    <span class="main-navigation__toggle-text">Menü</span>
                    <span class="main-navigation__toggle-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>
            <?php endif; ?>
        </div>
    </div>
</nav> 