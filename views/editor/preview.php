<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Block Preview</title>
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
    <div id="editor-content"></div>
    <script id="post-context" type="application/json"></script>
    <script id="mock-fields" type="application/json"></script>
    <script id="block-context" type="application/json"></script>
    <script id="timber-context" type="application/json"></script>
    <?php wp_footer(); ?>
</body>
</html>
