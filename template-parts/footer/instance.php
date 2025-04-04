<div class="footer-content footer-content--instance">
    <!-- Row 1: Header and description (one column) -->
    <div class="footer-instance-header">
        <div class="instance-info">
            <h1><?php echo get_theme_mod('instance_title', get_bloginfo('name')); ?></h1>
            <p><?php echo get_theme_mod('instance_description', get_bloginfo('description')); ?></p>
        </div>
    </div>

    <!-- Row 2: Contact information (three columns) -->
    <div class="footer-instance-contact">
        <!-- Column 1: Address -->
        <div class="contact-address">
            <h2><?php _e('Kontakt und Anfahrt', 'fau-elemental'); ?></h2>
            <address>
                <?php echo get_theme_mod('instance_university_name', 'Friedrich-Alexander-Universität Erlangen-Nürnberg'); ?><br>
                <?php echo get_theme_mod('instance_faculty_name', 'Technische Fakultät'); ?><br>
                <?php echo get_theme_mod('instance_street', 'Martensstr. 5a'); ?><br>
                <?php echo get_theme_mod('instance_city', '91058 Erlangen'); ?>
            </address>
        </div>

        <!-- Column 2: Contact details -->
        <div class="contact-details">
      
            <p>
                <?php _e('Telefon', 'fau-elemental'); ?>: 
                <a href="tel:<?php echo esc_attr(get_theme_mod('instance_phone', '+49.9131.85-27295')); ?>">
                    <?php echo get_theme_mod('instance_phone', '+49.9131.85-27295'); ?>
                </a>
            </p>
            <p>
                <?php _e('Mail', 'fau-elemental'); ?>: 
                <a href="mailto:<?php echo esc_attr(get_theme_mod('instance_email', 'tf-sekretariat@fau.de')); ?>">
                    <?php echo get_theme_mod('instance_email', 'tf-sekretariat@fau.de'); ?>
                </a>
            </p>
            <?php if(get_theme_mod('instance_directions_link')) : ?>
            <p>
                <a href="<?php echo esc_url(get_theme_mod('instance_directions_link')); ?>" class="directions-link">
                    <?php _e('Anfahrtsbeschreibung', 'fau-elemental'); ?>
                </a>
            </p>
            <?php endif; ?>
        </div>

        <!-- Column 3: Important links -->
        <div class="footer-important-links">
            <h2><?php _e('Wichtige Links', 'fau-elemental'); ?></h2>
            <?php
            wp_nav_menu(array(
                'theme_location' => 'footer-wichtige-links',
                'menu_class' => 'important-links-list',
                'container' => false,
                'fallback_cb' => function() {
                    echo '<ul class="important-links-list">';
                    echo '<li><a href="#">' . __('Stellenangebote', 'fau-elemental') . '</a></li>';
                    echo '<li><a href="#">' . __('Kontakt', 'fau-elemental') . '</a></li>';
                    echo '<li><a href="#">' . __('Impressum', 'fau-elemental') . '</a></li>';
                    echo '</ul>';
                }
            ));
            ?>
        </div>
    </div>

    <!-- Row 3: Footer menu (one column) -->
    <div class="footer-instance-menu">
        <?php
        wp_nav_menu(array(
            'theme_location' => 'footer-instance-menu',
            'menu_class' => 'footer-menu-list',
            'container' => false,
            'fallback_cb' => function() {
                echo '<ul class="footer-menu-list">';
                echo '<li><a href="#">' . __('Impressum', 'fau-elemental') . '</a></li>';
                echo '<li><a href="#">' . __('Datenschutz', 'fau-elemental') . '</a></li>';
                echo '<li><a href="#">' . __('Barrierefreiheit', 'fau-elemental') . '</a></li>';
                echo '</ul>';
            }
        ));
        ?>
    </div>
</div>

<!-- Footer Bottom with FAU Info -->
<div class="footer-bottom">
    <div class="footer-bottom-wrapper">
        <!-- Row 1: Logo and Toggle Button -->
        <div class="footer-bottom-row footer-controls">
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
            
            <div class="toggle-container">
                <button class="fau-info-toggle" aria-expanded="false" aria-controls="fau-info-section">
                    <?php _e('FAU Informationen anzeigen', 'fau-elemental'); ?>
                    <span class="toggle-icon" aria-hidden="true"></span>
                </button>
            </div>
        </div>
        
        <!-- Row 2: Collapsible Target Groups (4 columns) -->
        <div id="fau-info-section" class="footer-bottom-row fau-info-section" hidden>
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
        
        <!-- Row 3: Image Credits (1 column) -->
        <div class="footer-bottom-row footer-credits">
            <div class="image-credits">
                <p><?php _e('Bildnachweise:', 'fau-elemental'); ?> <?php echo get_theme_mod('image_credits', '© copyright kurz'); ?></p>
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
