<?php
namespace ClientBlocks\Blocks;

class BlockDefaults {
    public static function get_default_php() {
        return <<<'PHP'
<?php
// Example data
$example_title = get_field('example_title') ?: 'Example Title Not Registered Yet';
$content = get_field('content') ?: 'This is an example block with some default content.';
$items = get_field('items') ?: ['Item 1', 'Item 2', 'Item 3'];

// Return context for the template
return [
    'example_title' => $example_title,
    'content' => $content,
    'items' => $items
];
PHP;
    }
    
    public static function get_default_template() {
        return <<<'TWIG'
<div class="example-block">
    <h2 class="block-title">{{ example_title }}</h2>
    <div class="block-content">
        <p>{{ content }}</p>
        {% if items %}
            <ul class="block-items">
                {% for item in items %}
                    <li class="block-item">{{ item }}</li>
                {% endfor %}
            </ul>
        {% endif %}
        {% if block.inner_blocks %}
            <div class="block-inner-content">
                {{ block.inner_blocks | raw }}
            </div>
        {% endif %}
    </div>
</div>
TWIG;
    }
    
    public static function get_default_css() {
        return <<<'CSS'
.example-block {
    padding: 2rem;
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.block-title {
    font-size: 1.5rem;
    color: #333333;
    margin: 0 0 1rem;
}

.block-content {
    color: #666666;
}

.block-items {
    list-style: none;
    padding: 0;
    margin: 1rem 0;
}

.block-item {
    padding: 0.75rem 1rem;
    background: #f5f5f5;
    margin-bottom: 0.5rem;
    border-radius: 4px;
}

.block-inner-content {
    margin-top: 2rem;
    padding: 1rem;
    border: 1px dashed #ccc;
    border-radius: 4px;
}
CSS;
    }
    
    public static function get_default_js() {
        return <<<'JS'
document.addEventListener('DOMContentLoaded', function() {
    const block = document.getElementById('{{ block.id }}');
    if (!block) return;
    
    const items = block.querySelectorAll('.block-item');
    items.forEach(item => {
        item.addEventListener('click', function() {
            this.style.background = '#e0e0e0';
        });
    });
});
JS;
    }
}