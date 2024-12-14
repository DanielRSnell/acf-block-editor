<?php
namespace ClientBlocks\Blocks;

class BlockPreview {
    public static function render($content, $block) {
        $preview_styles = self::get_preview_styles();
        $preview_bar = self::get_preview_bar($block);
        
        echo $preview_styles;
        echo $preview_bar;
        echo '<div class="client-blocks-preview-content">';
        echo $content;
        echo '</div>';
    }
    
    private static function get_preview_styles() {
        return '
            <style>
                .client-blocks-preview-bar {
                    background: #1e1e1e;
                    color: #fff;
                    padding: 8px 12px;
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
                    font-size: 12px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    border-radius: 4px 4px 0 0;
                }
                .client-blocks-preview-bar code {
                    background: rgba(255, 255, 255, 0.1);
                    padding: 2px 6px;
                    border-radius: 3px;
                    margin: 0 4px;
                    font-family: Monaco, Consolas, "Andale Mono", "DejaVu Sans Mono", monospace;
                }
                .client-blocks-preview-content {
                    border: 1px solid #e2e4e7;
                    border-top: none;
                    border-radius: 0 0 4px 4px;
                    padding: 1px;
                }
            </style>
        ';
    }
    
    private static function get_preview_bar($block) {
        return sprintf(
            '<div class="client-blocks-preview-bar">
                <div>
                    <span>Block ID: <code>%s</code></span>
                    <span>Post ID: <code>%s</code></span>
                    <span>Type: <code>%s</code></span>
                </div>
                <div>Preview Mode</div>
            </div>',
            esc_html($block['id']),
            esc_html($block['post_id']),
            esc_html($block['name'])
        );
    }
}