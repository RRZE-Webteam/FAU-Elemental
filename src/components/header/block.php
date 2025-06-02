<?php
/**
 * Register Header Block
 *
 * @package fau-elemental
 */

function register_header_block() {
    register_block_type('fau-elemental/header', array(
        'render_callback' => 'render_header_block',
    ));
}
add_action('init', 'register_header_block');

function render_header_block() {
    require_once get_template_directory() . '/src/components/header/header.php';
    $header = new Header_Block();
    return $header->render();
}

function register_front_page_header_block() {
    register_block_type('fau-elemental/header-front-page', array(
        'render_callback' => 'render_front_page_header_block',
    ));
}
add_action('init', 'register_front_page_header_block');

function render_front_page_header_block() {
    require_once get_template_directory() . '/src/components/header/header-front-page.php';
    $header = new Header_Front_Page_Block();
    return $header->render();
} 