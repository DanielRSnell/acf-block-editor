<?php
namespace ClientBlocks\Admin;

use ClientBlocks\Blocks\BlockDefaults;
use ClientBlocks\Utils\Security;

class MetaBoxes
{
    private static $instance = null;
    private $fields = ['client_php', 'client_template', 'client_js', 'client_css'];

    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('add_meta_boxes', [$this, 'register']);
        add_action('save_post_client_blocks', [$this, 'save']);
        add_action('load-post-new.php', [$this, 'maybe_set_defaults']);

        // Prevent sanitization for specific fields
        add_filter('sanitize_meta__client_php', '__return_null');
        add_filter('sanitize_meta__client_js', '__return_null');
    }

    public function register()
    {
        add_meta_box(
            'client_block_fields',
            'Block Content',
            [$this, 'render'],
            'client_blocks',
            'normal',
            'high'
        );
    }

    public function render($post)
    {
        Security::verify_nonce('client_blocks_meta_box');

        $values = [];
        foreach ($this->fields as $field) {
            $values[$field] = get_post_meta($post->ID, '_' . $field, true);
        }

        \Timber::render('admin/meta-boxes.twig', [
            'fields' => $this->fields,
            'values' => $values,
        ]);
    }

    public function save($post_id)
    {
        if (!Security::verify_save_post($post_id, 'client_blocks_meta_box')) {
            return;
        }

        foreach ($this->fields as $field) {
            if (isset($_POST[$field])) {
                $value = $_POST[$field];

                // Skip sanitization for specific fields
                if (in_array($field, ['client_php', 'client_js'], true)) {
                    // Save raw content without sanitization
                    update_post_meta($post_id, '_' . $field, $value);
                } else {
                    // Apply sanitization for other fields
                    update_post_meta($post_id, '_' . $field, wp_kses_post($value));
                }
            }
        }
    }

    public function maybe_set_defaults()
    {
        global $typenow;

        if ($typenow !== 'client_blocks') {
            return;
        }

        add_filter('default_post_metadata', function ($value, $post_id, $meta_key, $single) {
            if (!$single) {
                return $value;
            }

            switch ($meta_key) {
                case '_client_php':
                    return BlockDefaults::get_default_php();
                case '_client_template':
                    return BlockDefaults::get_default_template();
                case '_client_js':
                    return BlockDefaults::get_default_js();
                case '_client_css':
                    return BlockDefaults::get_default_css();
                default:
                    return $value;
            }
        }, 10, 4);
    }
}
