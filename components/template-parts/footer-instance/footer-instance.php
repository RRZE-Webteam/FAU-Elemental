<div class="footer-content footer-content--instance">
    <div class="footer-content--instance-wrapper">

        <section class="footer-instance-header">
            <div class="instance-info">
                <h2><?php echo esc_html(get_theme_mod('instance_title', get_bloginfo('name'))); ?></h2>
                <p><?php echo wp_kses_post(get_theme_mod('instance_description', get_bloginfo('description'))); ?></p>
            </div>
        </section>

        <section class="footer-instance-contact">
            <?php $display_address = get_theme_mod('display_footer_address'); ?>
            
            <div class="contact-address">
                <?php if ($display_address): ?>
                <h3><?php esc_html_e('Contact and Directions', 'fau-elemental'); ?></h3>

                <div class="contact-address-and-tel-container">
                        <address>
                            <div>
                                <?php
                                echo esc_html(get_theme_mod('instance_university_name', __('Friedrich-Alexander-Universität Erlangen-Nürnberg', 'fau-elemental')));
                                ?><br>
                                <?php
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
                                $street = get_theme_mod('instance_street', '');
                                if (empty($street)) {
                                    $street = get_theme_mod('contact_address_street', '');
                                }
                                if (!empty($street)) {
                                    echo esc_html($street) . '<br>';
                                }
                                ?>
                                <?php
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
                                $country = get_theme_mod('instance_country', '');
                                if (empty($country)) {
                                    $country = get_theme_mod('contact_address_country', '');
                                }
                                if (!empty($country)):
                                ?>
                                    <br><?php echo esc_html($country); ?>
                                <?php endif; ?>
                            </div>

                            <div>
                                <?php
                                $phone = get_theme_mod('instance_phone', '');
                                if (!empty($phone)) :
                                    // Sanitize phone number using the format_phone_number function
                                    if (function_exists('fau_elemental_format_phone_number')) {
                                        $phone = fau_elemental_format_phone_number($phone);
                                    }
                                ?>
                                    <span>
                                        <?php esc_html_e('Phone', 'fau-elemental'); ?>:
                                        <a href="tel:<?php echo esc_attr($phone); ?>">
                                            <?php echo esc_html($phone); ?>
                                        </a>
                                    </span>
                                <?php endif; ?>
                                <?php 
                                $email = get_theme_mod('instance_email', '');
                                if (!empty($email)) : ?>
                                    <span>
                                        <?php esc_html_e('Mail', 'fau-elemental'); ?>:
                                        <a href="mailto:<?php echo esc_attr($email); ?>">
                                            <?php echo esc_html($email); ?>
                                        </a>
                                    </span>
                                <?php endif; ?>
                                <?php 
                                $directions_link = get_theme_mod('instance_directions_link', '');
                                if (!empty($directions_link)) : ?>
                                    <span class="directions">
                                        <a href="<?php echo esc_url($directions_link); ?>" class="directions-link">
                                            <?php esc_html_e('Directions', 'fau-elemental'); ?>
                                        </a>
                                    </span>
                                <?php endif; ?>
                            </div>

                        </address>
                </div>
                <?php endif; ?>
            </div>
            <?php if (has_nav_menu('footer-important-links')) : ?>
            <div class="footer-important-links-container">
                <?php 
                $important_links_heading = get_theme_mod('important_links_heading', __('Important Links', 'fau-elemental'));
                if (!empty($important_links_heading)) : ?>
                    <h3><?php echo esc_html($important_links_heading); ?></h3>
                <?php endif; ?>
                <nav class="footer-important-links">
                  
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'footer-important-links',
                        'menu_class' => 'important-links-list',
                        'container' => false,
                        'fallback_cb' => false
                    ));
                    ?>
                </nav>
            </div>
            <?php endif; ?>
        </section>

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

            <nav class="footer-social" aria-label="<?php echo esc_attr__('Social Media Links', 'fau-elemental'); ?>">
                <?php faue_render_social_media_links(); ?>
            </nav>
        </section>

    </div>
</div>

<section class="footer-bottom">
    <div class="footer-bottom-wrapper">
        <?php
        $hide_fau_info = get_theme_mod('hide_fau_info_section', false);

        if (!$hide_fau_info) : ?>
            <input type="checkbox" id="fau-info-toggle" class="fau-info-toggle-checkbox" aria-controls="fau-info-section">
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
                    <label for="fau-info-toggle" class="fau-info-toggle">
                        <span class="toggle-text toggle-text-collapsed"><?php echo esc_html(get_theme_mod('fau_info_toggle_text', faue_get_default('fau_info_toggle_text'))); ?></span>
                        <span class="toggle-text toggle-text-expanded"><?php echo esc_html(get_theme_mod('fau_info_toggle_text_expanded', faue_get_default('fau_info_toggle_text_expanded'))); ?></span>
                    </label>
                </div>
            </div>

            <div id="fau-info-section" class="footer-bottom-row fau-info-section">
                <section class="fau-claim" aria-labelledby="claim-title">
                    <p id="claim-title"><?php echo esc_html(get_theme_mod('fau_footer_title', faue_get_default('fau_footer_title'))); ?></p>
                    <p><?php echo wp_kses_post(get_theme_mod('fau_footer_description', faue_get_default('fau_footer_description'))); ?></p>
                </section>

                <?php
                require_once get_theme_file_path('components/template-parts/footer-instance/footer-target-groups.php');

                // get settings from FAU site
                $target_groups = faue_get_target_groups_from_fau_blog();

                echo render_footer_target_groups($target_groups, 'outline', 'small');
                ?>
            </div>
        <?php endif; ?>

        <div class="footer-bottom-row">
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
        </div>
    </div>
</section>