<?php
/**
 * The header template
 *
 * @package Fau-Elemental
 */

// Load navigation utilities
get_template_part('components/template-parts/navigation/navigation-utils');
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>

<body <?php body_class(faue_get_navigation_body_classes()); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#main"><?php esc_html_e('Skip to main content', 'fau-elemental'); ?></a>

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

    <!-- Show Logo and Claim for Print -->
    <div class="print-container" aria-hidden="true">
        <div class="print-text">
            <div>Friedrich-Alexander-Universität</div>
            <div>Erlangen-Nürnberg</div>
        </div>

        <div class="print-logo">
            <?php
            $logo_url = get_theme_mod('fau_footer_logo', get_theme_file_uri('assets/images/logo-print.svg'));
            if ($logo_url) : ?>
                <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr__('FAU Logo', 'fau-elemental'); ?>">
            <?php endif; ?>
        </div>
    </div>

    <?php 
    // Display breadcrumbs on all pages except front page
    if (function_exists('faue_breadcrumbs')) {
        faue_breadcrumbs();
    }
    ?>
