<?php
/**
 * Menu Registration Functions
 *
 * @package FAU-Elemental
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register all navigation menus for the theme.
 *
 * @since 1.0.0
 * @return void
 */
function fau_elemental_register_all_menus() {
	// Register theme navigation menus.
	register_nav_menus(
		array(
			'header_primary_menu' => esc_html__('Header Primary Menu', 'fau-elemental'),
			'header_direct_links_menu' => esc_html__('Header Direct Links Menu', 'fau-elemental'),
			'header_menu_links' => esc_html__('Header Menu Links', 'fau-elemental'),
			'top_header_nav_services' => esc_html__('Top Header Nav Services', 'fau-elemental'),
			'top_header_nav_structure' => esc_html__('Top Header Nav Structure', 'fau-elemental'),
			'search_options_menu' => esc_html__('Search Options Menu', 'fau-elemental'),
			'footer-menu'            => esc_html__( 'Footer Menu', 'fau-elemental' ),
			'footer-lists-menu'      => esc_html__( 'Footer Lists Menu', 'fau-elemental' ),
			'footer-important-links' => esc_html__( 'Footer Important Links', 'fau-elemental' ),
		)
	);
}
add_action( 'after_setup_theme', 'fau_elemental_register_all_menus' );

/**
 * Add support for selective refresh for nav menus in the Customizer.
 *
 * @since 1.0.0
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 * @return void
 */
function fau_elemental_customize_register_nav_menus( $wp_customize ) {
	// Bail early if selective refresh is not available.
	if ( ! isset( $wp_customize->selective_refresh ) ) {
		return;
	}

	// Primary Menu.
	$wp_customize->selective_refresh->add_partial(
		'nav_menu_locations[menu-1]',
		array(
			'selector'            => '#site-navigation',
			'render_callback'     => function() {
				wp_nav_menu(
					array(
						'theme_location' => 'menu-1',
						'menu_id'        => 'primary-menu',
					)
				);
			},
			'container_inclusive' => false,
		)
	);

	// Footer Menu - Target both footer-main and footer-instance containers.
	$wp_customize->selective_refresh->add_partial(
		'nav_menu_locations[footer-menu]',
		array(
			'selector'            => '.footer-bottom-top .footer-right, .footer-content--instance .footer-meta-nav',
			'render_callback'     => function() {
				// Check which context we're in
				$in_main_footer = strpos($_SERVER['REQUEST_URI'], 'customize_changeset_uuid') !== false;
				
				// For main footer
				if ($in_main_footer || !isset($GLOBALS['footer_instance_context'])) {
					echo '<div class="footer-right">';
					echo '<nav class="footer-links">';
					wp_nav_menu(
						array(
							'theme_location' => 'footer-menu',
							'menu_class'     => 'footer-meta-menu',
							'container'      => false,
							'depth'          => 1,
							'fallback_cb'    => false,
						)
					);
					echo '</nav>';
					echo '</div>';
				} else {
					// For footer instance
					echo '<nav class="footer-meta-nav">';
					wp_nav_menu(
						array(
							'theme_location' => 'footer-menu',
							'menu_class'     => 'footer-menu-list',
							'container'      => false,
							'depth'          => 1,
							'fallback_cb'    => false,
						)
					);
					echo '</nav>';
				}
			},
			'container_inclusive' => true,
		)
	);

	// Footer Lists Menu - Include section container for consistent pencil display.
	$wp_customize->selective_refresh->add_partial(
		'nav_menu_locations[footer-lists-menu]',
		array(
			'selector'            => '.footer-lists',
			'render_callback'     => function() {
				echo '<section class="footer-lists">';
				wp_nav_menu(
					array(
						'theme_location'  => 'footer-lists-menu',
						'menu_class'      => 'footer-lists-menu columns-layout',
						'container'       => 'nav',
						'container_class' => 'footer-lists-container',
						'depth'           => 2,
						'fallback_cb'     => false,
					)
				);
				echo '</section>';
			},
			'container_inclusive' => true,
		)
	);

	// Note: Footer menu in instance uses same theme location as main footer,
	// so it shares the same partial. WordPress will show pencils for both automatically.

	// Footer Important Links - Render nav without h3 (h3 is now outside nav).
	$wp_customize->selective_refresh->add_partial(
		'nav_menu_locations[footer-important-links]',
		array(
			'selector'            => '.footer-important-links',
			'render_callback'     => function() {
				echo '<nav class="footer-important-links">';
				wp_nav_menu(
					array(
						'theme_location' => 'footer-important-links',
						'menu_class'     => 'important-links-list',
						'container'      => false,
						'fallback_cb'    => false,
					)
				);
				echo '</nav>';
			},
			'container_inclusive' => true,
		)
	);
}
add_action( 'customize_register', 'fau_elemental_customize_register_nav_menus', 11 ); 
