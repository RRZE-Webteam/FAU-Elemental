<?php
/**
 * Unified Menu Modal Component
 * Handles both global menus (meta-nav) and local menus (website)
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    return;
}

// Ensure this file is only loaded in WordPress context
if (!function_exists('add_action')) {
    return;
}

/**
 * Unified Menu Modal Component Class (Singleton)
 */
class Menu_Modal {
    
    private $modal_configs = [];
    private $hooks_registered = false;
    
    /**
     * Public constructor (WordPress-friendly singleton)
     */
    public function __construct() {
        // Only register hooks once, even if constructor is called multiple times
        if (!$this->hooks_registered && function_exists('add_action')) {
            add_action('wp_footer', [$this, 'render_all_modals']);
            $this->hooks_registered = true;
        }
    }
    
    /**
     * Get the singleton instance
     * Optimized to avoid unnecessary null checks on subsequent calls
     *
     * @return Menu_Modal The singleton instance
     */
    public static function get_instance() {
        // Use static variable for better performance on subsequent calls
        static $instance = null;
        if ($instance === null) {
            $instance = new self();
        }
        return $instance;
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
        $default_config = [
            'theme_locations' => [],
            'use_global_menu' => false,
            'modal_class' => 'menu-modal',
            'menu_class' => 'menu-modal__menu',
            'aria_label' => 'Menu',
            'depth' => 0,
            'walker' => null,
            'show_back_button' => true,
            'show_close_button' => true,
            'location_depths' => [],
            'global_locations' => [],
        ];
        
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

        // Switch to main site with proper error handling
        switch_to_blog($main_site_id);
        
        try {
            // Get the menu location
            $locations = get_nav_menu_locations();
            if (!isset($locations[$theme_location])) {
                return false;
            }

            // Get the menu
            $menu_id = $locations[$theme_location];
            $menu_items = wp_get_nav_menu_items($menu_id);

            return $menu_items;
        } finally {
            // Always restore the blog context, even if an exception occurs
            restore_current_blog();
        }
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
    private function build_menu_html($menu_items, $menu_class, $walker_class = null, $max_depth = 0) {
        if (empty($menu_items)) {
            return '';
        }

        $walker = $walker_class ? new $walker_class() : new Menu_Modal_Walker();
        $output = '';
        
        // Create a mock args object for walker compatibility
        $args = (object) [
            'menu_items' => $menu_items
        ];
        
        // Create index for O(n) performance
        $index = [];
        foreach ($menu_items as $item) {
            $index[$item->menu_item_parent][] = $item;
        }
        
        // Start the menu - escape the menu class to prevent XSS
        $output .= '<ul class="' . esc_attr($menu_class) . '">';
        
        // Build the menu structure recursively
        $this->build_menu_items_recursive($index, $output, $walker, 0, 0, $args, $max_depth);
        
        // End the menu
        $output .= '</ul>';
        
        return $output;
    }

    /**
     * Recursively build menu items with proper nesting
     *
     * @param array $index Indexed menu items by parent ID
     * @param string &$output The output string (passed by reference)
     * @param object $walker The walker instance
     * @param int $parent_id The parent item ID (0 for top level)
     * @param int $depth The current depth level
     * @param object $args Optional args object for walker
     * @param int $max_depth Maximum depth to build (0 = unlimited)
     */
    private function build_menu_items_recursive($index, &$output, $walker, $parent_id = 0, $depth = 0, $args = null, $max_depth = 0) {
        // Get items for this level using the index (O(1) lookup)
        $current_level_items = isset($index[$parent_id]) ? $index[$parent_id] : [];

        foreach ($current_level_items as $item) {
            // Check if this item has children using the index (O(1) lookup)
            $has_children = isset($index[$item->ID]) && !empty($index[$item->ID]);
            
            // Add proper classes to the item
            $item_classes = empty($item->classes) ? [] : (array) $item->classes;
            if ($has_children) {
                $item_classes[] = 'menu-item-has-children';
            }
            $item->classes = $item_classes;
            
            // Start this item
            $walker->start_el($output, $item, $depth, $args ?: (object)[
                'before' => '',
                'after' => '',
                'has_children' => $has_children
            ]);
            
            // If this item has children and we haven't reached max depth, create a submenu
            if ($has_children && ($max_depth === 0 || $depth < $max_depth)) {
                $walker->start_lvl($output, $depth, $args);
                $this->build_menu_items_recursive($index, $output, $walker, $item->ID, $depth + 1, $args, $max_depth);
                $walker->end_lvl($output, $depth, $args);
            }
            
            // End this item
            $walker->end_el($output, $item, $depth, $args);
        }
    }

    /**
     * Render menu content for a specific modal
     *
     * @param array $config The modal configuration
     */
    private function render_menu_content($modal_id, $config) {
        // Shortcode fau-orga-breadcrumb structure menu
        if ($modal_id === 'structure') {
            echo do_shortcode('[fauorga show="menu"]');
            return;
        }
        // Special handling for search modal
        if ($modal_id === 'search') {
            // Check if RRZE Search plugin is active
            if (is_plugin_active('rrze-search/rrze-search.php')) {
                // Use the plugin's search sidebar
                dynamic_sidebar('rrze-search-sidebar');
            } else {
                // Use WordPress's block rendering system to render the fau-global-search block
                $block_content = '<!-- wp:fau-elemental/fau-global-search {"title":"' . esc_attr(__('Search', 'fau-elemental')) . '","searchScope":"fau-wide"} /-->';
                
                echo '<div class="menu-modal__search-wrapper">';
                echo '<h3 class="menu-modal__search-heading">' . __('Search all pages and documents:', 'fau-elemental') . '</h3>';
                echo do_blocks($block_content);
                echo '</div>';
            }
            return;
        }

        $theme_locations = $config['theme_locations'];
        $use_global_menu = $config['use_global_menu'];
        $menu_class = $config['menu_class'];
        $depth = $config['depth'];
        $walker_class = $config['walker'];
        $modal_class = $config['modal_class'];
        $location_depths = $config['location_depths'];
        $global_locations = $config['global_locations'];

        $menus_rendered = false;

        // Try each theme location and render all that exist
        foreach ($theme_locations as $location) {
            // Get the depth for this specific location, fallback to default depth
            $location_depth = isset($location_depths[$location]) ? $location_depths[$location] : $depth;
            
            // Check if this location should use global menu
            $is_global_location = in_array($location, $global_locations);
            
            if ($use_global_menu && $is_global_location) {
                // Try global menu first
                $global_menu_items = $this->get_main_site_menu($location);
                if ($global_menu_items) {
                    $location_class = sanitize_html_class(str_replace('_', '-', $location));
                    // Split the menu_class by spaces to handle multiple classes properly
                    $menu_classes = explode(' ', $menu_class);
                    $sanitized_menu_classes = array_map('sanitize_html_class', $menu_classes);
                    $base_menu_class = implode(' ', $sanitized_menu_classes);
                    $combined_menu_class = $base_menu_class . ' ' . $base_menu_class . '--' . $location_class;
                    echo $this->build_menu_html($global_menu_items, $combined_menu_class, $walker_class, $location_depth);
                    $menus_rendered = true;
                    continue; // Continue to next location instead of returning
                }
            }
            
            // Fallback to local menu
            if (has_nav_menu($location)) {
                $location_class = sanitize_html_class(str_replace('_', '-', $location));
                // Split the menu_class by spaces to handle multiple classes properly
                $menu_classes = explode(' ', $menu_class);
                $sanitized_menu_classes = array_map('sanitize_html_class', $menu_classes);
                $base_menu_class = implode(' ', $sanitized_menu_classes);
                $combined_menu_class = $base_menu_class . ' ' . $base_menu_class . '--' . $location_class;
                
                $menu_args = [
                    'theme_location' => $location,
                    'menu_class'     => $combined_menu_class,
                    'container'      => false,
                    'fallback_cb'    => false,
                    'depth'          => $location_depth,
                ];
                
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

        // Add language switcher after the last navigation item
        $this->render_language_switcher($modal_class);
    }

    /**
     * Render language switcher widget for modals
     *
     * @param string $modal_class The modal class name
     */
    private function render_language_switcher($modal_class = 'menu-modal') {
        // Check if the language switcher widget area has widgets
        if (!is_active_sidebar('language-switcher')) {
            return;
        }
        
        $modal_class_sanitized = sanitize_html_class($modal_class);
        ?>
        <div class="<?php echo esc_attr($modal_class_sanitized); ?>__language-switcher fau-language-switcher-wrapper">
            <?php dynamic_sidebar('language-switcher'); ?>
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
        <div id="<?php echo $modal_element_id; ?>" class="<?php echo esc_attr($modal_class); ?> u-hidden" tabindex="-1" aria-modal="true" role="dialog" aria-hidden="true" aria-labelledby="<?php echo $modal_title_id; ?>" aria-describedby="<?php echo $modal_description_id; ?>">
            <!-- Screen reader only title and description -->
            <h2 id="<?php echo $modal_title_id; ?>" class="screen-reader-text"><?php echo esc_html($aria_label); ?></h2>
            <div id="<?php echo $modal_description_id; ?>" class="screen-reader-text"><?php esc_html_e('Use Tab to navigate through menu items, Enter to select, Escape to close, or use the Close button.', 'fau-elemental'); ?></div>
            
            <!-- Live region for screen reader announcements -->
            <div class="menu-modal__announcements screen-reader-text" aria-live="polite" aria-atomic="true"></div>
            
            <div class="<?php echo esc_attr($modal_class); ?>__overlay" aria-hidden="true"></div>
            <div class="<?php echo esc_attr($modal_class); ?>__container" role="document">
                <div class="<?php echo esc_attr($modal_class); ?>__header">
                    <?php if ($show_back_button): ?>
                        <button type="button" class="<?php echo esc_attr($modal_class); ?>__back-btn u-hidden" aria-label="<?php esc_attr_e('Back to main menu', 'fau-elemental'); ?>">
                            <span class="<?php echo esc_attr($modal_class); ?>__back-icon" aria-hidden="true"></span>
                            <span class="<?php echo esc_attr($modal_class); ?>__back-text"><?php esc_html_e('Back', 'fau-elemental'); ?></span>
                        </button>
                    <?php endif; ?>
                    
                    <?php if ($show_close_button): ?>
                        <button type="button" class="<?php echo esc_attr($modal_class); ?>__close-btn" aria-label="<?php esc_attr_e('Close menu', 'fau-elemental'); ?>">
                            <span><?php esc_html_e('Close', 'fau-elemental'); ?></span>
                            <span class="<?php echo esc_attr($modal_class); ?>__close-icon" aria-hidden="true"></span>
                        </button>
                    <?php endif; ?>
                </div>
                <div class="<?php echo esc_attr($modal_class); ?>__content" role="navigation" aria-label="<?php echo esc_attr($aria_label); ?>">
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
    private $current_item_id = 0;
    
    /**
     * Check if submenu functionality should be disabled for this location
     *
     * @param object $args Menu arguments
     * @return bool True if submenus should be disabled
     */
    private function should_disable_submenus($args) {
        // Check if we're in the header_menu_links location
        if ($args && isset($args->theme_location) && $args->theme_location === 'header_menu_links') {
            return true;
        }
        
        // Also check if we're in a context where depth is limited to 1
        if ($args && isset($args->depth) && $args->depth === 1) {
            return true;
        }
        
        return false;
    }
    
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
        
        $classes = empty($item->classes) ? [] : (array) $item->classes;
        $classes[] = 'menu-item';
        
        // Check if we should disable submenu functionality for this location
        $disable_submenus = $this->should_disable_submenus($args);
        
        if (in_array('menu-item-has-children', $classes) && !$disable_submenus) {
            $classes[] = 'has-children';
        }

        // Add current page class and data attribute
        $current_url = rtrim(wp_parse_url(home_url(add_query_arg([])), PHP_URL_PATH), '/');
        $item_url = rtrim(parse_url($item->url, PHP_URL_PATH), '/');
        $is_current = ($current_url === $item_url);
        if ($is_current) {
            $classes[] = 'current-menu-item';
        }

        $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args));
        $class_names = $class_names ? ' class="' . esc_attr($class_names) . '"' : '';

        // Escape URLs and data attributes to prevent XSS
        $escaped_item_url = esc_attr($item_url);
        $escaped_item_id = esc_attr($item->ID);
        $escaped_item_title = esc_html(wp_strip_all_tags(apply_filters('the_title', $item->title, $item->ID)));
        $escaped_item_url_full = esc_url($item->url);

        $output .= '<li' . $class_names . ' data-menu-url="' . $escaped_item_url . '" data-menu-item-id="' . $escaped_item_id . '">';
        
        // For items with children, create a clickable row that opens submenu
        if (in_array('menu-item-has-children', $classes) && !$disable_submenus) {
            $button_classes = 'menu-modal__submenu-toggle menu-modal__submenu-row';
            $submenu_id = 'submenu-' . $escaped_item_id;
            
            $output .= '<button type="button" class="' . esc_attr($button_classes) . '" ';
            $output .= 'aria-expanded="false" ';
            $output .= 'aria-controls="' . esc_attr($submenu_id) . '" ';
            $output .= 'aria-haspopup="true" ';
            // translators: title of the submenu
            $output .= 'aria-label="' . esc_attr(sprintf(__('Open %s submenu', 'fau-elemental'), $escaped_item_title)) . '" ';
            $output .= 'data-parent-url="' . $escaped_item_url_full . '" ';
            $output .= 'data-parent-title="' . esc_attr($escaped_item_title) . '">';
            $output .= '<span class="menu-modal__item-title">' . $escaped_item_title . '</span>';
            $output .= '<span class="menu-modal__submenu-arrow" aria-hidden="true"></span>';
            $output .= '</button>';
        } else {
            // For items without children, keep normal link
            $link_attributes = '';
            if ($is_current) {
                $link_attributes .= ' aria-current="page"';
            }
            $output .= '<a href="' . $escaped_item_url_full . '"' . $link_attributes . '>' . $escaped_item_title . '</a>';
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
        
        $classes = empty($item->classes) ? [] : (array) $item->classes;
        $classes[] = 'menu-item';
        $classes[] = 'menu-item-depth-' . $depth;
        
        if (in_array('menu-item-has-children', $classes)) {
            $classes[] = 'has-children';
            $classes[] = 'menu-item-expanded';
        }

        // Add current page class
        $current_url = rtrim(wp_parse_url(home_url(add_query_arg([])), PHP_URL_PATH), '/');
        $item_url = rtrim(parse_url($item->url, PHP_URL_PATH), '/');
        if ($current_url === $item_url) {
            $classes[] = 'current-menu-item';
        }

        $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args));
        $class_names = $class_names ? ' class="' . esc_attr($class_names) . '"' : '';

        $id = apply_filters('nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args);
        $id = $id ? ' id="' . esc_attr($id) . '"' : '';

        // Escape URLs and data attributes to prevent XSS
        $escaped_item_url = esc_attr($item_url);
        $escaped_item_id = esc_attr($item->ID);
        $escaped_item_title = esc_html(wp_strip_all_tags(apply_filters('the_title', $item->title, $item->ID)));
        $escaped_item_url_full = esc_url($item->url);

        $output .= $indent . '<li' . $id . $class_names . ' data-menu-url="' . $escaped_item_url . '" data-menu-item-id="' . $escaped_item_id . '">';

        // For hierarchy view, create toggle buttons for items with children to enable breadcrumb navigation
        if (in_array('menu-item-has-children', $classes)) {
            $button_classes = 'menu-modal__submenu-toggle menu-modal__submenu-row';
            
            $output .= '<button type="button" class="' . esc_attr($button_classes) . '" aria-expanded="false" aria-label="' . esc_attr(sprintf(__('Open %s submenu', 'fau-elemental'), $escaped_item_title)) . '" data-parent-url="' . $escaped_item_url_full . '" data-parent-title="' . esc_attr($escaped_item_title) . '">';
            $output .= '<span class="menu-modal__item-title">' . $escaped_item_title . '</span>';
            $output .= '<span class="menu-modal__submenu-arrow"></span>';
            $output .= '</button>';
        } else {
            // For items without children, use regular links
            $attributes = ! empty($item->attr_title) ? ' title="'  . esc_attr($item->attr_title) .'"' : '';
            $attributes .= ! empty($item->target)     ? ' target="' . esc_attr($item->target     ) .'"' : '';
            $attributes .= ! empty($item->xfn)        ? ' rel="'    . esc_attr($item->xfn        ) .'"' : '';
            $attributes .= ! empty($item->url)        ? ' href="'   . $escaped_item_url_full .'"' : '';

            $item_output = $args->before ?? '';
            $item_output .= '<a' . $attributes . '>';
            $item_output .= ($args->link_before ?? '') . $escaped_item_title . ($args->link_after ?? '');
            $item_output .= '</a>';
            $item_output .= $args->after ?? '';

            $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
        }
    }

    public function end_el(&$output, $item, $depth = 0, $args = null) {
        $output .= "</li>\n";
    }
}

/**
 * Localize menu modal script with translatable strings
 */
function fau_elemental_localize_menu_modal_script() {
    // Only localize if the script is enqueued
    if (wp_script_is('faue-menu-modal', 'enqueued')) {
        wp_localize_script(
            'faue-menu-modal',
            'fauElementalMenuModal',
            array(
                'strings' => array(
                    'overview' => __('Overview:', 'fau-elemental'),
                    'menuOpened' => __('Menu opened', 'fau-elemental'),
                    'menuClosed' => __('Menu closed', 'fau-elemental'),
                    'navigatedTo' => __('Navigated to', 'fau-elemental'),
                    'submenu' => __('submenu', 'fau-elemental'),
                    'submenuCollapsed' => __('submenu collapsed', 'fau-elemental'),
                    'submenuExpanded' => __('submenu expanded', 'fau-elemental'),
                    'navigatedBack' => __('Navigated back', 'fau-elemental'),
                    'movedToLastMenuItem' => __('Moved to last menu item', 'fau-elemental'),
                    'movedToCloseButton' => __('Moved to close button', 'fau-elemental'),
                    'menuBreadcrumbs' => __('Menu breadcrumbs', 'fau-elemental'),
                    'goTo' => __('Go to', 'fau-elemental'),
                )
            )
        );
    }
}
add_action('wp_enqueue_scripts', 'fau_elemental_localize_menu_modal_script', 30);
