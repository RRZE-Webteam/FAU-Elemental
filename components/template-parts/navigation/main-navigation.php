<?php

/**
 * Template part for displaying the main navigation
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    return;
}

// Ensure the logo display function exists
if (!function_exists('fau_elemental_display_logo_title')) {
    require_once get_template_directory() . '/inc/logo-display.php';
}

// Check if website menu exists using unified system
$has_website_menu = fau_elemental_has_website_menu();
?>

<nav class="main-navigation">
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

        <?php if ($has_website_menu): ?>
            <div class="main-navigation__menu-container">
                <button type="button" class="main-navigation__toggle menu-modal__open-btn" aria-expanded="false" aria-controls="menu-website-modal" data-modal-target="menu-website-modal" aria-label="<?php esc_attr_e('Open menu', 'fau-elemental'); ?>">
                    <span class="main-navigation__toggle-text"><?php esc_html_e('Menu', 'fau-elemental'); ?></span>
                    <span class="main-navigation__toggle-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>
            </div>
        <?php endif; ?>
    </div>
</nav>