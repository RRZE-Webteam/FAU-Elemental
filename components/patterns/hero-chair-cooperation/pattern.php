<?php
/**
 * Title: Hero: Chair
 * Slug: fau-elemental/hero-chair-cooperation
 * Categories: hero, fau-elemental
 * Website Types: chair, cooperation
 * Viewport Width: 1376
 * Block Types: core/post-content
 * Post Types: page
 * Description: Hero pattern for chair and cooperation websites
 */

// Get the current website type
$current_website_type = get_theme_mod('faue_website_type', 'fau');

// Chair-specific content
$chair_content = [
    'title' => 'Institut für Politische Wissenschaft',
    'description' => 'Die Naturwissenschaftliche Fakultät der FAU gehört zu den forschungsstärksten naturwissenschaftlichen Fakultäten in Deutschland. Ihre Fachbereiche belegen regelmäßig vorderste Plätze in unterschiedlichsten Rankings.',
    'img' => '/assets/images/hero-faculty-phil.png'
];

// Cooperation website type content (fallback)
$cooperation_content = [
    'title' => 'Kooperationen Headline Text here',
    'description' => 'Die Naturwissenschaftliche Fakultät der FAU gehört zu den forschungsstärksten naturwissenschaftlichen Fakultäten in Deutschland. Ihre Fachbereiche belegen regelmäßig vorderste Plätze in unterschiedlichsten Rankings.',
    'img' => '/assets/images/hero-faculty-phil.png'
];

// Determine which content to use
if ($current_website_type === 'chair') {
    $current_content = $chair_content;
} else {
    $current_content = $cooperation_content;
}
?>
<!-- wp:columns {"templateLock":"all", "className": "hero-chair-cooperation"} -->
<div class="wp-block-columns hero-chair-bg hero-chair-cooperation <?php echo $current_website_type === 'chair' ? 'hero-chair' : 'hero-cooperation'; ?>">
    <!-- wp:column {"layout":{"type":"constrained"}} -->
    <div class="wp-block-column">

        <!-- wp:heading {"level":2,"className":"hero-front-page-title"} -->
        <h2 class="wp-block-heading hero-front-page-title"><?php echo esc_html($current_content['title']); ?></h2>
        <!-- /wp:heading -->

        <!-- wp:group {"className":"hero-mobile-optional"} -->
        <div class="wp-block-group hero-mobile-optional">
            <!-- wp:paragraph {"className":"hero-text"} -->
            <p class="hero-text"><?php echo esc_html($current_content['description']); ?></p>
            <!-- /wp:paragraph -->
             
            <!-- wp:buttons -->
            <div class="wp-block-buttons">
                 <!-- wp:button {"className":"is-style-tertiary"} -->
                 <div class="wp-block-button is-style-tertiary">
                    <a class="wp-block-button__link wp-element-button">Mehr erfahren</a>
                </div>
                <!-- /wp:button -->
            </div>
            <!-- /wp:buttons -->
        </div>
        <!-- /wp:group -->
    </div>
    <!-- /wp:column -->

         <!-- wp:column -->
         <div class="wp-block-column">
        <!-- wp:cover {"url":"<?php echo esc_url(get_theme_file_uri($current_content['img'])); ?>","dimRatio":0,"isUserOverlayColor":false,"layout":{"type":"constrained"}} -->
        <div class="wp-block-cover">
            <span aria-hidden="true" class="wp-block-cover__background  has-background-dim-0 has-background-dim"></span>
            <img class="wp-block-cover__image-background" alt="" src="<?php echo esc_url(get_theme_file_uri($current_content['img'])); ?>" data-object-fit="cover" />
            <div class="wp-block-cover__inner-container">
                <!-- wp:paragraph {"align":"center","placeholder":"Write title…","fontSize":"large","className":"hideParagraph"} -->
                <p class="has-text-align-center has-large-font-size hideParagraph"></p>
                <!-- /wp:paragraph -->
            </div>
        </div>
        <!-- /wp:cover -->
    </div>
    <!-- /wp:column -->
</div>
<!-- /wp:columns --> 
