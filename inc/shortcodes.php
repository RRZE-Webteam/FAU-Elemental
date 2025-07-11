<?php
/**
 * Shortcodes for FAU Elemental
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class for FAU shortcodes
 */
class FAU_Elemental_Shortcodes {
    /**
     * Constructor
     */
    function __construct() {
        add_action('init', array($this, 'add_shortcodes'));
    }

    /**
     * Register shortcodes
     */
    function add_shortcodes() {
        add_shortcode('portalmenu', array($this, 'fau_portalmenu'));
    }

    /**
     * Portal menu shortcode
     *
     * @param array $atts Shortcode attributes
     * @param string $content Shortcode content
     * @return string HTML output
     */
    function fau_portalmenu($atts, $content = null) {        
        $atts = shortcode_atts(
            array(
                'menu'     => '',
                'showsubs' => true,
                'nothumbs' => false,
                'theme'    => 'light', // Theme: light, dark
            ), 
        $atts);

        // Normalize menu parameter - could be an ID, slug, or name
        $menu = $atts['menu'] ? esc_attr($atts['menu']) : '';
        $error = '<p>' . __("No menu could be found with the specified name", 'fau-elemental') . '</p>';
        $error .= "name=$menu";

        if (empty($menu)) {
            return $error;
        }

        // Convert attribute types
        $showsubs = is_bool($atts['showsubs']) ? $atts['showsubs'] : ($atts['showsubs'] === 'true' || $atts['showsubs'] === '1');
        $nothumbs = is_bool($atts['nothumbs']) ? $atts['nothumbs'] : ($atts['nothumbs'] === 'true' || $atts['nothumbs'] === '1');
        $theme = $atts['theme'] === 'dark' ? 'dark' : 'light';

        // Find menu by ID, slug, or name
        $term = null;
        if (is_numeric($menu)) {
            $term = wp_get_nav_menu_object($menu);
        } else {
            // Try by slug first, then by name
            $term = get_term_by('slug', $menu, 'nav_menu');
            if (!$term) {
                $term = get_term_by('name', $menu, 'nav_menu');
            }
        }
        
        if (!$term) {
            return $error;
        }
        
        $slug = $term->slug;

        // Include Walker_Content_Menu class if not already included
        if (!class_exists('Walker_Content_Menu')) {
            require_once get_template_directory() . '/inc/class-walker-content-menu.php';
        }
        
        // Set up walker settings
        $walker_settings = array(
            'showsubs' => $showsubs,
            'nothumbs' => $nothumbs,
        );
        
        $out = "\n";
        $out .= '<div class="wp-block-group' . ($theme === 'dark' ? ' is-style-dark' : '') . '">' . "\n";
        $out .= '<div class="fau-portal-menu" role="navigation" aria-label="' . __('Portal Menu', 'fau-elemental') . '">' . "\n";
        
        // Generate menu HTML
        $out .= wp_nav_menu(
            array(
                'menu' => $slug,
                'echo' => false,
                'container' => true,
                'items_wrap' => '%3$s',
                'link_before' => '',
                'link_after' => '',
                'item_spacing' => 'discard',
                'walker' => new Walker_Content_Menu($walker_settings)
            )
        );

        $out .= "</div>\n</div>\n";

        return $out;
    }
}

// Initialize shortcodes
new FAU_Elemental_Shortcodes(); 
