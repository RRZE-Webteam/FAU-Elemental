<?php
/**
 * The template for displaying the footer
 */

$is_main_site = is_main_site();
?>
<footer class="site-footer <?php echo is_main_site() ? 'site-footer--main' : 'site-footer--instance'; ?>">
    <div class="footer-content">
        <?php if (is_main_site()): ?>
            <div class="fau-claim">
                <h2><?php echo get_theme_mod('fau_claim_title', __('FAU – Knowledge in Motion', 'fau')); ?></h2>
                <p><?php echo get_theme_mod('fau_claim_text', __('test', 'fau')); ?></p>
            </div>

            <div class="target-groups">
                <div class="target-group">
                    <h3><?php echo get_theme_mod('zur_fau_title', __('To FAU', 'fau')); ?></h3>
                    <p><?php echo get_theme_mod('zur_fau_description', __('History, special features, data, structure and much more', 'fau')); ?></p>
                    <a href="<?php echo get_theme_mod('zur_fau_link', '#'); ?>" class="arrow-link">
                        <?php esc_html_e('Learn more', 'fau'); ?>
                    </a>
                </div>
                <div class="target-group">
                    <h3><?php echo get_theme_mod('forschung_title', __('Research', 'fau')); ?></h3>
                    <p><?php echo get_theme_mod('forschung_description', __('Focus areas, mission statement, reputation, successes and much more', 'fau')); ?></p>
                    <a href="<?php echo get_theme_mod('forschung_link', '#'); ?>" class="arrow-link">
                        <?php esc_html_e('Learn more', 'fau'); ?>
                    </a>
                </div>
                <div class="target-group">
                    <h3><?php echo get_theme_mod('studierende_title', __('Students', 'fau')); ?></h3>
                    <p><?php echo get_theme_mod('studierende_description', __('Focus areas, mission statement, reputation, successes and much more', 'fau')); ?></p>
                    <a href="<?php echo get_theme_mod('studierende_link', '#'); ?>" class="arrow-link">
                        <?php esc_html_e('Learn more', 'fau'); ?>
                    </a>
                </div>
                <div class="target-group">
                    <h3><?php echo get_theme_mod('studieninteressierte_title', __('Prospective students', 'fau')); ?></h3>
                    <p><?php echo get_theme_mod('studieninteressierte_description', __('Focus areas, mission statement, reputation, successes and much more', 'fau')); ?></p>
                    <a href="<?php echo get_theme_mod('studieninteressierte_link', '#'); ?>" class="arrow-link">
                        <?php esc_html_e('Learn more', 'fau'); ?>
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="footer-instance">
                <div class="footer-instance-content">
                    <h2><?php echo get_theme_mod('instance_title', 'sdf'); ?></h2>
                    <div class="instance-description">
                        <?php echo get_theme_mod('instance_description', 'sdf'); ?>
                    </div>
                </div>

                <div class="footer-contact">
                    <h3><?php esc_html_e('Contact', 'fau'); ?></h3>
                    <?php echo get_theme_mod('instance_contact', 'sdf'); ?>
                </div>

                <nav class="footer-instance-nav">
                    <?php wp_nav_menu(['theme_location' => 'footer-instance']); ?>
                </nav>
            </div>
        <?php endif; ?>

        <div class="footer-main <?php echo is_main_site() ? 'footer-main--fau' : ''; ?>">
            <div class="footer-legal">
                <div class="image-credits">
                    <?php echo get_theme_mod('image_credits', ''); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="footer-logo">
            <?php if (has_custom_logo()): ?>
                <?php the_custom_logo(); ?>
            <?php else: ?>
                <img src="<?php echo esc_url(get_theme_file_uri('assets/img/fau-logo.svg')); ?>" alt="FAU Logo">
            <?php endif; ?>
            <span class="footer-logo-text">
                <?php esc_html_e('Friedrich-Alexander University', 'fau'); ?><br>
                <?php esc_html_e('Erlangen-Nuremberg', 'fau'); ?>
            </span>
        </div>

        <nav class="technical-nav" aria-label="<?php esc_attr_e('Technical Navigation', 'fau'); ?>">
            <?php
            wp_nav_menu([
                'theme_location' => 'footer-technical',
                'container' => false,
                'menu_class' => 'technical-menu',
            ]);
            ?>
        </nav>

        <?php get_template_part('template-parts/footer/social-media'); ?>
    </div>

    <?php if (get_theme_mod('image_credits')): ?>
        <div class="image-credits">
            <h3><?php esc_html_e('Photo credits:', 'fau'); ?></h3>
            <?php echo wp_kses_post(get_theme_mod('image_credits')); ?>
        </div>
    <?php endif; ?>
</footer>
</html>