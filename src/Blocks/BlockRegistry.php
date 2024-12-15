<?php
namespace ClientBlocks\Blocks;

use Timber\Timber;

class BlockRegistry
{
    private static $instance = null;

    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('init', [$this, 'register_blocks']);
        add_action('init', [$this, 'register_block_categories']);
    }

    public function register_blocks()
    {
        $blocks = $this->get_blocks();

        foreach ($blocks as $block) {
            $this->register_block($block);
        }
    }

    public function register_block_categories()
    {
        $categories = get_terms([
            'taxonomy' => 'block_categories',
            'hide_empty' => false,
        ]);

        if (empty($categories) || is_wp_error($categories)) {
            return;
        }

        add_filter('block_categories_all', function ($block_categories) use ($categories) {
            foreach ($categories as $category) {
                $block_categories[] = [
                    'slug' => $category->slug,
                    'title' => $category->name,
                    'icon' => null,
                ];
            }
            return $block_categories;
        });
    }

    private function get_blocks()
    {
        return get_posts([
            'post_type' => 'client_blocks',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ]);
    }

    private function register_block($block)
    {
        $block_data = $this->get_block_data($block);
        $block_name = sanitize_title($block->post_title);

        acf_register_block_type([
            'name' => $block_name,
            'title' => $block->post_title,
            'description' => $block->post_excerpt,
            'category' => $this->get_block_category($block),
            'icon' => 'screenoptions',
            'keywords' => [],
            'render_callback' => function ($block, $content = '', $is_preview = false, $post_id = 0) use ($block_data, $block_name) {
                $this->render_block($block, $content, $is_preview, $post_id, $block_data, $block_name);
            },
            'supports' => [
                'mode' => false,
                'jsx' => true,
            ],
        ]);
    }

    private function get_block_data($block)
    {
        return [
            'php' => get_post_meta($block->ID, '_client_php', true) ?: BlockDefaults::get_default_php(),
            'template' => get_post_meta($block->ID, '_client_template', true) ?: BlockDefaults::get_default_template(),
            'js' => get_post_meta($block->ID, '_client_js', true) ?: BlockDefaults::get_default_js(),
            'css' => get_post_meta($block->ID, '_client_css', true) ?: BlockDefaults::get_default_css(),
        ];
    }

    private function get_block_category($block)
    {
        $categories = wp_get_post_terms($block->ID, 'block_categories');
        return !empty($categories) ? $categories[0]->slug : 'common';
    }

    private function render_block($block, $content, $is_preview, $post_id, $block_data, $block_name)
    {
        try {
            $context = [];
            if (!empty($block_data['php'])) {
                $context = BlockRenderer::execute_php($block_data['php'], $block);
            }

            $context['block'] = array_merge($block, [
                'name' => $block_name,
                'post_id' => $post_id,
                'is_preview' => $is_preview,
                'template_id' => $block_data['template_id'],
            ]);

            $template = $this->prepare_template($block_data, $block);

            $rendered_content = Timber::compile_string($template, $context);

            if ($is_preview) {
                BlockPreview::render($rendered_content, $context['block']);
            } else {
                echo $rendered_content;
            }

        } catch (\Exception $e) {
            echo '<div class="notice notice-error"><p>Error rendering block: ' . esc_html($e->getMessage()) . '</p></div>';
        }
    }

    private function prepare_template($block_data, $block)
    {
        $template = $block_data['template'];

        $template = '<div id="block-' . esc_attr($block['id']) . '" class="client-block">' . $template . '</div>';

        if (!empty($block_data['css'])) {
            $css = str_replace('.example-block', '#block-' . esc_attr($block['id']), $block_data['css']);
            $template = '<style>' . $css . '</style>' . $template;
        }

        if (!empty($block_data['js'])) {
            $js = str_replace('{{ block.id }}', 'block-' . esc_attr($block['id']), $block_data['js']);
            $template .= '<script>' . $js . '</script>';
        }

        return $template;
    }

}
