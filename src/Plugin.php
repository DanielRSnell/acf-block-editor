<?php
namespace ClientBlocks;

use ClientBlocks\Blocks\Registry\BlockRegistrar;
use ClientBlocks\Blocks\Registry\CategoryRegistrar;
use ClientBlocks\Blocks\Support\SupportMetaBox;
use ClientBlocks\Admin\Editor\EditorPage;
use ClientBlocks\Admin\Editor\EditorRoute;

class Plugin {
    private static $instance = null;
    
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_modules();
        
        // Set flag to flush rewrite rules
        register_activation_hook(__FILE__, function() {
            add_option('client_blocks_flush_rewrite_rules', true);
        });
    }
    
    private function init_modules() {
        // Initialize core modules
        PostType\BlockPostType::instance();
        Taxonomy\BlockCategory::instance();
        Admin\MetaBoxes::instance();
        Admin\Menu::instance();
        Admin\Assets::instance();
        
        // Initialize block support
        SupportMetaBox::instance();
        
        // Initialize block registration
        BlockRegistrar::instance();
        CategoryRegistrar::instance();
        
        // Initialize REST API
        API\RestController::instance();
        
        // Initialize Editor
        EditorRoute::instance();
        EditorPage::instance();
    }
}