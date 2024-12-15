(function() {
    window.openClientBlocksEditor = function(blockId) {
        const editorUrl = `/wp-admin/admin.php?page=client-blocks-editor&block_id=${blockId}`;
        const iframe = document.createElement('iframe');
        iframe.src = editorUrl;
        iframe.style.position = 'fixed';
        iframe.style.top = '0';
        iframe.style.left = '0';
        iframe.style.width = '100%';
        iframe.style.height = '100%';
        iframe.style.border = 'none';
        iframe.style.zIndex = '9999';

        const closeButton = document.createElement('button');
        closeButton.textContent = 'Close Editor';
        closeButton.style.position = 'fixed';
        closeButton.style.top = '10px';
        closeButton.style.right = '10px';
        closeButton.style.zIndex = '10000';
        closeButton.addEventListener('click', function() {
            document.body.removeChild(iframe);
            document.body.removeChild(closeButton);
        });

        document.body.appendChild(iframe);
        document.body.appendChild(closeButton);
    };
})();
