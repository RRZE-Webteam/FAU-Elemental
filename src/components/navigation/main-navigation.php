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
        wp_enqueue_style('main-navigation', get_template_directory_uri() . '/src/components/navigation/main-navigation.css');
        wp_enqueue_script('main-navigation', get_template_directory_uri() . '/src/components/navigation/main-navigation.js', array('jquery'), '1.0.0', true);
    }

    /**
     * Render the main navigation
     */
    public function render() {
        ?>
        <nav class="main-navigation" role="navigation" aria-label="<?php esc_attr_e('Main Navigation', 'fau-elemental'); ?>">
            <div class="main-navigation__container">
                <button class="main-navigation__toggle" aria-expanded="false" aria-controls="main-menu">
                    <span class="screen-reader-text"><?php esc_html_e('Toggle Menu', 'fau-elemental'); ?></span>
                    <span class="main-navigation__toggle-icon"></span>
                </button>

                <?php
                wp_nav_menu(array(
                    'theme_location' => 'primary',
                    'menu_id'        => 'main-menu',
                    'menu_class'     => 'main-navigation__menu',
                    'container'      => false,
                    'fallback_cb'    => false,
                    'depth'          => 3,
                ));
                ?>
            </div>
        </nav>
        <?php
    }
}

// Initialize the component
$main_navigation = new Main_Navigation(); 