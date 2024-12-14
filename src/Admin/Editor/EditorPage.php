<?php
namespace ClientBlocks\Admin\Editor;

class EditorPage {
    private static $instance = null;
    
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_editor_assets']);
        add_filter('post_row_actions', [$this, 'add_editor_link'], 10, 2);
    }
    
    public function enqueue_editor_assets() {
        if (!$this->is_editor_page()) {
            return;
        }
        
        // Enqueue styles
        wp_enqueue_style('client-blocks-editor', CLIENT_BLOCKS_URL . 'assets/css/editor.css', [], filemtime(CLIENT_BLOCKS_PATH . 'assets/css/editor.css'));
        wp_enqueue_style('client-blocks-header', CLIENT_BLOCKS_URL . 'assets/css/components/header.css', [], filemtime(CLIENT_BLOCKS_PATH . 'assets/css/components/header.css'));
        wp_enqueue_style('client-blocks-sidebar', CLIENT_BLOCKS_URL . 'assets/css/components/sidebar.css', [], filemtime(CLIENT_BLOCKS_PATH . 'assets/css/components/sidebar.css'));
        wp_enqueue_style('client-blocks-preview', CLIENT_BLOCKS_URL . 'assets/css/components/preview.css', [], filemtime(CLIENT_BLOCKS_PATH . 'assets/css/components/preview.css'));
        wp_enqueue_style('client-blocks-breakpoints', CLIENT_BLOCKS_URL . 'assets/css/components/breakpoints.css', [], filemtime(CLIENT_BLOCKS_PATH . 'assets/css/components/breakpoints.css'));

        // Enqueue Monaco Editor
        wp_enqueue_script('monaco-loader', 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs/loader.min.js', [], '0.44.0', true);
        
        // Enqueue editor scripts
        wp_enqueue_script('client-blocks-editor', CLIENT_BLOCKS_URL . 'assets/js/editor.js', ['jquery'], filemtime(CLIENT_BLOCKS_PATH . 'assets/js/editor.js'), true);
        wp_enqueue_script('client-blocks-breakpoints', CLIENT_BLOCKS_URL . 'assets/js/breakpoints.js', ['jquery', 'client-blocks-editor'], filemtime(CLIENT_BLOCKS_PATH . 'assets/js/breakpoints.js'), true);
        wp_enqueue_script('client-blocks-preview', CLIENT_BLOCKS_URL . 'assets/js/preview.js', ['jquery', 'client-blocks-editor', 'client-blocks-breakpoints'], filemtime(CLIENT_BLOCKS_PATH . 'assets/js/preview.js'), true);
        
        // Localize script
        wp_localize_script('client-blocks-editor', 'clientBlocksEditor', [
            'restUrl' => rest_url('client-blocks/v1'),
            'nonce' => wp_create_nonce('wp_rest'),
            'blockId' => $_GET['block_id'] ?? null,
            'breakpoints' => BreakpointManager::instance()->get_breakpoints(),
            'monacoPath' => 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs'
        ]);
    }
    
    public function add_editor_link($actions, $post) {
        if ($post->post_type === 'client_blocks') {
            $actions['open_editor'] = sprintf(
                '<a href="%s" class="open-editor">%s</a>',
                admin_url('admin.php?page=client-blocks-editor&block_id=' . $post->ID),
                esc_html__('Open in Editor', 'client-blocks')
            );
        }
        return $actions;
    }
    
    private function is_editor_page() {
        return isset($_GET['page']) && $_GET['page'] === 'client-blocks-editor';
    }
}
