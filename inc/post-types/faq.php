<?php
/**
 * FAQ Custom Post Type
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register FAQ custom post type
 */
function fau_register_faq_post_type() {
    $labels = array(
        'name'                  => _x('FAQs', 'Post type general name', 'fau-elemental'),
        'singular_name'         => _x('FAQ', 'Post type singular name', 'fau-elemental'),
        'menu_name'            => _x('FAQs', 'Admin Menu text', 'fau-elemental'),
        'name_admin_bar'       => _x('FAQ', 'Add New on Toolbar', 'fau-elemental'),
        'add_new'              => __('Add New', 'fau-elemental'),
        'add_new_item'         => __('Add New FAQ', 'fau-elemental'),
        'new_item'             => __('New FAQ', 'fau-elemental'),
        'edit_item'            => __('Edit FAQ', 'fau-elemental'),
        'view_item'            => __('View FAQ', 'fau-elemental'),
        'all_items'            => __('All FAQs', 'fau-elemental'),
        'search_items'         => __('Search FAQs', 'fau-elemental'),
        'not_found'            => __('No FAQs found.', 'fau-elemental'),
        'not_found_in_trash'   => __('No FAQs found in Trash.', 'fau-elemental'),
        'featured_image'       => _x('FAQ Cover Image', 'Overrides the "Featured Image" phrase', 'fau-elemental'),
        'archives'             => _x('FAQ Archives', 'The post type archive label used in nav menus', 'fau-elemental'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'faq'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 20,
        'menu_icon'          => 'dashicons-editor-help',
        'supports'           => array('title', 'editor', 'excerpt'),
        'show_in_rest'       => true, // Enable Gutenberg editor
    );

    register_post_type('faq', $args);
}
add_action('init', 'fau_register_faq_post_type');

/**
 * Add FAQ categories
 */
function fau_register_faq_taxonomy() {
    $labels = array(
        'name'              => _x('FAQ Categories', 'taxonomy general name', 'fau-elemental'),
        'singular_name'     => _x('FAQ Category', 'taxonomy singular name', 'fau-elemental'),
        'search_items'      => __('Search FAQ Categories', 'fau-elemental'),
        'all_items'         => __('All FAQ Categories', 'fau-elemental'),
        'parent_item'       => __('Parent FAQ Category', 'fau-elemental'),
        'parent_item_colon' => __('Parent FAQ Category:', 'fau-elemental'),
        'edit_item'         => __('Edit FAQ Category', 'fau-elemental'),
        'update_item'       => __('Update FAQ Category', 'fau-elemental'),
        'add_new_item'      => __('Add New FAQ Category', 'fau-elemental'),
        'new_item_name'     => __('New FAQ Category Name', 'fau-elemental'),
        'menu_name'         => __('Categories', 'fau-elemental'),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'          => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'          => array('slug' => 'faq-category'),
        'show_in_rest'      => true,
    );

    register_taxonomy('faq_category', array('faq'), $args);
}
add_action('init', 'fau_register_faq_taxonomy'); 