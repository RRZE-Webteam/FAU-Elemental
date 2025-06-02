<?php
/**
 * Header Front Page Block Component
 *
 * @package fau-elemental
 */

class Header_Front_Page_Block {
    public function render() {
        ?>
        <div class="site-header__top site-header--front-page">
            <?php
            require_once get_template_directory() . '/src/components/navigation/fau-navigation.php';
            if (class_exists('FAU_Navigation')) {
                $fau_nav = new FAU_Navigation();
                $fau_nav->render();
            }
            ?>
        </div>

        <div class="site-header__main--front-page">
            <?php
            require_once get_template_directory() . '/src/components/navigation/main-navigation.php';
            if (class_exists('Main_Navigation')) {
                $main_nav = new Main_Navigation();
                $main_nav->render();
            }
            ?>
        </div>

        <?php
        require_once get_template_directory() . '/src/components/navigation/menu-website.php';
        if (class_exists('Menu_Website_Modal')) {
            $menu_website_modal = new Menu_Website_Modal();
            $menu_website_modal->render();
        }
        ?>
        <?php
         require_once get_template_directory() . '/src/components/navigation/menu-meta-nav.php';
         if (class_exists('Menu_Meta_Nav_Modal')) {
             $menu_website_modal = new Menu_Meta_Nav_Modal();
             $menu_website_modal->render();
         }
         ?>
         <?php
    }
} 