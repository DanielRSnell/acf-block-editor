<?php
namespace ClientBlocks\Blocks\Registry;

use ClientBlocks\Blocks\Support\BlockSupports;
use Timber\Timber;

class BlockRegistrar {
    private static $instance = null;
    
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', [$this, 'register_blocks']);
    }
    
    public function register_blocks() {
        $blocks = BlockFetcher::get_blocks();
        
        foreach ($blocks as $block) {
            $this->register_block($block);
        }
    }
    
    private function register_block($block) {
        $block_data = BlockDataProvider::get_block_data($block);
        $block_name = sanitize_title($block->post_title);
        
        acf_register_block_type([
            'name' => $block_name,
            'title' => $block->post_title,
            'description' => $block->post_excerpt,
            'category' => BlockDataProvider::get_block_category($block),
            'icon' => 'screenoptions',
            'keywords' => [],
            'render_callback' => function($block, $content = '', $is_preview = false, $post_id = 0) use ($block_data, $block_name) {
                BlockRenderer::render($block, $content, $is_preview, $post_id, $block_data, $block_name);
            },
            'supports' => BlockSupports::get_supports($block)
        ]);
    }
}