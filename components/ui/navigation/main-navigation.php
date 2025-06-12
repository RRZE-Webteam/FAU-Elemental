<?php
/**
 * Main Navigation Component
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main Navigation Component Class
 */
class Main_Navigation {
    /**
     * Initialize the component
     */
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    /**
     * Enqueue necessary scripts and styles
     */
    public function enqueue_scripts() {
        // Note: CSS is now handled by the main theme enqueue function (inc/enqueue-assets.php)
        // JavaScript is handled by the unified menu-modal system
    }

    /**
     * Render the main navigation
     */
    public function render() {
        // Ensure the logo display function exists
        if (!function_exists('fau_elemental_display_logo_title')) {
            require_once get_template_directory() . '/inc/logo-display.php';
        }

        // Check if website menu exists using unified system
        $has_website_menu = fau_elemental_has_website_menu();
        ?>
        <nav class="main-navigation" role="navigation" aria-label="<?php esc_attr_e('Main Navigation', 'fau-elemental'); ?>">
            <div class="main-navigation__container">
                <div class="main-navigation__brand">
                    <div class="main-navigation__logo">
                        <?php fau_elemental_display_logo_title(); ?>
                    </div>
                </div>

                <div class="main-navigation__direct-links">
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'primary_direct',
                        'menu_id'        => 'main-direct-links',
                        'menu_class'     => 'main-navigation__direct-menu',
                        'container'      => false,
                        'fallback_cb'    => false,
                        'depth'          => 1,
                    ));
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
        <?php
    }
} 