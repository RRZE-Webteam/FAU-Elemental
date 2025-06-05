<?php
/**
 * The template for displaying the footer
 *
 * @package Fau-Elemental
 */
$website_type = get_theme_mod('faue_website_type', 'fau');
?>

    </div><!-- #content -->

    <footer id="colophon" class="site-footer">
        <div class="inner-footer">
            <?php if ($website_type === 'fau') : ?>
                <?php include get_theme_file_path('template-parts/footer/main.php'); ?>
            <?php else : ?>
                <?php include get_theme_file_path('template-parts/footer/instance.php'); ?>
            <?php endif; ?>
        </div>
    </footer>

</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>