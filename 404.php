<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @package Fau-Elemental
 */

get_header();

// Set variables for the template part
$error_type = '404';
$error_title = __('Error: Page not found', 'fau-elemental');
$error_message = __('The page you are trying to access does not exist or its address has changed due to changes in the page structure.', 'fau-elemental');
$search_heading = __('Perhaps the search will help you further.', 'fau-elemental');

// Include the error page template part
get_template_part('components/template-parts/error-page/error-page', null, array(
    'error_type' => $error_type,
    'error_title' => $error_title,
    'error_message' => $error_message,
    'search_heading' => $search_heading
));

get_footer();
