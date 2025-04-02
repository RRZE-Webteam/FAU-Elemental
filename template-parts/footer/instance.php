<div class="footer-content footer-content--instance">
    <!-- Upper Instance Part -->
    <div class="footer-instance-main">
        <div class="instance-info">
            <h1><?php echo get_bloginfo('name'); ?></h1>
            <p><?php echo get_bloginfo('description'); ?></p>
        </div>

        <div class="footer-columns">
            <div class="footer-contact">
                <h2><?php _e('Kontakt und Anfahrt', 'fau-elemental'); ?></h2>
                <address>
                    <?php echo get_theme_mod('instance_university_name', 'Friedrich-Alexander-Universität Erlangen-Nürnberg'); ?><br>
                    <?php echo get_theme_mod('instance_faculty_name', 'Technische Fakultät'); ?><br>
                    <?php echo get_theme_mod('instance_street', 'Martensstr. 5a'); ?><br>
                    <?php echo get_theme_mod('instance_city', '91058 Erlangen'); ?>
                </address>

                <div class="contact-details">
                    <p>
                        <?php _e('Telefon', 'fau-elemental'); ?> 
                        <a href="tel:<?php echo esc_attr(get_theme_mod('instance_phone', '+49.9131.85-27295')); ?>">
                            <?php echo get_theme_mod('instance_phone', '+49.9131.85-27295'); ?>
                        </a>
                    </p>
                    <p>
                        <?php _e('Mail:', 'fau-elemental'); ?> 
                        <a href="mailto:<?php echo esc_attr(get_theme_mod('instance_email', 'tf-sekretariat@fau.de')); ?>">
                            <?php echo get_theme_mod('instance_email', 'tf-sekretariat@fau.de'); ?>
                        </a>
                    </p>
                </div>
            </div>

            <div class="footer-links">
                <h2><?php _e('Wichtige Links', 'fau-elemental'); ?></h2>
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'footer-instance-menu',
                    'menu_class' => 'important-links-list',
                    'container' => false,
                ));
                ?>
            </div>

            <div class="footer-social">
                <?php
                $social_platforms = array(
                    'facebook' => 'Facebook',
                    'xing' => 'Xing',
                    'linkedin' => 'LinkedIn'
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

<!-- Footer Bottom with FAU Info -->
<div class="footer-bottom">
    <div class="footer-bottom-wrapper">
        <!-- Collapsible FAU Info Section -->
        <div class="fau-info-section" hidden>
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

        <!-- Always visible bottom bar -->
        <div class="footer-bottom-bar">
            <div class="footer-logo">
                <img src="<?php echo get_theme_file_uri('assets/images/fau-logo-white.svg'); ?>" alt="FAU Logo">
            </div>
            
            <div class="footer-meta">
                <div class="footer-links">
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'footer-meta',
                        'menu_class' => 'footer-meta-menu',
                        'container' => false,
                    ));
                    ?>
                </div>
                
                <button class="fau-info-toggle" aria-expanded="false">
                    <?php _e('FAU Informationen anzeigen', 'fau-elemental'); ?>
                </button>
            </div>

            <div class="image-credits">
                <p><?php _e('Bildnachweise:', 'fau-elemental'); ?></p>
                <?php echo get_theme_mod('image_credits', '© copyright kurz'); ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleButton = document.querySelector('.fau-info-toggle');
    const fauInfoSection = document.querySelector('.fau-info-section');

    if (toggleButton && fauInfoSection) {
        toggleButton.addEventListener('click', function() {
            const isExpanded = toggleButton.getAttribute('aria-expanded') === 'true';
            toggleButton.setAttribute('aria-expanded', !isExpanded);
            fauInfoSection.hidden = isExpanded;
            toggleButton.textContent = isExpanded ? 
                '<?php _e('FAU Informationen anzeigen', 'fau-elemental'); ?>' : 
                '<?php _e('FAU Informationen ausblenden', 'fau-elemental'); ?>';
        });
    }
});
</script>
