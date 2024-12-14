<?php
namespace ClientBlocks\API;

use ClientBlocks\Blocks\BlockDefaults;

class BlockEndpoints {
    public static function get_blocks(\WP_REST_Request $request) {
        $blocks = get_posts([
            'post_type' => 'client_blocks',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ]);
        
        return array_map([self::class, 'format_block'], $blocks);
    }
    
    public static function get_block(\WP_REST_Request $request) {
        $block_id = $request->get_param('id');
        $block = get_post($block_id);
        
        if (!$block || $block->post_type !== 'client_blocks') {
            return new \WP_Error(
                'block_not_found',
                'Block not found',
                ['status' => 404]
            );
        }
        
        return self::format_block($block);
    }
    
    public static function update_block(\WP_REST_Request $request) {
        $block_id = $request->get_param('id');
        $block = get_post($block_id);
        
        if (!$block || $block->post_type !== 'client_blocks') {
            return new \WP_Error(
                'block_not_found',
                'Block not found',
                ['status' => 404]
            );
        }
        
        $fields = [
            'client_php',
            'client_template',
            'client_js',
            'client_css'
        ];
        
        foreach ($fields as $field) {
            $value = $request->get_param($field);
            if ($value !== null) {
                update_post_meta($block_id, '_' . $field, $value);
            }
        }
        
        return self::get_block($request);
    }
    
    private static function format_block($block) {
        $categories = wp_get_post_terms($block->ID, 'block_categories');
        
        // Get stored values or defaults
        $php = get_post_meta($block->ID, '_client_php', true);
        $template = get_post_meta($block->ID, '_client_template', true);
        $js = get_post_meta($block->ID, '_client_js', true);
        $css = get_post_meta($block->ID, '_client_css', true);
        
        return [
            'id' => $block->ID,
            'title' => $block->post_title,
            'slug' => $block->post_name,
            'status' => $block->post_status,
            'modified' => $block->post_modified,
            'categories' => array_map(function($term) {
                return [
                    'id' => $term->term_id,
                    'name' => $term->name,
                    'slug' => $term->slug,
                ];
            }, $categories),
            'fields' => [
                'php' => $php ?: BlockDefaults::get_default_php(),
                'template' => $template ?: BlockDefaults::get_default_template(),
                'js' => $js ?: BlockDefaults::get_default_js(),
                'css' => $css ?: BlockDefaults::get_default_css(),
            ],
        ];
    }
}
