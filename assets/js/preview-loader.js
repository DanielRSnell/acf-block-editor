(function($) {
    $(document).ready(function() {
        const urlParams = new URLSearchParams(window.location.search);
        const clientBlocks = urlParams.get('client_blocks');
        const blockId = urlParams.get('block_id');

        if (clientBlocks === 'preview' && blockId) {
            $('body').empty().append('<div id="client-blocks-preview-container"></div>');
            
            $.ajax({
                url: clientBlocksPreview.ajaxurl,
                type: 'POST',
                data: {
                    action: 'render_block_preview',
                    nonce: clientBlocksPreview.nonce,
                    block_id: blockId
                },
                success: function(response) {
                    if (response.success) {
                        $('#client-blocks-preview-container').html(response.data.html);
                    } else {
                        $('#client-blocks-preview-container').html('<p>Error loading preview</p>');
                    }
                },
                error: function() {
                    $('#client-blocks-preview-container').html('<p>Error loading preview</p>');
                }
            });
        }
    });
})(jQuery);
