// Preview Module
const ClientBlocksPreview = (function($) {
    // Configuration
    const config = {
        breakpoints: clientBlocksEditor.breakpoints || []
    };
    
    // DOM Elements
    const elements = {
        container: '.preview-frame-container',
        frame: '#preview-frame',
        breakpointButtons: '.breakpoint-button'
    };
    
    // State
    let currentBreakpoint = 'full';
    
    // Event Handlers
    const handleBreakpointClick = function(e) {
        e.preventDefault();
        const $button = $(this);
        const breakpoint = $button.data('breakpoint');
        
        if (currentBreakpoint === breakpoint) return;
        
        $(elements.breakpointButtons).removeClass('active');
        $button.addClass('active');
        
        $(elements.container).attr('data-breakpoint', breakpoint);
        currentBreakpoint = breakpoint;
        
        // Update preview container width based on breakpoint
        const breakpointData = config.breakpoints.find(b => b.id === breakpoint);
        if (breakpointData && breakpointData.width) {
            $(elements.container).css('max-width', breakpointData.width + 'px');
        } else {
            $(elements.container).css('max-width', 'none');
        }
    };
    
    // Initialize
    const init = () => {
        $(elements.breakpointButtons).on('click', handleBreakpointClick);
        
        // Set initial breakpoint
        const $defaultButton = $(elements.breakpointButtons).filter('[data-breakpoint="full"]');
        if ($defaultButton.length) {
            $defaultButton.trigger('click');
        }
    };
    
    return {
        init: init
    };
    
})(jQuery);

jQuery(document).ready(function() {
    ClientBlocksPreview.init();
});