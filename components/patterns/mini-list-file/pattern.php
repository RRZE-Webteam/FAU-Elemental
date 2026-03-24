<?php

/**
 * Title: Mini List: File
 * Slug: fau-elemental/mini-list-file
 * Categories: fau-elemental
 * Description: A list of files with a headline and a button to view more files.
 * Block Types: core/post-content
 * Post Types: post, page
 */

$demo_url = esc_url(get_theme_file_uri('assets/images/Demo-Cover.webp'));
?>

<!-- wp:group {"className":"mini-list-file"} -->
<div class="wp-block-group mini-list-file">

    <!-- wp:fau-elemental/fau-meta-headline {"headline":"Dateien","id":""} -->
    <h2 class="wp-block-fau-elemental-fau-meta-headline" id="headline-">Dateien</h2>
    <!-- /wp:fau-elemental/fau-meta-headline -->

    <!-- wp:file {"id":0,"href":"<?php echo $demo_url; ?>","coverImage":{"id":0,"url":"<?php echo $demo_url; ?>","alt":""},"fileDetails":{"filename":"Demo-Cover","filesize":3680,"mime_type":"image/webp"}} -->
    <div class="wp-block-file">
        <div class="wp-block-file__content-wrapper">
            <figure class="file-cover-image" aria-label="Cover image for file"><img src="<?php echo $demo_url; ?>" alt=""></figure>
            <section class="wp-block-file">
                <div class="file-content">
                    <div class="wp-block-file"><a id="wp-block-file--media-c3cd9330-06db-45ba-9c01-c31451df1dee" href="<?php echo $demo_url; ?>" aria-label="Demo-Cover Download" aria-describedby="wp-block-file--media-c3cd9330-06db-45ba-9c01-c31451df1dee">File Titel</a><a href="<?php echo $demo_url; ?>" class="wp-block-file__button wp-element-button" download aria-describedby="wp-block-file--media-c3cd9330-06db-45ba-9c01-c31451df1dee" aria-label="Demo-Cover Download" role="button"></a></div>
                    <div class="file-info-wrapper">
                        <dl class="file-info-list">
                            <div class="file-info-item">
                                <dt class="file-info-term">File Name</dt>
                                <dd class="file-info-definition">Demo-Cover</dd>
                            </div>
                            <div class="file-info-item">
                                <dt class="file-info-term">File Size</dt>
                                <dd class="file-info-definition">3.6 KB</dd>
                            </div>
                            <div class="file-info-item">
                                <dt class="file-info-term">File Type</dt>
                                <dd class="file-info-definition">WEBP</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </section>
        </div>
    </div>
    <!-- /wp:file -->

    <!-- wp:file {"id":0,"href":"<?php echo $demo_url; ?>","coverImage":{"id":0,"url":"<?php echo $demo_url; ?>","alt":""},"fileDetails":{"filename":"Demo-Cover","filesize":3680,"mime_type":"image/webp"}} -->
    <div class="wp-block-file">
        <div class="wp-block-file__content-wrapper">
            <figure class="file-cover-image" aria-label="Cover image for file"><img src="<?php echo $demo_url; ?>" alt=""></figure>
            <section class="wp-block-file">
                <div class="file-content">
                    <div class="wp-block-file"><a id="wp-block-file--media-347149d2-0503-4190-9656-3f473cee70ca" href="<?php echo $demo_url; ?>" aria-label="Demo-Cover Download" aria-describedby="wp-block-file--media-347149d2-0503-4190-9656-3f473cee70ca">File Titel</a><a href="<?php echo $demo_url; ?>" class="wp-block-file__button wp-element-button" download aria-describedby="wp-block-file--media-347149d2-0503-4190-9656-3f473cee70ca" aria-label="Demo-Cover Download" role="button"></a></div>
                    <div class="file-info-wrapper">
                        <dl class="file-info-list">
                            <div class="file-info-item">
                                <dt class="file-info-term">File Name</dt>
                                <dd class="file-info-definition">Demo-Cover</dd>
                            </div>
                            <div class="file-info-item">
                                <dt class="file-info-term">File Size</dt>
                                <dd class="file-info-definition">3.6 KB</dd>
                            </div>
                            <div class="file-info-item">
                                <dt class="file-info-term">File Type</dt>
                                <dd class="file-info-definition">WEBP</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </section>
        </div>
    </div>
    <!-- /wp:file -->

    <!-- wp:file {"id":0,"href":"<?php echo $demo_url; ?>","fileDetails":{"filename":"Demo-Cover","filesize":3680,"mime_type":"image/webp"}} -->
    <div class="wp-block-file">
        <div class="wp-block-file__content-wrapper">
            <figure class="file-cover-image" aria-label="Cover image for file"></figure>
            <section class="wp-block-file">
                <div class="file-content">
                    <div class="wp-block-file"><a id="wp-block-file--media-d47be20e-d0d5-44b0-b891-0ce21422397a" href="<?php echo $demo_url; ?>" aria-label="Demo-Cover Download" aria-describedby="wp-block-file--media-d47be20e-d0d5-44b0-b891-0ce21422397a">File Titel</a><a href="<?php echo $demo_url; ?>" class="wp-block-file__button wp-element-button" download aria-describedby="wp-block-file--media-d47be20e-d0d5-44b0-b891-0ce21422397a" aria-label="Demo-Cover Download" role="button"></a></div>
                    <div class="file-info-wrapper">
                        <dl class="file-info-list">
                            <div class="file-info-item">
                                <dt class="file-info-term">File Name</dt>
                                <dd class="file-info-definition">Demo-Cover</dd>
                            </div>
                            <div class="file-info-item">
                                <dt class="file-info-term">File Size</dt>
                                <dd class="file-info-definition">3.6 KB</dd>
                            </div>
                            <div class="file-info-item">
                                <dt class="file-info-term">File Type</dt>
                                <dd class="file-info-definition">WEBP</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </section>
        </div>
    </div>
    <!-- /wp:file -->

    <!-- wp:buttons {"layout":{"type":"flex","justifyContent":"right"}} -->
    <div class="wp-block-buttons">
        <!-- wp:button -->
        <div class="wp-block-button"><a class="wp-block-button__link wp-element-button">Weitere Inhalte</a></div>
        <!-- /wp:button -->
    </div>
    <!-- /wp:buttons -->

    <!-- wp:paragraph -->
    <p></p>
    <!-- /wp:paragraph -->
</div>
<!-- /wp:group -->