// Editor Module
const ClientBlocksEditor = (function($) {
    // State
    let editor;
    let currentTab = 'php';
    let blockData = {};
    
    // Configuration
    const config = {
        monacoPath: 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs',
        editorOptions: {
            value: '',
            language: 'php',
            theme: 'vs-dark',
            minimap: { enabled: true },
            automaticLayout: true,
            fontSize: 14,
            lineNumbers: 'on',
            scrollBeyondLastLine: false,
            wordWrap: 'on',
            formatOnPaste: true,
            formatOnType: true,
            wrappingIndent: 'indent'
        }
    };
    
    // Language configuration
    const languageConfig = {
        php: 'php',
        template: 'html',
        css: 'css',
        js: 'javascript'
    };
    
    // DOM Elements
    const elements = {
        editor: '#monaco-editor',
        preview: '#preview-frame',
        saveButton: '#save-block',
        tabs: '.tab-button'
    };
    
    // API Methods
    const api = {
        loadBlock: async () => {
            try {
                const response = await $.ajax({
                    url: `${clientBlocksEditor.restUrl}/blocks/${clientBlocksEditor.blockId}`,
                    headers: { 'X-WP-Nonce': clientBlocksEditor.nonce }
                });
                
                blockData = response;
                updateEditor();
                
            } catch (error) {
                console.error('Error loading block data:', error);
            }
        },
        
        saveBlock: async () => {
            const $saveButton = $(elements.saveButton);
            const originalText = $saveButton.text();
            
            try {
                $saveButton.prop('disabled', true).text('Saving...');
                
                await $.ajax({
                    url: `${clientBlocksEditor.restUrl}/blocks/${clientBlocksEditor.blockId}`,
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': clientBlocksEditor.nonce
                    },
                    data: JSON.stringify({
                        [`client_${currentTab}`]: editor.getValue()
                    })
                });
                
                $saveButton.text('Saved!');
                setTimeout(() => {
                    $saveButton.prop('disabled', false).text(originalText);
                }, 2000);
                
                $(elements.preview)[0].contentWindow.location.reload();
                
            } catch (error) {
                console.error('Error saving block data:', error);
                $saveButton.text('Error Saving').addClass('error');
                setTimeout(() => {
                    $saveButton.prop('disabled', false)
                             .text(originalText)
                             .removeClass('error');
                }, 3000);
            }
        }
    };
    
    // Editor Methods
    const updateEditor = () => {
        if (!editor || !blockData.fields) return;
        
        const language = languageConfig[currentTab] || currentTab;
        const model = editor.getModel();
        
        monaco.editor.setModelLanguage(model, language);
        editor.setValue(blockData.fields[currentTab] || '');
    };
    
    // Event Handlers
    const handleTabClick = function() {
        const $tab = $(this);
        const newTab = $tab.data('tab');
        
        if (currentTab === newTab) return;
        
        $(elements.tabs).removeClass('active');
        $tab.addClass('active');
        
        if (editor) {
            blockData.fields[currentTab] = editor.getValue();
        }
        
        currentTab = newTab;
        updateEditor();
    };
    
    // Initialize
    const init = () => {
        require.config({ paths: { vs: config.monacoPath }});
        
        require(['vs/editor/editor.main'], () => {
            editor = monaco.editor.create(
                $(elements.editor)[0],
                config.editorOptions
            );
            
            api.loadBlock();
            
            $(elements.tabs).on('click', handleTabClick);
            $(elements.saveButton).on('click', api.saveBlock);
            
            $(document).on('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                    e.preventDefault();
                    api.saveBlock();
                }
            });
        });
    };
    
    return {
        init: init
    };
    
})(jQuery);

jQuery(document).ready(function() {
    ClientBlocksEditor.init();
});