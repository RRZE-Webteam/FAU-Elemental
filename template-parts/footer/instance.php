<div class="footer-content footer-content--instance">

    <div class="footer-content--instance-wrapper">

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

                <div class="contact-address-and-tel-container">
                    <?php 
                    // Check if we should display the address (default to true if not set)
                    // Check both new and old field names for backward compatibility
                    $display_address = get_theme_mod('display_footer_address', true);
                    if (!isset($display_address)) {
                        $display_address = get_theme_mod('advanced_footer_display_address', true);
                    }
                    
                    if ($display_address): 
                    ?>
                    <address>
                        <?php 
                        // University name
                        echo get_theme_mod('instance_university_name', 'Friedrich-Alexander-Universität Erlangen-Nürnberg'); 
                        ?><br>
                        <?php 
                        // Faculty name - check new field first, then fallback to old fields
                        $faculty_name = get_theme_mod('instance_faculty_name', '');
                        if (empty($faculty_name)) {
                            $address_name = get_theme_mod('contact_address_name', '');
                            $address_name2 = get_theme_mod('contact_address_name2', '');
                            if (!empty($address_name)) {
                                echo $address_name;
                                if (!empty($address_name2)) {
                                    echo '<br>' . $address_name2;
                                }
                            } else {
                                echo 'Technische Fakultät';
                            }
                        } else {
                            echo $faculty_name;
                        }
                        ?><br>
                        <?php 
                        // Street address - check new field first, then fallback to old field
                        $street = get_theme_mod('instance_street', '');
                        if (empty($street)) {
                            $street = get_theme_mod('contact_address_street', 'Martensstr. 5a');
                        }
                        echo $street; 
                        ?><br>
                        <?php 
                        // City - check new field first, then construct from old fields
                        $city = get_theme_mod('instance_city', '');
                        if (empty($city)) {
                            $plz = get_theme_mod('contact_address_plz', '');
                            $ort = get_theme_mod('contact_address_ort', '');
                            if (!empty($plz) || !empty($ort)) {
                                $city = trim($plz . ' ' . $ort);
                            } else {
                                $city = '91058 Erlangen';
                            }
                        }
                        echo $city; 
                        ?>
                        <?php 
                        // Country - check both field names
                        $country = get_theme_mod('instance_country', '');
                        if (empty($country)) {
                            $country = get_theme_mod('contact_address_country', '');
                        }
                        if(!empty($country)): 
                        ?>
                            <br><?php echo $country; ?>
                        <?php endif; ?>
                    </address>
                    <?php endif; ?>

                    <div>
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
                </div>
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

        <!-- Row 3: Footer menu and social links (two columns) -->
        <div class="footer-instance-menu">
            <!-- Column 1: Footer Menu -->
            <?php
                wp_nav_menu(array(
                    'theme_location' => 'footer-menu',
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

            <!-- Column 2: Social Media Links -->
            <div class="social-links">
                <?php
                $social_platforms = array(
                    'instagram' => 'Instagram-',
                    'facebook' => 'Facebook',
                    'xing' => 'Xing',
                    'linkedin' => 'LinkedIn',
                    'x' => 'X',
                    'mastodon' => 'Mastodon',
                    'blog' => 'Blog',
                    'bluesky' => 'Bluesky',
                    'youtube' => 'YouTube',
                    'tiktok' => 'TikTok'
                );

                foreach ($social_platforms as $platform => $label) :
                    $url = get_theme_mod("social_${platform}");
                    if (!empty($url)) : ?>
                        <a href="<?php echo esc_url($url); ?>" class="social-link <?php echo esc_attr($platform); ?>" aria-label="<?php echo esc_attr($label); ?>" target="_blank" rel="noopener noreferrer">
                            <!-- <?php echo esc_html($label); ?> -->
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
        <?php 
        // Check if FAU info section should be hidden for cooperation websites
        $hide_fau_info = get_theme_mod('hide_fau_info_section', false);
        
        if (!$hide_fau_info) : ?>
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
                    <?php echo get_theme_mod('fau_info_toggle_text', __('Mehr anzeigen', 'fau-elemental')); ?>
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
                    'section1' => array(
                        'title' => get_theme_mod('target_section1_title', 'Target Group Section 1'),
                        'description' => get_theme_mod('target_section1_description', 'Geschichte, Besonderheiten Daten, Struktur u.v.m'),
                        'link' => get_theme_mod('target_section1_link', '#')
                    ),
                    'section2' => array(
                        'title' => get_theme_mod('target_section2_title', 'Target Group Section 2'),
                        'description' => get_theme_mod('target_section2_description', 'Schwerpunkte, Leitbild, Reputation, Erfolge u.v.m.'),
                        'link' => get_theme_mod('target_section2_link', '#')
                    ),
                    'section3' => array(
                        'title' => get_theme_mod('target_section3_title', 'Target Group Section 3'),
                        'description' => get_theme_mod('target_section3_description', 'Schwerpunkte, Leitbild, Reputation, Erfolge u.v.m.'),
                        'link' => get_theme_mod('target_section3_link', '#')
                    ),
                    'section4' => array(
                        'title' => get_theme_mod('target_section4_title', 'Target Group Section 4'),
                        'description' => get_theme_mod('target_section4_description', 'Schwerpunkte, Leitbild, Reputation, Erfolge u.v.m.'),
                        'link' => get_theme_mod('target_section4_link', '#')
                    )
                );

                foreach ($target_groups as $key => $group) : ?>
                    <a href="<?php echo esc_url($group['link']); ?>" class="target-group">
                        <h3><?php echo esc_html($group['title']); ?></h3>
                        <p><?php echo esc_html($group['description']); ?></p>
                        <span class="arrow-link"></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Row 3: Image Credits handled by fau-copyright-info block -->
        <div class="footer-bottom-row">
            <div class="footer-left">
                <?php echo do_blocks('<!-- wp:fau-elemental/fau-copyright-info /-->'); ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleButton = document.querySelector('.fau-info-toggle');
    const fauInfoSection = document.querySelector('.fau-info-section');
    
    // Only initialize toggle functionality if elements exist (FAU info section is not hidden)
    if (toggleButton && fauInfoSection) {
        // Define text strings for toggle states
        const showMoreText = '<?php _e('Mehr anzeigen', 'fau-elemental'); ?>';
        const showLessText = '<?php _e('Weniger anzeigen', 'fau-elemental'); ?>';

        toggleButton.addEventListener('click', function() {
            const isExpanded = toggleButton.getAttribute('aria-expanded') === 'true';
            toggleButton.setAttribute('aria-expanded', !isExpanded);
            fauInfoSection.hidden = isExpanded;
            
            // Update button text based on state
            const toggleIcon = toggleButton.querySelector('.toggle-icon');
            const currentText = isExpanded ? showMoreText : showLessText;
            
            // Update button text while preserving the icon
            toggleButton.innerHTML = currentText + '<span class="toggle-icon" aria-hidden="true"></span>';
        });
    }
});
</script>
