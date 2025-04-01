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
                    <span class="arrow-link"><?php _e('Mehr erfahren', 'your-theme-text-domain'); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="footer-logo">
            <?php 
            $logo_url = get_theme_mod('fau_footer_logo', get_theme_file_uri('assets/images/fau-logo-white.svg'));
            if ($logo_url) : ?>
                <img src="<?php echo esc_url($logo_url); ?>" alt="FAU Logo">
            <?php endif; ?>
        </div>
        
        <div class="footer-meta">
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
            
            <div class="footer-social">
                <p class="social-label"><?php echo get_theme_mod('social_label', 'Bildnachweise:'); ?></p>
                <div class="social-icons">
                    <?php
                    $social_platforms = array(
                        'instagram' => array('url' => get_theme_mod('social_instagram'), 'icon' => 'instagram'),
                        'facebook' => array('url' => get_theme_mod('social_facebook'), 'icon' => 'facebook'),
                        'xing' => array('url' => get_theme_mod('social_xing'), 'icon' => 'xing'),
                        'linkedin' => array('url' => get_theme_mod('social_linkedin'), 'icon' => 'linkedin'),
                        'twitter' => array('url' => get_theme_mod('social_twitter'), 'icon' => 'twitter'),
                        'mastodon' => array('url' => get_theme_mod('social_mastodon'), 'icon' => 'mastodon'),
                        'blog' => array('url' => get_theme_mod('social_blog'), 'icon' => 'blog'),
                        'youtube' => array('url' => get_theme_mod('social_youtube'), 'icon' => 'youtube'),
                        'tiktok' => array('url' => get_theme_mod('social_tiktok'), 'icon' => 'tiktok')
                    );

                    foreach ($social_platforms as $platform => $data) :
                        if (!empty($data['url'])) : ?>
                            <a href="<?php echo esc_url($data['url']); ?>" class="social-icon <?php echo esc_attr($data['icon']); ?>" target="_blank" rel="noopener noreferrer">
                                <span class="screen-reader-text"><?php echo esc_html(ucfirst($platform)); ?></span>
                            </a>
                        <?php endif;
                    endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
