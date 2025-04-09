<?php
/**
 * The header template
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<!DOCTYPE html>
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
    <a class="skip-link screen-reader-text" href="#primary">
        <?php esc_html_e('Skip to content', 'fau-elemental'); ?>
    </a>

    <header id="masthead" class="site-header">
        <!-- FAU Top Navigation -->
        <div class="site-header__top">
            <?php
            // Include and instantiate FAU Navigation
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
            // Include and instantiate Main Navigation
            require_once get_template_directory() . '/src/components/navigation/main-navigation.php';
            if (class_exists('Main_Navigation')) {
                $main_nav = new Main_Navigation();
                $main_nav->render();
            }
            ?>
        </div>
    </header><!-- #masthead -->
</body>
</html> 