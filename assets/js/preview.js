const ClientBlocksPreview = (function($) {
    // Configuration
    const config = {
        breakpoints: clientBlocksEditor.breakpoints || [],
        aspectRatio: 9/16 // height/width ratio
    };
    
    // DOM Elements
    const elements = {
        container: '.preview-container',
        frameContainer: '.preview-frame-container',
        frame: '#preview-frame',
        breakpointButtons: '.breakpoint-button',
        settingsButton: '.breakpoint-settings'
    };
    
    // State
    let currentBreakpoint = null;
    
    // Event Handlers
    const handleBreakpointClick = function(e) {
        e.preventDefault();
        const $button = $(this);
        const breakpoint = $button.data('breakpoint');
        
        $(elements.breakpointButtons).removeClass('active');
        $button.addClass('active');
        
        updatePreviewSize(breakpoint);
        currentBreakpoint = breakpoint;
    };
    
    const calculateFrameDimensions = (breakpoint) => {
        const breakpointData = config.breakpoints.find(b => b.id === breakpoint);
        const width = breakpointData ? breakpointData.width : 1024; // fallback to 1024 if no breakpoint
        const height = Math.round(width * config.aspectRatio);
        
        return { width, height };
    };
    
    const updatePreviewSize = (breakpoint) => {
        const $container = $(elements.container);
        const $frameContainer = $(elements.frameContainer);
        
        // Get container dimensions
        const containerWidth = $container.width();
        const containerHeight = $container.height();
        
        // Calculate frame dimensions based on breakpoint
        const { width: frameWidth, height: frameHeight } = calculateFrameDimensions(breakpoint);
        
        // Calculate scale
        const scale = Math.min(
            containerWidth / frameWidth,
            containerHeight / frameHeight,
            1 // Never scale up
        );
        
        // Apply new dimensions and scale
        $frameContainer.css({
            width: `${frameWidth}px`,
            height: `${frameHeight}px`,
            transform: `scale(${scale})`,
            transformOrigin: 'center top'
        });
        
        // Update data attribute
        $frameContainer.attr('data-breakpoint', breakpoint);
        
        // Log calculations for debugging
        console.log({
            breakpoint,
            containerWidth,
            containerHeight,
            frameWidth,
            frameHeight,
            scale,
            scaledWidth: frameWidth * scale,
            scaledHeight: frameHeight * scale
        });
    };
    
    // Handle window resize
    const handleResize = function() {
        if (currentBreakpoint) {
            updatePreviewSize(currentBreakpoint);
        }
    };
    
    // Initialize
    const init = () => {
        // Set up event listeners
        $(document).on('click', elements.breakpointButtons, handleBreakpointClick);
        $(document).on('click', elements.settingsButton, handleSettingsClick);
        $(window).on('resize', _.debounce(handleResize, 250));
        
        // Set initial breakpoint
        const $frameContainer = $(elements.frameContainer);
        const initialBreakpoint = $frameContainer.data('breakpoint') || 'full';
        updatePreviewSize(initialBreakpoint);
        currentBreakpoint = initialBreakpoint;
        
        // Highlight the corresponding button
        $(elements.breakpointButtons + `[data-breakpoint="${initialBreakpoint}"]`).addClass('active');
    };
    
    return {
        init: init,
        updatePreviewSize: updatePreviewSize
    };
    
})(jQuery);

// Initialize on document ready
jQuery(document).ready(function() {
    ClientBlocksPreview.init();
});