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
        global $fau_elemental_options;
        
        $atts = shortcode_atts(
            array(
                'menu' => '',
                'meganav' => false,
                'showsubs' => true,
                'nothumbs' => false,
                'nofallback' => false,
                'type' => 1,
                'columns' => 3,
                'listview' => false,
                'hoverzoom' => false,
                'hoverblur' => false,
            ), 
        $atts);

        $out = '';

        // Normalize menu parameter - could be an ID, slug, or name
        $menu = $atts['menu'] ? esc_attr($atts['menu']) : '';
        $error = '<p>' . __("No menu could be found with the specified name", 'fau-elemental') . '</p>';
        $error .= "name=$menu";
        
        if (!empty($menu)) {
            // Get default options from theme
            $global_hoverzoom = isset($fau_elemental_options['portalmenus_hover_zoom']) ? $fau_elemental_options['portalmenus_hover_zoom'] : false;
            $global_hoverzoom = filter_var($global_hoverzoom, FILTER_VALIDATE_BOOLEAN);
            $global_hoverblur = isset($fau_elemental_options['portalmenus_hover_blur']) ? $fau_elemental_options['portalmenus_hover_blur'] : false;
            $global_hoverblur = filter_var($global_hoverblur, FILTER_VALIDATE_BOOLEAN);

            // Convert string attributes to boolean
            $atts['meganav'] = is_bool($atts['meganav']) ? $atts['meganav'] : ($atts['meganav'] === 'true' || $atts['meganav'] === '1');
            $atts['showsubs'] = is_bool($atts['showsubs']) ? $atts['showsubs'] : ($atts['showsubs'] === 'true' || $atts['showsubs'] === '1');
            $atts['nothumbs'] = is_bool($atts['nothumbs']) ? $atts['nothumbs'] : ($atts['nothumbs'] === 'true' || $atts['nothumbs'] === '1');
            $atts['nofallback'] = is_bool($atts['nofallback']) ? $atts['nofallback'] : ($atts['nofallback'] === 'true' || $atts['nofallback'] === '1');
            $atts['listview'] = is_bool($atts['listview']) ? $atts['listview'] : ($atts['listview'] === 'true' || $atts['listview'] === '1');
            $atts['hoverzoom'] = is_bool($atts['hoverzoom']) ? $atts['hoverzoom'] : ($atts['hoverzoom'] === 'true' || $atts['hoverzoom'] === '1');
            $atts['hoverblur'] = is_bool($atts['hoverblur']) ? $atts['hoverblur'] : ($atts['hoverblur'] === 'true' || $atts['hoverblur'] === '1');
            
            $meganav = $atts['meganav'];
            $listview = $atts['listview'];
            $showsubs = $atts['showsubs'];
            $nothumbs = $atts['nothumbs'];
            $nofallback = $atts['nofallback'];
            $hoverzoom = $atts['hoverzoom'] ? $atts['hoverzoom'] : $global_hoverzoom;
            $hoverblur = $atts['hoverblur'] ? $atts['hoverblur'] : $global_hoverblur;
            $type = intval($atts['type']);
            $columns = intval($atts['columns']) ?: 3;

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
            $subentries = isset($fau_elemental_options['default_submenu_entries']) ? $fau_elemental_options['default_submenu_entries'] : 5;
            
            if ($showsubs === false) {
                $subentries = 0;
            }

            $a_contentmenuclasses = array('contentmenu');
            $thumbnail = 'medium';

            switch ($type) {
                case 1:
                    $thumbnail = 'medium';
                    $a_contentmenuclasses[] = 'size_2-1';
                    break;
                case 2:
                    $a_contentmenuclasses[] = 'size_3-2';
                    $thumbnail = 'full';
                    break;
                case 3:
                    $a_contentmenuclasses[] = 'size_3-4';
                    $thumbnail = 'full';
                    break;
                default:
                    $thumbnail = 'medium';
                    $type = 1;
                    $a_contentmenuclasses[] = 'size_2-1';
                    break;
            }

            // Add classes based on options
            if ($meganav) {
                $a_contentmenuclasses[] = 'meganav';
            }
            if (!$showsubs) {
                $a_contentmenuclasses[] = 'no-sub';
            }
            if ($nofallback) {
                $a_contentmenuclasses[] = 'no-fallback';
            }
            if ($nothumbs) {
                $a_contentmenuclasses[] = 'no-thumb';
            }
            if ($listview) {
                $a_contentmenuclasses[] = 'listview';
            }
            if ($hoverzoom) {
                $a_contentmenuclasses[] = 'hover-zoom';
            }
            if ($hoverblur) {
                $a_contentmenuclasses[] = 'hover-blur';
            }
            
            // Include Walker_Content_Menu class if not already included
            if (!class_exists('Walker_Content_Menu')) {
                require_once get_template_directory() . '/inc/class-walker-content-menu.php';
            }
            
            $out .= '<div class="' . implode(' ', $a_contentmenuclasses) . '" role="navigation" aria-label="' . __('Content Menu', 'fau-elemental') . '">';
            
            // Set up walker settings
            $walker_settings = array(
                'type' => $type,
                'showsubs' => $showsubs,
                'listview' => $listview,
                'nothumbs' => $nothumbs,
                'nofallback' => $nofallback,
                'hoverzoom' => $hoverzoom,
                'hoverblur' => $hoverblur,
                'columns' => $columns
            );
            
            // Generate menu HTML
            $outnav = wp_nav_menu(
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
            
            if ($listview) {
                $out .= $outnav;
            } else {
                $out .= '<ul class="subpages-menu">';
                $out .= $outnav;
                $out .= '</ul>';
            }
            $out .= '</div>';
        } else {
            $out = $error;
        }
        
        return $out;
    }
}

// Initialize shortcodes
new FAU_Elemental_Shortcodes(); 