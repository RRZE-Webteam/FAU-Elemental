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
            <?php include get_theme_file_path('components/template-parts/footer-main/footer-main.php'); ?>
        <?php else : ?>
            <?php include get_theme_file_path('components/template-parts/footer-instance/footer-instance.php'); ?>
        <?php endif; ?>
    </footer>

</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>