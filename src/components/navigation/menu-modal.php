<?php
/**
 * Unified Menu Modal Component
 * Handles both global menus (meta-nav) and local menus (website)
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Unified Menu Modal Component Class
 */
class Menu_Modal {
    
    private $modal_configs = [];
    
    /**
     * Initialize the component
     */
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_footer', array($this, 'render_all_modals'));
    }

    /**
     * Register a modal configuration
     *
     * @param string $modal_id The unique ID for the modal
     * @param array $config Configuration array containing:
     *   - theme_locations: array of theme locations to check
     *   - use_global_menu: boolean whether to use global menu from main site
     *   - modal_class: CSS class for the modal
     *   - menu_class: CSS class for the menu
     *   - aria_label: Aria label for the modal
     *   - depth: Menu depth (default 0 for unlimited)
     *   - walker: Custom walker class name (optional)
     */
    public function register_modal($modal_id, $config) {
        $default_config = array(
            'theme_locations' => array(),
            'use_global_menu' => false,
            'modal_class' => 'menu-modal',
            'menu_class' => 'menu-modal__menu',
            'aria_label' => 'Menu',
            'depth' => 0,
            'walker' => null,
            'show_back_button' => true,
            'show_close_button' => true,
        );
        
        $this->modal_configs[$modal_id] = wp_parse_args($config, $default_config);
    }

    /**
     * Enqueue necessary scripts and styles
     */
    public function enqueue_scripts() {
        wp_enqueue_style('menu-modal', get_template_directory_uri() . '/build/css/menu-modal.css');
        wp_enqueue_script('menu-modal', get_template_directory_uri() . '/build/js/menu-modal.js', array('jquery'), '1.0.0', true);
    }

    /**
     * Get menu from main site (for global menus)
     *
     * @param string $theme_location The theme location to get the menu for
     * @return array|false The menu items or false if not found
     */
    public function get_main_site_menu($theme_location) {
        if (!is_multisite()) {
            return false;
        }

        $main_site_id = get_main_site_id();
        if (!$main_site_id) {
            return false;
        }

        // Switch to main site
        switch_to_blog($main_site_id);

        // Get the menu location
        $locations = get_nav_menu_locations();
        if (!isset($locations[$theme_location])) {
            restore_current_blog();
            return false;
        }

        // Get the menu
        $menu_id = $locations[$theme_location];
        $menu_items = wp_get_nav_menu_items($menu_id);

        // Switch back to current site
        restore_current_blog();

        return $menu_items;
    }

    /**
     * Check if any of the theme locations has a menu
     *
     * @param array $theme_locations Array of theme locations to check
     * @param bool $use_global_menu Whether to check global menus
     * @return bool Whether any menu exists
     */
    public function has_menu($theme_locations, $use_global_menu = false) {
        foreach ($theme_locations as $location) {
            if ($use_global_menu) {
                $global_menu = $this->get_main_site_menu($location);
                if (!empty($global_menu)) {
                    return true;
                }
            }
            
            if (has_nav_menu($location)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Build menu HTML from menu items (for global menus)
     *
     * @param array $menu_items The menu items to build the menu from
     * @param string $menu_class The class to add to the menu
     * @param string $walker_class The walker class to use
     * @return string The menu HTML
     */
    private function build_menu_html($menu_items, $menu_class, $walker_class = null) {
        if (empty($menu_items)) {
            return '';
        }

        $walker = $walker_class ? new $walker_class() : new Menu_Modal_Walker();
        $output = '';
        
        // Start the menu
        $output .= '<ul class="' . esc_attr($menu_class) . '">';
        
        // Build the menu structure recursively
        $this->build_menu_items_recursive($menu_items, $output, $walker, 0, 0);
        
        // End the menu
        $output .= '</ul>';
        
        return $output;
    }

    /**
     * Recursively build menu items with proper nesting
     *
     * @param array $menu_items All menu items
     * @param string &$output The output string (passed by reference)
     * @param object $walker The walker instance
     * @param int $parent_id The parent item ID (0 for top level)
     * @param int $depth The current depth level
     */
    private function build_menu_items_recursive($menu_items, &$output, $walker, $parent_id = 0, $depth = 0) {
        // Get items for this level
        $current_level_items = array_filter($menu_items, function($item) use ($parent_id) {
            return $item->menu_item_parent == $parent_id;
        });

        foreach ($current_level_items as $item) {
            // Check if this item has children
            $has_children = false;
            foreach ($menu_items as $potential_child) {
                if ($potential_child->menu_item_parent == $item->ID) {
                    $has_children = true;
                    break;
                }
            }
            
            // Add proper classes to the item
            $item_classes = empty($item->classes) ? array() : (array) $item->classes;
            if ($has_children) {
                $item_classes[] = 'menu-item-has-children';
            }
            $item->classes = $item_classes;
            
            // Start this item
            $walker->start_el($output, $item, $depth, (object)[
                'before' => '',
                'after' => '',
                'has_children' => $has_children
            ]);
            
            // If this item has children, create a submenu
            if ($has_children) {
                $walker->start_lvl($output, $depth);
                $this->build_menu_items_recursive($menu_items, $output, $walker, $item->ID, $depth + 1);
                $walker->end_lvl($output, $depth);
            }
            
            // End this item
            $walker->end_el($output, $item, $depth);
        }
    }

    /**
     * Render menu content for a specific modal
     *
     * @param string $modal_id The modal ID
     * @param array $config The modal configuration
     */
    private function render_menu_content($modal_id, $config) {
        $theme_locations = $config['theme_locations'];
        $use_global_menu = $config['use_global_menu'];
        $menu_class = $config['menu_class'];
        $depth = $config['depth'];
        $walker_class = $config['walker'];

        $menus_rendered = false;

        // Try each theme location and render all that exist
        foreach ($theme_locations as $location) {
            if ($use_global_menu) {
                // Try global menu first
                $global_menu_items = $this->get_main_site_menu($location);
                if ($global_menu_items) {
                    echo $this->build_menu_html($global_menu_items, $menu_class . ' ' . $menu_class . '--' . str_replace('_', '-', $location), $walker_class);
                    $menus_rendered = true;
                    continue; // Continue to next location instead of returning
                }
            }
            
            // Fallback to local menu
            if (has_nav_menu($location)) {
                $menu_args = array(
                    'theme_location' => $location,
                    'menu_class'     => $menu_class . ' ' . $menu_class . '--' . str_replace('_', '-', $location),
                    'container'      => false,
                    'fallback_cb'    => false,
                    'depth'          => $depth,
                );
                
                if ($walker_class) {
                    $menu_args['walker'] = new $walker_class();
                }
                
                wp_nav_menu($menu_args);
                $menus_rendered = true;
                continue; // Continue to next location instead of returning
            }
        }

        // If no menus were rendered, show a message (optional)
        if (!$menus_rendered) {
            echo '<p class="no-menu-message">' . esc_html__('No menu items found.', 'fau-elemental') . '</p>';
        }
    }

    /**
     * Render a single modal
     *
     * @param string $modal_id The modal ID
     * @param array $config The modal configuration
     */
    private function render_modal($modal_id, $config) {
        $modal_class = $config['modal_class'];
        $aria_label = $config['aria_label'];
        $show_back_button = $config['show_back_button'];
        $show_close_button = $config['show_close_button'];
        ?>
        <div id="<?php echo esc_attr($modal_id); ?>-modal" class="<?php echo esc_attr($modal_class); ?>" style="display: none;" tabindex="-1" aria-modal="true" role="dialog" aria-hidden="true" aria-label="<?php echo esc_attr($aria_label); ?>">
            <div class="<?php echo esc_attr($modal_class); ?>__overlay"></div>
            <div class="<?php echo esc_attr($modal_class); ?>__container">
                <div class="<?php echo esc_attr($modal_class); ?>__header">
                    <?php if ($show_back_button): ?>
                        <button class="<?php echo esc_attr($modal_class); ?>__back-btn" aria-label="<?php esc_attr_e('Back to main menu', 'fau-elemental'); ?>" style="display: none;">
                            <svg class="<?php echo esc_attr($modal_class); ?>__back-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="<?php echo esc_attr($modal_class); ?>__back-text"><?php esc_html_e('Back', 'fau-elemental'); ?></span>
                        </button>
                    <?php endif; ?>
                    
                    <?php if ($show_close_button): ?>
                        <button class="<?php echo esc_attr($modal_class); ?>__close-btn" aria-label="<?php esc_attr_e('Close menu', 'fau-elemental'); ?>">
                            <svg class="<?php echo esc_attr($modal_class); ?>__close-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M6 18L18 6M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    <?php endif; ?>
                </div>
                <div class="<?php echo esc_attr($modal_class); ?>__content">
                    <?php $this->render_menu_content($modal_id, $config); ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render all registered modals
     */
    public function render_all_modals() {
        foreach ($this->modal_configs as $modal_id => $config) {
            $this->render_modal($modal_id, $config);
        }
    }
}

/**
 * Unified Menu Modal Walker
 */
class Menu_Modal_Walker extends Walker_Nav_Menu {
    public function start_lvl(&$output, $depth = 0, $args = null) {
        $output .= '<ul class="sub-menu">';
    }

    public function end_lvl(&$output, $depth = 0, $args = null) {
        $output .= '</ul>';
    }

    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $classes = empty($item->classes) ? array() : (array) $item->classes;
        $classes[] = 'menu-item';
        
        if (in_array('menu-item-has-children', $classes)) {
            $classes[] = 'has-children';
        }

        $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args));
        $class_names = $class_names ? ' class="' . esc_attr($class_names) . '"' : '';

        $output .= '<li' . $class_names . '>';
        
        // Add the link
        $output .= '<a href="' . esc_attr($item->url) . '">' . apply_filters('the_title', $item->title, $item->ID) . '</a>';

        // Add toggle button for items with children
        if (in_array('menu-item-has-children', $classes)) {
            $output .= '<button class="menu-modal__submenu-toggle" aria-expanded="false" aria-label="' . esc_attr($item->title) . ' submenu">';
            $output .= '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7 5L12 10L7 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
            $output .= '</button>';
        }
    }

    public function end_el(&$output, $item, $depth = 0, $args = null) {
        $output .= '</li>';
    }
}

// Initialize the unified component and make it globally accessible
global $menu_modal;
$menu_modal = new Menu_Modal(); 