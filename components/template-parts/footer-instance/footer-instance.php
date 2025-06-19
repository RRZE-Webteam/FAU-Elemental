<footer class="footer-content footer-content--instance">
    <div class="footer-content--instance-wrapper">

        <!-- Row 1: Header and description (one column) -->
        <header class="footer-instance-header">
            <div class="instance-info">
                <h2><?php echo esc_html(get_theme_mod('instance_title', get_bloginfo('name'))); ?></h2>
                <p><?php echo wp_kses_post(get_theme_mod('instance_description', get_bloginfo('description'))); ?></p>
            </div>
        </header>

        <!-- Row 2: Contact information (three columns) -->
        <section class="footer-instance-contact">
            <!-- Column 1: Address -->
            <div class="contact-address">
                <h3><?php esc_html_e('Contact and Directions', 'fau-elemental'); ?></h3>

                <div class="contact-address-and-tel-container">
                    <?php
                    // Check if we should display the address (default to true if not set)
                    $display_address = get_theme_mod('display_footer_address', true);
                    if (!isset($display_address)) {
                        $display_address = get_theme_mod('advanced_footer_display_address', true);
                    }

                    if ($display_address):
                    ?>
                        <address>
                            <?php
                            // University name
                            echo esc_html(get_theme_mod('instance_university_name', __('Friedrich-Alexander-Universität Erlangen-Nürnberg', 'fau-elemental')));
                            ?><br>
                            <?php
                            // Faculty name - check new field first, then fallback to old fields
                            $faculty_name = get_theme_mod('instance_faculty_name', '');
                            if (empty($faculty_name)) {
                                $address_name = get_theme_mod('contact_address_name', '');
                                $address_name2 = get_theme_mod('contact_address_name2', '');
                                if (!empty($address_name)) {
                                    echo esc_html($address_name);
                                    if (!empty($address_name2)) {
                                        echo '<br>' . esc_html($address_name2);
                                    }
                                }
                            } else {
                                echo esc_html($faculty_name);
                            }
                            if (!empty($faculty_name) || !empty($address_name)) echo '<br>';
                            ?>
                            <?php
                            // Street address - check new field first, then fallback to old field
                            $street = get_theme_mod('instance_street', '');
                            if (empty($street)) {
                                $street = get_theme_mod('contact_address_street', '');
                            }
                            if (!empty($street)) {
                                echo esc_html($street) . '<br>';
                            }
                            ?>
                            <?php
                            // City - check new field first, then construct from old fields
                            $city = get_theme_mod('instance_city', '');
                            if (empty($city)) {
                                $plz = get_theme_mod('contact_address_plz', '');
                                $ort = get_theme_mod('contact_address_ort', '');
                                if (!empty($plz) || !empty($ort)) {
                                    $city = trim($plz . ' ' . $ort);
                                }
                            }
                            if (!empty($city)) {
                                echo esc_html($city);
                            }
                            ?>
                            <?php
                            // Country - check both field names
                            $country = get_theme_mod('instance_country', '');
                            if (empty($country)) {
                                $country = get_theme_mod('contact_address_country', '');
                            }
                            if (!empty($country)):
                            ?>
                                <br><?php echo esc_html($country); ?>
                            <?php endif; ?>
                        </address>
                    <?php endif; ?>

                    <div class="contact-details">
                        <?php 
                        $phone = get_theme_mod('instance_phone', '');
                        if (!empty($phone)) :
                            // Sanitize phone number using the format_phone_number function
                            if (function_exists('fau_elemental_format_phone_number')) {
                                $phone = fau_elemental_format_phone_number($phone);
                            }
                        ?>
                            <p>
                                <?php esc_html_e('Phone', 'fau-elemental'); ?>:
                                <a href="tel:<?php echo esc_attr($phone); ?>">
                                    <?php echo esc_html($phone); ?>
                                </a>
                            </p>
                        <?php endif; ?>
                        <?php 
                        $email = get_theme_mod('instance_email', '');
                        if (!empty($email)) : ?>
                            <p>
                                <?php esc_html_e('Mail', 'fau-elemental'); ?>:
                                <a href="mailto:<?php echo esc_attr($email); ?>">
                                    <?php echo esc_html($email); ?>
                                </a>
                            </p>
                        <?php endif; ?>
                        <?php 
                        $directions_link = get_theme_mod('instance_directions_link', '');
                        if (!empty($directions_link)) : ?>
                            <p class="directions">
                                <a href="<?php echo esc_url($directions_link); ?>" class="directions-link">
                                    <?php esc_html_e('Directions', 'fau-elemental'); ?>
                                </a>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Column 3: Important links -->
            <nav class="footer-important-links">
                <h3><?php esc_html_e('Important Links', 'fau-elemental'); ?></h3>
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'footer-importnat-links',
                    'menu_class' => 'important-links-list',
                    'container' => false,
                    'fallback_cb' => false
                ));
                ?>
            </nav>
        </section>

        <!-- Row 3: Footer menu and social links (two columns) -->
        <section class="footer-instance-menu">
            <!-- Column 1: Footer Menu -->
            <nav class="footer-meta-nav">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'footer-menu',
                    'menu_class' => 'footer-menu-list',
                    'container' => false,
                    'depth' => 1,
                    'fallback_cb' => false
                ));
                ?>
            </nav>

            <!-- Column 2: Social Media Links -->
            <nav class="footer-social" aria-label="<?php echo esc_attr__('Social Media Links', 'fau-elemental'); ?>">
                <div class="social-links">
                    <?php
                    $social_platforms = array(
                        'instagram' => 'Instagram',
                        'facebook' => 'Facebook',
                        'xing' => 'Xing',
                        'linkedin' => 'LinkedIn',
                        'x' => 'X',
                        'mastodon' => 'Mastodon',
                        'bluesky' => 'Bluesky',
                        'youtube' => 'YouTube',
                        'tiktok' => 'TikTok'
                    );

                    foreach ($social_platforms as $platform => $label) :
                        $url = get_theme_mod("social_{$platform}");
                        if (!empty($url)) : ?>
                            <a href="<?php echo esc_url($url); ?>" class="<?php echo esc_attr($platform); ?>" aria-label="<?php echo esc_attr($label); ?>" rel="noopener noreferrer">
                                <span class="sr-only"><?php echo esc_html($label); ?></span>
                            </a>
                        <?php endif;
                    endforeach; ?>
                </div>
            </nav>
        </section>

    </div>
</footer>

<!-- Footer Bottom with FAU Info -->
<footer class="footer-bottom">
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

                <div class="toggle-container">
                    <button class="fau-info-toggle" aria-expanded="false" aria-controls="fau-info-section">
                        <?php echo esc_html(get_theme_mod('fau_info_toggle_text', __('Show more', 'fau-elemental'))); ?>
                        <span class="toggle-icon" aria-hidden="true"></span>
                    </button>
                </div>
            </div>

            <!-- Row 2: Collapsible Target Groups (4 columns) -->
            <div id="fau-info-section" class="footer-bottom-row fau-info-section" hidden>
                <section class="fau-claim">
                    <h3><?php echo esc_html(get_theme_mod('fau_footer_title', __('FAU - Knowledge in Motion', 'fau-elemental'))); ?></h3>
                    <p><?php echo wp_kses_post(get_theme_mod('fau_footer_description', __('FAU is Germany\'s most innovative university, ranking second in Europe. With 40,000 students, we are one of the largest universities in Germany with outstanding teaching and excellent research.', 'fau-elemental'))); ?></p>
                </section>

                <?php
                // Include the footer target groups component
                require_once get_theme_file_path('components/template-parts/footer-instance/footer-target-groups.php');
                
                // Get target groups from customizer
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

                // Render the target groups using the footer component with outline variant
                echo render_footer_target_groups($target_groups, 'outline', 'small');
                ?>
            </div>
        <?php endif; ?>

        <!-- Row 3: Image Credits handled by fau-copyright-info block -->
        <div class="footer-bottom-row">
            <div class="footer-left">
                <?php echo do_blocks('<!-- wp:fau-elemental/fau-copyright-info /-->'); ?>
            </div>
        </div>
    </div>
</footer>