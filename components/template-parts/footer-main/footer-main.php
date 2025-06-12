<div class="footer-content footer-content--main <?php echo get_theme_mod('footer_dark_style', false) ? 'is-style-dark' : ''; ?>">
    <div class="footer-main">
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

        <div class="footer-lists">
            <?php
            // Use a single menu that will be displayed in up to 4 columns
            // Sub-menu items will appear under their parent items
            wp_nav_menu(array(
                'theme_location' => 'footer-lists-menu',
                'menu_class' => 'footer-lists-menu columns-layout',
                'container' => 'div',
                'container_class' => 'footer-lists-container',
                'depth' => 2, // Only show top level and one sublevel
                'fallback_cb' => function() {
                    echo '<div class="footer-lists-container">';
                    echo '<p>' . __('Please assign a menu to the "Footer Lists Menu" location', 'fau-elemental') . '</p>';
                    echo '<p>' . __('Create a menu with top-level items that will display as column headers', 'fau-elemental') . '</p>';
                    echo '</div>';
                }
            ));
            ?>
            <script>
            // Script to handle column layout for the footer menu
            document.addEventListener('DOMContentLoaded', function() {
                const menu = document.querySelector('.footer-lists-menu');
                if (menu) {
                    const topLevelItems = menu.querySelectorAll('li.menu-item-has-children, li.page_item_has_children');
                    
                    // Add appropriate classes based on number of items
                    if (topLevelItems.length > 0) {
                        // Add class based on number of top-level items (up to 4 columns)
                        const columnCount = Math.min(topLevelItems.length, 4);
                        menu.classList.add(`columns-${columnCount}`);
                        
                        // If more than 4 items, add a class to handle wrapping
                        if (topLevelItems.length > 4) {
                            menu.classList.add('multi-row');
                        }
                        
                        // Add column class to each top level item
                        topLevelItems.forEach(item => {
                            item.classList.add('column-item');
                        });
                    }
                }
            });
            </script>
        </div>
    </div>
</div>

<div class="footer-bottom">
    <div class="footer-bottom-wrapper">
      
        <div class="footer-bottom-top">
            <div class="footer-left">
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
            </div>
            
            <div class="footer-right">
                <div class="footer-links">
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'footer-menu',
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
                <!-- Image credits handled by fau-copyright-info block -->
                <?php echo do_blocks('<!-- wp:fau-elemental/fau-copyright-info /-->'); ?>
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
    </div>
</div>
