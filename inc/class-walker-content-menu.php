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
        'type' => 1,        // Display type (1: 2:1 ratio, 2: 3:2 ratio, 3: 3:4 ratio)
        'showsubs' => true, // Whether to show submenus
        'listview' => false, // Whether to use list view
        'nothumbs' => false, // Whether to hide thumbnails
        'nofallback' => false, // Whether to skip fallback images
        'hoverzoom' => false, // Whether to use hover zoom effect
        'hoverblur' => false, // Whether to use hover blur effect
        'theme' => 'light',  // Theme: light, dark
        'faculty_color' => '', // Faculty color: phil, med, nat, tf, rw, default is none
        'meganav' => false, // Whether to use mega navigation
        'columns' => 3,     // Default number of columns
    );

    /**
     * Menu settings for this instance
     */
    protected $settings = array();

    /**
     * Constructor
     */
    public function __construct($settings = array()) {
        $this->settings = wp_parse_args($settings, $this->defaults);
    }

    /**
     * Start level output
     */
    public function start_lvl(&$output, $depth = 0, $args = array()) {
        if (!$this->settings['showsubs']) {
            return;
        }

        $indent = str_repeat("\t", $depth);
        if ($this->settings['listview']) {
            $output .= "\n$indent<ul class=\"portal-submenu\">\n";
        } else {
            $output .= "\n$indent<div class=\"portal-submenu\">\n";
        }
    }

    /**
     * End level output
     */
    public function end_lvl(&$output, $depth = 0, $args = array()) {
        if (!$this->settings['showsubs']) {
            return;
        }

        $indent = str_repeat("\t", $depth);
        if ($this->settings['listview']) {
            $output .= "$indent</ul>\n";
        } else {
            $output .= "$indent</div>\n";
        }
    }

    /**
     * Start element output
     */
    public function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0) {
        $indent = ($depth) ? str_repeat("\t", $depth) : '';
        
        // Get menu item details
        $title = apply_filters('the_title', $item->title, $item->ID);
        $permalink = !empty($item->url) ? $item->url : '';
        
        // Generate item classes
        $classes = empty($item->classes) ? array() : (array) $item->classes;
        $classes[] = 'menu-item-' . $item->ID;
        
        // Thumbnail/image handling
        $thumbnail = '';
        $thumbnail_id = get_post_thumbnail_id($item->object_id);
        
        if (!$this->settings['nothumbs'] && $depth === 0) {
            if ($thumbnail_id) {
                $img_src = wp_get_attachment_image_src($thumbnail_id, 'medium');
                $thumbnail = $img_src[0];
            }
        }
        
        // Start element based on depth
        if ($depth === 0) {
            // Parent item (top level) - Simple card structure
            $column_class = 'portal-column-' . $this->settings['columns'];
            
            $output .= $indent . '<li class="portal-item ' . $column_class . '">';
            
            // Image section at top
            if (!$this->settings['nothumbs']) {
                $output .= '<div class="portal-thumbnail">';
                $output .= '<a href="' . esc_url($permalink) . '" class="image-link" aria-label="' . esc_attr(sprintf(__('Go to %s', 'fau-elemental'), $title)) . '">';
                if ($thumbnail) {
                    $output .= '<img src="' . esc_url($thumbnail) . '" alt="' . esc_attr(sprintf(__('Featured image for %s', 'fau-elemental'), $title)) . '" loading="lazy" />';
                } else {
                    $output .= '<div class="portal-placeholder-image" role="img" aria-label="' . esc_attr(sprintf(__('No image available for %s', 'fau-elemental'), $title)) . '">';
                    $output .= '<span>' . __('No Image', 'fau-elemental') . '</span>';
                    $output .= '</div>';
                }
                $output .= '</a>';
                $output .= '</div>';
            }
            
            // Content section
            $output .= '<div class="portal-content">';
            
            // Title as h3 with link
            $output .= '<h3 class="portal-title">';
            $output .= '<a href="' . esc_url($permalink) . '" class="portal-main-link" aria-label="' . esc_attr(sprintf(__('Go to main page: %s', 'fau-elemental'), $title)) . '">';
            $output .= esc_html($title);
            $output .= '</a>';
            $output .= '</h3>';
            
            // Start submenu list if we have children
            if ($this->settings['showsubs']) {
                $output .= '<div class="portal-submenu" role="list">';
            }
        } else {
            // Child items (sublinks) - Simple list items
            $output .= $indent . '<div class="portal-subitem" role="listitem">';
            $output .= '<a href="' . esc_url($permalink) . '" class="portal-sublink" aria-label="' . esc_attr(sprintf(__('Go to %s', 'fau-elemental'), $title)) . '">';
            $output .= '<span class="portal-link-text">' . esc_html($title) . '</span>';
            $output .= '<span class="portal-link-button" aria-hidden="true"></span>';
            $output .= '</a>';
        }
    }

    /**
     * End element output
     */
    public function end_el(&$output, $item, $depth = 0, $args = array()) {
        if ($depth === 0) {
            // Parent item (top level)
            if ($this->settings['showsubs']) {
                $output .= '</div>'; // Close .portal-content
                $output .= '</div>'; // Close .portal-submenu
            }
            $output .= "</li>\n"; // Close .portal-item
        } else {
            // Child items (sublinks)
            $output .= "</div>\n";
            $output .= "</li>\n";
        }
    }
} 