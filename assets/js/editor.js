const ClientBlocksEditor = (function($) {
  let editor;
  let currentTab = 'php';
  let blockData = {};
  let contextEditor;
  
  const editorStore = {
    php: '',
    template: '',
    css: '',
    js: '',
    'global-css': ''
  };
  
  const config = {
    monacoPath: 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs',
    editorOptions: {
      value: '',
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
  
  const languageConfig = {
    php: 'php',
    template: 'html',
    css: 'css',
    js: 'javascript',
    context: 'json',
    'global-css': 'css'
  };
  
  const elements = {
    editor: '#monaco-editor',
    preview: '#preview-frame',
    saveButton: '#save-block',
    tabs: '.tab-button',
    acfForm: '#acf-form-container',
    contextEditor: '#context-editor',
    topBarTitle: '.editor-top-bar-title',
    settingsContainer: '#settings-container'
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
          if (editorStore.hasOwnProperty(field.replace('client_', ''))) {
            editorStore[field.replace('client_', '')] = unescapeHTML(response.fields[field]);
          }
        }
        
        const globalCssResponse = await $.ajax({
          url: `${clientBlocksEditor.restUrl}/global-css`,
          headers: { 'X-WP-Nonce': clientBlocksEditor.nonce }
        });
        
        editorStore['global-css'] = globalCssResponse.css;
        blockData = response;
        updateEditor();
        updatePreviewWithBlockData();
      } catch (error) {
        console.error('Error loading block:', error);
      }
    },

    saveBlock: async () => {
      const $saveButton = $(elements.saveButton);
      const originalText = $saveButton.text();
      
      try {
        $saveButton.prop('disabled', true).text('Saving...');
        
        if (currentTab === 'global-css') {
          await $.ajax({
            url: `${clientBlocksEditor.restUrl}/global-css`,
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-WP-Nonce': clientBlocksEditor.nonce
            },
            data: JSON.stringify({ css: editorStore['global-css'] })
          });
        } else {
          const dataToSave = {
            [`client_${currentTab}`]: editorStore[currentTab]
          };
          
          await $.ajax({
            url: `${clientBlocksEditor.restUrl}/blocks/${clientBlocksEditor.blockId}`,
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-WP-Nonce': clientBlocksEditor.nonce
            },
            data: JSON.stringify(dataToSave)
          });
        }
        
        $saveButton.removeClass('error').text('Saved!');
        setTimeout(() => {
          $saveButton.prop('disabled', false).text(originalText);
        }, 2000);
        
        updatePreviewWithBlockData();
      } catch (error) {
        console.error('Error saving:', error);
        $saveButton.addClass('error').text('Error Saving');
        setTimeout(() => {
          $saveButton.prop('disabled', false).removeClass('error').text(originalText);
        }, 3000);
      }
    }
  };

  const updateEditor = () => {
    if (!editor) return;
    
    const language = languageConfig[currentTab];
    const model = editor.getModel();
    
    monaco.editor.setModelLanguage(model, language);
    editor.setValue(editorStore[currentTab] || '');
  };

  const updateContextEditor = (context) => {
    if (!contextEditor) return;
    contextEditor.setValue(JSON.stringify(context, null, 2));
  };

  const handleTabClick = function() {
    const $tab = $(this);
    const newTab = $tab.data('tab');
    
    if (currentTab === newTab) return;
    
    $(elements.tabs).removeClass('active');
    $tab.addClass('active');
    
    currentTab = newTab;
    
    $(elements.topBarTitle).text($tab.data('title'));
    
    $(elements.editor).hide();
    $(elements.contextEditor).hide();
    $(elements.acfForm).hide();
    $(elements.settingsContainer).hide();
    $(elements.saveButton).show();
    
    if (currentTab === 'acf') {
      $(elements.acfForm).show();
      $(elements.saveButton).hide();
    } else if (currentTab === 'context') {
      $(elements.contextEditor).show();
    } else if (currentTab === 'settings') {
      $(elements.settingsContainer).show();
      $(elements.saveButton).hide();
    } else {
      $(elements.editor).show();
      updateEditor();
    }
  };

  const updatePreviewWithBlockData = () => {
    const iframe = document.getElementById('preview-frame');
    if (!iframe || !iframe.contentDocument) return;
    
    const postContextScript = iframe.contentDocument.getElementById('post-context');
    const mockFieldsScript = iframe.contentDocument.getElementById('mock-fields');
    const blockContextScript = iframe.contentDocument.getElementById('block-context');
    
    if (postContextScript) {
      postContextScript.textContent = JSON.stringify(blockData.timber_context || {}, null, 2);
    }
    
    if (mockFieldsScript) {
      mockFieldsScript.textContent = JSON.stringify(blockData.acf || {}, null, 2);
    }
    
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
      
      const response = await $.ajax({
        url: `${clientBlocksEditor.restUrl}/preview`,
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': clientBlocksEditor.nonce
        },
        data: JSON.stringify({
          block_id: clientBlocksEditor.blockId,
          post_context: JSON.stringify(postContext),
          mock_fields: JSON.stringify(mockFields),
          block_context: JSON.stringify({
            ...blockContext,
            ...window.getClientBlocksEditorContent()
          })
        })
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
      $saveButton.addClass('error').text('Preview update failed');
      setTimeout(() => {
        $saveButton.prop('disabled', false).removeClass('error').text(originalText);
      }, 3000);
    }
  };

  const debouncedUpdatePreview = _.debounce(updatePreview, 1000);

  const init = () => {
    require.config({ paths: { vs: config.monacoPath }});
    
    require(['vs/editor/editor.main'], () => {
      editor = monaco.editor.create($(elements.editor)[0], {
        ...config.editorOptions,
        language: languageConfig[currentTab]
      });
      
      contextEditor = monaco.editor.create($(elements.contextEditor)[0], {
        ...config.editorOptions,
        language: 'json',
        readOnly: true,
        minimap: { enabled: false }
      });
      
      api.loadBlock();
      
      $(elements.tabs).on('click', handleTabClick);
      $(elements.saveButton).on('click', api.saveBlock);

      $(elements.editor).show();
      $(elements.contextEditor).hide();
      $(elements.acfForm).hide();
      $(elements.settingsContainer).hide();
      $(elements.topBarTitle).text('PHP Logic');
      
      $(document).on('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
          e.preventDefault();
          api.saveBlock();
        }
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
    init,
    updatePreview
  };
})(jQuery);

jQuery(document).ready(function() {
  ClientBlocksEditor.init();
});
