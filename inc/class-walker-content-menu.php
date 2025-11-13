<?php
/**
 * FAU Elemental Content Menu Walker
 *
 * This class handles the rendering of portal menus as content boxes
 * 
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Walker Class for Content Menus
 */
class Walker_Content_Menu extends Walker_Nav_Menu {
    /**
     * Default settings for content menu
     */
    protected $defaults = array(
        'showsubs' => true,     // Whether to show submenus
        'nothumbs' => false,    // Whether to hide thumbnails
    );

    /**
     * Menu settings for this instance
     */
    protected $settings = array();

    /**
     * Constructor
     */
    protected function __construct($settings = array()) {
        $this->settings = wp_parse_args($settings, $this->defaults);
    }

    /**
     * Start element output
     */
    public function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0) {
        $indent = str_repeat("\t", $depth + 1);

        if (!$this->settings['showsubs'] && $depth !== 0) {
            return;
        }
        
        // Get menu item details
        $title = apply_filters('the_title', $item->title, $item->ID);
        $permalink = !empty($item->url) ? $item->url : '';
        
        // Start element based on depth
        if ($depth === 0) {   
            $output .= $indent . '<li class="fau-portal-item">' . "\n";
            
            // Image section
            if (!$this->settings['nothumbs']) {
                $thumbnail = false;
                $thumbnail_id = false;

                // Prefer the menu item's object ID for performance and reliability
                if (!empty($item->object_id) && in_array($item->object, ['page', 'post'], true)) {
                    $thumbnail_id = get_post_thumbnail_id((int) $item->object_id);
                }

                // Fallback to linked post data if available (preloaded via render_portalmenu)
                if (!$thumbnail_id && !empty($item->linked_post)) {
                    $post_id = is_object($item->linked_post) ? $item->linked_post->ID : $item->linked_post;
                    $thumbnail_id = get_post_thumbnail_id((int) $post_id);
                }

                if ($thumbnail_id) {
                    $img_src = wp_get_attachment_image_src($thumbnail_id, 'medium_large');
                    $thumbnail = $img_src ? $img_src[0] : false;
                }

                $output .= $indent . "\t" . '<div class="fau-portal-thumbnail">';
                if ($thumbnail) {
                    $output .= '<img src="' . esc_url($thumbnail) . '" alt="' . esc_attr(sprintf(__('Featured image for %s', 'fau-elemental'), $title)) . '" loading="lazy">';
                } else {
                    // Use customizer fallback image if available, otherwise use default logo
                    $fallback_image_url = faue_get_post_fallback_image(null, 'medium_large');
                    $output .= '<img src="' . esc_url($fallback_image_url) . '" alt="" loading="lazy">';
                }
                $output .= "</div>\n";
            }
            
            // Content section
            $output .= $indent . "\t" . '<div class="fau-portal-wrapper"><div class="fau-portal-content">' . "\n";
            
            // Simple link without heading structure
            $output .= $indent . "\t\t";
            $output .= '<a href="' . esc_url($permalink) . '">';
            $output .= esc_html($title);
            $output .= "<span></span></a>\n";
        } else {
            // Child items (sublinks) - Simple list items with proper ARIA
            $output .= $indent . "\t\t<li>\n";
            $output .= $indent . "\t\t\t" . '<a href="' . esc_url($permalink) . '" aria-label="' . esc_attr(sprintf(__('Go to %s', 'fau-elemental'), $title)) . '">';
            $output .= esc_html($title) . "</a>\n";
        }
    }

    /**
     * End element output
     */
    public function end_el(&$output, $item, $depth = 0, $args = array()) {
        $indent = str_repeat("\t", $depth + 1);

        if (!$this->settings['showsubs'] && $depth !== 0) {
            return;
        }

        if ($depth === 0) {
            // Parent item (top level)
            $output .= $indent . "\t</div></div>\n";
            $output .= $indent . "</li>\n";
        } else {
            // Child items (sublinks)
            $output .= $indent . "\t\t</li>\n";
        }
    }

    /**
     * Start level output
     */
    public function start_lvl(&$output, $depth = 0, $args = array()) {
        if (!$this->settings['showsubs']) {
            return;
        }

        $indent = str_repeat("\t", $depth + 3);
        $output .= "$indent<ul>\n";
    }

    /**
     * End level output
     */
    public function end_lvl(&$output, $depth = 0, $args = array()) {
        if (!$this->settings['showsubs']) {
            return;
        }

        $indent = str_repeat("\t", $depth + 3);
        $output .= "$indent</ul>\n";
    }

    public static function render_portalmenu($slug, $settings = array()) {
        $menu_items = wp_get_nav_menu_items($slug);
        if (empty($menu_items)) {
            return '';
        }

        $post_ids = [];
        foreach ($menu_items as $item) {
            if ($item->menu_item_parent === 0 && ($item->object === 'page' || $item->object === 'post')) {
                $post_ids[] = (int) $item->object_id;
            }
        }

        // Limit to prevent memory issues on large sites - reduced default to 50
        $max_items = apply_filters('fau_portal_menu_max_items', 50);
        if (count($post_ids) > $max_items) {
            $post_ids = array_slice($post_ids, 0, $max_items);
        }

        if (!empty($post_ids)) {
            // Process posts in smaller batches to prevent memory exhaustion
            // Removed _prime_post_caches() to prevent double-loading and memory issues
            $linked_posts = [];
            $batch_size = 20; // Process in smaller batches
            $batches = array_chunk($post_ids, $batch_size);
            
            foreach ($batches as $batch) {
                $batch_query = new WP_Query([
                    'post__in' => $batch,
                    'post_type' => ['page', 'post'],
                    'post_status' => 'publish',
                    'posts_per_page' => count($batch),
                    'no_found_rows' => true,
                    'update_post_meta_cache' => false, // Skip meta cache to save memory
                    'update_post_term_cache' => false, // Skip term cache to save memory
                    'update_post_author_cache' => false, // Skip author cache to save memory
                ]);
                
                foreach ($batch_query->posts as $post) {
                    $linked_posts[$post->ID] = $post;
                }
                
                wp_reset_postdata();
                unset($batch_query);
            }
        } else {
            $linked_posts = [];
        }

        $menu_filter = function ($items) use ($linked_posts) {
            foreach ($items as $item) {
                if (isset($linked_posts[$item->object_id])) {
                    $item->linked_post = $linked_posts[$item->object_id];
                }
            }
            return $items;
        };

        add_filter('wp_nav_menu_objects', $menu_filter);

        // Generate menu HTML
        $out = "\n";
        $out .= '<nav class="fau-portal-menu" aria-label="' . __('Portal Menu', 'fau-elemental') . '">' . "\n";
        $out .= wp_nav_menu(
            array(
                'menu' => $slug,
                'echo' => false,
                'container' => true,
                'link_before' => '',
                'link_after' => '',
                'item_spacing' => 'discard',
                'walker' => new Walker_Content_Menu($settings)
            )
        );
        $out .= "</nav>\n";

        remove_filter('wp_nav_menu_objects', $menu_filter);

        return $out;
    }
} 