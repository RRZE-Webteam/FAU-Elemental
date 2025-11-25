<?php
/**
 * Legacy sidebar compatibility helpers.
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Surface sidebar content that was stored by the FAU-Einrichtungen theme.
 */
class FAU_Elemental_Legacy_Sidebar {

    /**
     * Maximum number of links per legacy block that will be inspected.
     */
    private const MAX_LINKS = 12;

    /**
     * Bootstrap hooks.
     */
    public static function init() {
        add_action('enqueue_block_editor_assets', array(__CLASS__, 'maybe_localize_editor_data'), 20);
    }

    /**
     * Pass legacy sidebar data to the block editor when relevant.
     */
    public static function maybe_localize_editor_data() {
        if (!is_admin()) {
            return;
        }

        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if ($screen && 'post' !== $screen->base) {
            return;
        }

        $post_id = self::resolve_post_id();
        if (!$post_id) {
            return;
        }

        if ('page' !== get_post_type($post_id)) {
            return;
        }

        $data = self::collect_sidebar_data($post_id);
        if (empty($data['hasLegacyData'])) {
            return;
        }

        wp_localize_script(
            'faue-block-editor-script',
            'fauElementalLegacySidebar',
            array(
                'data' => $data,
                'strings' => self::get_ui_strings(),
            )
        );
    }

    /**
     * Resolve the post ID currently being edited.
     */
    private static function resolve_post_id() {
        if (isset($_GET['post'])) {
            return absint(wp_unslash($_GET['post']));
        }

        if (isset($_POST['post_ID'])) {
            return absint(wp_unslash($_POST['post_ID']));
        }

        return 0;
    }

    /**
     * Gather all legacy sidebar data for a post.
     */
    private static function collect_sidebar_data($post_id) {
        $link_blocks = array();

        foreach (array(1, 2) as $block_index) {
            $block = self::prepare_link_block($post_id, $block_index);
            if (!empty($block)) {
                $block['block'] = $block_index;
                $link_blocks[] = $block;
            }
        }

        $data = array(
            'postId' => $post_id,
            'top' => self::prepare_text_section($post_id, 'above'),
            'bottom' => self::prepare_text_section($post_id, 'below'),
            'contacts' => self::prepare_contacts($post_id),
            'linkBlocks' => $link_blocks,
            'order' => self::get_sidebar_order($post_id),
        );

        $data['hasLegacyData'] = self::has_legacy_data($data);

        return $data;
    }

    /**
     * Return translation strings for the editor UI.
     */
    private static function get_ui_strings() {
        return array(
            'panelTitle' => __('Migration Assistant', 'fau-elemental'),
            'panelDescription' => __('This page still contains sidebar entries that were stored in the FAU-Einrichtungen theme. Use this reference to copy the content or inject it into the current page.', 'fau-elemental'),
            'orderLinksFirst' => __('Links were displayed before contacts in the legacy sidebar.', 'fau-elemental'),
            'orderContactsFirst' => __('Contacts were displayed before links in the legacy sidebar.', 'fau-elemental'),
            'textTopLabel' => __('Top text', 'fau-elemental'),
            'textBottomLabel' => __('Bottom text', 'fau-elemental'),
            'contactsLabel' => __('Contacts', 'fau-elemental'),
            'linkBlockLabel' => __('Link block %d', 'fau-elemental'),
            'titleLabel' => __('Title', 'fau-elemental'),
            'contentLabel' => __('Content', 'fau-elemental'),
            'linksLabel' => __('Links', 'fau-elemental'),
            'insertButton' => __('Insert as blocks', 'fau-elemental'),
            'insertedLabel' => __('Legacy sidebar content inserted.', 'fau-elemental'),
            'noTitleFallback' => __('No title', 'fau-elemental'),
            'contactFallback' => __('Contact', 'fau-elemental'),
            'linkFallback' => __('Link', 'fau-elemental'),
            'shortcodeLabel' => __('Legacy shortcode', 'fau-elemental'),
            'shortcodeDescription' => __('Add this shortcode to display the selected contacts.', 'fau-elemental'),
        );
    }

    /**
     * Normalize a text section (top/bottom).
     */
    private static function prepare_text_section($post_id, $position) {
        $title_key = 'sidebar_title_' . $position;
        $text_key = 'sidebar_text_' . $position;

        $title = sanitize_text_field(get_post_meta($post_id, $title_key, true));
        $content = get_post_meta($post_id, $text_key, true);

        if ('' !== $content) {
            $content = wp_kses_post($content);
        }

        if ('' === $title && '' === $content) {
            return null;
        }

        return array(
            'title' => $title,
            'content' => $content,
        );
    }

    /**
     * Prepare contacts data.
     */
    private static function prepare_contacts($post_id) {
        $ids = get_post_meta($post_id, 'sidebar_personen', true);
        if (!is_array($ids) || empty($ids)) {
            return null;
        }

        $items = array();
        $shortcode_ids = array();
        foreach ($ids as $raw_id) {
            $person_id = absint($raw_id);
            if (!$person_id) {
                continue;
            }

            $title = get_the_title($person_id);
            if ('' === $title) {
                continue;
            }

            $shortcode_ids[] = $person_id;

            $items[] = array(
                'id' => $person_id,
                'title' => sanitize_text_field($title),
                'url' => self::prepare_url(get_permalink($person_id)),
            );
        }

        if (empty($items)) {
            return null;
        }

        $title = sanitize_text_field(get_post_meta($post_id, 'sidebar_title_personen', true));
        $shortcode = '';
        if (!empty($shortcode_ids)) {
            $shortcode = '[kontakt id="' . implode(',', array_map('absint', $shortcode_ids)) . '"]';
        }

        return array(
            'title' => $title,
            'items' => $items,
            'shortcode' => $shortcode,
        );
    }

    /**
     * Collect data for a link block.
     */
    private static function prepare_link_block($post_id, $block_index) {
        $title_key = sprintf('fauval_sidebar_title_linkblock%d', $block_index);
        $title = sanitize_text_field(get_post_meta($post_id, $title_key, true));

        $links = array();
        for ($i = 1; $i <= self::MAX_LINKS; $i++) {
            $meta_base = sprintf('fauval_linkblock%d_link%d', $block_index, $i);
            $stored_id = absint(get_post_meta($post_id, $meta_base, true));
            $stored_url = get_post_meta($post_id, $meta_base . '_url', true);
            $stored_title = get_post_meta($post_id, $meta_base . '_title', true);

            if (!$stored_id && '' === trim((string) $stored_url) && '' === trim((string) $stored_title)) {
                continue;
            }

            $link_title = sanitize_text_field($stored_title);
            if ('' === $link_title && $stored_id) {
                $link_title = sanitize_text_field(get_the_title($stored_id));
            }

            $link_url = '';
            if ('' !== trim((string) $stored_url)) {
                $link_url = esc_url_raw($stored_url);
            } elseif ($stored_id) {
                $link_url = self::prepare_url(get_permalink($stored_id));
            }

            $links[] = array(
                'id' => $stored_id ? $stored_id : null,
                'title' => $link_title,
                'url' => $link_url,
            );
        }

        if (empty($links) && '' === $title) {
            return null;
        }

        return array(
            'title' => $title,
            'links' => $links,
        );
    }

    /**
     * Determine how the legacy sidebar was ordered.
     */
    private static function get_sidebar_order($post_id) {
        $order = get_post_meta($post_id, 'fauval_sidebar_order_personlinks', true);
        if ('' === $order) {
            return null;
        }

        return absint($order);
    }

    /**
     * Check if the page actually has legacy data.
     */
    private static function has_legacy_data($data) {
        if (!empty($data['top']) && (!empty($data['top']['title']) || !empty($data['top']['content']))) {
            return true;
        }

        if (!empty($data['bottom']) && (!empty($data['bottom']['title']) || !empty($data['bottom']['content']))) {
            return true;
        }

        if (!empty($data['contacts']) && !empty($data['contacts']['items'])) {
            return true;
        }

        if (!empty($data['linkBlocks'])) {
            foreach ($data['linkBlocks'] as $block) {
                if (!empty($block['title']) || !empty($block['links'])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Sanitize URLs.
     */
    private static function prepare_url($raw_url) {
        if (!$raw_url || is_wp_error($raw_url)) {
            return '';
        }

        return esc_url_raw($raw_url);
    }
}

FAU_Elemental_Legacy_Sidebar::init();
