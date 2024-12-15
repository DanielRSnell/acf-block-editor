const ClientBlocksEditor = (function($) {
    let editor;
    let currentTab = 'php';
    let blockData = {};
    let contextEditor;
    
    const editorStore = {
        php: '',
        template: '',
        css: '',
        js: ''
    };
    
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
        },
        contextEditorOptions: {
            value: '',
            language: 'json',
            theme: 'vs-dark',
            readOnly: true,
            minimap: { enabled: false },
            automaticLayout: true,
            fontSize: 14,
            lineNumbers: 'on',
            scrollBeyondLastLine: false,
            wordWrap: 'on'
        }
    };
    
    const languageConfig = {
        php: 'php',
        template: 'html',
        css: 'css',
        js: 'javascript',
        context: 'json'
    };
    
    const elements = {
        editor: '#monaco-editor',
        preview: '#preview-frame',
        saveButton: '#save-block',
        tabs: '.tab-button'
    };
    
    const unescapeHTML = (escapedString) => {
        const textarea = document.createElement('textarea');
        textarea.innerHTML = escapedString;
        return textarea.value;
    };
    
    const api = {
        loadBlock: async () => {
            try {
                const response = await $.ajax({
                    url: `${clientBlocksEditor.restUrl}/blocks/${clientBlocksEditor.blockId}`,
                    headers: { 'X-WP-Nonce': clientBlocksEditor.nonce }
                });
                
                for (const field in response.fields) {
                    response.fields[field] = unescapeHTML(response.fields[field]);
                    if (editorStore.hasOwnProperty(field)) {
                        editorStore[field] = response.fields[field];
                    }
                }
                
                blockData = response;
                updateEditor();
                updatePreviewWithBlockData();
                
            } catch (error) {
                console.error('Error loading block data:', error);
            }
        },
        
        saveBlock: async () => {
            const $saveButton = $(elements.saveButton);
            const originalText = $saveButton.text();
            
            try {
                $saveButton.prop('disabled', true).text('Saving...');
                
                const dataToSave = {
                    [`client_${currentTab}`]: editorStore[currentTab]
                };
                
                const response = await $.ajax({
                    url: `${clientBlocksEditor.restUrl}/blocks/${clientBlocksEditor.blockId}`,
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': clientBlocksEditor.nonce
                    },
                    data: JSON.stringify(dataToSave)
                });
                
                if (response.fields[`client_${currentTab}`] !== dataToSave[`client_${currentTab}`]) {
                    throw new Error('Data mismatch');
                }
                
                $saveButton.text('Saved!');
                setTimeout(() => {
                    $saveButton.prop('disabled', false).text(originalText);
                }, 2000);
                
                updatePreviewWithBlockData();
                
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
    
    const updateEditor = () => {
        if (!editor || !blockData.fields) return;
        
        const language = languageConfig[currentTab] || currentTab;
        const model = editor.getModel();
        
        monaco.editor.setModelLanguage(model, language);
        editor.setValue(editorStore[currentTab] || '');
    };
    
    const updateContextEditor = (context) => {
        if (contextEditor) {
            contextEditor.setValue(JSON.stringify(context, null, 2));
        }
    };
    
    const handleTabClick = function() {
        const $tab = $(this);
        const newTab = $tab.data('tab');
        
        if (currentTab === newTab) return;
        
        $(elements.tabs).removeClass('active');
        $tab.addClass('active');
        
        currentTab = newTab;
        
        if (currentTab === 'context') {
            $(elements.editor).hide();
            $('#context-editor').show();
        } else {
            $(elements.editor).show();
            $('#context-editor').hide();
            updateEditor();
        }
    };
    
    const updatePreviewWithBlockData = () => {
        const iframe = document.getElementById('preview-frame');
        
        const postContextScript = iframe.contentDocument.getElementById('post-context');
        if (postContextScript) {
            postContextScript.textContent = JSON.stringify(blockData.timber_context || {}, null, 2);
        }

        const mockFieldsScript = iframe.contentDocument.getElementById('mock-fields');
        if (mockFieldsScript) {
            mockFieldsScript.textContent = JSON.stringify(blockData.acf || {}, null, 2);
        }
        
        const blockContextScript = iframe.contentDocument.getElementById('block-context');
        if (blockContextScript) {
            const blockContext = {
                id: blockData.id,
                name: blockData.slug,
                data: {},
                is_preview: true,
                post_id: blockData.id,
                ...editorStore
            };
            blockContextScript.textContent = JSON.stringify(blockContext, null, 2);
        }
        
        updatePreview();
    };
    
    const updatePreview = async () => {
        const $saveButton = $(elements.saveButton);
        const originalText = $saveButton.text();
        
        try {
            $saveButton.prop('disabled', true).text('Updating preview...');
            
            const iframe = document.getElementById('preview-frame');
            const postContext = JSON.parse(iframe.contentDocument.getElementById('post-context').textContent);
            const mockFields = JSON.parse(iframe.contentDocument.getElementById('mock-fields').textContent);
            const blockContext = JSON.parse(iframe.contentDocument.getElementById('block-context').textContent);

            const currentEditorContent = window.getClientBlocksEditorContent();

            const previewData = {
                block_id: clientBlocksEditor.blockId,
                post_context: JSON.stringify(postContext),
                mock_fields: JSON.stringify(mockFields),
                block_context: JSON.stringify({
                    ...blockContext,
                    ...currentEditorContent
                })
            };
            
            const response = await $.ajax({
                url: `${clientBlocksEditor.restUrl}/preview`,
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': clientBlocksEditor.nonce
                },
                data: JSON.stringify(previewData)
            });
            
            const editorContent = iframe.contentDocument.getElementById('editor-content');
            if (editorContent) {
                editorContent.innerHTML = response.content;
            }
            
            updateContextEditor(response.context);
            
            $saveButton.text('Preview updated');
            setTimeout(() => {
                $saveButton.prop('disabled', false).text(originalText);
            }, 2000);
            
        } catch (error) {
            console.error('Error updating preview:', error);
            $saveButton.text('Error updating preview').addClass('error');
            setTimeout(() => {
                $saveButton.prop('disabled', false)
                         .text(originalText)
                         .removeClass('error');
            }, 3000);
        }
    };
    
    const debouncedUpdatePreview = _.debounce(updatePreview, 1000);
    
    const init = () => {
        require.config({ paths: { vs: config.monacoPath }});
        
        require(['vs/editor/editor.main'], () => {
            editor = monaco.editor.create(
                $(elements.editor)[0],
                config.editorOptions
            );
            
            const contextEditorContainer = document.createElement('div');
            contextEditorContainer.id = 'context-editor';
            contextEditorContainer.style.width = '100%';
            contextEditorContainer.style.height = '100%';
            contextEditorContainer.style.display = 'none';
            $(elements.editor).after(contextEditorContainer);
            
            contextEditor = monaco.editor.create(
                contextEditorContainer,
                config.contextEditorOptions
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
            
            $(elements.preview).on('load', updateContextEditor);
            
            $(elements.saveButton).on('click', async (e) => {
                e.preventDefault();
                await updatePreview();
            });
            
            editor.onDidChangeModelContent(() => {
                editorStore[currentTab] = editor.getValue();
                debouncedUpdatePreview();
            });
        });
    };
    
    const getEditorContent = (editorName) => {
        if (editorName) {
            return editorStore[editorName] || null;
        }
        return { ...editorStore };
    };
    
    window.getClientBlocksEditorContent = getEditorContent;
    
    return {
        init: init,
        updatePreview: updatePreview
    };
    
})(jQuery);

jQuery(document).ready(function() {
    ClientBlocksEditor.init();
});
