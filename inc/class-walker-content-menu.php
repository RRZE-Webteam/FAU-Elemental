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
            } elseif (!$this->settings['nofallback']) {
                // Use a fallback image if no thumbnail is set
                $thumbnail = get_template_directory_uri() . '/assets/img/fallback.jpg';
            }
        }
        
        // Determine theme class
        $theme_class = 'portal-' . $this->settings['theme'];
        
        // Add faculty color if set
        $faculty_class = '';
        if (!empty($this->settings['faculty_color'])) {
            $faculty_class = 'portal-faculty-' . $this->settings['faculty_color'];
        }
        
        // Start element based on depth
        if ($depth === 0) {
            // Parent item (top level)
            if ($this->settings['listview']) {
                // List view (traditional)
                $output .= $indent . '<li class="' . implode(' ', $classes) . '">';
                
                $output .= '<div class="portal-item ' . $theme_class . ' ' . $faculty_class . '">';
                if ($thumbnail && !$this->settings['nothumbs']) {
                    $hover_classes = '';
                    if ($this->settings['hoverzoom']) $hover_classes .= ' portal-hover-zoom';
                    if ($this->settings['hoverblur']) $hover_classes .= ' portal-hover-blur';
                    
                    $output .= '<div class="portal-thumbnail' . $hover_classes . '">';
                    $output .= '<img src="' . esc_url($thumbnail) . '" alt="' . esc_attr($title) . '" />';
                    $output .= '</div>';
                }
                
                $output .= '<div class="portal-content">';
                $output .= '<h3 class="portal-title">';
                $output .= '<a href="' . esc_url($permalink) . '">' . $title . '</a>';
                $output .= '</h3>';
            } else {
                // Grid view / Column view (modern)
                $type_class = 'portal-type-' . $this->settings['type'];
                $has_thumbnail = ($thumbnail && !$this->settings['nothumbs']) ? 'has-thumbnail' : 'no-thumbnail';
                
                // Calculate column class based on settings
                $column_class = 'portal-column-' . $this->settings['columns'];
                
                // Add modern FAU card layout classes
                $output .= $indent . '<li class="portal-item ' . $type_class . ' ' . $has_thumbnail . ' ' . $theme_class . ' ' . $faculty_class . ' ' . $column_class . ' fau-card">';
                
                // Image section first
                if ($thumbnail && !$this->settings['nothumbs']) {
                    $hover_classes = '';
                    if ($this->settings['hoverzoom']) $hover_classes .= ' portal-hover-zoom';
                    if ($this->settings['hoverblur']) $hover_classes .= ' portal-hover-blur';
                    
                    $output .= '<div class="portal-thumbnail fau-card-image' . $hover_classes . '">';
                    $output .= '<a href="' . esc_url($permalink) . '" class="image-link">';
                    $output .= '<img src="' . esc_url($thumbnail) . '" alt="' . esc_attr($title) . '" />';
                    $output .= '</a>';
                    $output .= '</div>';
                }
                
                // Main content section with title and arrow button
                $output .= '<div class="portal-content fau-card-content">';
                $output .= '<h3 class="portal-title fau-card-title">';
                $output .= '<a href="' . esc_url($permalink) . '" class="portal-main-link">' . $title;
                // Add arrow button based on faculty color
                $output .= '<span class="portal-button-arrow ' . $faculty_class . '"><span class="screen-reader-text">' . __('Go to', 'fau-elemental') . ' ' . $title . '</span>→</span>';
                $output .= '</a>';
                $output .= '</h3>';
            }
        } else {
            // Child items (sublinks)
            if ($this->settings['listview']) {
                $output .= $indent . '<li class="portal-subitem ' . implode(' ', $classes) . '">';
                $output .= '<a href="' . esc_url($permalink) . '">' . $title . '</a>';
            } else {
                $output .= $indent . '<div class="portal-subitem fau-card-sublink">';
                $output .= '<a href="' . esc_url($permalink) . '" class="portal-sublink">' . $title . '</a>';
            }
        }
    }

    /**
     * End element output
     */
    public function end_el(&$output, $item, $depth = 0, $args = array()) {
        if ($depth === 0) {
            // Parent item (top level)
            if ($this->settings['listview']) {
                $output .= '</div>'; // Close .portal-content
                $output .= '</div>'; // Close .portal-item
                $output .= "</li>\n";
            } else {
                $output .= '</div>'; // Close .portal-content
                $output .= "</li>\n"; // Close .portal-item as li instead of div
            }
        } else {
            // Child items (sublinks)
            if ($this->settings['listview']) {
                $output .= "</li>\n";
            } else {
                $output .= "</div>\n";
            }
        }
    }
} 