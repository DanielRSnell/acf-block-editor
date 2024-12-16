<?php
namespace ClientBlocks;

use ClientBlocks\Blocks\Registry\BlockRegistrar;
use ClientBlocks\Blocks\Registry\CategoryRegistrar;
use ClientBlocks\Admin\Editor\EditorPage;
use ClientBlocks\Admin\Editor\BreakpointManager;

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
        
        register_activation_hook(__FILE__, function() {
            add_option('client_blocks_flush_rewrite_rules', true);
        });
    }
    
    private function init_modules() {
        PostType\BlockPostType::instance();
        Taxonomy\BlockCategory::instance();
        Admin\MetaBoxes::instance();
        Admin\Menu::instance();
        Admin\Assets::instance();
        
        BlockRegistrar::instance();
        CategoryRegistrar::instance();
        
        API\RestController::instance();
        
        EditorPage::instance();
        
        BreakpointManager::instance();
    }
}
