<?php
/**
 * Image Links Migration
 * 
 * Provides backward compatibility for image links custom post type 
 * from previous FAU themes
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main migration orchestrator - synchronizes with current imagelink posts
 */
function fau_elemental_migrate_image_links() {
    error_log('FAU Elemental: Starting image links migration...');
    
    $current_posts = fau_elemental_get_current_imagelink_posts();
    
    if (empty($current_posts)) {
        error_log('FAU Elemental: No current image links found');
        // Clear any existing migrated data since no imagelinks exist
        update_option('fau_elemental_migrated_image_links', array());
        return;
    }
    
    $migrated_logos = fau_elemental_process_imagelink_posts($current_posts);
    fau_elemental_save_current_migrated_data($migrated_logos, count($current_posts));
    fau_elemental_migrate_page_settings_once();
}

/**
 * Get all current imagelink posts that exist in the database
 */
function fau_elemental_get_current_imagelink_posts() {
    $current_posts = get_posts(array(
        'post_type' => 'imagelink',
        'posts_per_page' => -1,
        'post_status' => array('publish', 'private', 'draft')
    ));
    
    error_log('FAU Elemental: Found ' . count($current_posts) . ' current imagelink posts');
    return $current_posts;
}

/**
 * Process an array of imagelink posts and convert them to logo data
 */
function fau_elemental_process_imagelink_posts($posts) {
    $migrated_logos = array();
    
    foreach ($posts as $post) {
        $logo_data = fau_elemental_convert_post_to_logo($post);
        if ($logo_data) {
            $migrated_logos[] = $logo_data;
        }
    }
    
    return $migrated_logos;
}

/**
 * Convert a single imagelink post to logo data format
 */
function fau_elemental_convert_post_to_logo($post) {
    $thumbnail_id = get_post_thumbnail_id($post->ID);
    $thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'medium') : '';
    $link_url = fau_elemental_extract_link_url($post);
    $category_data = fau_elemental_get_category_data($post->ID);
    
    // Only migrate if we have an image or a link
    if (!$thumbnail_id && empty($link_url)) {
        return null;
    }
    
    return array(
        'imageId' => $thumbnail_id,
        'imageUrl' => $thumbnail_url,
        'link' => $link_url,
        'category' => $category_data['name'],
        'originalId' => $post->ID,
        'originalCategoryId' => $category_data['id']
    );
}

/**
 * Extract link URL from post meta fields or content
 */
function fau_elemental_extract_link_url($post) {
    $url_fields = array(
        'fauval_imagelink_url',    // Found in debug - correct field for current site
        'imagelink_url',           // Common pattern
        'url',                     // Generic
        'link_url', 
        'link', 
        'href', 
        'target_url'
    );
    
    // Check meta fields first
    foreach ($url_fields as $field) {
        $url = get_post_meta($post->ID, $field, true);
        if (!empty($url)) {
            return $url;
        }
    }
    
    // Fallback: check if post content is a URL
    if (filter_var($post->post_content, FILTER_VALIDATE_URL)) {
        return $post->post_content;
    }
    
    return '';
}

/**
 * Get category data for a post (name and ID)
 */
function fau_elemental_get_category_data($post_id) {
    if (!taxonomy_exists('imagelinks_category')) {
        return array('name' => '', 'id' => 0);
    }
    
    $categories = wp_get_post_terms($post_id, 'imagelinks_category');
    if (is_wp_error($categories) || empty($categories)) {
        return array('name' => '', 'id' => 0);
    }
    
    return array(
        'name' => $categories[0]->name,
        'id' => $categories[0]->term_id
    );
}

/**
 * Save the current migrated data (replaces all previous data)
 */
function fau_elemental_save_current_migrated_data($migrated_logos, $total_count) {
    update_option('fau_elemental_migrated_image_links', $migrated_logos);
    error_log("FAU Elemental: Successfully synchronized $total_count image links");
}

/**
 * Migrate page settings only once
 */
function fau_elemental_migrate_page_settings_once() {
    if (!get_option('fau_elemental_page_settings_migrated', false)) {
        fau_elemental_migrate_page_imagelink_settings();
        update_option('fau_elemental_page_settings_migrated', true);
    }
}

/**
 * Get migrated image links for use in logo grid blocks
 * 
 * @return array Array of migrated logo data
 */
function fau_elemental_get_migrated_image_links() {
    return get_option('fau_elemental_migrated_image_links', array());
}

/**
 * Check if there are migrated image links available
 * 
 * @return bool True if migrated links exist, false otherwise
 */
function fau_elemental_has_migrated_image_links() {
    $migrated_links = fau_elemental_get_migrated_image_links();
    return !empty($migrated_links);
}

/**
 * Migrate page-level image links settings from old theme
 * This migrates the settings that determined which category of image links to show on each page
 */
function fau_elemental_migrate_page_imagelink_settings() {
    // Get all pages that have image link category settings
    $pages_with_imagelinks = get_posts(array(
        'post_type' => 'page',
        'posts_per_page' => -1,
        'post_status' => 'any',
        'meta_query' => array(
            array(
                'key' => 'fauval_imagelink_catid',
                'compare' => 'EXISTS'
            )
        )
    ));
    
    $migrated_count = 0;
    foreach ($pages_with_imagelinks as $page) {
        $cat_id = get_post_meta($page->ID, 'fauval_imagelink_catid', true);
        $size = get_post_meta($page->ID, 'fauval_imagelink_size', true);
        
        if (!empty($cat_id)) {
            // Store in new meta field for potential future use
            update_post_meta($page->ID, 'fau_elemental_legacy_imagelink_catid', $cat_id);
            update_post_meta($page->ID, 'fau_elemental_legacy_imagelink_size', $size);
            
            // Add a note that this page had automatic image links
            update_post_meta($page->ID, 'fau_elemental_had_auto_imagelinks', true);
            
            $migrated_count++;
        }
    }
    
    if ($migrated_count > 0) {
        error_log("FAU Elemental: Migrated image link settings for $migrated_count pages");
        
        // Set transient for admin notice
        set_transient('fau_elemental_image_links_pages_migrated', 1, 60);
        set_transient('fau_elemental_image_links_pages_count', $migrated_count, 60);
    }
    
    return $migrated_count;
}

/**
 * Manual migration function that can be called from admin
 * 
 * @param bool $force Whether to force migration even if already done
 * @return bool True if migration was performed, false otherwise
 */
function fau_elemental_force_image_links_migration($force = false) {
    if ($force) {
        delete_option('fau_elemental_migrated_image_links');
        delete_option('fau_elemental_page_settings_migrated');
        error_log('FAU Elemental: Cleared migration data, forcing full re-migration');
    }
    
    fau_elemental_migrate_image_links();
    
    return fau_elemental_has_migrated_image_links();
}

/**
 * Check if we should run scheduled migration and run it safely
 */
function fau_elemental_run_scheduled_migration() {
    if (get_option('fau_elemental_schedule_image_links_migration', false)) {
        delete_option('fau_elemental_schedule_image_links_migration');
        fau_elemental_migrate_image_links();
    }
}
// Run migration after WordPress is fully loaded
add_action('wp_loaded', 'fau_elemental_run_scheduled_migration');

/**
 * Register the migrated image links option for REST API access
 */
function fau_elemental_register_image_links_rest_setting() {
    register_setting(
        'general',
        'fau_elemental_migrated_image_links',
        array(
            'type' => 'array',
            'default' => array(),
            'show_in_rest' => array(
                'schema' => array(
                    'type' => 'array',
                    'items' => array(
                        'type' => 'object',
                        'properties' => array(
                            'imageId' => array('type' => 'integer'),
                            'imageUrl' => array('type' => 'string'),
                            'link' => array('type' => 'string'),
                            'title' => array('type' => 'string'),
                            'description' => array('type' => 'string'),
                            'category' => array('type' => 'string'),
                            'originalId' => array('type' => 'integer'),
                            'originalCategoryId' => array('type' => 'integer')
                        )
                    )
                )
            )
        )
    );
}
add_action('rest_api_init', 'fau_elemental_register_image_links_rest_setting');

/**
 * Add admin notice about available image links migration
 */
function fau_elemental_image_links_migration_notice() {
    // Only show on themes page and only if migration hasn't been done
    $screen = get_current_screen();
    if (!$screen || $screen->id !== 'themes' || get_option('fau_elemental_image_links_migrated', false)) {
        return;
    }
    
    // Check if there are any image links to migrate
    $check_posts = get_posts(array(
        'post_type' => array('imagelink', 'image-links', 'imagelinks', 'image_links', 'logo_links', 'logos'),
        'posts_per_page' => 1,
        'post_status' => 'publish'
    ));
    
    // Also check if the imagelinks_category taxonomy exists
    if (empty($check_posts)) {
        $check_taxonomy = taxonomy_exists('imagelinks_category');
        if ($check_taxonomy) {
            // If taxonomy exists, there might be image links
            $check_posts = array('found_taxonomy');
        }
    }
    
    if (empty($check_posts)) {
        return;
    }
    
    $migration_url = wp_nonce_url(
        add_query_arg('fau-migrate-image-links', '1', admin_url('themes.php')),
        'fau-migrate-image-links'
    );
    
    ?>
    <div class="notice notice-info is-dismissible">
        <p>
            <strong><?php _e('FAU Elemental Theme:', 'fau-elemental'); ?></strong>
            <?php _e('Image links from your previous theme were detected.', 'fau-elemental'); ?>
            <a href="<?php echo esc_url($migration_url); ?>" class="button button-secondary">
                <?php _e('Migrate Image Links', 'fau-elemental'); ?>
            </a>
        </p>
    </div>
    <?php
}
add_action('admin_notices', 'fau_elemental_image_links_migration_notice');

/**
 * Handle manual migration request from admin
 */
function fau_elemental_process_image_links_migration_request() {
    if (isset($_GET['fau-migrate-image-links']) && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'fau-migrate-image-links')) {
        // Force migration
        $migrated = fau_elemental_force_image_links_migration(true);
        
        // Set transient for admin notice
        if ($migrated) {
            set_transient('fau_elemental_image_links_migrated_success', 1, 60);
        } else {
            set_transient('fau_elemental_image_links_migrated_none', 1, 60);
        }
        
        // Redirect back to themes page
        wp_redirect(admin_url('themes.php'));
        exit;
    }
}
add_action('admin_init', 'fau_elemental_process_image_links_migration_request');

/**
 * Show migration result notices for image links
 */
function fau_elemental_image_links_migration_success_notice() {
    if (get_transient('fau_elemental_image_links_migrated_success')) {
        delete_transient('fau_elemental_image_links_migrated_success');
        $migrated_links = fau_elemental_get_migrated_image_links();
        $count = count($migrated_links);
        
        // Get categories for more detailed info
        $categories = array();
        foreach ($migrated_links as $link) {
            if (!empty($link['category'])) {
                $categories[$link['category']] = ($categories[$link['category']] ?? 0) + 1;
            }
        }
        
        ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <strong><?php _e('FAU Elemental Theme:', 'fau-elemental'); ?></strong>
                <?php printf(__('Successfully migrated %d image links from your previous theme!', 'fau-elemental'), $count); ?>
            </p>
            <?php if (!empty($categories)): ?>
                <p>
                    <?php _e('Migrated categories:', 'fau-elemental'); ?>
                    <?php 
                    $cat_list = array();
                    foreach ($categories as $cat_name => $cat_count) {
                        $cat_list[] = sprintf('%s (%d)', $cat_name, $cat_count);
                    }
                    echo implode(', ', $cat_list);
                    ?>
                </p>
            <?php endif; ?>
            <p>
                <em><?php _e('These logos are now available when you add a Logo Grid block to your pages.', 'fau-elemental'); ?></em>
            </p>
        </div>
        <?php
    } elseif (get_transient('fau_elemental_image_links_migrated_none')) {
        delete_transient('fau_elemental_image_links_migrated_none');
        ?>
        <div class="notice notice-warning is-dismissible">
            <p>
                <strong><?php _e('FAU Elemental Theme:', 'fau-elemental'); ?></strong>
                <?php _e('No image links from your previous theme were found to migrate.', 'fau-elemental'); ?>
            </p>
        </div>
        <?php
    } elseif (get_transient('fau_elemental_image_links_pages_migrated')) {
        delete_transient('fau_elemental_image_links_pages_migrated');
        $page_count = get_transient('fau_elemental_image_links_pages_count');
        delete_transient('fau_elemental_image_links_pages_count');
        ?>
        <div class="notice notice-info is-dismissible">
            <p>
                <strong><?php _e('FAU Elemental Theme:', 'fau-elemental'); ?></strong>
                <?php printf(__('Found %d pages that had automatic image links in the previous theme.', 'fau-elemental'), $page_count); ?>
            </p>
            <p>
                <em><?php _e('You can now add Logo Grid blocks to these pages manually for more flexible logo placement.', 'fau-elemental'); ?></em>
            </p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'fau_elemental_image_links_migration_success_notice'); 