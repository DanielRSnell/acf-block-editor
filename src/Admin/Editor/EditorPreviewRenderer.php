<?php
namespace ClientBlocks\Admin\Editor;

use ClientBlocks\Blocks\BlockContext;
use ClientBlocks\Blocks\BlockRenderer;
use Timber\Timber;

class EditorPreviewRenderer {
    public static function render($block_id) {
        $block = get_post($block_id);
        if (!$block || $block->post_type !== 'client_blocks') {
            return new \WP_Error('invalid_block', 'Invalid block ID');
        }

        $block_data = [
            'php' => get_post_meta($block->ID, '_client_php', true),
            'template' => get_post_meta($block->ID, '_client_template', true),
            'js' => get_post_meta($block->ID, '_client_js', true),
            'css' => get_post_meta($block->ID, '_client_css', true),
        ];

        $block_arr = [
            'id' => "block_{$block->ID}",
            'name' => sanitize_title($block->post_title),
            'data' => [],
            'is_preview' => true,
            'post_id' => $block->ID,
        ];

        $context = BlockContext::get_context();

        if (!empty($block_data['php'])) {
            $php_context = BlockRenderer::execute_php($block_data['php'], $block_arr);
            if (is_array($php_context)) {
                $context = array_merge($context, $php_context);
            }
        }

        $context['block'] = array_merge($block_arr, [
            'name' => sanitize_title($block->post_title),
            'post_id' => $block->ID,
            'is_preview' => true,
            'inner_blocks' => ''
        ]);

        $context['fields'] = get_fields($block->ID);

        $context['attributes'] = [];

        $template = self::prepare_template($block_data, $block_arr);

        $rendered_content = Timber::compile_string($template, $context);

        return [
            'content' => $rendered_content,
            'context' => $context
        ];
    }

    private static function prepare_template($block_data, $block) {
        $template = $block_data['template'];

        $template = '<div id="block-' . esc_attr($block['id']) . '" class="client-block">' . $template . '</div>';

        if (!empty($block_data['css'])) {
            $css = str_replace('.example-block', '#block-' . esc_attr($block['id']), $block_data['css']);
            $template = '<style>' . $css . '</style>' . $template;
        }

        if (!empty($block_data['js'])) {
            $js = str_replace('{{ block.id }}', 'block-' . esc_attr($block['id']), $block_data['js']);
            $template .= '<script>' . $js . '</script>';
        }

        return $template;
    }
}
