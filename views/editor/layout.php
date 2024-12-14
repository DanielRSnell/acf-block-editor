<!DOCTYPE html>
<html <?php language_attributes();?>>
<head>
    <meta charset="<?php bloginfo('charset');?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($block->post_title); ?> - Block Editor</title>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <?php wp_head();?>
</head>
<body class="client-blocks-editor-page">
    <div class="client-blocks-editor">
        <div class="editor-header">
            <div class="editor-logo">
                <ion-icon name="cube-outline"></ion-icon>
                <span><?php echo esc_html($block->post_title); ?></span>
            </div>
            <div class="breakpoint-controls">
                <?php
$breakpoints = \ClientBlocks\Admin\Editor\BreakpointManager::instance()->get_breakpoints();
foreach ($breakpoints as $breakpoint):
    $title = $breakpoint['width'] ? "{$breakpoint['name']} ({$breakpoint['width']}px)" : $breakpoint['name'];
    ?>
			                    <button type="button"
			                            class="breakpoint-button<?php echo $breakpoint['id'] === 'full' ? ' active' : ''; ?>"
			                            data-breakpoint="<?php echo esc_attr($breakpoint['id']); ?>"
			                            title="<?php echo esc_attr($title); ?>">
			                        <ion-icon name="<?php echo esc_attr($breakpoint['icon']); ?>"></ion-icon>
			                    </button>
			                <?php endforeach;?>
                <button type="button" class="breakpoint-settings" title="Breakpoint Settings">
                    <ion-icon name="settings-outline"></ion-icon>
                </button>
            </div>
            <div class="editor-actions">
                <button type="button" class="button button-primary" id="save-block">Save Changes</button>
            </div>
        </div>

        <div class="editor-container">
            <div class="editor-sidebar">
                <div class="editor-tabs">
                    <button type="button" class="tab-button active" data-tab="php" title="PHP Logic">
                        <ion-icon name="code-slash-outline"></ion-icon>
                    </button>
                    <button type="button" class="tab-button" data-tab="template" title="HTML Template">
                        <ion-icon name="document-text-outline"></ion-icon>
                    </button>
                    <button type="button" class="tab-button" data-tab="css" title="CSS Styles">
                        <ion-icon name="brush-outline"></ion-icon>
                    </button>
                    <button type="button" class="tab-button" data-tab="js" title="JavaScript">
                        <ion-icon name="logo-javascript"></ion-icon>
                    </button>
                </div>
                <div class="editor-settings">
                    <button type="button" class="settings-button" title="Block Settings">
                        <ion-icon name="settings-outline"></ion-icon>
                    </button>
                </div>
            </div>

            <div class="editor-main">
                <div class="editor-pane">
                    <div id="monaco-editor"></div>
                </div>
                <div class="editor-preview">
                    <div class="preview-container" style="width: 100%; height: 100%;">
                        <div class="preview-frame-container" data-breakpoint="full">
                            <iframe id="preview-frame" src="<?php echo esc_url(home_url("client-blocks/preview/{$block->ID}")); ?>" style="max-width: 100%; width: 100%; height: 100%;"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php wp_footer();?>
    <script src="<?php echo plugins_url('assets/js/preview.js', dirname(dirname(dirname(__FILE__)))); ?>"></script>
</body>
</html>
