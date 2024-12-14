<!DOCTYPE html>
<html <?php language_attributes();?>>
<head>
    <meta charset="<?php bloginfo('charset');?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Block Preview</title>
    <?php wp_head();?>
    <style>
        body {
            margin: 0;
            padding: 20px;
            background: #f0f0f0;
            min-height: 100vh;
        }

        #wpadminbar {
            display: none;
        }

        .preview-container {
            max-width: 1200px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="client-blocks-preview-page">
    <div class="preview-container">
        <?php
// Get block data
$block = get_post($block->ID);
$block_data = [
    'php' => get_post_meta($block->ID, '_client_php', true),
    'template' => get_post_meta($block->ID, '_client_template', true),
    'js' => get_post_meta($block->ID, '_client_js', true),
    'css' => get_post_meta($block->ID, '_client_css', true),
];

// Create block array for rendering
$block_arr = [
    'id' => "block_{$block->ID}",
    'name' => sanitize_title($block->post_title),
    'data' => [],
    'is_preview' => true,
    'post_id' => $block->ID,
];

// Render block using BlockRenderer
\ClientBlocks\Blocks\Registry\BlockRenderer::render(
    $block_arr,
    '',
    true,
    $block->ID,
    $block_data,
    sanitize_title($block->post_title)
);
?>
    </div>
    <?php wp_footer();?>
</body>
</html>