<?php
/**
 * Header Block Component
 *
 * @package fau-elemental
 */

class Header_Block {
    public function render() {
        ?>
        <div class="site-header__top">
            <?php
            require_once get_template_directory() . '/src/components/navigation/fau-navigation.php';
            if (class_exists('FAU_Navigation')) {
                $fau_nav = new FAU_Navigation();
                $fau_nav->render();
            }
            ?>
        </div>

        <div class="site-header__main">
            <?php
            require_once get_template_directory() . '/src/components/navigation/main-navigation.php';
            if (class_exists('Main_Navigation')) {
                $main_nav = new Main_Navigation();
                $main_nav->render();
            }
            ?>
        </div>

        <div class="site-header__breadcrumbs">
            <?php get_template_part('parts/breadcrumbs'); ?>
        </div>

        <?php
        // The unified menu modal system automatically renders all modals via wp_footer
        // The navigation components above include the menu-modal-config.php which sets everything up
        ?>
        <?php
    }
} 