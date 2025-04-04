<div class="footer-content footer-content--main">
    <div class="footer-main">
        <div class="fau-claim">
            <h2><?php echo get_theme_mod('fau_footer_title', 'FAU - Wissen in Bewegung'); ?></h2>
            <p><?php echo get_theme_mod('fau_footer_description', 'Die FAU ist die innovativste Universität Deutschlands, europaweit auf dem zweiten Platz. Mit 40.000 Studierenden gehören wir zu den größten Hochschulen in Deutschland mit herausragender Lehre und exzellenter Forschung.'); ?></p>
        </div>

        <div class="target-groups">
            <?php
            $target_groups = array(
                'zur_fau' => array(
                    'title' => get_theme_mod('target_zur_fau_title', 'Zur FAU'),
                    'description' => get_theme_mod('target_zur_fau_description', 'Geschichte, Besonderheiten Daten, Struktur u.v.m'),
                    'link' => get_theme_mod('target_zur_fau_link', '#')
                ),
                'forschung' => array(
                    'title' => get_theme_mod('target_forschung_title', 'Forschung'),
                    'description' => get_theme_mod('target_forschung_description', 'Schwerpunkte, Leitbild, Reputation, Erfolge u.v.m.'),
                    'link' => get_theme_mod('target_forschung_link', '#')
                ),
                'studierende' => array(
                    'title' => get_theme_mod('target_studierende_title', 'Studierende'),
                    'description' => get_theme_mod('target_studierende_description', 'Schwerpunkte, Leitbild, Reputation, Erfolge u.v.m.'),
                    'link' => get_theme_mod('target_studierende_link', '#')
                ),
                'studieninteressierte' => array(
                    'title' => get_theme_mod('target_studieninteressierte_title', 'Studieninteressierte'),
                    'description' => get_theme_mod('target_studieninteressierte_description', 'Schwerpunkte, Leitbild, Reputation, Erfolge u.v.m.'),
                    'link' => get_theme_mod('target_studieninteressierte_link', '#')
                )
            );

            foreach ($target_groups as $key => $group) : ?>
                <a href="<?php echo esc_url($group['link']); ?>" class="target-group">
                    <h3><?php echo esc_html($group['title']); ?></h3>
                    <p><?php echo esc_html($group['description']); ?></p>
                    <span class="arrow-link"><?php _e('Mehr erfahren', 'fau-elemental'); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="footer-bottom">
    <div class="footer-bottom-wrapper">
      
        <div class="footer-bottom-top">
            <div class="footer-left">
                <div class="footer-logo-container">

                <div class="footer-logo-container">
                    <div class="footer-logo">
                        <?php 
                        $logo_url = get_theme_mod('fau_footer_logo', get_theme_file_uri('assets/images/Logo-white.svg'));
                        if ($logo_url) : ?>
                            <img src="<?php echo esc_url($logo_url); ?>" alt="FAU Logo">
                        <?php endif; ?>
                    </div>
                    <div class="footer-logo-tagline">
                        <?php 
                        $tagline = get_theme_mod('footer_logo_tagline', "Friedrich-Alexander-Universität\nErlangen-Nürnberg");
                        echo nl2br(esc_html($tagline)); 
                        ?>
                    </div>
                </div>
                    <div class="footer-logo-tagline">
                        <?php 
                        $tagline = get_theme_mod('footer_logo_tagline', "Friedrich-Alexander-Universität\nErlangen-Nürnberg");
                        echo nl2br(esc_html($tagline)); 
                        ?>
                    </div>
                </div>
            </div>
            
            <div class="footer-right">
                <div class="footer-links">
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'footer-meta',
                        'menu_class' => 'footer-meta-menu',
                        'container' => false,
                        'fallback_cb' => function() {
                            $default_links = array(
                                'Kontakt' => '#',
                                'Hilfe im Notfall' => '#',
                                'Fehler melden' => '#',
                                'Impressum' => '#',
                                'Datenschutz' => '#',
                                'Barrierefreiheit' => '#'
                            );
                            echo '<ul class="footer-meta-menu">';
                            foreach ($default_links as $text => $url) {
                                echo '<li><a href="' . esc_url($url) . '">' . esc_html($text) . '</a></li>';
                            }
                            echo '</ul>';
                        }
                    ));
                    ?>
                </div>
            </div>
        </div>
        
      
        <div class="footer-bottom-bottom">
            <div class="footer-left">
                <div class="photo-credits">
                    <?php echo get_theme_mod('image_credits', __('Bildnachweise:', 'fau-elemental')); ?>
                </div>
            </div>
            
            <div class="footer-right">
                <div class="footer-social">
                
                    <div class="social-links">
                        <?php
                        $social_platforms = array(
                            'instagram' => 'Instagram',
                            'facebook' => 'Facebook',
                            'xing' => 'Xing',
                            'linkedin' => 'LinkedIn',
                            'twitter' => 'X',
                            'mastodon' => 'Mastodon',
                            'blog' => 'Blog',
                            'youtube' => 'YouTube',
                            'tiktok' => 'TikTok'
                        );

                        foreach ($social_platforms as $platform => $label) :
                            $url = get_theme_mod("social_${platform}");
                            if (!empty($url)) : ?>
                                <a href="<?php echo esc_url($url); ?>" class="social-link <?php echo esc_attr($platform); ?>" target="_blank" rel="noopener noreferrer">
                                    <?php echo esc_html($label); ?>
                                </a>
                            <?php endif;
                        endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
