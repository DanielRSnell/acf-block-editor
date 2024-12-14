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
        
        // Enqueue Monaco Editor
        wp_enqueue_script(
            'monaco-loader',
            'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs/loader.min.js',
            [],
            '0.44.0',
            true
        );
        
        // Enqueue Ionic Icons
        wp_enqueue_script(
            'ionicons',
            'https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js',
            [],
            '7.1.0',
            true
        );
        
        // Enqueue editor styles
        wp_enqueue_style(
            'client-blocks-editor',
            plugins_url('assets/css/editor.css', dirname(dirname(__DIR__))),
            [],
            filemtime(dirname(dirname(dirname(__DIR__))) . '/assets/css/editor.css')
        );
        
        // Enqueue breakpoints styles
        wp_enqueue_style(
            'client-blocks-breakpoints',
            plugins_url('assets/css/components/breakpoints.css', dirname(dirname(__DIR__))),
            ['client-blocks-editor'],
            filemtime(dirname(dirname(dirname(__DIR__))) . '/assets/css/components/breakpoints.css')
        );
        
        // Enqueue editor scripts
        wp_enqueue_script(
            'client-blocks-editor',
            plugins_url('assets/js/editor.js', dirname(dirname(__DIR__))),
            ['jquery'],
            filemtime(dirname(dirname(dirname(__DIR__))) . '/assets/js/editor.js'),
            true
        );
        
        // Enqueue breakpoints script
        wp_enqueue_script(
            'client-blocks-breakpoints',
            plugins_url('assets/js/breakpoints.js', dirname(dirname(__DIR__))),
            ['jquery', 'client-blocks-editor'],
            filemtime(dirname(dirname(dirname(__DIR__))) . '/assets/js/breakpoints.js'),
            true
        );
        
        // Enqueue preview script
        wp_enqueue_script(
            'client-blocks-preview',
            plugins_url('assets/js/preview.js', dirname(dirname(__DIR__))),
            ['jquery', 'client-blocks-editor', 'client-blocks-breakpoints'],
            filemtime(dirname(dirname(dirname(__DIR__))) . '/assets/js/preview.js'),
            true
        );
        
        // Localize script
        wp_localize_script('client-blocks-editor', 'clientBlocksEditor', [
            'restUrl' => rest_url('client-blocks/v1'),
            'nonce' => wp_create_nonce('wp_rest'),
            'blockId' => $_GET['block_id'] ?? null,
            'breakpoints' => BreakpointManager::instance()->get_breakpoints()
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
