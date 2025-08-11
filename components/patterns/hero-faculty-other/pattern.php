<?php
/**
 * Title: Hero: Faculty
 * Slug: fau-elemental/hero-faculty-other
 * Categories: hero, fau-elemental
 * Website Types: faculty, other
 * Viewport Width: 1376
 * Block Types: core/post-content
 * Post Types: page
 * Description: Hero pattern for faculty websites
 */
?>
<?php
// Get the current website type and faculty
$current_website_type = get_theme_mod('faue_website_type', 'fau');
$current_faculty = get_theme_mod('faue_faculty', 'phil');

// Faculty-specific content
$faculty_content = [
    'phil' => [
        'title' => 'Die Philosophische Fakultät und Fachbereich Theologie',
        'description' => 'Die Philosophische Fakultät der FAU gehört zu den forschungsstärksten philosophischen Fakultäten in Deutschland. Ihre Fachbereiche belegen regelmäßig vorderste Plätze in unterschiedlichsten Rankings.',
        'img' => '/assets/images/hero-faculty-phil.png'
    ],
    'nat' => [
        'title' => 'Die Naturwissenschaftliche Fakultät',
        'description' => 'Die Naturwissenschaftliche Fakultät der FAU gehört zu den forschungsstärksten naturwissenschaftlichen Fakultäten in Deutschland. Ihre Fachbereiche belegen regelmäßig vorderste Plätze in unterschiedlichsten Rankings.',
        'img' => '/assets/images/hero-faculty.jpg'
    ],
    'med' => [
        'title' => 'Die Medizinische Fakultät',
        'description' => 'Die Medizinische Fakultät der FAU gehört zu den forschungsstärksten medizinischen Fakultäten in Deutschland. Ihre Fachbereiche belegen regelmäßig vorderste Plätze in unterschiedlichsten Rankings.',
        'img' => '/assets/images/hero-faculty.jpg'

    ],
    'rw' => [
        'title' => 'Die Rechts- und Wirtschaftswissenschaftliche Fakultät',
        'description' => 'Die Rechts- und Wirtschaftswissenschaftliche Fakultät der FAU gehört zu den forschungsstärksten rechts- und wirtschaftswissenschaftlichen Fakultäten in Deutschland. Ihre Fachbereiche belegen regelmäßig vorderste Plätze in unterschiedlichsten Rankings.',
        'img' => '/assets/images/hero-faculty-rw.png'
    ],
    'tf' => [
        'title' => 'Die Technische Fakultät',
        'description' => 'Die Technische Fakultät der FAU gehört zu den forschungsstärksten technischen Fakultäten in Deutschland. Ihre Fachbereiche belegen regelmäßig vorderste Plätze in unterschiedlichsten Rankings.',
        'img' => '/assets/images/hero-faculty-tf.png'
    ]
];

// Other website type content
$other_content = [
    'title' => 'Willkommen bei der FAU',
    'description' => 'Die Friedrich-Alexander-Universität Erlangen-Nürnberg ist eine der forschungsstärksten Universitäten in Deutschland. Entdecken Sie unsere vielfältigen Angebote und Forschungsschwerpunkte.',
    'img' => '/assets/images/hero-faculty-tf.png'
];

// Determine which content to use
if ($current_website_type === 'other') {
    $current_content = $other_content;
} else {
    $current_content = $faculty_content[$current_faculty] ?? $faculty_content['phil'];
}
?>
<!-- wp:columns {"align":"wide","templateLock":"all", "className": "hero-faculty-other"} -->
<div class="wp-block-columns alignwide hero-faculty-other <?php echo $current_website_type === 'other' ? 'hero-other' : 'faculty-' . esc_attr($current_faculty); ?>">
    <!-- wp:column {"layout":{"type":"constrained"},"className":"hero-faculty-left"} -->
    <div class="wp-block-column hero-faculty-left">

        <!-- wp:heading {"level":2} -->
        <h2 class="wp-block-heading"><?php echo esc_html($current_content['title']); ?></h2>
        <!-- /wp:heading -->

        <!-- wp:group {"className":"hero-mobile-optional"} -->
        <div class="wp-block-group hero-mobile-optional">
            <!-- wp:paragraph {"className":"hero-text"} -->
            <p class="hero-text"><?php echo esc_html($current_content['description']); ?></p>
            <!-- /wp:paragraph -->

            <!-- wp:buttons -->
            <div class="wp-block-buttons">
                 <!-- wp:button {"className":"is-style-tertiary"} -->
                 <div class="wp-block-button is-style-tertiary"><a class="wp-block-button__link wp-element-button">Mehr erfahren</a></div>
                <!-- /wp:button -->
            </div>
            <!-- /wp:buttons -->
        </div>
        <!-- /wp:group -->
    </div>
    <!-- /wp:column -->

    <!-- wp:column  -->
    <div class="wp-block-column" >
        <!-- wp:cover {"url":"<?php echo esc_url(get_theme_file_uri($current_content['img']));?>","dimRatio":0,"contentPosition":"center","className":"is-dark-theme"} -->
        <div class="wp-block-cover is-dark-theme">
            <span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span>
            <img class="wp-block-cover__image-background" alt="" src="<?php echo esc_url(get_theme_file_uri($current_content['img'])); ?>" data-object-fit="cover">
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