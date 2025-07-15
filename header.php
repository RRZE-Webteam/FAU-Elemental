<?php
/**
 * The header template
 *
 * @package Fau-Elemental
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
    <header id="masthead" class="site-header">
        <nav id="site-navigation" class="main-navigation">
            <!-- Top Navigation -->
            <div class="site-header__top">
                <div class="site-header-top__wrapper">
                    <?php get_template_part('components/template-parts/navigation/fau-navigation'); ?>
                </div>
            </div>

            <!-- Main Navigation -->
            <div class="site-header__main">
                <?php get_template_part('components/template-parts/navigation/main-navigation'); ?>
            </div>
        </nav>
    </header>

    <?php 
    // Display breadcrumbs on all pages except front page
    if (function_exists('faue_breadcrumbs')) {
        faue_breadcrumbs();
    }
    ?>

    <div id="content" class="site-content"> 
