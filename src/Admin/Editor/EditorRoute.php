<?php
namespace ClientBlocks\Admin\Editor;

class EditorRoute {
    private static $instance = null;
    
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', [$this, 'add_rewrite_rules']);
        add_filter('query_vars', [$this, 'add_query_vars']);
        add_action('parse_request', [$this, 'handle_custom_routes']);
        add_action('admin_init', [$this, 'flush_rewrite_rules_if_needed']);
    }
    
    public function add_rewrite_rules() {
        add_rewrite_rule(
            '^client-blocks/editor/([0-9]+)/?$',
            'index.php?client_blocks_editor=1&block_id=$matches[1]',
            'top'
        );
        
        add_rewrite_rule(
            '^client-blocks/preview/([0-9]+)/?$',
            'index.php?client_blocks_preview=1&block_id=$matches[1]',
            'top'
        );
    }
    
    public function add_query_vars($vars) {
        $vars[] = 'client_blocks_editor';
        $vars[] = 'client_blocks_preview';
        $vars[] = 'block_id';
        return $vars;
    }
    
    public function handle_custom_routes($wp) {
        if (!empty($wp->query_vars['client_blocks_editor']) || !empty($wp->query_vars['client_blocks_preview'])) {
            if (!current_user_can('edit_posts')) {
                wp_die('Unauthorized access');
            }
            
            $block_id = $wp->query_vars['block_id'];
            
            if (!empty($wp->query_vars['client_blocks_editor'])) {
                $this->render_editor_template($block_id);
                exit;
            }
            
            if (!empty($wp->query_vars['client_blocks_preview'])) {
                $this->render_preview_template($block_id);
                exit;
            }
        }
    }
    
    private function render_editor_template($block_id) {
        $block = get_post($block_id);
        if (!$block || $block->post_type !== 'client_blocks') {
            wp_die('Invalid block ID');
        }
        
        // Ensure jQuery is loaded
        wp_enqueue_script('jquery');
        
        // Load editor template
        include_once dirname(dirname(dirname(dirname(__FILE__)))) . '/views/editor/layout.php';
    }
    
    private function render_preview_template($block_id) {
        $block = get_post($block_id);
        if (!$block || $block->post_type !== 'client_blocks') {
            wp_die('Invalid block ID');
        }
        
        // Load preview template
        include_once dirname(dirname(dirname(dirname(__FILE__)))) . '/views/editor/preview.php';
    }
    
    public function flush_rewrite_rules_if_needed() {
        if (get_option('client_blocks_flush_rewrite_rules', false)) {
            flush_rewrite_rules();
            delete_option('client_blocks_flush_rewrite_rules');
        }
    }
}
