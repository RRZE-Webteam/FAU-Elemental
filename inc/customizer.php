<?php
/**
 * Footer Customizer Settings
 * Reorganized for better user experience
 */

/**
 * ============================================================================
 * FAU-EINRICHTUNGEN BACKWARDS COMPATIBILITY
 * ============================================================================
 * 
 * This section handles backwards compatibility for address information
 * when upgrading from the FAU-Einrichtungen theme to FAU-Elemental.
 * 
 * OLD THEME FIELDS (FAU-Einrichtungen):
 * - advanced_footer_display_address: Whether to show address
 * - contact_address_name: Organization name (line 1)
 * - contact_address_name2: Organization name (line 2)
 * - contact_address_street: Street address
 * - contact_address_plz: Postal code
 * - contact_address_ort: City
 * - contact_address_country: Country
 * 
 * NEW THEME FIELDS (FAU-Elemental):
 * - display_footer_address: Whether to show address
 * - instance_university_name: University name
 * - instance_faculty_name: Faculty name
 * - instance_street: Street address
 * - instance_city: City with postal code
 * - instance_country: Country

 */

/**
 * Sanitize checkbox input
 */
function sanitize_checkbox( $checked ) {
    return ( ( isset( $checked ) && true == $checked ) ? true : false );
}

/**
 * Sanitize and format telephone number according to FAU standards
 * @param string $phone The phone number to sanitize
 * @return string Formatted phone number
 */
function fau_sanitize_phone_number($phone) {
    // Entferne alle Zeichen außer Zahlen, "+", "(", ")", "-" und Leerzeichen
    $phone = preg_replace('/[^\d\+\-\(\) ]/', '', $phone);
    $phone = preg_replace('/\s+/', ' ', trim($phone));

    // Falls die Nummer mit "+49(0)" beginnt → zu "+49" umwandeln
    $phone = preg_replace('/^\+49\s*\(0\)/', '+49', $phone);
    $phone = preg_replace('/^0049/', '+49', $phone);

    // Falls die Nummer mit "0" beginnt (deutsche Nummer ohne Ländercode)
    if (preg_match('/^0[1-9]/', $phone)) {
        $phone = preg_replace('/^0/', '+49 ', $phone);
    }

    // Standardisiere das Format mit Leerzeichen zwischen Gruppen
    $phone = preg_replace('/(\+?\d{1,3})\s*(\d{3,4})\s*(\d{3,4})\s*(\d{0,4})/', '$1 $2 $3 $4', $phone);

    return trim($phone); // Entfernt überflüssige Leerzeichen am Ende
}

/**
 * Sanitize and validate social media URL
 * Uses the same logic as the JavaScript urlValidation.js utility
 * @param string $url The URL to sanitize and validate
 * @return string Valid URL or empty string
 */
function faue_sanitize_social_media_url($url) {
    // If empty, return empty string (empty URLs are valid for optional fields)
    if (empty($url)) {
        return '';
    }
    
    // Trim whitespace
    $url = trim($url);
    
    // Check for obvious non-URL text patterns
    if (preg_match('/^[a-zA-Z\s]+$/', $url)) {
        // If it's just plain text without any URL indicators, reject it
        return '';
    }
    
    // Ensure the URL has a proper scheme
    if (!preg_match('/^https?:\/\//', $url)) {
        // If no scheme, add https://
        $url = 'https://' . $url;
    }
    
    // Use WordPress's built-in URL validation (same as @wordpress/url isURL)
    if (!wp_http_validate_url($url)) {
        // If not a valid URL, return empty string
        return '';
    }
    
    // Return the sanitized URL
    return esc_url_raw($url);
}

/**
 * Enqueue customizer scripts and styles
 */
function faue_enqueue_customizer_scripts() {
    // Enqueue customizer validation styles
    wp_enqueue_style(
        'faue-customizer-validation',
        get_template_directory_uri() . '/build/css/customizer-validation.css',
        array('customize-controls'),
        wp_get_theme()->get('Version')
    );
    
    // Enqueue customizer validation initialization script
    $validation_script_path = get_theme_file_path('build/js/customizer-validation.asset.php');
    if (file_exists($validation_script_path)) {
        $validation_asset = include $validation_script_path;
        
        wp_enqueue_script(
            'faue-customizer-validation-init',
            get_parent_theme_file_uri('build/js/customizer-validation.js'),
            array_merge($validation_asset['dependencies'], array('faue-url-validation', 'customize-controls', 'jquery')),
            $validation_asset['version'],
            false
        );
    }
}
add_action('customize_controls_enqueue_scripts', 'faue_enqueue_customizer_scripts');

function fau_customizer_settings($wp_customize) {
    // Remove the Additional CSS section to disable custom CSS option
    if (!current_user_can('manage_sites')) {
        // Allow to add CSS only for superadmins or admins on a single site installation
        $wp_customize->remove_section( 'custom_css' );  
    } 
 
    // Get the faculty for default values
    $faculty = get_theme_mod('faue_faculty', 'phil');
    
    // Main Footer Panel
    $wp_customize->add_panel('fau_footer_panel', [
        'title' => __('Footer Settings', 'fau-elemental'),
        'priority' => 140,
        'description' => __('Settings for the footer', 'fau-elemental'),
    ]);
    
    // ======= 1. CLAIM SECTION =======
    $website_type = get_theme_mod('faue_website_type', faue_get_default('faue_website_type'));
    $claim_description = __('Configure the main claim section', 'fau-elemental');
    if ($website_type !== 'fau') {
        $claim_description = __('This section is managed centrally by FAU and cannot be edited by faculties.', 'fau-elemental');
    }
    
    $wp_customize->add_section('footer_claim', [
        'title' => __('Claim', 'fau-elemental'),
        'panel' => 'fau_footer_panel',
        'priority' => 10,
        'description' => $claim_description,
    ]);
    
    // Add a notice for non-FAU websites
    if ($website_type !== 'fau') {
        $wp_customize->add_setting('claim_notice', [
            'default' => '',
            'sanitize_callback' => 'sanitize_text_field'
        ]);
        $wp_customize->add_control('claim_notice', [
            'label' => '',
            'description' => __('Note: The FAU claim is centrally managed by FAU and cannot be edited by faculties. The section will display with default FAU content.', 'fau-elemental'),
            'section' => 'footer_claim',
            'type' => 'hidden',
            'priority' => 1
        ]);
    }
    
    // Dark Theme Toggle (Priority 15 - only for FAU main site)
    $wp_customize->add_setting('footer_dark_style', [
        'default' => false,
        'transport' => 'refresh',
        'sanitize_callback' => 'sanitize_checkbox',
    ]);
    
    $wp_customize->add_control('footer_dark_style', [
        'label' => __('Dark Theme Toggle', 'fau-elemental'),
        'description' => __('Apply dark styling to the footer', 'fau-elemental'),
        'section' => 'footer_claim',
        'type' => 'checkbox',
        'priority' => 15,
        'active_callback' => function() {
            return get_theme_mod('faue_website_type', faue_get_default('faue_website_type')) === 'fau';
        },
    ]);
    
    // Überschrift (was FAU Claim Title)
    $wp_customize->add_setting('fau_footer_title', [
        'default' => 'FAU - Wissen in Bewegung',
        'sanitize_callback' => 'sanitize_text_field'
    ]);
    $wp_customize->add_control('fau_footer_title', [
        'label' => __('Heading', 'fau-elemental'),
        'description' => __('Main heading for the claim section', 'fau-elemental'),
        'section' => 'footer_claim',
        'type' => 'text',
        'priority' => 20,
        'active_callback' => function() {
            return get_theme_mod('faue_website_type', faue_get_default('faue_website_type')) === 'fau';
        }
    ]);
    
    // Text (was FAU Claim Text)
    $wp_customize->add_setting('fau_footer_description', [
        'default' => __('Die FAU ist die innovativste Universität Deutschlands, europaweit auf dem zweiten Platz. Mit 40.000 Studierenden gehören wir zu den größten Hochschulen in Deutschland mit herausragender Lehre und exzellenter Forschung.', 'fau-elemental'),
        'sanitize_callback' => 'sanitize_textarea_field'
    ]);
    $wp_customize->add_control('fau_footer_description', [
        'label' => __('Text', 'fau-elemental'),
        'description' => __('Descriptive text for the claim section', 'fau-elemental'),
        'section' => 'footer_claim',
        'type' => 'textarea',
        'priority' => 30,
        'active_callback' => function() {
            return get_theme_mod('faue_website_type', faue_get_default('faue_website_type')) === 'fau';
        }
    ]);
    
    // ======= 2. BESCHREIBUNG SECTION (Faculty Information) =======
    $wp_customize->add_section('footer_beschreibung', [
        'title' => __('Description', 'fau-elemental'),
        'panel' => 'fau_footer_panel',
        'priority' => 20,
        'description' => __('Configure faculty information', 'fau-elemental'),
        'active_callback' => function() {
            return get_theme_mod('faue_website_type', faue_get_default('faue_website_type')) !== 'fau';
        },
    ]);
    
    // Überschrift (was Faculty Title)
    $wp_customize->add_setting('instance_title', [
        'default' => get_bloginfo('name'),
        'sanitize_callback' => 'sanitize_text_field'
    ]);
    $wp_customize->add_control('instance_title', [
        'label' => __('Heading', 'fau-elemental'),
        'description' => __('Main heading for the faculty section', 'fau-elemental'),
        'section' => 'footer_beschreibung',
        'type' => 'text',
        'priority' => 10,
    ]);
    
    // Beschreibung (was Faculty Description)
    $wp_customize->add_setting('instance_description', [
        'default' => get_bloginfo('description'),
        'sanitize_callback' => 'sanitize_textarea_field'
    ]);
    $wp_customize->add_control('instance_description', [
        'label' => __('Description', 'fau-elemental'),
        'description' => __('Descriptive text for the faculty section', 'fau-elemental'),
        'section' => 'footer_beschreibung',
        'type' => 'textarea',
        'priority' => 20,
    ]);
    
    // ======= 3. KONTAKTINFORMATION SECTION =======
    $wp_customize->add_section('footer_kontaktinformation', [
        'title' => __('Contact Information', 'fau-elemental'),
        'panel' => 'fau_footer_panel',
        'priority' => 30,
        'description' => __('Configure contact information', 'fau-elemental'),
        'active_callback' => function() {
            return get_theme_mod('faue_website_type', faue_get_default('faue_website_type')) !== 'fau';
        },
    ]);
    
    // Get faculty-specific default values
    $defaults = [
        'phil' => [
            'name' => __('Faculty of Humanities, Social Sciences, and Theology', 'fau-elemental'),
            'street' => 'Bismarckstraße 1',
            'city' => '91054 Erlangen',
            'phone' => '+49 9131 85 22345',
            'email' => 'dekanat-phil@fau.de'
        ],
        'nat' => [
            'name' => __('Faculty of Sciences', 'fau-elemental'),
            'street' => 'Naturwissenschaftliche Fakultät',
            'city' => '91058 Erlangen',
            'phone' => '+49 9131 85 27032',
            'email' => 'dekanat-nat@fau.de'
        ],
        'med' => [
            'name' => __('Faculty of Medicine', 'fau-elemental'),
            'street' => 'Krankenhausstraße 12',
            'city' => '91054 Erlangen',
            'phone' => '+49 9131 85 26730',
            'email' => 'med-dekanat@fau.de'
        ],
        'rw' => [
            'name' => __('Faculty of Business, Economics, and Law', 'fau-elemental'),
            'street' => 'Schillerstraße 1',
            'city' => '91054 Erlangen',
            'phone' => '+49 9131 85 22260',
            'email' => 'dekanat-rw@fau.de'
        ],
        'tf' => [
            'name' => __('Faculty of Engineering', 'fau-elemental'),
            'street' => 'Martensstraße 5a',
            'city' => '91058 Erlangen',
            'phone' => '+49 9131 85 27130',
            'email' => 'tf-dekanat@fau.de'
        ]
    ];
    
    // Set defaults based on selected faculty
    $faculty_defaults = isset($defaults[$faculty]) ? $defaults[$faculty] : $defaults['phil'];
    
    // Display toggle for address information
    $wp_customize->add_setting('display_footer_address', [
        'default' => faue_get_default('display_footer_address'),
        'transport' => 'refresh',
        'sanitize_callback' => 'sanitize_checkbox',
    ]);
    
    $wp_customize->add_control('display_footer_address', [
        'label' => __('Display Address Information', 'fau-elemental'),
        'description' => __('Display address information in the footer', 'fau-elemental'),
        'section' => 'footer_kontaktinformation',
        'type' => 'checkbox',
        'priority' => 5,
    ]);
    
    $contact_fields = [
        'instance_university_name' => [
            'label' => __('Name of the University', 'fau-elemental'),
            'default' => ''
        ],
        'instance_faculty_name' => [
            'label' => __('Name of the Faculty', 'fau-elemental'),
            'default' => ''
        ],
        'instance_street' => [
            'label' => __('Street', 'fau-elemental'),
            'default' => ''
        ],
        'instance_city' => [
            'label' => __('Postal Code and City', 'fau-elemental'),
            'default' => ''
        ],
        'instance_phone' => [
            'label' => __('Phone Number', 'fau-elemental'),
            'default' => ''
        ],
        'instance_email' => [
            'label' => __('E-Mail Address', 'fau-elemental'),
            'default' => ''
        ],
        'instance_directions_link' => [
            'label' => __('Directions Link', 'fau-elemental'),
            'default' => ''
        ]
    ];

    foreach ($contact_fields as $setting => $config) {
        $sanitize_callback = 'sanitize_text_field';
        
        // Use phone sanitizer for phone number fields
        if ($setting === 'instance_phone') {
            $sanitize_callback = 'fau_elemental_format_phone_number';
        }
        
        $wp_customize->add_setting($setting, [
            'default' => $config['default'],
            'sanitize_callback' => $sanitize_callback,
        ]);

        $wp_customize->add_control($setting, [
            'label' => $config['label'],
            'section' => 'footer_kontaktinformation',
            'type' => 'text',
        ]);
    }
    
    // Country field for backward compatibility
    $wp_customize->add_setting('instance_country', [
        'default' => '',
        'transport' => 'refresh',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    
    $wp_customize->add_control('instance_country', [
        'label' => __('Country', 'fau-elemental'),
        'section' => 'footer_kontaktinformation',
        'type' => 'text',
        'priority' => 65,
    ]);
    
    // Important Links heading
    $wp_customize->add_setting('important_links_heading', [
        'default' => __('Important Links', 'fau-elemental'),
        'transport' => 'refresh',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    
    $wp_customize->add_control('important_links_heading', [
        'label' => __('Important Links Heading', 'fau-elemental'),
        'description' => __('Heading text for the important links section in the footer', 'fau-elemental'),
        'section' => 'footer_kontaktinformation',
        'type' => 'text',
        'priority' => 70,
    ]);
    
    // ======= 4. ZIELGRUPPEN-LINKS SECTION =======
    $website_type = get_theme_mod('faue_website_type', faue_get_default('faue_website_type'));
    $section_description = __('Configure the target group sections', 'fau-elemental');
    if ($website_type !== 'fau') {
        $section_description = __('These settings are managed centrally by FAU and cannot be edited by faculties.', 'fau-elemental');
    }
    
    $wp_customize->add_section('footer_zielgruppen', [
        'title' => __('Target Group Links', 'fau-elemental'),
        'panel' => 'fau_footer_panel',
        'priority' => 40,
        'description' => $section_description
    ]);
    
    // Hide FAU info section for cooperation websites (only for non-FAU sites)
    $wp_customize->add_setting('hide_fau_info_section', [
        'default' => false,
        'transport' => 'refresh',
        'sanitize_callback' => 'sanitize_checkbox',
    ]);
                
    $wp_customize->add_control('hide_fau_info_section', [
        'label' => __('Hide FAU section', 'fau-elemental'),
        'description' => __('Hide FAU section (only show copyright information). Recommended for external cooperation websites.', 'fau-elemental'),
        'section' => 'footer_zielgruppen',
        'type' => 'checkbox',
        'priority' => 5,
        'active_callback' => function() {
            return get_theme_mod('faue_website_type', faue_get_default('faue_website_type')) === 'cooperation';
        },
    ]);
    
    $target_groups = [
        'section1' => __('Section 1', 'fau-elemental'),
        'section2' => __('Section 2', 'fau-elemental'),
        'section3' => __('Section 3', 'fau-elemental'),
        'section4' => __('Section 4', 'fau-elemental')
    ];
    
    // Add a notice for non-FAU websites
    if ($website_type !== 'fau') {
        $wp_customize->add_setting('target_groups_notice', [
            'default' => '',
            'sanitize_callback' => 'sanitize_text_field'
        ]);
        $wp_customize->add_control('target_groups_notice', [
            'label' => '',
            'description' => __('Note: Target group links are centrally managed by FAU and cannot be edited by faculties. The sections will display with default FAU content.', 'fau-elemental'),
            'section' => 'footer_zielgruppen',
            'type' => 'hidden',
            'priority' => 1
        ]);
    }
    
    foreach ($target_groups as $key => $label) {
        // Group heading
        $wp_customize->add_setting('target_group_heading_' . $key, [
            'default' => $label,
            'sanitize_callback' => 'sanitize_text_field'
        ]);
        $wp_customize->add_control(new WP_Customize_Control($wp_customize, 'target_group_heading_' . $key, [
            'label' => $label,
            'section' => 'footer_zielgruppen',
            'settings' => 'target_group_heading_' . $key,
            'type' => 'hidden'
        ]));
        
        // Überschrift (was Title)
        $wp_customize->add_setting('target_' . $key . '_title', [
            'default' => $label,
            'sanitize_callback' => 'sanitize_text_field'
        ]);
        $wp_customize->add_control('target_' . $key . '_title', [
            'label' => __('Heading', 'fau-elemental'),
            'section' => 'footer_zielgruppen',
            'type' => 'text',
            'active_callback' => function() {
                return get_theme_mod('faue_website_type', faue_get_default('faue_website_type')) === 'fau';
            }
        ]);
        
        // Beschreibung (was Description)
        $default_desc = __('Focus, Mission, Reputation, Successes, etc.', 'fau-elemental');
        if ($key === 'section1') {
            $default_desc = __('History, Specialties, Data, Structure, etc.', 'fau-elemental');
        }
        
        $wp_customize->add_setting('target_' . $key . '_description', [
            'default' => $default_desc,
            'sanitize_callback' => 'sanitize_textarea_field'
        ]);
        $wp_customize->add_control('target_' . $key . '_description', [
            'label' => __('Description', 'fau-elemental'),
            'section' => 'footer_zielgruppen',
            'type' => 'textarea',
            'active_callback' => function() {
                return get_theme_mod('faue_website_type', faue_get_default('faue_website_type')) === 'fau';
            }
        ]);
        
        // Link URL (unchanged)
        $wp_customize->add_setting('target_' . $key . '_link', [
            'default' => '#',
            'sanitize_callback' => 'esc_url_raw'
        ]);
        $wp_customize->add_control('target_' . $key . '_link', [
            'label' => __('Link URL', 'fau-elemental'),
            'section' => 'footer_zielgruppen',
            'type' => 'url',
            'active_callback' => function() {
                return get_theme_mod('faue_website_type', faue_get_default('faue_website_type')) === 'fau';
            }
        ]);
        
        // Separator
        if ($key != 'section4') {
            $wp_customize->add_setting('target_separator_' . $key, [
                'sanitize_callback' => 'sanitize_text_field'
            ]);
            $wp_customize->add_control(new WP_Customize_Control($wp_customize, 'target_separator_' . $key, [
                'label' => '',
                'section' => 'footer_zielgruppen',
                'settings' => 'target_separator_' . $key,
                'type' => 'hidden'
            ]));
        }
    }
    
    // ======= 5. SOCIAL-MEDIA-LINKS SECTION =======
    $wp_customize->add_section('footer_social_media', [
        'title' => __('Social Media Links', 'fau-elemental'),
        'panel' => 'fau_footer_panel',
        'priority' => 50,
        'description' => __('Configure social media links. You can either use individual platform settings below or create a "Social Media Menu" in Appearance > Menus.', 'fau-elemental'),
    ]);
    
    // Social Media Mode Selection
    $wp_customize->add_setting('faue_social_media_mode', [
        'default' => 'menu',
        'sanitize_callback' => 'sanitize_text_field',
        'transport' => 'refresh',
    ]);
    
    $wp_customize->add_control('faue_social_media_mode', [
        'label' => __('Social Media Display Mode', 'fau-elemental'),
        'description' => __('Choose how to manage social media links', 'fau-elemental'),
        'section' => 'footer_social_media',
        'type' => 'radio',
        'choices' => [
            'customizer' => __('Individual Platform Settings (below)', 'fau-elemental'),
            'menu' => __('WordPress Menu (Appearance > Menus > Social Media Menu)', 'fau-elemental'),
        ],
        'priority' => 5,
    ]);
    
    // Get social platforms from config (including custom ones)
    $social_platforms = faue_get_combined_social_platforms();

    foreach ($social_platforms as $key => $label) {
        // Platform URL setting
        $wp_customize->add_setting('social_' . $key, [
            'sanitize_callback' => 'faue_sanitize_social_media_url',
            'transport' => 'refresh',
        ]);
        
        $wp_customize->add_control('social_' . $key, [
            'label' => sprintf(
                /* translators: %s: Social media platform name */
                __('%s URL', 'fau-elemental'), 
                $label
            ),
            /* translators: social media platform */
                'description' => sprintf(
                    /* translators: %s: Social media platform name */
                    __('Enter the %s URL (leave empty to hide)', 'fau-elemental'), 
                    $label
                ),
            'section' => 'footer_social_media',
            'type' => 'url',
            'priority' => 10 + array_search($key, array_keys($social_platforms)),
            'active_callback' => function() {
                return get_theme_mod('faue_social_media_mode', 'customizer') === 'customizer';
            },
        ]);
    }
    
    // ======= 6. POST OPTIONS SECTION =======
    $wp_customize->add_section('faue_post_options', array(
        'title'    => esc_html__('Post Settings', 'fau-elemental'),
        'priority' => 135,
    ));

    // Add setting for showing/hiding post meta
    $wp_customize->add_setting('faue_show_post_meta', array(
        'default'           => true,
        'sanitize_callback' => 'faue_sanitize_checkbox',
        'transport'         => 'refresh',
    ));

    // Add control for showing/hiding post meta
    $wp_customize->add_control('faue_show_post_meta', array(
        'label'       => esc_html__('Display post metadata', 'fau-elemental'),
        'description' => esc_html__('Shows date and reading-time on posts.', 'fau-elemental'),
        'section'     => 'faue_post_options',
        'type'        => 'checkbox',
        'priority'    => 10,
    ));

    // Add setting for post meta dark theme
    $wp_customize->add_setting('faue_post_meta_dark_theme', array(
        'default'           => false,
        'sanitize_callback' => 'faue_sanitize_checkbox',
        'transport'         => 'refresh',
    ));

    // Add control for post meta dark theme
    $wp_customize->add_control('faue_post_meta_dark_theme', array(
        'label'           => esc_html__('Apply dark-theme style to metadata', 'fau-elemental'),
        'description'     => esc_html__('Turns the metadata bar dark-blue with white text.', 'fau-elemental'),
        'section'         => 'faue_post_options',
        'type'            => 'checkbox',
        'priority'        => 20,
        'active_callback' => function() {
            return get_theme_mod('faue_show_post_meta', true);
        },
    ));

    // Add setting for hiding copyright on single posts
    $wp_customize->add_setting('faue_hide_copyright_on_single', array(
        'default'           => faue_get_default('faue_hide_copyright_on_single'),
        'sanitize_callback' => 'faue_sanitize_checkbox',
        'transport'         => 'refresh',
    ));

    // Add control for hiding copyright on single posts
    $wp_customize->add_control('faue_hide_copyright_on_single', array(
        'label'       => esc_html__('Hide copyright on single posts', 'fau-elemental'),
        'description' => esc_html__('Hides the copyright block on individual post pages.', 'fau-elemental'),
        'section'     => 'faue_post_options',
        'type'        => 'checkbox',
        'priority'    => 30,
    ));

    // Add Archive Pagination Settings Section
    $wp_customize->add_section('faue_pagination_settings', array(
        'title'    => esc_html__('Archive pages settings', 'fau-elemental'),
        'priority' => 130,
    ));

    // Pagination Type Setting
    $wp_customize->add_setting('faue_pagination_type', array(
        'default'           => 'numbers',
        'transport'         => 'refresh',
        'sanitize_callback' => 'faue_sanitize_pagination_type',
    ));

    $wp_customize->add_control('faue_pagination_type', array(
        'label'       => esc_html__('Pagination Type', 'fau-elemental'),
        'section'     => 'faue_pagination_settings',
        'type'        => 'select',
        'choices'     => array(
            'numbers'   => esc_html__('Page Numbers', 'fau-elemental'),
            'load-more' => esc_html__('Load More Button', 'fau-elemental'),
        ),
        'description' => esc_html__('Choose how pagination is displayed on archive pages and teaser grids.', 'fau-elemental'),
    ));

    // Items Per Page Setting
    $wp_customize->add_setting('faue_items_per_page', array(
        'default'           => 6,
        'transport'         => 'refresh',
        'sanitize_callback' => 'faue_sanitize_items_per_page',
    ));

    $wp_customize->add_control('faue_items_per_page', array(
        'label'       => esc_html__('Items Per Page', 'fau-elemental'),
        'section'     => 'faue_pagination_settings',
        'type'        => 'number',
        'description' => esc_html__('Number of items to display per page in archive pages and teaser grids.', 'fau-elemental'),
        'input_attrs' => array(
            'min'  => 1,
            'max'  => 50,
            'step' => 1,
        ),
    ));
}
add_action('customize_register', 'fau_customizer_settings');

/**
 * Sanitize pagination type input
 */
function faue_sanitize_pagination_type($input) {
    $valid_types = array('numbers', 'load-more');
    
    if (!in_array($input, $valid_types)) {
        return 'numbers';
    }
    
    return $input;
}

/**
 * Sanitize items per page input
 */
function faue_sanitize_items_per_page($input) {
    $input = intval($input);
    return max(1, min(50, $input)) ?: 6;
}

/**
 * Get theme pagination settings
 */
function faue_get_pagination_type() {
    return get_theme_mod('faue_pagination_type', 'numbers');
}

/**
 * Get theme items per page setting
 */
function faue_get_items_per_page() {
    return get_theme_mod('faue_items_per_page', 6);
}

/**
 * Synchronize WordPress's posts_per_page setting with theme's custom setting
 * This ensures pagination URLs work correctly
 */
function faue_sync_posts_per_page($query) {
    // Only apply to main query on archive pages and front page
    if (!is_admin() && $query->is_main_query()) {
        if (is_home() || is_archive() || is_category() || is_tag() || is_author() || is_date()) {
            $theme_posts_per_page = faue_get_items_per_page();
            $query->set('posts_per_page', $theme_posts_per_page);
        }
    }
}
add_action('pre_get_posts', 'faue_sync_posts_per_page');

/**
 * Get list of possible previous FAU themes
 * 
 * @return array Array of theme names that could contain migration data
 */
function fau_elemental_get_previous_theme_names() {
    return array(
        'FAU-Einrichtungen',
        'FAU-Medfak',
        'FAU-Natfak', 
        'FAU-Philfak',
        'FAU-RWFak',
        'FAU-Techfak'
    );
}

/**
 * Normalize theme name by removing branch suffixes like -master, -main, -develop
 * 
 * @param string $theme_name The theme name (possibly with branch suffix)
 * @return string Normalized theme name without branch suffix
 */
function fau_elemental_normalize_theme_name($theme_name) {
    if (empty($theme_name)) {
        return '';
    }
    
    // Remove common branch suffixes
    $branch_suffixes = array('-master', '-main', '-develop', '-dev', '-branch', '-trunk');
    foreach ($branch_suffixes as $suffix) {
        if (substr($theme_name, -strlen($suffix)) === $suffix) {
            $theme_name = substr($theme_name, 0, -strlen($suffix));
            break;
        }
    }
    
    return $theme_name;
}

/**
 * Get theme name from style.css file
 * 
 * @param string $theme_stylesheet The theme stylesheet/directory name
 * @return string|false Theme name from style.css or false if not found
 */
function fau_elemental_get_theme_name_from_stylecss($theme_stylesheet) {
    if (empty($theme_stylesheet)) {
        return false;
    }
    
    // Try to locate the theme directory
    $theme_root = get_theme_root();
    $theme_path = $theme_root . '/' . $theme_stylesheet;
    
    if (!is_dir($theme_path)) {
        return false;
    }
    
    $style_css = $theme_path . '/style.css';
    if (!file_exists($style_css)) {
        return false;
    }
    
    // Read first 20 lines of style.css to get theme header
    $file = fopen($style_css, 'r');
    if (!$file) {
        return false;
    }
    
    $theme_name = false;
    $line_count = 0;
    while (!feof($file) && $line_count < 20) {
        $line = fgets($file);
        $line_count++;
        
        // Look for Theme Name: in the header
        if (preg_match('/^\s*\*\s*Theme Name:\s*(.+)$/i', $line, $matches)) {
            $theme_name = trim($matches[1]);
            break;
        }
    }
    fclose($file);
    
    return $theme_name;
}

/**
 * Detect the most recent previous theme configuration
 * 
 * Uses multiple criteria to determine which configuration is most recent:
 * 1. Priority (matching previous theme name, faculty-specific themes)
 * 2. date-last-use timestamp (if available in theme_mods - most recent wins)
 * 3. optiontable_version (fallback if date-last-use not available)
 * 
 * Also checks WordPress's stored previous theme name as a fallback.
 * Checks all 6 possible FAU themes: FAU-Einrichtungen, FAU-Medfak, FAU-Natfak, 
 * FAU-Philfak, FAU-RWFak, FAU-Techfak
 * 
 * @return array|false Array with theme name and mods data, or false if none found
 */
function fau_elemental_detect_previous_theme_config() {
    $theme_names = fau_elemental_get_previous_theme_names();
    $found_configs = array();
    
    // First, try to get the previous theme name from WordPress (WP 5.9+)
    $previous_theme_name = '';
    $previous_theme_stylesheet = '';
    if (function_exists('wp_get_previous_theme')) {
        $previous_theme = wp_get_previous_theme();
        if ($previous_theme && is_object($previous_theme)) {
            // Get both display name and stylesheet (directory name)
            $previous_theme_name = $previous_theme->get('Name');
            $previous_theme_stylesheet = $previous_theme->get_stylesheet();
        }
    }
    
    // Check our captured theme (most reliable - captured before switch)
    if (empty($previous_theme_stylesheet)) {
        $captured = get_transient('fau_elemental_captured_previous_theme');
        if (empty($captured)) {
            $captured = get_option('fau_elemental_captured_previous_theme');
        }
        if (!empty($captured) && is_array($captured)) {
            $previous_theme_stylesheet = isset($captured['stylesheet']) ? $captured['stylesheet'] : '';
            $previous_theme_name = isset($captured['name']) ? $captured['name'] : $previous_theme_stylesheet;
        }
    }
    
    // Also check the transient that WordPress sets during theme switch
    $transient_name = get_transient('_theme_switched');
    if (empty($previous_theme_name) && !empty($transient_name)) {
        $previous_theme_name = $transient_name;
        $previous_theme_stylesheet = $transient_name;
    }
    
    // Check WordPress options (sometimes stored in options table)
    if (empty($previous_theme_stylesheet)) {
        $old_stylesheet = get_option('theme_switched');
        if (!empty($old_stylesheet)) {
            $previous_theme_stylesheet = $old_stylesheet;
            $previous_theme_name = $old_stylesheet;
        }
    }
    
    // Normalize theme name by removing branch suffixes
    $normalized_previous_theme_name = fau_elemental_normalize_theme_name($previous_theme_name);
    $normalized_previous_stylesheet = fau_elemental_normalize_theme_name($previous_theme_stylesheet);
    
    // Try to get theme name from style.css if we have a stylesheet
    if (!empty($previous_theme_stylesheet)) {
        $stylecss_theme_name = fau_elemental_get_theme_name_from_stylecss($previous_theme_stylesheet);
        if ($stylecss_theme_name) {
            // Use style.css theme name if it matches one of our known themes
            $theme_names = fau_elemental_get_previous_theme_names();
            foreach ($theme_names as $known_theme) {
                if (strcasecmp($stylecss_theme_name, $known_theme) === 0) {
                    $normalized_previous_theme_name = $known_theme;
                    $normalized_previous_stylesheet = $known_theme;
                    break;
                }
            }
        }
    }
    
    // Use normalized stylesheet if available (more reliable for theme_mods lookup)
    if (!empty($normalized_previous_stylesheet)) {
        $previous_theme_name = $normalized_previous_stylesheet;
    } elseif (!empty($normalized_previous_theme_name)) {
        $previous_theme_name = $normalized_previous_theme_name;
    }
    
    // Check each possible theme for configuration data
    foreach ($theme_names as $theme_name) {
        // Check both the base name and the name with suffix (if previous theme had suffix)
        $option_names_to_check = array('theme_mods_' . $theme_name);
        
        // If previous theme had a suffix, also check with that suffix
        if (!empty($previous_theme_stylesheet) && $previous_theme_stylesheet !== $normalized_previous_stylesheet) {
            // Check if the previous theme stylesheet matches this theme name with a suffix
            if (stripos($previous_theme_stylesheet, $theme_name) === 0) {
                $option_names_to_check[] = 'theme_mods_' . $previous_theme_stylesheet;
            }
        }
        
        $theme_mods = array();
        $found_option_name = '';
        
        // Try each option name
        foreach ($option_names_to_check as $option_name) {
            $check_mods = get_option($option_name, array());
            
            // Also check if the option exists but is empty (might be serialized differently)
            if (empty($check_mods) || !is_array($check_mods)) {
                $raw_option = get_option($option_name);
                if ($raw_option !== false && !empty($raw_option)) {
                    // Option exists but might not be unserialized correctly
                    $check_mods = maybe_unserialize($raw_option);
                    if (!is_array($check_mods)) {
                        $check_mods = array();
                    }
                }
            }
            
            if (!empty($check_mods) && is_array($check_mods)) {
                $theme_mods = $check_mods;
                $found_option_name = $option_name;
                break;
            }
        }
        
        
        if (!empty($theme_mods)) {
            // Get optiontable version to determine which is most recent
            $version = isset($theme_mods['optiontable_version']) ? intval($theme_mods['optiontable_version']) : 0;
            
            // Get date-last-use timestamp if available (helps identify most recently used theme)
            // Check both possible field names: 'date-last-use' and 'Date-last-use'
            $date_last_use = 0;
            if (isset($theme_mods['date-last-use'])) {
                $date_last_use = intval($theme_mods['date-last-use']);
            } elseif (isset($theme_mods['Date-last-use'])) {
                $date_last_use = intval($theme_mods['Date-last-use']);
            }
            
            // Give priority to themes that match the previous theme name
            $priority = 0;
            $priority_reason = '';
            if ($previous_theme_name || $normalized_previous_theme_name) {
                $check_name = $normalized_previous_theme_name ? $normalized_previous_theme_name : $previous_theme_name;
                // Check if names match exactly (case-insensitive)
                if (strcasecmp($check_name, $theme_name) === 0) {
                    $priority = 2000; // Highest priority for exact match
                    $priority_reason = 'exact match with previous theme';
                } elseif (stripos($check_name, $theme_name) !== false || stripos($theme_name, $check_name) !== false) {
                    $priority = 1000; // High priority for partial match
                    $priority_reason = 'partial match with previous theme';
                }
            }
            
            // Also prioritize faculty-specific themes over FAU-Einrichtungen
            if ($theme_name !== 'FAU-Einrichtungen') {
                $priority += 100;
                $priority_reason .= ($priority_reason ? ' + ' : '') . 'faculty-specific theme';
            }
            
            $config_info = array(
                'theme_name' => $theme_name,
                'theme_mods' => $theme_mods,
                'version' => $version,
                'date_last_use' => $date_last_use,
                'priority' => $priority,
                'priority_reason' => $priority_reason ?: 'no priority'
            );
            
            $found_configs[] = $config_info;
        }
    }
    
    // Also check if previous theme stylesheet directly matches a theme_mods option
    // Check both with and without suffix
    if (!empty($previous_theme_stylesheet) && empty($found_configs)) {
        $options_to_check = array(
            'theme_mods_' . $previous_theme_stylesheet,
            'theme_mods_' . $normalized_previous_stylesheet
        );
        
        foreach ($options_to_check as $direct_option_name) {
            $direct_theme_mods = get_option($direct_option_name, array());
            if (!empty($direct_theme_mods) && is_array($direct_theme_mods)) {
                // Check if this stylesheet name (normalized) matches any of our known FAU themes
                $check_stylesheet = $direct_option_name === 'theme_mods_' . $previous_theme_stylesheet 
                    ? $normalized_previous_stylesheet 
                    : $normalized_previous_stylesheet;
                    
                foreach ($theme_names as $theme_name) {
                    if (strcasecmp($check_stylesheet, $theme_name) === 0 || stripos($check_stylesheet, $theme_name) === 0) {
                        // Get date-last-use if available
                        $date_last_use = 0;
                        if (isset($direct_theme_mods['date-last-use'])) {
                            $date_last_use = intval($direct_theme_mods['date-last-use']);
                        } elseif (isset($direct_theme_mods['Date-last-use'])) {
                            $date_last_use = intval($direct_theme_mods['Date-last-use']);
                        }
                        
                        return array(
                            'theme_name' => $theme_name,
                            'theme_mods' => $direct_theme_mods,
                            'version' => isset($direct_theme_mods['optiontable_version']) ? intval($direct_theme_mods['optiontable_version']) : 0,
                            'date_last_use' => $date_last_use,
                            'priority' => 3000
                        );
                    }
                }
            }
        }
    }
    
    // If no configs found, but we have a previous theme name, try to infer faculty from it
    $inference_check_name = $normalized_previous_theme_name ? $normalized_previous_theme_name : $previous_theme_name;
    if (empty($found_configs) && !empty($inference_check_name)) {
        // Check if the previous theme name contains any faculty indicator
        foreach ($theme_names as $theme_name) {
            if (stripos($inference_check_name, $theme_name) !== false || stripos($theme_name, $inference_check_name) !== false) {
                // Found a matching theme name, create a minimal config
                return array(
                    'theme_name' => $theme_name,
                    'theme_mods' => array(),
                    'version' => 0,
                    'priority' => 0
                );
            }
        }
        
        // Try to extract faculty from theme name directly
        $faculty_from_name = '';
        if (stripos($inference_check_name, 'techfak') !== false || stripos($inference_check_name, 'tech') !== false) {
            $faculty_from_name = 'FAU-Techfak';
        } elseif (stripos($inference_check_name, 'rwfak') !== false || stripos($inference_check_name, 'rw') !== false) {
            $faculty_from_name = 'FAU-RWFak';
        } elseif (stripos($inference_check_name, 'medfak') !== false || stripos($inference_check_name, 'med') !== false) {
            $faculty_from_name = 'FAU-Medfak';
        } elseif (stripos($inference_check_name, 'natfak') !== false || stripos($inference_check_name, 'nat') !== false) {
            $faculty_from_name = 'FAU-Natfak';
        } elseif (stripos($inference_check_name, 'philfak') !== false || stripos($inference_check_name, 'phil') !== false) {
            $faculty_from_name = 'FAU-Philfak';
        }
        
        if (!empty($faculty_from_name)) {
            return array(
                'theme_name' => $faculty_from_name,
                'theme_mods' => array(),
                'version' => 0,
                'priority' => 0
            );
        }
    }
    
    // If no configs found, return false
    if (empty($found_configs)) {
        return false;
    }
    
    // If only one config found, use it
    if (count($found_configs) === 1) {
        return $found_configs[0];
    }
    
    // If multiple configs found, prioritize by:
    // 1. Priority (matching previous theme name, faculty-specific themes)
    // 2. date-last-use (most recent timestamp - highest number wins)
    // 3. Version number (most recent - fallback if date-last-use not available)
    usort($found_configs, function($a, $b) {
        // First sort by priority
        if ($a['priority'] != $b['priority']) {
            return $b['priority'] - $a['priority'];
        }
        
        // Then by date-last-use (most recent timestamp wins)
        // If both have date-last-use, use it; if only one has it, prioritize that one
        $a_has_date = isset($a['date_last_use']) && $a['date_last_use'] > 0;
        $b_has_date = isset($b['date_last_use']) && $b['date_last_use'] > 0;
        
        if ($a_has_date && $b_has_date) {
            // Both have date-last-use, compare timestamps
            if ($a['date_last_use'] != $b['date_last_use']) {
                return $b['date_last_use'] - $a['date_last_use'];
            }
        } elseif ($a_has_date && !$b_has_date) {
            // A has date-last-use, B doesn't - prioritize A
            return -1;
        } elseif (!$a_has_date && $b_has_date) {
            // B has date-last-use, A doesn't - prioritize B
            return 1;
        }
        
        // If neither has date-last-use or they're equal, fall back to version number
        return $b['version'] - $a['version'];
    });
    
    return $found_configs[0];
}

/**
 * Map faculty theme names to faculty codes
 * 
 * @param string $theme_name The theme name
 * @return string Faculty code
 */
function fau_elemental_map_theme_to_faculty($theme_name) {
    $mapping = array(
        'FAU-Medfak' => 'med',
        'FAU-Natfak' => 'nat',
        'FAU-Philfak' => 'phil',
        'FAU-RWFak' => 'rw',
        'FAU-Techfak' => 'tf',
        'FAU-Einrichtungen' => 'phil' // Default fallback
    );
    
    return isset($mapping[$theme_name]) ? $mapping[$theme_name] : 'phil';
}

/**
 * Comprehensive migration function for all theme settings
 * This replaces the individual migration functions and consolidates all migration logic
 * 
 * @param bool $force Whether to force migration even if already done
 * @return array Migration results
 */
function fau_elemental_migrate_all_settings($force = false) {
    // Check if we've already migrated
    if (!$force && get_option('fau_elemental_all_settings_migrated')) {
        return array('migrated' => false, 'reason' => 'already_migrated');
    }
    
    // Detect the previous theme configuration
    $previous_config = fau_elemental_detect_previous_theme_config();
    
    // Try to get previous theme name even if no config found
    // Use the same detection logic as in detect_previous_theme_config
    $previous_theme_name = '';
    $previous_theme_stylesheet = '';
    
    // First try WordPress function
    if (function_exists('wp_get_previous_theme')) {
        $previous_theme = wp_get_previous_theme();
        if ($previous_theme && is_object($previous_theme)) {
            $previous_theme_name = $previous_theme->get('Name');
            $previous_theme_stylesheet = $previous_theme->get_stylesheet();
        }
    }
    
    // Check our captured theme (most reliable)
    if (empty($previous_theme_stylesheet)) {
        $captured = get_transient('fau_elemental_captured_previous_theme');
        if (empty($captured)) {
            $captured = get_option('fau_elemental_captured_previous_theme');
        }
        if (!empty($captured) && is_array($captured)) {
            $previous_theme_stylesheet = isset($captured['stylesheet']) ? $captured['stylesheet'] : '';
            $previous_theme_name = isset($captured['name']) ? $captured['name'] : $previous_theme_stylesheet;
        }
    }
    
    // Check WordPress transient
    if (empty($previous_theme_name)) {
        $previous_theme_name = get_transient('_theme_switched');
        if (!empty($previous_theme_name) && empty($previous_theme_stylesheet)) {
            $previous_theme_stylesheet = $previous_theme_name;
        }
    }
    
    // Check WordPress option
    if (empty($previous_theme_stylesheet)) {
        $old_stylesheet = get_option('theme_switched');
        if (!empty($old_stylesheet)) {
            $previous_theme_stylesheet = $old_stylesheet;
            $previous_theme_name = $old_stylesheet;
        }
    }
    
    // Normalize theme names by removing branch suffixes
    // But preserve original for checking options
    $original_previous_stylesheet = $previous_theme_stylesheet; // Store original before normalization
    $original_previous_theme_name = $previous_theme_name; // Store original before normalization
    
    $normalized_previous_theme_name = fau_elemental_normalize_theme_name($previous_theme_name);
    $normalized_previous_stylesheet = fau_elemental_normalize_theme_name($previous_theme_stylesheet);
    
    // Use normalized stylesheet if available (more reliable)
    if (!empty($normalized_previous_stylesheet)) {
        $previous_theme_name = $normalized_previous_stylesheet;
    } elseif (!empty($normalized_previous_theme_name)) {
        $previous_theme_name = $normalized_previous_theme_name;
    }
    
    if (!$previous_config) {
        // No previous config found, but try to infer faculty from theme name
        $inferred_faculty = null;
        $inferred_theme_name = '';
        $inferred_website_type = null;
        
        // Use normalized theme name for inference, but keep original for checking options
        $inference_theme_name = $normalized_previous_theme_name ? $normalized_previous_theme_name : $previous_theme_name;
        // Use the original stylesheet we preserved earlier
        $original_previous_stylesheet_for_inference = $original_previous_stylesheet;
        
        if (!empty($inference_theme_name)) {
            // First check if the theme name exactly matches one of our known themes
            $theme_names = fau_elemental_get_previous_theme_names();
            foreach ($theme_names as $theme_name) {
                if (strcasecmp($inference_theme_name, $theme_name) === 0) {
                    $inferred_theme_name = $theme_name;
                    
                    // If it's FAU-Einrichtungen, try to get settings from its theme_mods
                    if ($theme_name === 'FAU-Einrichtungen') {
                        // Check both normalized name and original name with suffix
                        $einrichtungen_options = array('theme_mods_FAU-Einrichtungen');
                        if (!empty($original_previous_stylesheet_for_inference) && $original_previous_stylesheet_for_inference !== $normalized_previous_stylesheet && stripos($original_previous_stylesheet_for_inference, 'FAU-Einrichtungen') === 0) {
                            // Also check with suffix if it exists and matches FAU-Einrichtungen
                            $einrichtungen_options[] = 'theme_mods_' . $original_previous_stylesheet_for_inference;
                        }
                        
                        $einrichtungen_mods = array();
                        foreach ($einrichtungen_options as $option_name) {
                            $check_mods = get_option($option_name, array());
                            if (!empty($check_mods) && is_array($check_mods)) {
                                $einrichtungen_mods = $check_mods;
                                break;
                            } elseif (get_option($option_name) !== false) {
                                // Option exists but might be empty or unserialized
                                $raw_option = get_option($option_name);
                                if (!empty($raw_option)) {
                                    $check_mods = maybe_unserialize($raw_option);
                                    if (is_array($check_mods) && !empty($check_mods)) {
                                        $einrichtungen_mods = $check_mods;
                                        break;
                                    }
                                }
                            }
                        }
                        
                        if (!empty($einrichtungen_mods) && is_array($einrichtungen_mods)) {
                            // Check website_type
                            if (isset($einrichtungen_mods['website_type'])) {
                                $website_type_mapping = array(
                                    0 => 'faculty',
                                    1 => 'chair',
                                    2 => 'other',
                                    3 => 'cooperation',
                                    -1 => 'fau',
                                );
                                $old_website_type = $einrichtungen_mods['website_type'];
                                if (isset($website_type_mapping[$old_website_type])) {
                                    $inferred_website_type = $website_type_mapping[$old_website_type];
                                }
                            }
                            
                            // Check faculty setting if website_type is faculty
                            if ($inferred_website_type === 'faculty' || (isset($einrichtungen_mods['website_type']) && $einrichtungen_mods['website_type'] == 0)) {
                                $old_faculty_keys = array('website_usefaculty', 'faculty', 'faue_faculty', 'fau_faculty', 'orga_faculty');
                                foreach ($old_faculty_keys as $key) {
                                    if (isset($einrichtungen_mods[$key]) && !empty($einrichtungen_mods[$key])) {
                                        $old_faculty = sanitize_text_field($einrichtungen_mods[$key]);
                                        if (in_array($old_faculty, array('phil', 'nat', 'med', 'rw', 'tf'))) {
                                            $inferred_faculty = $old_faculty;
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                        
                        // Default to phil if FAU-Einrichtungen and no faculty found
                        if (empty($inferred_faculty)) {
                            $inferred_faculty = 'phil';
                        }
                    } else {
                        // It's a faculty-specific theme, map directly
                        $inferred_faculty = fau_elemental_map_theme_to_faculty($theme_name);
                        // Faculty-specific themes should have website_type = 'faculty'
                        $inferred_website_type = 'faculty';
                    }
                    break;
                }
            }
            
            // If no exact match, try to extract faculty from theme name
            if (empty($inferred_theme_name)) {
                if (stripos($inference_theme_name, 'techfak') !== false || stripos($inference_theme_name, 'tech') !== false) {
                    $inferred_theme_name = 'FAU-Techfak';
                    $inferred_faculty = 'tf';
                    $inferred_website_type = 'faculty';
                } elseif (stripos($inference_theme_name, 'rwfak') !== false || stripos($inference_theme_name, 'rw') !== false) {
                    $inferred_theme_name = 'FAU-RWFak';
                    $inferred_faculty = 'rw';
                    $inferred_website_type = 'faculty';
                } elseif (stripos($inference_theme_name, 'medfak') !== false || stripos($inference_theme_name, 'med') !== false) {
                    $inferred_theme_name = 'FAU-Medfak';
                    $inferred_faculty = 'med';
                    $inferred_website_type = 'faculty';
                } elseif (stripos($inference_theme_name, 'natfak') !== false || stripos($inference_theme_name, 'nat') !== false) {
                    $inferred_theme_name = 'FAU-Natfak';
                    $inferred_faculty = 'nat';
                    $inferred_website_type = 'faculty';
                } elseif (stripos($inference_theme_name, 'philfak') !== false || stripos($inference_theme_name, 'phil') !== false) {
                    $inferred_theme_name = 'FAU-Philfak';
                    $inferred_faculty = 'phil';
                    $inferred_website_type = 'faculty';
                }
            }
        }
        
        // Set defaults first
        fau_elemental_set_default_settings();
        
        // Override faculty if we inferred it
        if (!empty($inferred_faculty)) {
            set_theme_mod('faue_faculty', $inferred_faculty);
        }
        
        // Override website_type if we inferred it
        if (!empty($inferred_website_type)) {
            set_theme_mod('faue_website_type', $inferred_website_type);
        }
        
        update_option('fau_elemental_all_settings_migrated', true);
        return array(
            'migrated' => false, 
            'reason' => 'no_previous_config', 
            'faculty_inferred' => !empty($inferred_faculty),
            'faculty' => $inferred_faculty ? true : false,
            'website_type_inferred' => !empty($inferred_website_type)
        );
    }
    
    $old_theme_mods = $previous_config['theme_mods'];
    $theme_name = $previous_config['theme_name'];
    $migration_results = array(
        'address' => false,
        'website_type' => false,
        'faculty' => false,
        'theme_name' => $theme_name
    );
    
    // Get normalized names for FAU-Einrichtungen checking
    $normalized_previous_stylesheet_for_check = $normalized_previous_stylesheet;
    $original_previous_stylesheet_for_check = $original_previous_stylesheet;
    
    // Migrate address information
    $migration_results['address'] = fau_elemental_migrate_address_data($old_theme_mods);
    
    // Migrate website type
    $migration_results['website_type'] = fau_elemental_migrate_website_type_data($old_theme_mods);
    
    // Set faculty based on theme name
    $faculty_code = fau_elemental_map_theme_to_faculty($theme_name);
    
    // Special handling for FAU-Einrichtungen: check its settings to determine website type and faculty
    if ($theme_name === 'FAU-Einrichtungen') {
        // If old_theme_mods is empty or website_type wasn't migrated, check FAU-Einrichtungen theme_mods directly
        if (empty($old_theme_mods) || !$migration_results['website_type']) {
            // Check both normalized name and original name with suffix
            $einrichtungen_options = array('theme_mods_FAU-Einrichtungen');
            if (!empty($original_previous_stylesheet_for_check) && $original_previous_stylesheet_for_check !== $normalized_previous_stylesheet_for_check && stripos($original_previous_stylesheet_for_check, 'FAU-Einrichtungen') === 0) {
                // Also check with suffix if it exists and matches FAU-Einrichtungen
                $einrichtungen_options[] = 'theme_mods_' . $original_previous_stylesheet_for_check;
            }
            
            $einrichtungen_mods = array();
            foreach ($einrichtungen_options as $option_name) {
                $check_mods = get_option($option_name, array());
                if (!empty($check_mods) && is_array($check_mods)) {
                    $einrichtungen_mods = $check_mods;
                    break;
                } elseif (get_option($option_name) !== false) {
                    // Option exists but might be empty or unserialized
                    $raw_option = get_option($option_name);
                    if (!empty($raw_option)) {
                        $check_mods = maybe_unserialize($raw_option);
                        if (is_array($check_mods) && !empty($check_mods)) {
                            $einrichtungen_mods = $check_mods;
                            break;
                        }
                    }
                }
            }
            
            // Use einrichtungen_mods if found, otherwise use old_theme_mods
            if (!empty($einrichtungen_mods)) {
                $old_theme_mods = $einrichtungen_mods;
            }
        }
        
        // Check if website_type was migrated, if not, check the old mods (now potentially from einrichtungen_mods)
        if (!$migration_results['website_type']) {
            $old_website_type = isset($old_theme_mods['website_type']) ? $old_theme_mods['website_type'] : null;
            $website_type_mapping = array(
                0 => 'faculty',
                1 => 'chair',
                2 => 'other',
                3 => 'cooperation',
                -1 => 'fau',
            );
            if ($old_website_type !== null && isset($website_type_mapping[$old_website_type])) {
                $new_website_type = $website_type_mapping[$old_website_type];
                set_theme_mod('faue_website_type', $new_website_type);
                $migration_results['website_type'] = true;
            }
        }
        
        // If website_type is faculty, check for faculty setting
        $current_website_type = get_theme_mod('faue_website_type', 'faculty');
        if ($current_website_type === 'faculty' || (isset($old_theme_mods['website_type']) && $old_theme_mods['website_type'] == 0)) {
            // Check for faculty settings in old theme mods
            $old_faculty_keys = array('website_usefaculty', 'faculty', 'faue_faculty', 'fau_faculty', 'orga_faculty');
            foreach ($old_faculty_keys as $key) {
                if (isset($old_theme_mods[$key]) && !empty($old_theme_mods[$key])) {
                    $old_faculty = sanitize_text_field($old_theme_mods[$key]);
                    if (in_array($old_faculty, array('phil', 'nat', 'med', 'rw', 'tf'))) {
                        $faculty_code = $old_faculty;
                        break;
                    }
                }
            }
        }
    } else {
        // For faculty-specific themes, ensure website_type is set to 'faculty'
        if (!$migration_results['website_type']) {
            set_theme_mod('faue_website_type', 'faculty');
            $migration_results['website_type'] = true;
        }
    }
    
    // Double-check: if we have previous theme name, verify it matches
    // This ensures we get the right faculty even if multiple configs exist
    if (!empty($previous_theme_name) && !empty($previous_theme_stylesheet)) {
        // Check if previous theme exactly matches the detected theme name
        $exact_match = (strcasecmp($previous_theme_stylesheet, $theme_name) === 0);
        
        if (!$exact_match) {
            // Previous theme doesn't match detected config, check if we should use previous theme instead
            $theme_names = fau_elemental_get_previous_theme_names();
            foreach ($theme_names as $check_theme_name) {
                if (strcasecmp($previous_theme_stylesheet, $check_theme_name) === 0) {
                    // Found exact match, use this theme's faculty instead
                    $faculty_code = fau_elemental_map_theme_to_faculty($check_theme_name);
                    $theme_name = $check_theme_name; // Update theme name for consistency
                    break;
                }
            }
        }
    }
    
    // Also check if old theme mods had a faculty setting we can use
    if (!empty($old_theme_mods)) {
        // Check for old faculty settings in various formats
        $old_faculty_keys = array(
            'website_usefaculty',
            'faculty',
            'faue_faculty',
            'fau_faculty',
            'orga_faculty'
        );
        
        foreach ($old_faculty_keys as $key) {
            if (isset($old_theme_mods[$key]) && !empty($old_theme_mods[$key])) {
                $old_faculty = sanitize_text_field($old_theme_mods[$key]);
                // Map old faculty codes to new ones if needed
                if (in_array($old_faculty, array('phil', 'nat', 'med', 'rw', 'tf'))) {
                    $faculty_code = $old_faculty;
                    break;
                }
            }
        }
    }
    
    // Always set the faculty - this ensures it's never left as default 'phil' incorrectly
    set_theme_mod('faue_faculty', $faculty_code);
    $migration_results['faculty'] = true;
    
    // Mark as migrated
    update_option('fau_elemental_all_settings_migrated', true);
    
    // Set success transient
    set_transient('fau_elemental_migration_success', $migration_results, 60);
    
    return $migration_results;
}

/**
 * Migrate address data from old theme mods
 * 
 * @param array $old_theme_mods Old theme configuration
 * @return bool True if migration was performed
 */
function fau_elemental_migrate_address_data($old_theme_mods) {
    $migration_performed = false;
    
    // Extract address fields from old theme mods
    $old_display_address = isset($old_theme_mods['advanced_footer_display_address']) ? $old_theme_mods['advanced_footer_display_address'] : false;
    $old_address_name = isset($old_theme_mods['contact_address_name']) ? $old_theme_mods['contact_address_name'] : '';
    $old_address_name2 = isset($old_theme_mods['contact_address_name2']) ? $old_theme_mods['contact_address_name2'] : '';
    $old_address_street = isset($old_theme_mods['contact_address_street']) ? $old_theme_mods['contact_address_street'] : '';
    $old_address_plz = isset($old_theme_mods['contact_address_plz']) ? $old_theme_mods['contact_address_plz'] : '';
    $old_address_ort = isset($old_theme_mods['contact_address_ort']) ? $old_theme_mods['contact_address_ort'] : '';
    $old_address_country = isset($old_theme_mods['contact_address_country']) ? $old_theme_mods['contact_address_country'] : '';
    
    // Migrate display address setting
    if ($old_display_address !== false) {
        set_theme_mod('display_footer_address', $old_display_address);
        $migration_performed = true;
    }
    
    // Migrate address fields if they exist
    if (!empty($old_address_name)) {
        set_theme_mod('instance_university_name', $old_address_name);
        $migration_performed = true;
    }
    
    if (!empty($old_address_name2)) {
        set_theme_mod('instance_faculty_name', $old_address_name2);
        $migration_performed = true;
    }
    
    if (!empty($old_address_street)) {
        set_theme_mod('instance_street', $old_address_street);
        $migration_performed = true;
    }
    
    if (!empty($old_address_plz) || !empty($old_address_ort)) {
        $city_combined = trim($old_address_plz . ' ' . $old_address_ort);
        if (!empty($city_combined)) {
            set_theme_mod('instance_city', $city_combined);
            $migration_performed = true;
        }
    }
    
    if (!empty($old_address_country)) {
        set_theme_mod('instance_country', $old_address_country);
        $migration_performed = true;
    }
    
    // Also preserve the old field names for backward compatibility (only if new fields are empty)
    if (!empty($old_address_name) && empty(get_theme_mod('contact_address_name'))) {
        set_theme_mod('contact_address_name', $old_address_name);
    }
    if (!empty($old_address_name2) && empty(get_theme_mod('contact_address_name2'))) {
        set_theme_mod('contact_address_name2', $old_address_name2);
    }
    if (!empty($old_address_street) && empty(get_theme_mod('contact_address_street'))) {
        set_theme_mod('contact_address_street', $old_address_street);
    }
    if (!empty($old_address_plz) && empty(get_theme_mod('contact_address_plz'))) {
        set_theme_mod('contact_address_plz', $old_address_plz);
    }
    if (!empty($old_address_ort) && empty(get_theme_mod('contact_address_ort'))) {
        set_theme_mod('contact_address_ort', $old_address_ort);
    }
    if (!empty($old_address_country) && empty(get_theme_mod('contact_address_country'))) {
        set_theme_mod('contact_address_country', $old_address_country);
    }
    
    // Set default university name only if no data was migrated
    if (empty($old_address_name) && empty(get_theme_mod('instance_university_name'))) {
        set_theme_mod('instance_university_name', 'Friedrich-Alexander-Universität Erlangen-Nürnberg');
    }
    
    return $migration_performed;
}

/**
 * Migrate website type data from old theme mods
 * 
 * @param array $old_theme_mods Old theme configuration
 * @return bool True if migration was performed
 */
function fau_elemental_migrate_website_type_data($old_theme_mods) {
    $migration_performed = false;
    
    // Extract website type from old theme mods
    $old_website_type = isset($old_theme_mods['website_type']) ? $old_theme_mods['website_type'] : null;
    
    // Map old website type values to new ones
    $website_type_mapping = array(
        0 => 'faculty',      // Fakultätsportal
        1 => 'chair',        // Department, Lehrstuhl, Einrichtung
        2 => 'other',        // Zentrale Einrichtung
        3 => 'cooperation',  // Website für uniübergreifende Kooperationen mit Externen
        -1 => 'fau',         // Zentrales FAU-Portal www.fau.de
    );
    
    // Migrate website type if it exists and is valid
    if ($old_website_type !== null && isset($website_type_mapping[$old_website_type])) {
        $new_website_type = $website_type_mapping[$old_website_type];
        set_theme_mod('faue_website_type', $new_website_type);
        $migration_performed = true;
    }
    
    return $migration_performed;
}

/**
 * Set default settings when no previous configuration is found
 */
function fau_elemental_set_default_settings() {
    // Set default faculty
    set_theme_mod('faue_faculty', 'phil');
    
    // Set default website type
    set_theme_mod('faue_website_type', 'faculty');
    
    // Set default university name
    set_theme_mod('instance_university_name', 'Friedrich-Alexander-Universität Erlangen-Nürnberg');
}

/**
 * Capture the current theme name before switching
 * This ensures we have the previous theme name even if WordPress doesn't store it properly
 */
function fau_elemental_capture_current_theme_before_switch($new_name, $new_theme) {
    $current_theme = wp_get_theme();
    if ($current_theme && $current_theme->exists()) {
        $stylesheet = $current_theme->get_stylesheet();
        $name = $current_theme->get('Name');
        
        // Store both stylesheet and name for later use
        set_transient('fau_elemental_captured_previous_theme', array(
            'stylesheet' => $stylesheet,
            'name' => $name
        ), 3600);
        
        // Also store in option for more permanent storage
        update_option('fau_elemental_captured_previous_theme', array(
            'stylesheet' => $stylesheet,
            'name' => $name,
            'timestamp' => time()
        ));
    }
}
add_action('switch_theme', 'fau_elemental_capture_current_theme_before_switch', 10, 2);

// Run the comprehensive migration when switching themes
add_action('after_switch_theme', 'fau_elemental_migrate_all_settings');

// Also run migration when Customizer is opened if not already migrated
// This ensures migration happens even when switching via Customizer preview
add_action('customize_register', function($wp_customize) {
    // Only run if not already migrated
    if (!get_option('fau_elemental_all_settings_migrated')) {
        // Check if previous config exists
        $previous_config = fau_elemental_detect_previous_theme_config();
        if ($previous_config) {
            // Run migration silently in background
            fau_elemental_migrate_all_settings(false);
        }
    }
}, 1);

// Legacy functions for backward compatibility (deprecated)
function fau_elemental_migrate_address_information($force = false) {
    $result = fau_elemental_migrate_all_settings($force);
    return $result['address'];
}

function fau_elemental_migrate_website_type($force = false) {
    $result = fau_elemental_migrate_all_settings($force);
    return $result['website_type'];
}

/**
 * Add country field to contact information for backward compatibility
 */
function fau_elemental_add_country_field($wp_customize) {
    // Only add if we're in the faculty contact section
    if ($wp_customize->get_section('footer_kontaktinformation')) {
        $wp_customize->add_setting('instance_country', [
            'default' => '',
            'transport' => 'refresh',
            'sanitize_callback' => 'sanitize_text_field',
        ]);
        
        $wp_customize->add_control('instance_country', [
            'label' => __('Land', 'fau-elemental'),
            'section' => 'footer_kontaktinformation',
            'type' => 'text',
            'priority' => 65,
        ]);
    }
}
add_action('customize_register', 'fau_elemental_add_country_field', 30);

/**
 * Modify the footer template to include the country field if it exists
 */
function fau_elemental_footer_address_country() {
    $country = get_theme_mod('instance_country');
    if (!empty($country)) {
        echo '<span itemprop="addressCountry">' . esc_html($country) . '</span>';
    }
}
add_action('fau_elemental_after_footer_address', 'fau_elemental_footer_address_country');

/**
 * Show success notice after migration
 */
function fau_elemental_migration_success_notice() {
    if (get_transient('fau_elemental_migration_success')) {
        $results = get_transient('fau_elemental_migration_success');
        delete_transient('fau_elemental_migration_success');
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php 
                printf(
                    /* translators: %s: name of the previous theme */
                    __('Theme settings were successfully migrated from %s!', 'fau-elemental'),
                    esc_html($results['theme_name'])
                ); 
            ?></p>
            <?php if ($results['address'] || $results['website_type'] || $results['faculty']): ?>
            <ul>
                <?php if ($results['address']): ?>
                <li><?php _e('Address information', 'fau-elemental'); ?></li>
                <?php endif; ?>
                <?php if ($results['website_type']): ?>
                <li><?php _e('Website type settings', 'fau-elemental'); ?></li>
                <?php endif; ?>
                <?php if ($results['faculty']): ?>
                <li><?php _e('Faculty configuration', 'fau-elemental'); ?></li>
                <?php endif; ?>
            </ul>
            <?php endif; ?>
        </div>
        <?php
    } elseif (get_transient('fau_elemental_migration_none')) {
        $results = get_transient('fau_elemental_migration_none');
        delete_transient('fau_elemental_migration_none');
        ?>
        <div class="notice notice-warning is-dismissible">
            <p><?php 
                if ($results['reason'] === 'no_previous_config') {
                    _e('No previous theme settings were found to migrate. Default settings have been applied.', 'fau-elemental');
                } else {
                    _e('No settings from previous themes were found to migrate.', 'fau-elemental');
                }
            ?></p>
        </div>
        <?php
    }
    
    // Legacy notices for backward compatibility
    if (get_transient('fau_elemental_address_migrated_success')) {
        delete_transient('fau_elemental_address_migrated_success');
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Address settings were successfully migrated from FAU-Einrichtungen theme!', 'fau-elemental'); ?></p>
        </div>
        <?php
    } elseif (get_transient('fau_elemental_address_migrated_none')) {
        delete_transient('fau_elemental_address_migrated_none');
        ?>
        <div class="notice notice-warning is-dismissible">
            <p><?php _e('No address settings from FAU-Einrichtungen theme were found to migrate.', 'fau-elemental'); ?></p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'fau_elemental_migration_success_notice');


/**
 * Sanitize checkbox value.
 *
 * @param bool $checked Whether the checkbox is checked.
 * @return bool Whether the checkbox is checked.
 */
function faue_sanitize_checkbox($checked) {
    return (bool) $checked;
}

/**
 * Check if post meta should be displayed.
 *
 * @return bool
 */
function faue_show_post_meta() {
    return get_theme_mod('faue_show_post_meta', true);
}

/**
 * Check if post meta should use dark theme.
 *
 * @return bool
 */
function faue_post_meta_dark_theme() {
    return get_theme_mod('faue_post_meta_dark_theme', false);
}

// TODO
/**
 * Hero Customizer Settings
 */
function fau_hero_customizer_settings($wp_customize) {
    // Add Hero panel
    $wp_customize->add_panel('fau_hero_panel', [
        'title' => __('Hero Settings', 'fau-elemental'),
        'priority' => 130,
    ]);

    // Mobile Display Section
    $wp_customize->add_section('hero_mobile_display', [
        'title' => __('Mobile Display Options', 'fau-elemental'),
        'panel' => 'fau_hero_panel',
    ]);

    // Show/Hide Text and Link on Mobile
    $wp_customize->add_setting('hero_show_text_mobile', [
        'default' => true,
        'sanitize_callback' => 'sanitize_checkbox'
    ]);
    $wp_customize->add_control('hero_show_text_mobile', [
        'label' => __('Show Description Text and Link on Mobile', 'fau-elemental'),
        'section' => 'hero_mobile_display',
        'type' => 'checkbox',
    ]);
}
add_action('customize_register', 'fau_hero_customizer_settings');

/**
 * Add hero styles
 */
function fau_hero_styles() {
    $show_text = get_theme_mod('hero_show_text_mobile', true);

    $css = '';
    if (!$show_text) {
        $css = '@media screen and (max-width: 991px) { .hero-mobile-optional { display: none; } }';
        $css .= '@media screen and (max-width: 393px) { .wp-block-group.hero-content.is-layout-flow { margin-bottom: 8.125rem; } }';
    }

    if ($css) {
        wp_add_inline_style('wp-block-library', $css);
    }
}
add_action('wp_enqueue_scripts', 'fau_hero_styles', 999);
