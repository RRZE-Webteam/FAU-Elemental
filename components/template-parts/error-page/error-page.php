<?php
/**
 * Template part for displaying error pages (404, 403, etc.)
 *
 * @package Fau-Elemental
 * @param string $error_type The error type (404, 403, etc.)
 * @param string $error_title The error title to display
 * @param string $error_message The error message to display
 * @param string $search_heading The heading for the search block
 */

if (!defined('ABSPATH')) {
    return;
}

// Extract variables passed from parent template
$error_type = isset($args['error_type']) ? $args['error_type'] : 'error';
$error_title = isset($args['error_title']) ? $args['error_title'] : '';
$error_message = isset($args['error_message']) ? $args['error_message'] : '';
$search_heading = isset($args['search_heading']) ? $args['search_heading'] : '';
?>

<main id="main" class="wp-block-group alignwide">
    <div class="error-<?php echo esc_attr($error_type); ?>">
        <h1><?php echo esc_html($error_title); ?></h1>
        <p><?php echo esc_html($error_message); ?></p>
        <?php
        // Add global search block with heading
        $search_block = '<!-- wp:fau-elemental/fau-global-search {"heading":"' . esc_attr($search_heading) . '","width":"content-size"} /-->';
        echo do_blocks($search_block);
        ?>
    </div>
     
    <?php
    // Add portal menu block with fallback menu
    $menus = wp_get_nav_menus();
    if (!empty($menus)) {
        // Add meta headline before portal menu
        $meta_headline_block = '<!-- wp:fau-elemental/fau-meta-headline {"headline":"' . __('Other offers', 'fau-elemental') . '","id":""} -->
<div class="wp-block-fau-elemental-fau-meta-headline" id="headline-">' . __('Other offers', 'fau-elemental') . '</div>
<!-- /wp:fau-elemental/fau-meta-headline -->';
        echo do_blocks($meta_headline_block);
        
        $first_menu = $menus[0];
        $portal_menu_block = '<!-- wp:fau-elemental/portalmenu {"menuName":"' . esc_attr($first_menu->name) . '","showSubs":true,"noThumbs":false} /-->';
        echo do_blocks($portal_menu_block);
    }
    ?>
</main>
