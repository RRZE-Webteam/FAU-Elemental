<?php
/**
 * Navigation Components
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

// Include the unified menu modal system first
require_once __DIR__ . '/menu-modal-config.php';

// Include navigation components
require_once __DIR__ . '/fau-navigation.php';
require_once __DIR__ . '/main-navigation.php';
require_once __DIR__ . '/footer-navigation.php';

// Initialize navigation components globally to ensure CSS/JS are enqueued
function fau_elemental_init_navigation_components() {
    global $fau_navigation, $main_navigation, $footer_navigation;
    
    // Initialize components so their enqueue hooks are registered
    $fau_navigation = new FAU_Navigation();
    $main_navigation = new Main_Navigation();
    $footer_navigation = new Footer_Navigation();
}
add_action('init', 'fau_elemental_init_navigation_components'); 