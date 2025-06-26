<?php
/**
 * FAU Elemental Theme Configuration
 *
 * @package FAU_Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}
// Default Values
$faue_defaults = array(
    // Website Type
    'faue_website_type' => 'fau',
    // Breadcrumb Mode
    'faue_breadcrumb_variant_blue' => true,
    // Search Results Configuration
    'faue_search_excerpt_length' => 30,
    'faue_search_separator' => '|',
    'faue_search_arrow' => '→',
);

// Helper function to get default values
function faue_get_default($key, $subkey = null) {
    global $faue_defaults;
    
    if ($subkey !== null) {
        return isset($faue_defaults[$key][$subkey]) ? $faue_defaults[$key][$subkey] : null;
    }
    
    return isset($faue_defaults[$key]) ? $faue_defaults[$key] : null;
} 