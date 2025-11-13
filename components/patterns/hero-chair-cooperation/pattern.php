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

// Get the current website type and faculty
$current_website_type = get_theme_mod('faue_website_type', faue_get_default('faue_website_type'));
$current_faculty = get_theme_mod('faue_faculty', 'phil');

// Chair-specific content for each faculty
$chair_content = [
    'phil' => [
        'title' => 'Institut für Philosophie',
        'description' => 'Die Philosophische Fakultät der FAU ist eine der größten geisteswissenschaftlichen Fakultäten in Deutschland. Ihre Forschung und Lehre decken ein breites Spektrum von den klassischen Geisteswissenschaften bis zu modernen Kultur- und Sozialwissenschaften ab. Interdisziplinäre Ansätze und internationale Kooperationen prägen ihr Profil.',
        'img' => '/assets/images/hero-faculty-phil.png'
    ],
    'nat' => [
        'title' => 'Naturwissenschaftliche Fakultät',
        'description' => 'Die Naturwissenschaftliche Fakultät der FAU gehört zu den forschungsstärksten naturwissenschaftlichen Fakultäten in Deutschland. Ihre Fachbereiche belegen regelmäßig vorderste Plätze in unterschiedlichsten Rankings. Innovative Forschung und exzellente Lehre prägen ihr Profil.',
        'img' => '/assets/images/hero-faculty.jpg'
    ],
    'med' => [
        'title' => 'Medizinische Fakultät',
        'description' => 'Die Medizinische Fakultät der FAU zählt zu den traditionsreichsten und forschungsintensivsten Fakultäten in Deutschland. Sie ist eng mit dem Universitätsklinikum Erlangen verbunden und verbindet Spitzenmedizin mit moderner Lehre. Zahlreiche Forschungsverbünde tragen zu internationalen Fortschritten in der Medizin bei.',
        'img' => '/assets/images/hero-faculty.jpg'
    ],
    'rw' => [
        'title' => 'Rechts- und Wirtschaftswissenschaftliche Fakultät',
        'description' => 'Die Rechts- und Wirtschaftswissenschaftliche Fakultät genießt hohes Ansehen in Forschung und Lehre. Sie verbindet eine lange Tradition juristischer und ökonomischer Ausbildung mit modernen, praxisorientierten Studiengängen. Ihre Institute sind national und international hervorragend vernetzt.',
        'img' => '/assets/images/hero-faculty-rw.png'
    ],
    'tf' => [
        'title' => 'Technische Fakultät',
        'description' => 'Die Technische Fakultät ist eine der jüngsten, aber zugleich dynamischsten Fakultäten der FAU. Sie vereint klassische Ingenieurwissenschaften mit modernen Zukunftsfeldern wie Künstliche Intelligenz, Medizintechnik und Materialwissenschaften. Ihre enge Kooperation mit der Industrie macht sie zu einem starken Partner für Innovation.',
        'img' => '/assets/images/hero-faculty-tf.png'
    ]
];

// Cooperation website type content (fallback)
$cooperation_content = [
    'title' => 'Kooperationen Headline Text here',
    'description' => 'Die Naturwissenschaftliche Fakultät der FAU gehört zu den forschungsstärksten naturwissenschaftlichen Fakultäten in Deutschland. Ihre Fachbereiche belegen regelmäßig vorderste Plätze in unterschiedlichsten Rankings.',
    'img' => '/assets/images/hero-faculty-phil.png'
];

// Determine which content to use
if ($current_website_type === 'chair') {
    $current_content = $chair_content[$current_faculty] ?? $chair_content['phil'];
} else {
    $current_content = $cooperation_content;
}
?>
<!-- wp:columns {"templateLock":"all", "className": "hero-chair-cooperation"} -->
<div class="wp-block-columns alignwide hero-chair-cooperation">

    <!-- wp:column {"className":"hero-chair-content-wrapper"} -->
    <div class="wp-block-column hero-chair-content-wrapper">

        <!-- wp:column {"className":"hero-chair-content"} -->
        <div class="wp-block-column hero-chair-content">

            <!-- wp:heading {"level":1} -->
            <h1 class="wp-block-heading"><?php echo esc_html($current_content['title']); ?></h1>
            <!-- /wp:heading -->

            <!-- wp:group {"className":"hero-mobile-optional"} -->
            <div class="wp-block-group hero-mobile-optional">
                <!-- wp:paragraph {"className":"hero-text"} -->
                <p class="hero-text"><?php echo esc_html($current_content['description']); ?></p>
                <!-- /wp:paragraph -->

                <!-- wp:buttons -->
                <div class="wp-block-buttons">
                    <!-- wp:button {"className":"is-style-secondary"} -->
                    <div class="wp-block-button is-style-secondary"><a class="wp-block-button__link wp-element-button">Mehr erfahren</a></div>
                    <!-- /wp:button -->
                </div>
                <!-- /wp:buttons -->
            </div>
            <!-- /wp:group -->

        </div>
        <!-- /wp:column -->

    </div>
    <!-- /wp:column -->

    <!-- wp:column {"className":"hero-chair-bg-left"} -->
    <div class="wp-block-column hero-chair-bg-left">
    </div>
    <!-- /wp:column -->

    <!-- wp:column {"className":"hero-chair-bg-right"} -->
    <div class="wp-block-column hero-chair-bg-right">
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
