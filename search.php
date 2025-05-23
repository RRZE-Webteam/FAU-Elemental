<?php
/**
 * Search Results Template
 *
 * Handles both normal and FAU-wide (network) search results.
 *
 * @package FAU-Elemental
 */

global $wp_query;

// Check if this is a FAU-wide search
$is_fau_wide = isset($_GET['fau_search_scope']) && $_GET['fau_search_scope'] === 'fau-wide';
$network_results = false;
if ($is_fau_wide) {
    // Try current blog
    $network_results = get_transient('fau_network_search_results_' . get_current_blog_id());
    // Fallback: try main site
    if (empty($network_results)) {
        $network_results = get_transient('fau_network_search_results_1');
    }
    // If still empty, run the network search logic directly
    if (empty($network_results)) {
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
                if ($site->blog_id === $current_blog_id) {
                    continue;
                }
                if (!switch_to_blog($site->blog_id)) {
                    continue;
                }
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

            // Sort results by date, most recent first
            usort($results, function($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });

            $network_results = $results;
        } else {
            $network_results = [];
        }
    }
}

get_header();
?>
<main class="site-main">
    <div class="search-results-container">
        <h1 class="search-results-title"><?php esc_html_e('Search Results', 'fau-elemental'); ?></h1>
        <div class="search-results-list">
            <?php if ($is_fau_wide): ?>
                <?php if (!empty($network_results)): ?>
                    <?php foreach ($network_results as $result): ?>
                        <article class="search-result">
                            <h3><a href="<?php echo esc_url($result['link']); ?>"><?php echo esc_html($result['title']); ?></a></h3>
                            <div class="search-result-meta">
                                <span class="search-result-site"><?php echo esc_html($result['blog_name']); ?></span>
                                <span class="search-result-date"><?php echo esc_html($result['date']); ?></span>
                                <span class="search-result-type"><?php echo esc_html($result['type']); ?></span>
                            </div>
                            <?php if (!empty($result['excerpt'])): ?>
                                <div class="search-result-excerpt"><?php echo wp_kses_post($result['excerpt']); ?></div>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-results-message"><?php esc_html_e('No results found. Try adjusting your search terms or browse our suggestions below.', 'fau-elemental'); ?></p>
                <?php endif; ?>
            <?php else: ?>
                <?php if (have_posts()): ?>
                    <?php while (have_posts()): the_post(); ?>
                        <article class="search-result">
                            <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            <div class="search-result-meta">
                                <span class="search-result-date"><?php echo get_the_date(); ?></span>
                                <span class="search-result-type"><?php echo get_post_type(); ?></span>
                            </div>
                            <div class="search-result-excerpt"><?php the_excerpt(); ?></div>
                        </article>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="no-results-message"><?php esc_html_e('No results found. Try adjusting your search terms or browse our suggestions below.', 'fau-elemental'); ?></p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php get_footer(); ?> 
