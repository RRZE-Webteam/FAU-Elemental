<?php
/**
 * Footer Navigation Component
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Footer Navigation Component Class
 */
class Footer_Navigation {
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
    }

    /**
     * Render the footer navigation
     */
    public function render() {
        ?>
        <nav class="footer-navigation" role="navigation" aria-label="<?php esc_attr_e('Footer Navigation', 'fau-elemental'); ?>">
            <div class="footer-navigation__container">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'footer',
                    'menu_id'        => 'footer-menu',
                    'menu_class'     => 'footer-navigation__menu',
                    'container'      => false,
                    'fallback_cb'    => false,
                    'depth'          => 1,
                ));
                ?>
            </div>
        </nav>
        <?php
    }
} 