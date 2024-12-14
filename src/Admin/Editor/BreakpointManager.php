<?php
namespace ClientBlocks\Admin\Editor;

class BreakpointManager {
    private static $instance = null;
    private $upload_dir;
    private $breakpoints_file;
    
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->upload_dir = wp_upload_dir();
        $this->breakpoints_file = $this->upload_dir['basedir'] . '/client-blocks/data/breakpoints.json';
        
        // Create directory if it doesn't exist
        if (!file_exists(dirname($this->breakpoints_file))) {
            wp_mkdir_p(dirname($this->breakpoints_file));
        }
        
        // Create default breakpoints if file doesn't exist
        if (!file_exists($this->breakpoints_file)) {
            $this->save_default_breakpoints();
        }
        
        add_action('rest_api_init', [$this, 'register_rest_routes']);
    }
    
    private function save_default_breakpoints() {
        $default_breakpoints = [
            [
                'id' => 'xs',
                'name' => 'Mobile',
                'width' => 375,
                'icon' => 'phone-portrait-outline'
            ],
            [
                'id' => 'sm',
                'name' => 'Small Tablet',
                'width' => 640,
                'icon' => 'phone-landscape-outline'
            ],
            [
                'id' => 'md',
                'name' => 'Tablet',
                'width' => 768,
                'icon' => 'tablet-landscape-outline'
            ],
            [
                'id' => 'lg',
                'name' => 'Laptop',
                'width' => 1024,
                'icon' => 'laptop-outline'
            ],
            [
                'id' => 'full',
                'name' => 'Full Width',
                'width' => null,
                'icon' => 'expand-outline'
            ]
        ];
        
        file_put_contents($this->breakpoints_file, json_encode($default_breakpoints, JSON_PRETTY_PRINT));
    }
    
    public function get_breakpoints() {
        if (!file_exists($this->breakpoints_file)) {
            $this->save_default_breakpoints();
        }
        
        $breakpoints = json_decode(file_get_contents($this->breakpoints_file), true);
        return $breakpoints ?: [];
    }
    
    public function save_breakpoints($breakpoints) {
        file_put_contents($this->breakpoints_file, json_encode($breakpoints, JSON_PRETTY_PRINT));
    }
    
    public function register_rest_routes() {
        register_rest_route('client-blocks/v1', '/breakpoints', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_breakpoints_endpoint'],
                'permission_callback' => '__return_true'
            ],
            [
                'methods' => 'POST',
                'callback' => [$this, 'update_breakpoints_endpoint'],
                'permission_callback' => function() {
                    return current_user_can('manage_options');
                }
            ]
        ]);
    }
    
    public function get_breakpoints_endpoint() {
        return rest_ensure_response($this->get_breakpoints());
    }
    
    public function update_breakpoints_endpoint($request) {
        $breakpoints = $request->get_json_params();
        
        if (!is_array($breakpoints)) {
            return new \WP_Error('invalid_breakpoints', 'Invalid breakpoints data', ['status' => 400]);
        }
        
        $this->save_breakpoints($breakpoints);
        return rest_ensure_response($this->get_breakpoints());
    }
}