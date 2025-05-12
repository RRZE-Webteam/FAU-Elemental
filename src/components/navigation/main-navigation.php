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
        wp_enqueue_style('main-navigation', get_template_directory_uri() . '/src/components/navigation/main-navigation.scss');
        wp_enqueue_script('main-navigation', get_template_directory_uri() . '/src/components/navigation/main-navigation.js', array('jquery'), '1.0.0', true);
    }

    /**
     * Render the main navigation
     */
    public function render() {
        $has_website_menu = has_nav_menu('primary');
        ?>
        <nav class="main-navigation" role="navigation" aria-label="<?php esc_attr_e('Main Navigation', 'fau-elemental'); ?>">
            <div class="main-navigation__container">
                <div class="main-navigation__brand">
                    <div class="main-navigation__logo">
                        <?php fau_elemental_display_logo('regular', 'main-navigation__logo-image'); ?>
                    </div>
                    <div class="main-navigation__university-name">
                        <?php fau_elemental_display_university_name('main-navigation__university-name-text'); ?>
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
                        <button class="main-navigation__toggle" aria-expanded="false" aria-controls="menu-website-modal">
                            <span class="main-navigation__toggle-text">Menü</span>
                            <span class="main-navigation__toggle-icon"></span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
        <?php
    }
}

// Initialize the component
$main_navigation = new Main_Navigation(); 