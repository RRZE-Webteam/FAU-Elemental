<?php
/**
 * Lightweight regression tests for the copyright-info renderer.
 *
 * Run with: npm run test:php
 */

define('ABSPATH', __DIR__);

$GLOBALS['copyright_test_posts'] = array(
    10 => (object) array(
        'ID' => 10,
        'post_type' => 'page',
        'post_content' => 'Private page content that must never become copyright text.',
        'is_image' => false,
    ),
    11 => (object) array(
        'ID' => 11,
        'post_type' => 'attachment',
        'post_content' => 'A document description.',
        'is_image' => false,
    ),
    20 => (object) array(
        'ID' => 20,
        'post_type' => 'attachment',
        'post_content' => 'Attachment description',
        'is_image' => true,
    ),
    21 => (object) array(
        'ID' => 21,
        'post_type' => 'attachment',
        'post_content' => 'Description fallback',
        'is_image' => true,
    ),
    100 => (object) array(
        'ID' => 100,
        'post_type' => 'page',
        'post_content' => 'queried-page-blocks',
        'is_image' => false,
    ),
    200 => (object) array(
        'ID' => 200,
        'post_type' => 'page',
        'post_content' => 'wrong-global-post-blocks',
        'is_image' => false,
    ),
);

$GLOBALS['copyright_test_metadata'] = array(
    20 => array(
        'image_meta' => array(
            'copyright' => 'IPTC copyright',
        ),
    ),
    21 => array(),
);

$GLOBALS['copyright_test_blocks'] = array(
    'queried-page-blocks' => array(
        array(
            'blockName' => 'core/image',
            'attrs' => array('id' => 20),
            'innerBlocks' => array(),
        ),
        array(
            'blockName' => 'core/image',
            'attrs' => array('id' => 20),
            'innerBlocks' => array(),
        ),
    ),
    'wrong-global-post-blocks' => array(
        array(
            'blockName' => 'core/image',
            'attrs' => array('id' => 21),
            'innerBlocks' => array(),
        ),
    ),
);

$GLOBALS['copyright_test_singular'] = false;
$GLOBALS['copyright_test_queried_id'] = 0;
$GLOBALS['copyright_test_priority'] = 'field';
$GLOBALS['copyright_test_featured_images'] = array();
$GLOBALS['copyright_test_parsed_content'] = array();
$GLOBALS['copyright_test_post_field_calls'] = array();
$GLOBALS['post'] = $GLOBALS['copyright_test_posts'][200];

function absint($value) {
    return abs((int) $value);
}

function get_post_type($post_id) {
    $post = get_post($post_id);
    return $post ? $post->post_type : false;
}

function wp_attachment_is_image($post_id) {
    $post = get_post($post_id);
    return $post && $post->post_type === 'attachment' && $post->is_image;
}

function get_post($post_id) {
    return $GLOBALS['copyright_test_posts'][$post_id] ?? null;
}

function wp_get_attachment_metadata($post_id) {
    return $GLOBALS['copyright_test_metadata'][$post_id] ?? false;
}

function get_post_field($field, $post_id) {
    $GLOBALS['copyright_test_post_field_calls'][] = $post_id;
    $post = get_post($post_id);
    return $post && isset($post->{$field}) ? $post->{$field} : '';
}

function is_singular() {
    return $GLOBALS['copyright_test_singular'];
}

function get_queried_object_id() {
    return $GLOBALS['copyright_test_queried_id'];
}

function parse_blocks($content) {
    $GLOBALS['copyright_test_parsed_content'][] = $content;
    return $GLOBALS['copyright_test_blocks'][$content] ?? array();
}

function get_theme_mod($name, $default = false) {
    return $name === 'faue_copyright_info_priority'
        ? $GLOBALS['copyright_test_priority']
        : $default;
}

function has_post_thumbnail($post_id = null) {
    return !empty($GLOBALS['copyright_test_featured_images'][$post_id]);
}

function get_post_thumbnail_id($post_id = null) {
    return $GLOBALS['copyright_test_featured_images'][$post_id] ?? 0;
}

function apply_filters($hook_name, $value) {
    return $value;
}

function esc_html__($text) {
    return $text;
}

function wp_get_attachment_url($post_id) {
    return 'https://example.test/image-' . $post_id . '.jpg';
}

function esc_url($url) {
    return $url;
}

function esc_html($text) {
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}

$attributes = array();
$content = '';

ob_start();
require dirname(__DIR__) . '/components/blocks/fau-copyright-info/render.php';
require dirname(__DIR__) . '/components/blocks/fau-copyright-info/render.php';
ob_end_clean();

$failures = 0;

function copyright_test_assert($condition, $message) {
    global $failures;

    if ($condition) {
        echo "PASS: {$message}\n";
        return;
    }

    $failures++;
    echo "FAIL: {$message}\n";
}

copyright_test_assert(true, 'The dynamic renderer can be included more than once.');

$page_result = fau_elemental_gather_copyright_info_from_metadata(array(
    'blockName' => 'core/image',
    'attrs' => array('id' => 10),
));
copyright_test_assert($page_result === null, 'A block ID that points to a page is rejected.');
copyright_test_assert(
    !in_array(10, $GLOBALS['copyright_test_post_field_calls'], true),
    'Rejected page IDs are never read through get_post_field().'
);

$document_result = fau_elemental_gather_copyright_info_from_metadata(array(
    'blockName' => 'core/image',
    'attrs' => array('id' => 11),
));
copyright_test_assert($document_result === null, 'A non-image attachment is rejected.');

$attribute_result = fau_elemental_gather_copyright_info_from_attribute(array(
    'blockName' => 'core/image',
    'attrs' => array(
        'id' => 10,
        'copyrightInfo' => 'Explicit source',
    ),
));
copyright_test_assert(
    $attribute_result['text'] === 'Explicit source' && $attribute_result['image_id'] === null,
    'Explicit copyright text is retained without linking an invalid image ID.'
);

$field_priority = fau_elemental_gather_copyright_info_from_image_id(20, 'field');
copyright_test_assert(
    count($field_priority) === 1 && $field_priority[0]['text'] === 'Attachment description',
    'Field priority returns only the attachment description for a featured image.'
);

$iptc_priority = fau_elemental_gather_copyright_info_from_image_id(20, 'iptc');
copyright_test_assert(
    count($iptc_priority) === 1 && $iptc_priority[0]['text'] === 'IPTC copyright',
    'IPTC priority returns only IPTC metadata for a featured image.'
);

$description_fallback = fau_elemental_gather_copyright_info_from_image_id(21, 'iptc');
copyright_test_assert(
    count($description_fallback) === 1 && $description_fallback[0]['text'] === 'Description fallback',
    'The attachment description remains available as the IPTC fallback.'
);

$deduplicated = fau_elemental_deduplicate_copyright_info(array(
    array('text' => 'Source A', 'image_id' => 20),
    array('text' => 'Source A', 'image_id' => 20),
    array('text' => 'Source A', 'image_id' => 21),
    array('text' => 'Source B', 'image_id' => null),
    array('text' => 'Source B', 'image_id' => null),
));
copyright_test_assert(
    count($deduplicated) === 3,
    'Duplicate image/text pairs are removed while distinct images are preserved.'
);

$GLOBALS['copyright_test_singular'] = true;
$GLOBALS['copyright_test_queried_id'] = 100;
$GLOBALS['copyright_test_priority'] = 'iptc';
$GLOBALS['copyright_test_featured_images'][100] = 20;
$GLOBALS['copyright_test_parsed_content'] = array();

$gathered = fau_elemental_gather_copyright_info();
copyright_test_assert(
    $GLOBALS['copyright_test_parsed_content'] === array('queried-page-blocks'),
    'The queried singular post is parsed instead of the mutable global post.'
);
copyright_test_assert(
    count($gathered) === 1 && $gathered[0]['text'] === 'IPTC copyright',
    'Featured and repeated block entries are deduplicated in gathered output.'
);

$GLOBALS['copyright_test_singular'] = false;
$GLOBALS['copyright_test_parsed_content'] = array();
$archive_result = fau_elemental_gather_copyright_info();
copyright_test_assert($archive_result === array(), 'Archive views do not select an arbitrary source post.');
copyright_test_assert(
    $GLOBALS['copyright_test_parsed_content'] === array(),
    'Archive views do not parse the global post content.'
);

if ($failures > 0) {
    echo "\n{$failures} regression test(s) failed.\n";
    exit(1);
}

echo "\nAll copyright-info regression tests passed.\n";
