<?php

/**
 * The template for displaying search results
 *
 * @package Fau-Elemental
 */

get_header();

// Configuration values - should ideally come from theme config
$search_config = array(
    'excerpt_length' => 30,
    'separator' => '|',
    'arrow' => '→'
);
?>

<main class="search-results-page" role="main">
    <div class="search-header">
        <h1 class="search-title">
            <?php 
            if (have_posts()) {
                printf(__('Search Results for: %s', 'fau-elemental'), '<span class="search-query">' . get_search_query() . '</span>'); 
            } else {
                _e('Search Results', 'fau-elemental');
            }
            ?>
        </h1>
        
        <!-- Search form -->
        <div class="search-form-container">
            <form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
                <div class="search-form-wrapper">
                    <label class="screen-reader-text" for="search-field"><?php _e('Search for:', 'fau-elemental'); ?></label>
                    <input type="search" 
                           id="search-field" 
                           class="search-field" 
                           placeholder="<?php _e('Enter search term...', 'fau-elemental'); ?>" 
                           value="<?php echo get_search_query(); ?>" 
                           name="s" 
                           aria-describedby="search-instructions" />
                    <button type="submit" class="search-submit" aria-label="<?php _e('Submit search', 'fau-elemental'); ?>">
                        <?php _e('Search', 'fau-elemental'); ?>
                    </button>
                </div>
                <div id="search-instructions" class="screen-reader-text">
                    <?php _e('Press Enter to search or use the search button to submit your query', 'fau-elemental'); ?>
                </div>
            </form>
        </div>
    </div>

    <?php if (have_posts()) : ?>
        <div class="search-results" itemscope itemtype="https://schema.org/SearchResultsPage">
            <div class="results-count">
                <p role="status" aria-live="polite"><?php 
                    global $wp_query;
                    $total_results = $wp_query->found_posts;
                    printf(
                        _nx(
                            '%s result found',
                            '%s results found', 
                            $total_results,
                            'search results count', 
                            'fau-elemental'
                        ), 
                        number_format_i18n($total_results)
                    ); 
                ?></p>
            </div>

            <div class="search-results-list">
                <?php while (have_posts()) : the_post(); ?>
                    <article class="search-result-item" itemscope itemtype="https://schema.org/Article">
                        <div class="result-header">
                            <div class="result-date-info">
                                <time class="result-date" 
                                      datetime="<?php echo get_the_date('c'); ?>" 
                                      itemprop="datePublished">
                                    <?php echo get_the_date('d.m.Y'); ?>
                                </time>
                                <span class="result-separator" aria-hidden="true"><?php echo $search_config['separator']; ?></span>
                                <span class="result-content-type">
                                    <?php echo esc_html(get_post_type_object(get_post_type())->labels->singular_name); ?>
                                </span>
                                <?php 
                                $categories = get_the_category();
                                if (!empty($categories)) : ?>
                                    <span class="result-separator" aria-hidden="true"><?php echo $search_config['separator']; ?></span>
                                    <span class="result-category" itemprop="articleSection">
                                        <?php echo esc_html($categories[0]->name); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="result-content">
                            <?php if (has_post_thumbnail()) : ?>
                                <div class="result-image" itemprop="image" itemscope itemtype="https://schema.org/ImageObject">
                                    <a href="<?php the_permalink(); ?>" aria-label="<?php printf(__('View article: %s', 'fau-elemental'), get_the_title()); ?>">
                                        <?php 
                                        the_post_thumbnail('medium', array(
                                            'class' => 'result-thumbnail',
                                            'itemprop' => 'url',
                                            'alt' => get_the_title()
                                        )); 
                                        ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <div class="result-text">
                                <h2 class="result-title" itemprop="headline">
                                    <a href="<?php the_permalink(); ?>" 
                                       class="result-link" 
                                       itemprop="url"
                                       aria-label="<?php printf(__('Read full article: %s', 'fau-elemental'), get_the_title()); ?>">
                                        <?php the_title(); ?>
                                    </a>
                                </h2>
                                
                                <div class="result-excerpt" itemprop="description">
                                    <?php 
                                    if (has_excerpt()) {
                                        the_excerpt();
                                    } else {
                                        echo wp_trim_words(get_the_content(), $search_config['excerpt_length'], '...');
                                    }
                                    ?>
                                </div>
                                
                                <a href="<?php the_permalink(); ?>" 
                                   class="result-read-more"
                                   aria-label="<?php printf(__('Read more about: %s', 'fau-elemental'), get_the_title()); ?>">
                                    <?php _e('Read more', 'fau-elemental'); ?> 
                                    <span aria-hidden="true"><?php echo $search_config['arrow']; ?></span>
                                </a>
                            </div>
                        </div>

                        <!-- Hidden structured data -->
                        <meta itemprop="author" content="<?php echo esc_attr(get_the_author()); ?>" />
                        <meta itemprop="dateModified" content="<?php echo get_the_modified_date('c'); ?>" />
                    </article>
                <?php endwhile; ?>
            </div>
            
            <?php 
            // Pagination
            the_posts_pagination(array(
                'mid_size' => 2,
                'prev_text' => __('&laquo; Previous', 'fau-elemental'),
                'next_text' => __('Next &raquo;', 'fau-elemental'),
                'class' => 'search-pagination',
                'screen_reader_text' => __('Search results navigation', 'fau-elemental')
            )); 
            ?>
        </div>

    <?php else : ?>
        <div class="no-results" role="region" aria-labelledby="no-results-heading">
            <h2 id="no-results-heading" class="screen-reader-text"><?php _e('No Search Results', 'fau-elemental'); ?></h2>
            <p class="no-results-message">
                <?php _e('No results were found.', 'fau-elemental'); ?>
            </p>
            <div class="no-results-suggestions">
                <h3><?php _e('Try:', 'fau-elemental'); ?></h3>
                <ul>
                    <li><?php _e('Check the spelling of your search terms.', 'fau-elemental'); ?></li>
                    <li><?php _e('Use more general search terms.', 'fau-elemental'); ?></li>
                    <li><?php _e('Use fewer search terms.', 'fau-elemental'); ?></li>
                </ul>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php get_footer(); ?>