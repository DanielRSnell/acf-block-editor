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
        
        wp_enqueue_style('client-blocks-editor', CLIENT_BLOCKS_URL . 'assets/css/editor.css', [], filemtime(CLIENT_BLOCKS_PATH . 'assets/css/editor.css'));
        wp_enqueue_style('client-blocks-header', CLIENT_BLOCKS_URL . 'assets/css/components/header.css', [], filemtime(CLIENT_BLOCKS_PATH . 'assets/css/components/header.css'));
        wp_enqueue_style('client-blocks-sidebar', CLIENT_BLOCKS_URL . 'assets/css/components/sidebar.css', [], filemtime(CLIENT_BLOCKS_PATH . 'assets/css/components/sidebar.css'));
        wp_enqueue_style('client-blocks-preview', CLIENT_BLOCKS_URL . 'assets/css/components/preview.css', [], filemtime(CLIENT_BLOCKS_PATH . 'assets/css/components/preview.css'));
        wp_enqueue_style('client-blocks-breakpoints', CLIENT_BLOCKS_URL . 'assets/css/components/breakpoints.css', [], filemtime(CLIENT_BLOCKS_PATH . 'assets/css/components/breakpoints.css'));
        wp_enqueue_style('client-blocks-topbar', CLIENT_BLOCKS_URL . 'assets/css/components/topbar.css', [], filemtime(CLIENT_BLOCKS_PATH . 'assets/css/components/topbar.css'));
        wp_enqueue_style('client-blocks-monaco', CLIENT_BLOCKS_URL . 'assets/css/components/monaco.css', [], filemtime(CLIENT_BLOCKS_PATH . 'assets/css/components/monaco.css'));
        wp_enqueue_style('client-blocks-containers', CLIENT_BLOCKS_URL . 'assets/css/components/containers.css', [], filemtime(CLIENT_BLOCKS_PATH . 'assets/css/components/containers.css'));

        wp_enqueue_script('monaco-loader', 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs/loader.min.js', [], '0.44.0', true);
        
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('lodash', 'https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js', [], '4.17.21', true);
        
        wp_enqueue_script('client-blocks-editor-config', CLIENT_BLOCKS_URL . 'assets/js/editor/config.js', ['jquery'], filemtime(CLIENT_BLOCKS_PATH . 'assets/js/editor/config.js'), true);
        wp_enqueue_script('client-blocks-editor-status', CLIENT_BLOCKS_URL . 'assets/js/editor/status.js', ['jquery', 'client-blocks-editor-config'], filemtime(CLIENT_BLOCKS_PATH . 'assets/js/editor/status.js'), true);
        wp_enqueue_script('client-blocks-editor-preview', CLIENT_BLOCKS_URL . 'assets/js/editor/preview.js', ['jquery', 'client-blocks-editor-status'], filemtime(CLIENT_BLOCKS_PATH . 'assets/js/editor/preview.js'), true);
        wp_enqueue_script('client-blocks-editor-api', CLIENT_BLOCKS_URL . 'assets/js/editor/api.js', ['jquery', 'client-blocks-editor-preview'], filemtime(CLIENT_BLOCKS_PATH . 'assets/js/editor/api.js'), true);
        wp_enqueue_script('client-blocks-editor', CLIENT_BLOCKS_URL . 'assets/js/editor.js', ['jquery', 'lodash', 'client-blocks-editor-api'], filemtime(CLIENT_BLOCKS_PATH . 'assets/js/editor.js'), true);
        
        wp_enqueue_script('client-blocks-breakpoints', CLIENT_BLOCKS_URL . 'assets/js/breakpoints.js', ['jquery', 'client-blocks-editor'], filemtime(CLIENT_BLOCKS_PATH . 'assets/js/breakpoints.js'), true);
        wp_enqueue_script('client-blocks-preview', CLIENT_BLOCKS_URL . 'assets/js/preview.js', ['jquery', 'client-blocks-editor', 'client-blocks-breakpoints'], filemtime(CLIENT_BLOCKS_PATH . 'assets/js/preview.js'), true);
        
        wp_localize_script('client-blocks-editor', 'clientBlocksEditor', [
            'restUrl' => rest_url('client-blocks/v1'),
            'nonce' => wp_create_nonce('wp_rest'),
            'blockId' => $_GET['block_id'] ?? null,
            'breakpoints' => BreakpointManager::instance()->get_breakpoints(),
            'monacoPath' => 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs'
        ]);

        if (function_exists('acf_form_head')) {
            acf_form_head();
        }
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
    
    public function render_editor_page() {
        $block_id = isset($_GET['block_id']) ? intval($_GET['block_id']) : 0;
        $block = get_post($block_id);
        
        if (!$block || $block->post_type !== 'client_blocks') {
            wp_die('Invalid block ID');
        }
        
        include_once dirname(dirname(dirname(__FILE__))) . '/views/editor/layout.php';
    }
    
    private function is_editor_page() {
        return isset($_GET['page']) && $_GET['page'] === 'client-blocks-editor';
    }
}
