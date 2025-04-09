<?php
/**
 * The header template
 *
 * @package fau-elemental
 */

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header">
    <!-- Top Navigation -->
    <div class="site-header__top">
        <?php
        require_once get_template_directory() . '/src/components/navigation/fau-navigation.php';
        if (class_exists('FAU_Navigation')) {
            $fau_nav = new FAU_Navigation();
            $fau_nav->render();
        }
        ?>
    </div>

    <!-- Main Navigation -->
    <div class="site-header__main">
        <?php
        require_once get_template_directory() . '/src/components/navigation/main-navigation.php';
        if (class_exists('Main_Navigation')) {
            $main_nav = new Main_Navigation();
            $main_nav->render();
        }
        ?>
    </div>

    <!-- Breadcrumbs -->
    <div class="site-header__breadcrumbs">
        <?php get_template_part('parts/breadcrumbs'); ?>
    </div>
</header> 