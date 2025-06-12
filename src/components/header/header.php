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
            if (class_exists('FAU_Navigation')) {
                global $fau_navigation;
                if ($fau_navigation) {
                    $fau_navigation->render();
                }
            }
            ?>
        </div>

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