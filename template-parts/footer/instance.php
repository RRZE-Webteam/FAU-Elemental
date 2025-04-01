<div class="footer-content footer-content--instance">
    <div class="footer-instance-main">
        <h1><?php echo get_bloginfo('name'); ?></h1>
        <p><?php echo get_bloginfo('description'); ?></p>

        <div class="footer-columns">
            <div class="footer-contact">
                <h2><?php _e('Kontakt und Anfahrt', 'your-theme-text-domain'); ?></h2>
                <address>
                    <?php echo get_theme_mod('instance_university_name', 'Friedrich-Alexander-Universität Erlangen-Nürnberg'); ?><br>
                    <?php echo get_theme_mod('instance_faculty_name', 'Technische Fakultät'); ?><br>
                    <?php echo get_theme_mod('instance_street', 'Martensstr. 5a'); ?><br>
                    <?php echo get_theme_mod('instance_city', '91058 Erlangen'); ?>
                </address>

                <div class="contact-details">
                    <p>
                        <?php _e('Telefon', 'your-theme-text-domain'); ?> 
                        <a href="tel:<?php echo esc_attr(get_theme_mod('instance_phone', '+49.9131.85-27295')); ?>">
                            <?php echo get_theme_mod('instance_phone', '+49.9131.85-27295'); ?>
                        </a>
                    </p>
                    <p>
                        <?php _e('Mail:', 'your-theme-text-domain'); ?> 
                        <a href="mailto:<?php echo esc_attr(get_theme_mod('instance_email', 'tf-sekretariat@fau.de')); ?>">
                            <?php echo get_theme_mod('instance_email', 'tf-sekretariat@fau.de'); ?>
                        </a>
                    </p>
                    <?php if (get_theme_mod('instance_directions_link')) : ?>
                        <p><a href="<?php echo esc_url(get_theme_mod('instance_directions_link')); ?>" class="directions-link">
                            <?php _e('Anfahrt', 'your-theme-text-domain'); ?>
                        </a></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="footer-links">
                <h2><?php _e('Wichtige Links', 'your-theme-text-domain'); ?></h2>
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'footer-instance-menu',
                    'menu_class' => 'important-links-list',
                    'container' => false,
                    'fallback_cb' => function() {
                        $default_links = array(
                            'Startseite' => home_url('/'),
                            'Fakultät' => '#',
                            'Studium' => '#',
                            'Forschung' => '#',
                            'Infocenter' => '#'
                        );
                        echo '<ul class="important-links-list">';
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

    <div class="footer-bottom">
        <nav class="footer-meta-nav">
            <?php
            wp_nav_menu(array(
                'theme_location' => 'footer-meta',
                'menu_class' => 'footer-meta-menu',
                'container' => false,
                'fallback_cb' => function() {
                    $meta_links = array(
                        'Kontakt' => '#',
                        'Hilfe im Notfall' => '#',
                        'Fehler melden' => '#',
                        'Impressum' => '#',
                        'Datenschutz' => '#'
                    );
                    echo '<ul class="footer-meta-menu">';
                    foreach ($meta_links as $text => $url) {
                        echo '<li><a href="' . esc_url($url) . '">' . esc_html($text) . '</a></li>';
                    }
                    echo '</ul>';
                }
            ));
            ?>
        </nav>

        <div class="footer-social">
            <?php
            $social_platforms = array(
                'facebook' => get_theme_mod('social_facebook'),
                'xing' => get_theme_mod('social_xing'),
                'linkedin' => get_theme_mod('social_linkedin')
            );

            foreach ($social_platforms as $platform => $url) :
                if (!empty($url)) : ?>
                    <a href="<?php echo esc_url($url); ?>" class="social-icon <?php echo esc_attr($platform); ?>" target="_blank" rel="noopener noreferrer">
                        <span class="screen-reader-text"><?php echo esc_html(ucfirst($platform)); ?></span>
                    </a>
                <?php endif;
            endforeach; ?>
        </div>
    </div>
</div>
