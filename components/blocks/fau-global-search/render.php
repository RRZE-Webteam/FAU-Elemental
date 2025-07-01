<?php
/**
 * FAU Global Search Block
 *
 * @package FAU-Elemental
 */

if (!function_exists('render_block_fau_global_search')) {
    /**
     * Renders the FAU Global Search block.
     *
     * @param array    $attributes Block attributes.
     * @param string   $content    Block default content.
     * @param WP_Block $block      Block instance.
     * @return string The block HTML.
     */
    function render_block_fau_global_search($attributes, $content, $block) {
        // Enqueue search suggestions script
        wp_enqueue_script(
            'fau-global-search',
            get_template_directory_uri() . '/components/blocks/fau-global-search/search-suggestions.js',
            array('jquery'),
            wp_get_theme()->get('Version'),
            true
        );

        // Add translations and configuration
        wp_localize_script('fau-global-search', 'fauGlobalSearch', array(
            'strings' => array(
                'frequentQueriesTitle' => __('Frequently Searched Queries', 'fau-elemental'),
                'suggestionsTitle' => __('Search Suggestions', 'fau-elemental'),
                'noResults' => __('No results found', 'fau-elemental'),
            ),
            'restUrl' => rest_url(),
            'restNonce' => wp_create_nonce('wp_rest'),
        ));

        $title = !empty($attributes['title']) ? $attributes['title'] : __('Search', 'fau-elemental');
        $search_scope = !empty($attributes['searchScope']) ? $attributes['searchScope'] : 'current';
        $layout_size = !empty($attributes['layoutSize']) ? $attributes['layoutSize'] : 'content';

        if (isset($_GET['fau_search_scope'])) {
            $search_scope = sanitize_text_field($_GET['fau_search_scope']);
        }

        $block_instance_id = 'fau-global-search';
        if (isset($block->context['postId'])) {
            $block_instance_id .= '-' . $block->context['postId'];
        }
        if (isset($block->parsed_block['id'])) {
            $block_instance_id .= '-' . $block->parsed_block['id'];
        } else {
            $block_instance_id .= '-' . uniqid();
        }
        
        $wrapper_attributes_array = [];
        $css_classes = [];
        if (!empty($attributes['align'])) {
            $css_classes[] = 'align' . $attributes['align'];
        }
        // Add layout size class
        $css_classes[] = 'layout-' . $layout_size;
        
        if (!empty($css_classes)) {
            $wrapper_attributes_array['class'] = implode(' ', $css_classes);
        }
        $wrapper_attributes = get_block_wrapper_attributes($wrapper_attributes_array);

        $search_action_url = home_url('/');
        $current_search_query = get_search_query();
        $has_search_results = is_search() && !empty($current_search_query);

        ob_start();
        ?>
        <div <?php echo $wrapper_attributes; ?> id="<?php echo esc_attr($block_instance_id); ?>-wrapper">
            <?php if (!empty($title)) : ?>
                <h2 class="wp-block-fau-elemental-fau-global-search__title"><?php echo esc_html($title); ?></h2>
            <?php endif; ?>

            <form role="search" method="get" class="wp-block-fau-elemental-fau-global-search__form search-form" action="<?php echo esc_url($search_action_url); ?>">
                <label class="wp-block-fau-elemental-fau-global-search__label screen-reader-text" for="<?php echo esc_attr($block_instance_id); ?>-search-field">
                    <?php _e('Searching incident', 'fau-elemental'); ?>
                </label>
                <input 
                    type="search" 
                    id="<?php echo esc_attr($block_instance_id); ?>-search-field"
                    class="wp-block-fau-elemental-fau-global-search__field search-field" 
                    placeholder="<?php esc_attr_e('Search …', 'fau-elemental'); ?>" 
                    value="<?php echo esc_attr($current_search_query); ?>" 
                    name="s" 
                />
                <input type="submit" class="wp-block-fau-elemental-fau-global-search__submit search-submit" value="<?php esc_attr_e('Search', 'fau-elemental'); ?>" />
                
                <input type="hidden" name="fau_search_scope" id="<?php echo esc_attr($block_instance_id); ?>-scope-hidden" value="<?php echo esc_attr($search_scope); ?>" />

                <?php if ($layout_size === 'content') : ?>
                <div class="wp-block-fau-elemental-fau-global-search__scope-toggle search-scope-toggle">
                    <fieldset>
                        <legend class="screen-reader-text"><?php _e('Search Scope', 'fau-elemental'); ?></legend>
                        <label for="<?php echo esc_attr($block_instance_id); ?>-scope-current">
                            <input 
                                type="radio" 
                                class="fau-search-scope-option"
                                name="search_scope_option" 
                                id="<?php echo esc_attr($block_instance_id); ?>-scope-current" 
                                value="current" 
                                <?php checked($search_scope, 'current'); ?>
                                onclick="document.getElementById('<?php echo esc_attr($block_instance_id); ?>-scope-hidden').value='current';"
                            />
                            <?php _e('Only in this website', 'fau-elemental'); ?>
                        </label>
                        <label for="<?php echo esc_attr($block_instance_id); ?>-scope-fau-wide">
                            <input 
                                type="radio" 
                                class="fau-search-scope-option"
                                name="search_scope_option" 
                                id="<?php echo esc_attr($block_instance_id); ?>-scope-fau-wide" 
                                value="fau-wide" 
                                <?php checked($search_scope, 'fau-wide'); ?>
                                onclick="document.getElementById('<?php echo esc_attr($block_instance_id); ?>-scope-hidden').value='fau-wide';"
                            />
                            <?php _e('FAU-wide', 'fau-elemental'); ?>
                        </label>
                    </fieldset>
                </div>
                <?php endif; ?>
            </form>

            <div class="wp-block-fau-elemental-fau-global-search__suggestions-area search-suggestions-area">
                <?php if (has_nav_menu('search_options_menu')) : ?>
                    <div class="search-options-menu">
                        <h3 class="search-options-title"><?php _e('Further Search Options', 'fau-elemental'); ?></h3>
                        <?php
                        wp_nav_menu([
                            'theme_location' => 'search_options_menu',
                            'container' => 'nav',
                            'container_class' => 'search-options-nav',
                            'menu_class' => 'search-options-list',
                            'fallback_cb' => false,
                            'depth' => 1,
                        ]);
                        ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($has_search_results) : ?>
                <?php
                if (is_search()) {
                    if (
                        isset($_GET['fau_search_scope']) &&
                        $_GET['fau_search_scope'] === 'fau-wide' &&
                        is_multisite()
                    ) {
                        $search_query = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
                        if (!empty($search_query)) {
                            $sites = get_sites([
                                'network_id' => get_current_network_id(),
                                'public' => 1,
                                'archived' => 0,
                                'mature' => 0,
                                'spam' => 0,
                                'deleted' => 0
                            ]);
                            
                            $results = [];
                            $current_blog_id = get_current_blog_id();
                            
                            // Search current site
                            $current_site_args = [
                                'post_type' => ['post', 'page'],
                                'post_status' => 'publish',
                                's' => $search_query,
                                'posts_per_page' => 5,
                            ];
                            $current_site_query = new WP_Query($current_site_args);
                            if ($current_site_query->have_posts()) {
                                while ($current_site_query->have_posts()) {
                                    $current_site_query->the_post();
                                    $results[] = [
                                        'blog_id' => $current_blog_id,
                                        'blog_url' => get_site_url(),
                                        'blog_name' => get_bloginfo('name'),
                                        'title' => get_the_title(),
                                        'link' => get_permalink(),
                                        'excerpt' => get_the_excerpt(),
                                        'date' => get_the_date(),
                                        'type' => get_post_type_object(get_post_type())->labels->singular_name,
                                        'is_current_site' => true
                                    ];
                                }
                            }
                            wp_reset_postdata();

                            // Search other sites
                            foreach ($sites as $site) {
                                if ($site->blog_id === $current_blog_id) continue;
                                
                                if (!switch_to_blog($site->blog_id)) continue;
                                
                                $args = [
                                    'post_type' => ['post', 'page'],
                                    'post_status' => 'publish',
                                    's' => $search_query,
                                    'posts_per_page' => 5,
                                ];
                                $query = new WP_Query($args);
                                if ($query->have_posts()) {
                                    while ($query->have_posts()) {
                                        $query->the_post();
                                        $results[] = [
                                            'blog_id' => $site->blog_id,
                                            'blog_url' => get_site_url(),
                                            'blog_name' => get_bloginfo('name'),
                                            'title' => get_the_title(),
                                            'link' => get_permalink(),
                                            'excerpt' => get_the_excerpt(),
                                            'date' => get_the_date(),
                                            'type' => get_post_type_object(get_post_type())->labels->singular_name,
                                            'is_current_site' => false
                                        ];
                                    }
                                }
                                wp_reset_postdata();
                                restore_current_blog();
                            }
                            
                            usort($results, function($a, $b) {
                                return strtotime($b['date']) - strtotime($a['date']);
                            });
                            
                            set_transient('fau_network_search_results_' . get_current_blog_id(), $results, HOUR_IN_SECONDS);

                        }
                    }
                }
                ?>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}

/**
 * Helper function to write to debug log
 */
function debug_to_file($message) {
    if (defined('WP_DEBUG') && WP_DEBUG === true) {
        if (is_array($message) || is_object($message)) {
            error_log(print_r($message, true));
        } else {
            error_log($message);
        }
    }
}

// Add filter to modify the main query for network search results
add_filter('pre_get_posts', function($query) {
    if (!is_admin() && $query->is_main_query() && is_search() && isset($_GET['fau_search_scope']) && $_GET['fau_search_scope'] === 'fau-wide') {
        $network_results = get_transient('fau_network_search_results_' . get_current_blog_id());
        if (!empty($network_results)) {
            $fake_posts = array_map(function($result) {
                $post = new stdClass();
                $post->ID = absint($result['blog_id']) . '_' . uniqid();
                $post->post_title = $result['title'];
                $post->post_content = $result['excerpt'];
                $post->post_excerpt = $result['excerpt'];
                $post->post_date = $result['date'];
                $post->post_type = 'post';
                $post->guid = $result['link'];
                $post->post_status = 'publish';
                $post->blog_id = $result['blog_id'];
                $post->blog_url = $result['blog_url'];
                $post->blog_name = $result['blog_name'];
                $post->permalink = $result['link'];
                return $post;
            }, $network_results);

            $query->posts = $fake_posts;
            $query->post_count = count($fake_posts);
            $query->found_posts = count($fake_posts);
            $query->max_num_pages = ceil(count($fake_posts) / get_option('posts_per_page'));
            $query->set('post__in', array(0));
        }
    }
    return $query;
});

// Add filter to modify post links for network search results
add_filter('post_link', function($permalink, $post) {
    return isset($post->permalink) ? $post->permalink : $permalink;
}, 10, 2);

// Add filter to modify post titles for network search results
add_filter('the_title', function($title, $post_id) {
    global $post;
    return isset($post->blog_name) ? $title . ' <span class="search-result-site-name">(' . esc_html($post->blog_name) . ')</span>' : $title;
}, 10, 2);

// Add filter to modify post excerpts for network search results
add_filter('get_the_excerpt', function($excerpt, $post) {
    return isset($post->post_excerpt) ? $post->post_excerpt : $excerpt;
}, 10, 2);

// Execute the render function and output the content
echo render_block_fau_global_search($attributes, $content, $block);