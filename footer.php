<?php
/**
 * The template for displaying the footer
 */
$website_type = get_theme_mod('faue_website_type', 'fau');
?>

<div class="inner-footer">
    <?php if ($website_type === 'fau') : ?>

        
    <?php 
    include get_theme_file_path('template-parts/footer/main.php');

    else : ?>

    <?php 
    include get_theme_file_path('template-parts/footer/instance.php');
    endif; ?>
</div>



</body>
</html>