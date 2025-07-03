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

// Ensure this file is only loaded in WordPress context
if (!function_exists('add_action')) {
    return;
}

/**
 * Unified Menu Modal Component Class (Singleton)
 */
class Menu_Modal {
    
    /**
     * The single instance of the class
     */
    private static $instance = null;
    
    private $modal_configs = [];
    private $hooks_registered = false;
    
    /**
     * Public constructor (WordPress-friendly singleton)
     */
    public function __construct() {
        // Only register hooks once, even if constructor is called multiple times
        if (!$this->hooks_registered && function_exists('add_action')) {
            add_action('wp_footer', array($this, 'render_all_modals'));
            $this->hooks_registered = true;
        }
    }
    
    /**
     * Get the singleton instance
     *
     * @return Menu_Modal The singleton instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
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
     * @param array $config The modal configuration
     */
    private function render_menu_content($config) {
        $theme_locations = $config['theme_locations'];
        $use_global_menu = $config['use_global_menu'];
        $menu_class = $config['menu_class'];
        $depth = $config['depth'];
        $walker_class = $config['walker'];
        $modal_class = $config['modal_class'];

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

        // Add language switcher for menu-website modal
        if ($modal_class === 'menu-website-modal') {
            $this->render_language_switcher();
        }

        // If no menus were rendered, show a message (optional)
        if (!$menus_rendered) {
            echo '<p class="no-menu-message">' . esc_html__('No menu items found.', 'fau-elemental') . '</p>';
        }
    }

    /**
     * Render language switcher for menu-website modal
     */
    private function render_language_switcher() {
        ?>
        <div class="menu-website-modal__language-switcher">
            <button class="menu-website-modal__language-button" aria-label="Language" aria-expanded="false">
                DE
                <span class="menu-website-modal__language-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M7 10L12 15L17 10H7Z" fill="currentColor"/>
                    </svg>
                </span>
            </button>
        </div>
        <?php
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
        
        // Generate unique IDs for proper ARIA relationships
        $modal_element_id = esc_attr($modal_id) . '-modal';
        $modal_title_id = esc_attr($modal_id) . '-modal-title';
        $modal_description_id = esc_attr($modal_id) . '-modal-description';
        ?>
        <div id="<?php echo $modal_element_id; ?>" class="<?php echo esc_attr($modal_class); ?>" style="display: none;" tabindex="-1" aria-modal="true" role="dialog" aria-hidden="true" aria-labelledby="<?php echo $modal_title_id; ?>" aria-describedby="<?php echo $modal_description_id; ?>">
            <!-- Screen reader only title and description -->
            <h2 id="<?php echo $modal_title_id; ?>" class="screen-reader-text"><?php echo esc_html($aria_label); ?></h2>
            <div id="<?php echo $modal_description_id; ?>" class="screen-reader-text"><?php esc_html_e('Use Tab to navigate through menu items, Enter to select, Escape to close, or use the Close button.', 'fau-elemental'); ?></div>
            
            <!-- Live region for screen reader announcements -->
            <div class="menu-modal__announcements screen-reader-text" aria-live="polite" aria-atomic="true"></div>
            
            <div class="<?php echo esc_attr($modal_class); ?>__overlay" aria-hidden="true"></div>
            <div class="<?php echo esc_attr($modal_class); ?>__container" role="document">
                <div class="<?php echo esc_attr($modal_class); ?>__header">
                    <?php if ($show_back_button): ?>
                        <button class="<?php echo esc_attr($modal_class); ?>__back-btn" aria-label="<?php esc_attr_e('Back to main menu', 'fau-elemental'); ?>" style="display: none;">
                            <span class="<?php echo esc_attr($modal_class); ?>__back-icon" aria-hidden="true"></span>
                            <span class="<?php echo esc_attr($modal_class); ?>__back-text"><?php esc_html_e('Zurück', 'fau-elemental'); ?></span>
                        </button>
                    <?php endif; ?>
                    
                    <?php if ($show_close_button): ?>
                        <button class="<?php echo esc_attr($modal_class); ?>__close-btn" aria-label="<?php esc_attr_e('Close menu', 'fau-elemental'); ?>">
                            <span><?php esc_html_e('Schließen', 'fau-elemental'); ?></span>
                            <span class="<?php echo esc_attr($modal_class); ?>__close-icon" aria-hidden="true"></span>
                        </button>
                    <?php endif; ?>
                </div>
                <div class="<?php echo esc_attr($modal_class); ?>__content" role="navigation" aria-label="<?php echo esc_attr($aria_label); ?>">
                    <?php $this->render_menu_content($config); ?>
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
    private $current_item_id = 0;
    
    public function start_lvl(&$output, $depth = 0, $args = null) {
        $submenu_id = 'submenu-' . $this->current_item_id;
        $output .= '<ul class="sub-menu" id="' . esc_attr($submenu_id) . '">';
    }

    public function end_lvl(&$output, $depth = 0, $args = null) {
        $output .= '</ul>';
    }

    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        // Store current item ID for use in start_lvl
        $this->current_item_id = $item->ID;
        
        $classes = empty($item->classes) ? array() : (array) $item->classes;
        $classes[] = 'menu-item';
        
        if (in_array('menu-item-has-children', $classes)) {
            $classes[] = 'has-children';
        }

        // Add current page class and data attribute
        $current_url = rtrim($_SERVER['REQUEST_URI'], '/');
        $item_url = rtrim(parse_url($item->url, PHP_URL_PATH), '/');
        $is_current = ($current_url === $item_url);
        if ($is_current) {
            $classes[] = 'current-menu-item';
        }

        $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args));
        $class_names = $class_names ? ' class="' . esc_attr($class_names) . '"' : '';

        $output .= '<li' . $class_names . ' data-menu-url="' . esc_attr($item_url) . '" data-menu-item-id="' . esc_attr($item->ID) . '">';
        
        // For items with children, create a clickable row that opens submenu
        if (in_array('menu-item-has-children', $classes)) {
            $button_classes = 'menu-modal__submenu-toggle menu-modal__submenu-row';
            $submenu_id = 'submenu-' . $item->ID;
            
            $output .= '<button class="' . esc_attr($button_classes) . '" ';
            $output .= 'aria-expanded="false" ';
            $output .= 'aria-controls="' . esc_attr($submenu_id) . '" ';
            $output .= 'aria-haspopup="true" ';
            $output .= 'aria-label="' . esc_attr(sprintf(__('Open %s submenu', 'fau-elemental'), $item->title)) . '" ';
            $output .= 'data-parent-url="' . esc_attr($item->url) . '" ';
            $output .= 'data-parent-title="' . esc_attr($item->title) . '">';
            $output .= '<span class="menu-modal__item-title">' . apply_filters('the_title', $item->title, $item->ID) . '</span>';
            $output .= '<span class="menu-modal__submenu-arrow" aria-hidden="true"></span>';
            $output .= '</button>';
        } else {
            // For items without children, keep normal link
            $link_attributes = '';
            if ($is_current) {
                $link_attributes .= ' aria-current="page"';
            }
            $output .= '<a href="' . esc_attr($item->url) . '"' . $link_attributes . '>' . apply_filters('the_title', $item->title, $item->ID) . '</a>';
        }
    }

    public function end_el(&$output, $item, $depth = 0, $args = null) {
        $output .= '</li>';
    }
}

/**
 * Hierarchy Menu Modal Walker - Shows full menu hierarchy expanded
 * Used for structure menu to display all levels at once
 */
class Menu_Modal_Hierarchy_Walker extends Walker_Nav_Menu {
    public function start_lvl(&$output, $depth = 0, $args = null) {
        $indent = str_repeat("\t", $depth);
        $output .= "\n$indent<ul class=\"sub-menu sub-menu--level-$depth\">\n";
    }

    public function end_lvl(&$output, $depth = 0, $args = null) {
        $indent = str_repeat("\t", $depth);
        $output .= "$indent</ul>\n";
    }

    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $indent = ($depth) ? str_repeat("\t", $depth) : '';
        
        $classes = empty($item->classes) ? array() : (array) $item->classes;
        $classes[] = 'menu-item';
        $classes[] = 'menu-item-depth-' . $depth;
        
        if (in_array('menu-item-has-children', $classes)) {
            $classes[] = 'has-children';
            $classes[] = 'menu-item-expanded';
        }

        // Add current page class
        $current_url = rtrim($_SERVER['REQUEST_URI'], '/');
        $item_url = rtrim(parse_url($item->url, PHP_URL_PATH), '/');
        if ($current_url === $item_url) {
            $classes[] = 'current-menu-item';
        }

        $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args));
        $class_names = $class_names ? ' class="' . esc_attr($class_names) . '"' : '';

        $id = apply_filters('nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args);
        $id = $id ? ' id="' . esc_attr($id) . '"' : '';

        $output .= $indent . '<li' . $id . $class_names . ' data-menu-url="' . esc_attr($item_url) . '" data-menu-item-id="' . esc_attr($item->ID) . '">';

        // For hierarchy view, create toggle buttons for items with children to enable breadcrumb navigation
        if (in_array('menu-item-has-children', $classes)) {
            $button_classes = 'menu-modal__submenu-toggle menu-modal__submenu-row';
            
            $output .= '<button class="' . esc_attr($button_classes) . '" aria-expanded="false" aria-label="' . esc_attr(sprintf(__('Open %s submenu', 'fau-elemental'), $item->title)) . '" data-parent-url="' . esc_attr($item->url) . '" data-parent-title="' . esc_attr($item->title) . '">';
            $output .= '<span class="menu-modal__item-title">' . apply_filters('the_title', $item->title, $item->ID) . '</span>';
            $output .= '<span class="menu-modal__submenu-arrow"></span>';
            $output .= '</button>';
        } else {
            // For items without children, use regular links
            $attributes = ! empty($item->attr_title) ? ' title="'  . esc_attr($item->attr_title) .'"' : '';
            $attributes .= ! empty($item->target)     ? ' target="' . esc_attr($item->target     ) .'"' : '';
            $attributes .= ! empty($item->xfn)        ? ' rel="'    . esc_attr($item->xfn        ) .'"' : '';
            $attributes .= ! empty($item->url)        ? ' href="'   . esc_attr($item->url        ) .'"' : '';

            $item_output = $args->before ?? '';
            $item_output .= '<a' . $attributes . '>';
            $item_output .= ($args->link_before ?? '') . apply_filters('the_title', $item->title, $item->ID) . ($args->link_after ?? '');
            $item_output .= '</a>';
            $item_output .= $args->after ?? '';

            $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
        }
    }

    public function end_el(&$output, $item, $depth = 0, $args = null) {
        $output .= "</li>\n";
    }
}

// Initialize the unified component and make it globally accessible
global $menu_modal;
$menu_modal = new Menu_Modal(); 