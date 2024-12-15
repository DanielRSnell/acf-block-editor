<?php
namespace ClientBlocks\Blocks;

use Timber\Timber;

class BlockRenderer {
    public static function render($block, $content = '', $is_preview = false, $post_id = 0) {
        $block_id = $block['id'];
        $block_post = get_post($post_id);
        
        if (!$block_post || $block_post->post_type !== 'client_blocks') {
            return '';
        }

        // Fetch fields directly from the post
        $client_php = get_post_meta($post_id, '_client_php', true);
        $template = get_post_meta($post_id, '_client_template', true);
        $css = get_post_meta($post_id, '_client_css', true);
        $js = get_post_meta($post_id, '_client_js', true);

        // Initialize context
        $context = Timber::context();
        $context['block'] = $block;
        $context['fields'] = get_fields();
        $context['is_preview'] = $is_preview;
        $context['post_id'] = $post_id;
        $context['content'] = $content;

        // Execute PHP
        if (!empty($client_php)) {
            $context = self::execute_php($client_php, $context, $block);
        }

        // Apply the filter to the context
        $context = apply_filters('client_blocks_context', $context, $block);

        // Process CSS
        if (!empty($css)) {
            $processed_css = self::process_php_content($css, $context, $block);
            $template = '<style>' . $processed_css . '</style>' . $template;
        }

        // Process main template
        $processed_template = self::process_php_content($template, $context, $block);

        // Process JS
        if (!empty($js)) {
            $processed_js = self::process_php_content($js, $context, $block);
            $processed_template .= '<script>' . $processed_js . '</script>';
        }

        $rendered_content = Timber::compile_string($processed_template, $context);

        return $rendered_content;
    }

    private static function execute_php($code, $context, $block) {
        extract($context);
        ob_start();
        $result = eval('?>' . $code);
        $output = ob_get_clean();
        
        if (is_array($result)) {
            $context = array_merge($context, $result);
        }
        
        if (!empty($output)) {
            $context['output'] = $output;
        }
        
        return $context;
    }

    private static function process_php_content($content, $context, $block) {
        ob_start();
        extract($context);
        eval('?>' . $content);
        $processed_content = ob_get_clean();
        return $processed_content;
    }
}
