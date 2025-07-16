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
    public function __construct($settings = array()) {
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
            $output .= $indent . '<div class="fau-portal-item">' . "\n";
            
            // Image section
            if (!$this->settings['nothumbs']) {
                $thumbnail = false;
                $thumbnail_id = get_post_thumbnail_id($item->object_id);
                if ($thumbnail_id) {
                    $img_src = wp_get_attachment_image_src($thumbnail_id, 'medium');
                    $thumbnail = $img_src ? $img_src[0] : false;
                }

                $output .= $indent . "\t" . '<div class="fau-portal-thumbnail">';
                if ($thumbnail) {
                    $output .= '<img src="' . esc_url($thumbnail) . '" alt="' . esc_attr(sprintf(__('Featured image for %s', 'fau-elemental'), $title)) . '" loading="lazy">';
                } else {
                    $output .= '<img src="' . esc_url(get_template_directory_uri() . '/assets/images/logo.svg') . '" alt="' . esc_attr(sprintf(__('No image available for %s', 'fau-elemental'), $title)). '" loading="lazy">';
                }
                $output .= "</div>\n";
            }
            
            // Content section
            $output .= $indent . "\t" . '<div class="fau-portal-wrapper"><div class="fau-portal-content">' . "\n";
            
            // Use proper heading hierarchy (h3 for portal items)
            $output .= $indent . "\t\t";
            $output .= '<a href="' . esc_url($permalink) . '" aria-label="' . esc_attr(sprintf(__('Go to %s', 'fau-elemental'), $title)) . '">';
            $output .= '<h3>';
            $output .= esc_html($title);
            $output .= "</h3><span></span></a>\n";
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
            $output .= $indent . "</div>\n";
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
} 