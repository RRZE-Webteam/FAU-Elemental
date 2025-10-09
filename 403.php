<?php
/**
 * The template for displaying 403 pages (forbidden)
 *
 * @package Fau-Elemental
 */

get_header();

// Set variables for the template part
$error_type = '403';
$error_title = __('Error: Access Forbidden', 'fau-elemental');
$error_message = __('You do not have permission to access this page. Please contact the administrator if you believe this is an error.', 'fau-elemental');
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
