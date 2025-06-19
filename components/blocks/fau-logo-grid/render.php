<?php
/**
 * Server-side rendering of the FAU Logo Grid block.
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

// Create class attribute
$class_name = 'fau-logo-grid';
if (!empty($attributes['className'])) {
    $class_name .= ' ' . $attributes['className'];
}
if (!empty($attributes['align'])) {
    $class_name .= ' align' . $attributes['align'];
}

// Load values from attributes
$logos = is_array($attributes['logos'] ?? null) ? $attributes['logos'] : [];

?>
<div class="<?php echo esc_attr($class_name); ?>">

    <?php if (!empty($logos)) : ?>
        <div class="fau-logo-grid__container">
            <?php foreach ($logos as $logo) : ?>
                <?php
                // Skip invalid logo entries
                if (!is_array($logo)) {
                    continue;
                }
                ?>
                <div class="fau-logo-grid__item">
                    <?php if (!empty($logo['link'])) : ?>
                        <a href="<?php echo esc_url($logo['link']); ?>" class="fau-logo-grid__link">
                    <?php endif; ?>

                    <?php if (!empty($logo['imageId'])) : ?>
                        <?php 
                        $image = wp_get_attachment_image($logo['imageId'], 'medium', false, array(
                            'class' => 'fau-logo-grid__image',
                            'loading' => 'lazy'
                        ));
                        if ($image) {
                            echo $image;
                        } elseif (!empty($logo['imageUrl'])) {
                            // Fallback to URL if ID is invalid
                            echo '<img src="' . esc_url($logo['imageUrl']) . '" alt="" class="fau-logo-grid__image" loading="lazy">';
                        }
                        ?>
                    <?php elseif (!empty($logo['imageUrl'])) : ?>
                        <img src="<?php echo esc_url($logo['imageUrl']); ?>" 
                             alt="" 
                             class="fau-logo-grid__image" 
                             loading="lazy">
                    <?php endif; ?>

                    <?php if (!empty($logo['link'])) : ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div> 