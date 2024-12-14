<?php
namespace ClientBlocks\Blocks\Support;

class SupportMetaBox {
    private static $instance = null;
    
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('add_meta_boxes', [$this, 'register']);
        add_action('save_post_client_blocks', [$this, 'save']);
    }
    
    public function register() {
        add_meta_box(
            'block_support_options',
            'Block Support Options',
            [$this, 'render'],
            'client_blocks',
            'side',
            'default'
        );
    }
    
    public function render($post) {
        wp_nonce_field('block_support_options', 'block_support_options_nonce');
        
        $options = [
            'supports_inner_blocks' => 'Support Inner Blocks',
            'supports_align' => 'Support Block Alignment',
            'supports_align_text' => 'Support Text Alignment',
            'supports_align_content' => 'Support Content Alignment'
        ];
        
        foreach ($options as $key => $label) {
            $value = get_post_meta($post->ID, '_' . $key, true);
            printf(
                '<p><label><input type="checkbox" name="%s" value="1" %s> %s</label></p>',
                esc_attr($key),
                checked($value, '1', false),
                esc_html($label)
            );
        }
    }
    
    public function save($post_id) {
        if (!$this->can_save($post_id)) {
            return;
        }
        
        $options = [
            'supports_inner_blocks',
            'supports_align',
            'supports_align_text',
            'supports_align_content'
        ];
        
        foreach ($options as $option) {
            update_post_meta(
                $post_id,
                '_' . $option,
                isset($_POST[$option]) ? '1' : '0'
            );
        }
    }
    
    private function can_save($post_id) {
        if (!isset($_POST['block_support_options_nonce'])) {
            return false;
        }
        
        if (!wp_verify_nonce($_POST['block_support_options_nonce'], 'block_support_options')) {
            return false;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return false;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return false;
        }
        
        return true;
    }
}
