<?php
/**
 * The template for displaying 401 pages (unauthorized)
 *
 * @package Fau-Elemental
 */

get_header();

// Set variables for the template part
$error_type = '401';
$error_title = __('Error: Unauthorized Access', 'fau-elemental');
$error_message = __('You are not authorized to access this page. Please log in with appropriate credentials or contact the administrator if you believe this is an error.', 'fau-elemental');
$search_heading = __('Perhaps the search will help you find what you\'re looking for.', 'fau-elemental');

// Include the error page template part
get_template_part('components/template-parts/error-page/error-page', null, array(
    'error_type' => $error_type,
    'error_title' => $error_title,
    'error_message' => $error_message,
    'search_heading' => $search_heading
));

get_footer();
?>
