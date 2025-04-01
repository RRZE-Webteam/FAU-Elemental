<?php
/**
 * The template for displaying the footer
 */
$website_type = get_option('faue_website_type', 'fau');
?>

<footer class="site-footer">
    <?php if ($website_type === 'fau') : ?>

        
    <?php 
    include get_theme_file_path('template-parts/footer/main.php');

    else : ?>

    <?php 
    include get_theme_file_path('template-parts/footer/instance.php');
    endif; ?>
</footer>



</body>
</html>