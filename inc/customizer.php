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
    $website_type = get_theme_mod('faue_website_type', 'faculty');
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
            return get_theme_mod('faue_website_type', 'faculty') === 'fau';
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
            return get_theme_mod('faue_website_type', 'faculty') === 'fau';
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
            return get_theme_mod('faue_website_type', 'faculty') === 'fau';
        }
    ]);
    
    // ======= 2. BESCHREIBUNG SECTION (Faculty Information) =======
    $wp_customize->add_section('footer_beschreibung', [
        'title' => __('Description', 'fau-elemental'),
        'panel' => 'fau_footer_panel',
        'priority' => 20,
        'description' => __('Configure faculty information', 'fau-elemental'),
        'active_callback' => function() {
            return get_theme_mod('faue_website_type', 'faculty') !== 'fau';
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
            return get_theme_mod('faue_website_type', 'faculty') !== 'fau';
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
    
    // ======= 4. ZIELGRUPPEN-LINKS SECTION =======
    $website_type = get_theme_mod('faue_website_type', 'faculty');
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
            return get_theme_mod('faue_website_type', 'faculty') === 'cooperation';
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
                return get_theme_mod('faue_website_type', 'faculty') === 'fau';
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
                return get_theme_mod('faue_website_type', 'faculty') === 'fau';
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
                return get_theme_mod('faue_website_type', 'faculty') === 'fau';
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
            'sanitize_callback' => 'esc_url_raw',
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
 * Migrate address information from old theme (FAU-Einrichtungen) to new theme (FAU-Elemental)
 * This ensures backward compatibility for footer contact information
 * 
 * @param bool $force Whether to force migration even if already done
 * @return bool True if migration was performed, false otherwise
 */
function fau_elemental_migrate_address_information($force = false) {
    // Check if we've already migrated
    if (!$force && get_option('fau_elemental_address_migrated')) {
        return false;
    }
    
    // Get the old theme's stored data from theme_mods_FAU-Einrichtungen-master
    $old_theme_mods = get_option('theme_mods_FAU-Einrichtungen-master', array());
    
    // Extract address fields from old theme mods
    $old_display_address = isset($old_theme_mods['advanced_footer_display_address']) ? $old_theme_mods['advanced_footer_display_address'] : false;
    $old_address_name = isset($old_theme_mods['contact_address_name']) ? $old_theme_mods['contact_address_name'] : '';
    $old_address_name2 = isset($old_theme_mods['contact_address_name2']) ? $old_theme_mods['contact_address_name2'] : '';
    $old_address_street = isset($old_theme_mods['contact_address_street']) ? $old_theme_mods['contact_address_street'] : '';
    $old_address_plz = isset($old_theme_mods['contact_address_plz']) ? $old_theme_mods['contact_address_plz'] : '';
    $old_address_ort = isset($old_theme_mods['contact_address_ort']) ? $old_theme_mods['contact_address_ort'] : '';
    $old_address_country = isset($old_theme_mods['contact_address_country']) ? $old_theme_mods['contact_address_country'] : '';
    
    $migration_performed = false;
    
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
    
    // Mark as migrated
    update_option('fau_elemental_address_migrated', true);
    
    if ($migration_performed) {
        set_transient('fau_elemental_address_migrated_success', true, 30);
    } else {
        set_transient('fau_elemental_address_migrated_none', true, 30);
    }
    
    return $migration_performed;
}

// Run the migration when switching themes only (not on every customizer save)
add_action('after_switch_theme', 'fau_elemental_migrate_address_information');

/**
 * Migrate website type from old theme (FAU-Einrichtungen) to new theme (FAU-Elemental)
 * This ensures backward compatibility for website type settings
 * 
 * @param bool $force Whether to force migration even if already done
 * @return bool True if migration was performed, false otherwise
 */
function fau_elemental_migrate_website_type($force = false) {
    // Check if we've already migrated
    if (!$force && get_option('fau_elemental_website_type_migrated')) {
        return false;
    }
    
    // Get the old theme's stored data from theme_mods_FAU-Einrichtungen-master
    $old_theme_mods = get_option('theme_mods_FAU-Einrichtungen-master', array());
    
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
    
    $migration_performed = false;
    
    // Migrate website type if it exists and is valid
    if ($old_website_type !== null && isset($website_type_mapping[$old_website_type])) {
        $new_website_type = $website_type_mapping[$old_website_type];
        set_theme_mod('faue_website_type', $new_website_type);
        $migration_performed = true;
    }
    
    // Mark as migrated
    update_option('fau_elemental_website_type_migrated', true);
    
    if ($migration_performed) {
        set_transient('fau_elemental_website_type_migrated_success', true, 30);
    } else {
        set_transient('fau_elemental_website_type_migrated_none', true, 30);
    }
    
    return $migration_performed;
}

// Run the website type migration when switching themes
add_action('after_switch_theme', 'fau_elemental_migrate_website_type');

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
 * Add an admin notice that shows when old theme settings are detected but not yet migrated
 */
function fau_elemental_migration_admin_notice() {
    // Only show to admins
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Check if we've already migrated
    $migration_flag = get_option('fau_elemental_address_migrated');
    
    if ($migration_flag) {
        return;
    }
    
    // Check if there are any old theme settings
    $old_theme_mods = get_option('theme_mods_FAU-Einrichtungen-master', array());
    
    if (!empty($old_theme_mods)) {
        $has_address_data = isset($old_theme_mods['contact_address_name']) || 
                           isset($old_theme_mods['contact_address_street']) || 
                           isset($old_theme_mods['contact_address_plz']);
        
        if ($has_address_data) {
            ?>
            <div class="notice notice-info is-dismissible">
                <p><?php _e('FAU-Elemental detected address settings from the FAU-Einrichtungen theme that can be migrated.', 'fau-elemental'); ?></p>
                <p>
                    <a href="<?php echo esc_url(admin_url('themes.php?fau-migrate-address=1&_wpnonce=' . wp_create_nonce('fau-migrate-address'))); ?>" class="button button-primary">
                        <?php _e('Migrate Address Settings', 'fau-elemental'); ?>
                    </a>
                </p>
            </div>
            <?php
        }
    }
}
add_action('admin_notices', 'fau_elemental_migration_admin_notice');

/**
 * Process the migration request when the admin clicks the migrate button
 */
function fau_elemental_process_migration_request() {
    // Check if migration request and nonce are set
    if (isset($_GET['fau-migrate-address']) && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'fau-migrate-address')) {
        // Force migration even if already done
        $migrated = fau_elemental_migrate_address_information(true);
        
        // Set transient for admin notice
        if ($migrated) {
            set_transient('fau_elemental_address_migrated_success', 1, 60);
        } else {
            set_transient('fau_elemental_address_migrated_none', 1, 60);
        }
        
        // Redirect back to themes page
        wp_redirect(admin_url('themes.php'));
        exit;
    }
}
add_action('admin_init', 'fau_elemental_process_migration_request');

/**
 * Show success notice after migration
 */
function fau_elemental_migration_success_notice() {
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
