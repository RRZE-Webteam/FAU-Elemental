<?php
/**
 * Template part for displaying the FAU top navigation
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    return;
}

// Check for Services and Structure menus using unified system
$has_services = fau_elemental_has_services_menu();
$has_structure = fau_elemental_has_structure_menu();
$website_type = get_theme_mod('faue_website_type', 'fau');
?>

<nav class="fau-navigation" aria-label="<?php esc_attr_e('FAU Navigation', 'fau-elemental'); ?>">
    <?php if ($website_type !== 'fau'): ?>
    <a href="<?php echo esc_url(set_url_scheme('https://www.fau.de')); ?>" class="fau-navigation__fau-link">
        <span class="fau-navigation__back-icon"></span>
        <?php esc_html_e('FAU.de', 'fau-elemental'); ?>
    </a>
    <?php endif; ?>
    <div class="fau-nav-modals">
        <?php if ($has_services): ?>
            <button type="button" class="fau-navigation__button menu-modal__open-btn"
                data-modal-target="services-modal"
                aria-label="<?php esc_attr_e('Open Services menu', 'fau-elemental'); ?>"
                aria-expanded="false">
                <?php esc_html_e('Services', 'fau-elemental'); ?>
                <span class="fau-navigation__services-icon"></span>
            </button>
        <?php endif; ?>
        <?php if ($has_structure): ?>
            <button type="button" class="fau-navigation__button menu-modal__open-btn"
                data-modal-target="structure-modal"
                aria-label="<?php esc_attr_e('Open Structure menu', 'fau-elemental'); ?>"
                aria-expanded="false">
                <?php esc_html_e('Structure', 'fau-elemental'); ?>
                <span class="fau-navigation__structure-icon"></span>
            </button>
        <?php endif; ?>
            <button type="button" class="fau-navigation__button menu-modal__open-btn"
                data-modal-target="search"
                aria-label="<?php esc_attr_e('Open Search', 'fau-elemental'); ?>"
                aria-expanded="false">
                <?php esc_html_e('Search', 'fau-elemental'); ?>
                <span class="fau-navigation__search-icon"></span>
            </button>
    </div>
    <?php
    // Check if the language switcher widget area has widgets
    if (is_active_sidebar('language-switcher')): ?>
        <div class="fau-language-switcher-wrapper">
            <?php dynamic_sidebar('language-switcher'); ?>
        </div>
    <?php endif; ?>
</nav> 