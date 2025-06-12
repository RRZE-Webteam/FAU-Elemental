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
        <div class="site-branding">
            <?php if (has_custom_logo()) : ?>
                <div class="site-logo">
                    <?php the_custom_logo(); ?>
                </div>
            <?php endif; ?>
            
            <div class="site-identity">
                <?php if (is_front_page() && is_home()) : ?>
                    <h1 class="site-title">
                        <a href="<?php echo esc_url(home_url('/')); ?>" rel="home">
                            <?php bloginfo('name'); ?>
                        </a>
                    </h1>
                <?php else : ?>
                    <p class="site-title">
                        <a href="<?php echo esc_url(home_url('/')); ?>" rel="home">
                            <?php bloginfo('name'); ?>
                        </a>
                    </p>
                <?php endif; ?>
                
                <?php $description = get_bloginfo('description', 'display'); ?>
                <?php if ($description || is_customize_preview()) : ?>
                    <p class="site-description"><?php echo $description; ?></p>
                <?php endif; ?>
            </div>
        </div>

        <nav id="site-navigation" class="main-navigation">
                <!-- Top Navigation -->
    <div class="site-header__top">
        <?php
        if (class_exists('FAU_Navigation')) {
            global $fau_navigation;
            if ($fau_navigation) {
                $fau_navigation->render();
            }
        }
        ?>
    </div>

    <!-- Main Navigation -->
    <div class="site-header__main">
        <?php
        if (class_exists('Main_Navigation')) {
            global $main_navigation;
            if ($main_navigation) {
                $main_navigation->render();
            }
        }
        ?>
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
