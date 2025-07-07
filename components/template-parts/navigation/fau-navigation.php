<?php
/**
 * Template part for displaying the FAU top navigation
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

// Check for Services and Structure menus using unified system
$has_services = fau_elemental_has_services_menu();
$has_structure = fau_elemental_has_structure_menu();
$website_type = get_theme_mod('faue_website_type', 'fau');
?>

<nav class="fau-navigation" role="navigation" aria-label="<?php esc_attr_e('FAU Navigation', 'fau-elemental'); ?>">
    <?php if ($website_type !== 'fau'): ?>
    <a href="https://www.fau.de" class="fau-navigation__fau-link">
        <span class="fau-navigation__back-icon"></span>
        FAU.de
    </a>
    <?php endif; ?>
    <div class="fau-nav-modals">
        <?php if ($has_services): ?>
            <button class="fau-navigation__button menu-modal__open-btn"
                data-modal-target="services-modal"
                aria-label="Services"
                aria-expanded="false">
                Services
                <span class="fau-navigation__services-icon"></span>
            </button>
        <?php endif; ?>
        <?php if ($has_structure): ?>
            <button class="fau-navigation__button menu-modal__open-btn"
                data-modal-target="structure-modal"
                aria-label="Structure"
                aria-expanded="false">
                Structure
                <span class="fau-navigation__structure-icon"></span>
            </button>
        <?php endif; ?>
            <button class="fau-navigation__button menu-modal__open-btn"
                data-modal-target="search"
                aria-label="<?php esc_attr_e('Search', 'fau-elemental'); ?>"
                aria-expanded="false">
                <?php esc_html_e('Search', 'fau-elemental'); ?>
                <span class="fau-navigation__search-icon"></span>
            </button>
    </div>
    <button class="language-switcher fau-navigation__button" aria-label="Language" aria-expanded="false">
        DE
        <span class="fau-navigation__language-switcher-icon"></span>
    </button>
</nav> 