<?php
namespace ClientBlocks\API;

class RestController
{
    private static $instance = null;
    private $namespace = 'client-blocks/v1';

    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes()
    {
        // Block endpoints
        register_rest_route($this->namespace, '/blocks', [
            [
                'methods' => 'GET',
                'callback' => [BlockEndpoints::class, 'get_blocks'],
                'permission_callback' => '__return_true', // Make GET public
            ],
        ]);

        register_rest_route($this->namespace, '/blocks/(?P<id>\d+)', [
            [
                'methods' => 'GET',
                'callback' => [BlockEndpoints::class, 'get_block'],
                'permission_callback' => '__return_true', // Make GET public
            ],
            [
                'methods' => 'POST',
                'callback' => [BlockEndpoints::class, 'update_block'],
                'permission_callback' => [$this, 'check_permission'],
                'args' => $this->get_block_args(),
            ],
        ]);

        // Category endpoints
        register_rest_route($this->namespace, '/categories', [
            [
                'methods' => 'GET',
                'callback' => [CategoryEndpoints::class, 'get_categories'],
                'permission_callback' => '__return_true', // Make GET public
            ],
        ]);
    }

    public function check_permission()
    {
        return current_user_can('edit_posts');
    }

    private function get_block_args()
    {
        return [
            'client_php' => [
                'type' => 'string',
                'required' => false,
                // 'sanitize_callback' => 'wp_kses_post',
            ],
            'client_template' => [
                'type' => 'string',
                'required' => false,
                // 'sanitize_callback' => 'wp_kses_post',
            ],
            'client_js' => [
                'type' => 'string',
                'required' => false,
                // 'sanitize_callback' => 'wp_kses_post',
            ],
            'client_css' => [
                'type' => 'string',
                'required' => false,
                // 'sanitize_callback' => 'wp_kses_post',
            ],
        ];
    }
}
