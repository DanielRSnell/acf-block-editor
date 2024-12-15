<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html(get_the_title()); ?> - Block Preview</title>
    <?php wp_head(); ?>
    <style>
        html {
            margin-top: 0 !important;
        }
        body {
            margin: 0;
            padding: 0;
        }
        #wpadminbar,
        #windpress-app {
            display: none !important;
        }
    </style>
</head>
<body <?php body_class(); ?>>
<?php
while (have_posts()) :
    the_post();

    $block_id = get_the_ID();
    $block = get_post($block_id);
    $block_data = [
        'php' => get_post_meta($block_id, '_client_php', true),
        'template' => get_post_meta($block_id, '_client_template', true),
        'js' => get_post_meta($block_id, '_client_js', true),
        'css' => get_post_meta($block_id, '_client_css', true),
    ];

    $block_arr = [
        'id' => "block_{$block_id}",
        'name' => sanitize_title($block->post_title),
        'data' => [],
        'is_preview' => true,
        'post_id' => $block_id,
    ];

    \ClientBlocks\Blocks\Registry\BlockRenderer::render(
        $block_arr,
        '',
        true,
        $block_id,
        $block_data,
        sanitize_title($block->post_title)
    );

    $context = \ClientBlocks\Blocks\BlockContext::get_context();
    echo '<script id="block-context" type="application/json">';
    echo json_encode($context, JSON_PRETTY_PRINT);
    echo '</script>';

endwhile;

wp_footer();
?>
</body>
</html>
