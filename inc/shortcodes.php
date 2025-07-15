<?php
/**
 * Shortcodes for FAU Elemental
 *
 * @package FAU-Elemental
 * 
 * Backward Compatibility Shortcodes:
 * 
 * [imagelink] - Displays a grid of logos from the old imagelink custom post type
 * 
 * Usage examples:
 * - [imagelink] - Shows all available logos
 * - [imagelink cat="partners"] - Shows logos from "partners" category
 * - [imagelink catid="5"] - Shows logos from category ID 5
 * 
 * The shortcode automatically uses migrated data from the old theme's imagelink posts,
 * or falls back to current imagelink posts if migration hasn't been performed.
 * 
 * The shortcode converts to the new logo grid block, which supports:
 * - Category filtering (cat/catid attributes)
 * - Logo images and links
 * - Responsive grid layout
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
        add_shortcode('imagelink', array($this, 'fau_imagelink'));
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

    /**
     * Image link shortcode for backward compatibility
     * Converts old [imagelink] shortcode to the new logo grid block
     *
     * @param array $atts Shortcode attributes
     * @param string $content Shortcode content
     * @return string HTML output
     */
    function fau_imagelink($atts, $content = null) {
        // Get default values - only support what the new block actually uses
        $defaults = array(
            'cat' => '',
            'catid' => 0
        );
        $atts = shortcode_atts($defaults, $atts, 'imagelink');

        // Parse and validate attributes
        $category = isset($atts['cat']) ? $atts['cat'] : '';
        $catid = isset($atts['catid']) ? intval($atts['catid']) : 0;

        // Determine category ID - priority: catid, category
        $category_id = 0;
        
        if ($catid > 0) {
            $category_id = $catid;
        } elseif (!empty($category)) {
            // Try to find category by name or slug
            if (taxonomy_exists('imagelinks_category')) {
                $term = get_term_by('name', $category, 'imagelinks_category');
                if (!$term) {
                    $term = get_term_by('slug', $category, 'imagelinks_category');
                }
                if ($term) {
                    $category_id = $term->term_id;
                }
            }
        }

        // Get logos based on category
        $logos = $this->get_logos_by_category($category_id, 'ASC', $category);

        // If no logos found, return empty
        if (empty($logos)) {
            return '';
        }

        // Build block attributes - only include what the block actually supports
        $block_attributes = array(
            'logos' => $logos
        );

        // Create block markup
        $block_markup = '<!-- wp:fau/logo-grid ' . json_encode($block_attributes) . ' -->';
        $block_markup .= '<div class="fau-logo-grid">';
        $block_markup .= '<div class="fau-logo-grid__container">';
        
        foreach ($logos as $logo) {
            if (empty($logo['imageUrl'])) {
                continue;
            }
            
            $block_markup .= '<div class="fau-logo-grid__item">';
            
            if (!empty($logo['link'])) {
                $block_markup .= '<a href="' . esc_url($logo['link']) . '" class="fau-logo-grid__link">';
            }
            
            $block_markup .= '<img src="' . esc_url($logo['imageUrl']) . '" alt="" class="fau-logo-grid__image" loading="lazy" />';
            
            if (!empty($logo['link'])) {
                $block_markup .= '</a>';
            }
            
            $block_markup .= '</div>';
        }
        
        $block_markup .= '</div>';
        $block_markup .= '</div>';
        $block_markup .= '<!-- /wp:fau/logo-grid -->';

        return $block_markup;
    }



    /**
     * Convert a single imagelink post to logo data format
     *
     * @param WP_Post $post The post object
     * @return array|null Logo data or null if invalid
     */
    private function convert_post_to_logo($post) {
        $thumbnail_id = get_post_thumbnail_id($post->ID);
        $thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'medium') : '';
        $link_url = $this->extract_link_url($post);
        $category_data = $this->get_category_data($post->ID);
        
        // Only return if we have an image or a link
        if (!$thumbnail_id && empty($link_url)) {
            return null;
        }
        
        return array(
            'imageId' => $thumbnail_id,
            'imageUrl' => $thumbnail_url,
            'link' => $link_url,
            'category' => $category_data['name']
        );
    }

    /**
     * Extract link URL from post meta fields or content
     *
     * @param WP_Post $post The post object
     * @return string Link URL
     */
    private function extract_link_url($post) {
        $url_fields = array(
            'fauval_imagelink_url',
            'imagelink_url',
            'url',
            'link_url', 
            'link', 
            'href', 
            'target_url'
        );
        
        // Check meta fields first
        foreach ($url_fields as $field) {
            $url = get_post_meta($post->ID, $field, true);
            if (!empty($url)) {
                return $url;
            }
        }
        
        // Fallback: check if post content is a URL
        if (filter_var($post->post_content, FILTER_VALIDATE_URL)) {
            return $post->post_content;
        }
        
        return '';
    }

    /**
     * Get category data for a post
     *
     * @param int $post_id Post ID
     * @return array Category data
     */
    private function get_category_data($post_id) {
        if (!taxonomy_exists('imagelinks_category')) {
            return array('name' => '', 'id' => 0);
        }
        
        $categories = wp_get_post_terms($post_id, 'imagelinks_category');
        if (is_wp_error($categories) || empty($categories)) {
            return array('name' => '', 'id' => 0);
        }
        
        return array(
            'name' => $categories[0]->name,
            'id' => $categories[0]->term_id
        );
    }

    /**
     * Get logos by category ID with ordering
     */
    private function get_logos_by_category($category_id, $order = 'ASC', $category_name = '') {
        $logos = array();

        // First try to get from migrated data
        if (function_exists('fau_elemental_get_migrated_image_links')) {
            $migrated_links = fau_elemental_get_migrated_image_links();
            
            if ($category_id > 0 || !empty($category_name)) {
                // Filter by category - only return logos from the specified category
                foreach ($migrated_links as $link) {
                    $matches_category = false;
                    
                    // Check by category ID if we have one
                    if ($category_id > 0 && isset($link['originalCategoryId']) && $link['originalCategoryId'] == $category_id) {
                        $matches_category = true;
                    }
                    
                    // Check by category name if we have one and no ID match
                    if (!$matches_category && !empty($category_name) && isset($link['category'])) {
                        // Case-insensitive comparison
                        if (strtolower($link['category']) === strtolower($category_name)) {
                            $matches_category = true;
                        }
                    }
                    
                    if ($matches_category) {
                        $logos[] = array(
                            'imageId' => $link['imageId'] ?? 0,
                            'imageUrl' => $link['imageUrl'] ?? '',
                            'link' => $link['link'] ?? '',
                            'category' => $link['category'] ?? '',
                            'title' => $link['title'] ?? '',
                            'migrated' => true
                        );
                    }
                }
                
                // If we found logos for the specific category, return them
                if (!empty($logos)) {
                    return $logos;
                }
                
                // If no logos found for the specific category, return empty array
                return array();
                
            } else {
                // No category specified - use all migrated links
                foreach ($migrated_links as $link) {
                    $logos[] = array(
                        'imageId' => $link['imageId'] ?? 0,
                        'imageUrl' => $link['imageUrl'] ?? '',
                        'link' => $link['link'] ?? '',
                        'category' => $link['category'] ?? '',
                        'title' => $link['title'] ?? '',
                        'migrated' => true
                    );
                }
            }
        }

        // If no migrated data found, try to get from current imagelink posts
        if (empty($logos) && post_type_exists('imagelink')) {
            
            $args = array(
                'post_type' => 'imagelink',
                'posts_per_page' => -1,
                'post_status' => 'publish',
                'orderby' => 'name',
                'order' => $order
            );
            
            // Only add taxonomy query if a specific category is requested
            if ($category_id > 0 && taxonomy_exists('imagelinks_category')) {
                $args['tax_query'] = array(
                    array(
                        'taxonomy' => 'imagelinks_category',
                        'field' => 'term_id',
                        'terms' => $category_id
                    )
                );
            }
            
            $imagelink_posts = get_posts($args);
            
            foreach ($imagelink_posts as $post) {
                $logo_data = $this->convert_post_to_logo($post);
                if ($logo_data) {
                    $logos[] = $logo_data;
                }
            }
        }

        return $logos;
    }




}

// Initialize shortcodes
new FAU_Elemental_Shortcodes(); 