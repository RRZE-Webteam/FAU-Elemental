<?php
/**
 * The main footer template part
 *
 * @package fau-elemental
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$is_main_site = is_main_site();
?>

<?php if ($is_main_site): ?>
    <div class="fau-claim">
        <h2><?php echo esc_html(get_theme_mod('fau_footer_claim_title', 'FAU - Wissen in Bewegung')); ?></h2>
        <p><?php echo esc_html(get_theme_mod('fau_footer_claim_text', 'Die FAU ist die innovativste Universität Deutschlands, europaweit auf dem zweiten Platz. Mit 40.000 Studierenden gehören wir zu den größten Hochschulen in Deutschland mit herausragender Lehre und exzellenter Forschung.')); ?></p>
    </div>

    <div class="target-groups">
        <?php
        $target_groups = array(
            'zur_fau' => array(
                'title' => __('Zur FAU', 'fau-elemental'),
                'text' => __('Geschichte, Besonderheiten Daten, Struktur u.v.m.', 'fau-elemental'),
            ),
            'forschung' => array(
                'title' => __('Forschung', 'fau-elemental'),
                'text' => __('Schwerpunkte, Leitbild, Reputation, Erfolge u.v.m.', 'fau-elemental'),
            ),
            'studierende' => array(
                'title' => __('Studierende', 'fau-elemental'),
                'text' => __('Schwerpunkte, Leitbild, Reputation, Erfolge u.v.m.', 'fau-elemental'),
            ),
            'studieninteressierte' => array(
                'title' => __('Studieninteressierte', 'fau-elemental'),
                'text' => __('Schwerpunkte, Leitbild, Reputation, Erfolge u.v.m.', 'fau-elemental'),
            ),
        );

        foreach ($target_groups as $key => $group): ?>
            <div class="target-group">
                <h3><?php echo esc_html(get_theme_mod("target_group_{$key}_title", $group['title'])); ?></h3>
                <p><?php echo esc_html(get_theme_mod("target_group_{$key}_text", $group['text'])); ?></p>
                <a href="<?php echo esc_url(get_theme_mod("target_group_{$key}_link", '#')); ?>" class="arrow-link">
                    <?php _e('Mehr erfahren', 'fau-elemental'); ?>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<footer class="site-footer">
    <div class="footer-content">
        <?php get_template_part( 'template-parts/footer/instance' ); ?>
        
        <div class="footer-main <?php echo $is_main_site ? 'footer-main--fau' : ''; ?>">
            <?php if ($is_main_site): ?>
                <div class="footer-legal">
                    <?php if (has_nav_menu('footer-technical')): ?>
                    <nav class="footer-technical-nav">
                        <?php
                        wp_nav_menu(array(
                            'theme_location' => 'footer-technical',
                            'menu_class' => 'footer-technical-menu',
                            'container' => false,
                            'fallback_cb' => false,
                            'items_wrap' => '<ul class="%2$s">%3$s</ul>',
                        ));
                        ?>
                    </nav>
                    <?php endif; ?>

                    <div class="image-credits">
                        <?php get_template_part('template-parts/footer/image-credits'); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
 
</footer> 