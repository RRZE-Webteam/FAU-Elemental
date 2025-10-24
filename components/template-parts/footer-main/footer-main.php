<div class="footer-content footer-content--main <?php echo esc_attr( get_theme_mod('footer_dark_style', false) ? 'is-style-dark' : '' ); ?>">
    <div class="footer-main">
        <section class="fau-claim">
            <p class="claim-title"><?php echo esc_html(get_theme_mod('fau_footer_title', __('FAU - Knowledge in Motion', 'fau-elemental'))); ?></p>
            <p><?php echo wp_kses_post(get_theme_mod('fau_footer_description', __('FAU is Germany\'s most innovative university, ranking second in Europe. With 40,000 students, we are one of the largest universities in Germany with outstanding teaching and excellent research.', 'fau-elemental'))); ?></p>
        </section>

        <section class="target-groups">
            <?php
            require_once get_theme_file_path('components/template-parts/footer-instance/footer-target-groups.php');
            
            $target_groups = array(
                array(
                    'title' => get_theme_mod('target_section1_title', __('Target Group Section 1', 'fau-elemental')),
                    'description' => get_theme_mod('target_section1_description', __('History, features, data, structure and more', 'fau-elemental')),
                    'link' => get_theme_mod('target_section1_link', '')
                ),
                array(
                    'title' => get_theme_mod('target_section2_title', __('Target Group Section 2', 'fau-elemental')),
                    'description' => get_theme_mod('target_section2_description', __('Focus areas, mission, reputation, achievements and more', 'fau-elemental')),
                    'link' => get_theme_mod('target_section2_link', '')
                ),
                array(
                    'title' => get_theme_mod('target_section3_title', __('Target Group Section 3', 'fau-elemental')),
                    'description' => get_theme_mod('target_section3_description', __('Focus areas, mission, reputation, achievements and more', 'fau-elemental')),
                    'link' => get_theme_mod('target_section3_link', '')
                ),
                array(
                    'title' => get_theme_mod('target_section4_title', __('Target Group Section 4', 'fau-elemental')),
                    'description' => get_theme_mod('target_section4_description', __('Focus areas, mission, reputation, achievements and more', 'fau-elemental')),
                    'link' => get_theme_mod('target_section4_link', '')
                )
            );

            echo render_footer_target_groups($target_groups, 'outline', 'small');
            ?>
        </section>

        <section class="footer-lists">
            <?php
            wp_nav_menu(array(
                'theme_location' => 'footer-lists-menu',
                'menu_class' => 'footer-lists-menu columns-layout',
                'container' => 'nav',
                'container_class' => 'footer-lists-container',
                'depth' => 2,
                'fallback_cb' => false
            ));
            ?>
        </section>
    </div>
</div>

<section class="footer-bottom">
    <div class="footer-bottom-wrapper">
        <div class="footer-bottom-top">
            <div class="footer-left">
                <div class="footer-logo-container">
                    <div class="footer-logo">
                        <?php 
                        $logo_url = get_theme_mod('fau_footer_logo', get_theme_file_uri('assets/images/Logo-white.svg'));
                        if ($logo_url) : ?>
                            <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr__('FAU Logo', 'fau-elemental'); ?>" loading="lazy" decoding="async">
                        <?php endif; ?>
                    </div>
                    <div class="footer-logo-tagline">
                        <?php 
                        $tagline = get_theme_mod('footer_logo_tagline', __("Friedrich-Alexander-Universität\nErlangen-Nürnberg", 'fau-elemental'));
                        echo nl2br(esc_html($tagline)); 
                        ?>
                    </div>
                </div>
            </div>
            
            <div class="footer-right">
                <nav class="footer-links">
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'footer-menu',
                        'menu_class' => 'footer-meta-menu',
                        'container' => false,
                        'depth' => 1,
                        'fallback_cb' => false
                    ));
                    ?>
                </nav>
            </div>
        </div>
        
        <div class="footer-bottom-bottom">
            <div class="footer-left">
                <?php if (!is_single() || !get_theme_mod('faue_hide_copyright_on_single', faue_get_default('faue_hide_copyright_on_single'))) : ?>
                    <?php echo render_block([
                        'blockName' => 'fau-elemental/fau-copyright-info',
                        'attrs' => [],
                        'innerBlocks' => [],
                        'innerHTML' => '',
                        'innerContent' => []
                    ]); ?>
                <?php endif; ?>
            </div>
            
            <div class="footer-right">
                <nav class="footer-social" aria-label="<?php echo esc_attr__('Social Media Links', 'fau-elemental'); ?>">
                    <ul class="social-links">
                        <?php
                        $social_platforms = faue_get_social_platforms();

                        foreach ($social_platforms as $platform => $label) :
                            $url = get_theme_mod("social_{$platform}");
                            if (!empty($url)) : ?>
                                <li>
                                    <a href="<?php echo esc_url($url); ?>" class="<?php echo esc_attr($platform); ?>">
                                        <span class="sr-only"><?php echo esc_html($label); ?></span>
                                    </a>
                                </li>
                            <?php endif;
                        endforeach; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</section>
