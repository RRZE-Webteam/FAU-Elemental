<?php
/**
 * The template for displaying the footer
 *
 * @package Fau-Elemental
 */
$faue_website_type = get_theme_mod('faue_website_type');
?>

    </div><!-- #content -->

    <footer id="colophon" class="site-footer">
        <?php if ($faue_website_type === 'fau') : ?>
            <?php get_template_part('components/template-parts/footer-main/footer-main'); ?>
        <?php else : ?>
            <?php get_template_part('components/template-parts/footer-instance/footer-instance'); ?>
        <?php endif; ?>
    </footer>

</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>