<?php
/**
 * Instance-specific footer content
 *
 * @package fau-elemental
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
?>

<div class="footer-instance">
    <?php if (get_theme_mod('instance_footer_title') || get_theme_mod('instance_footer_description')): ?>
    <div class="footer-instance-content">
        <h2><?php echo esc_html(get_theme_mod('instance_footer_title')); ?></h2>
        <div class="instance-description">
            <?php echo wp_kses_post(get_theme_mod('instance_footer_description')); ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (get_theme_mod('instance_footer_contact')): ?>
    <div class="footer-contact">
        <h3><?php _e('Contact', 'fau-elemental'); ?></h3>
        <?php echo wp_kses_post(get_theme_mod('instance_footer_contact')); ?>
    </div>
    <?php endif; ?>

    <?php if (has_nav_menu('footer-instance')): ?>
    <nav class="footer-instance-nav">
        <?php
        wp_nav_menu(array(
            'theme_location' => 'footer-instance',
            'menu_class' => 'footer-instance-menu',
            'container' => false,
        ));
        ?>
    </nav>
    <?php endif; ?>

    <?php get_template_part('template-parts/footer/social-media'); ?>
</div> 