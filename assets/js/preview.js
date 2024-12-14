const ClientBlocksPreview = (function($) {
    // Configuration
    const config = {
        breakpoints: clientBlocksEditor.breakpoints || [],
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
        const $container = $(elements.container);
        // Get computed dimensions of the container
        const containerWidth = $container.width();
        const containerHeight = $container.height();
        
        const breakpointData = config.breakpoints.find(b => b.id === breakpoint);
        const targetWidth = breakpointData ? breakpointData.width : 1024;
        
        // Calculate scale needed to fit target width in container
        const scale = containerWidth / targetWidth;
        
        // Calculate required frame height to fill container after scaling
        const frameHeight = Math.ceil(containerHeight / scale);
        
        return {
            width: targetWidth,
            height: frameHeight,
            scale: scale
        };
    };
    
    const updatePreviewSize = (breakpoint) => {
        const $container = $(elements.container);
        const $frameContainer = $(elements.frameContainer);
        
        const { width, height, scale } = calculateFrameDimensions(breakpoint);
        
        // Apply new dimensions and positioning
        $frameContainer.css({
            width: `${width}px`,
            height: `${height}px`,
            transform: `scale(${scale})`,
            transformOrigin: '0 0',  // Origin at top-left for predictable scaling
            position: 'absolute',
            left: '0',
            top: '0'
        });
        
        // Update data attribute
        $frameContainer.attr('data-breakpoint', breakpoint);
        
        // Log calculations for debugging
        console.log({
            breakpoint,
            containerWidth: $container.width(),
            containerHeight: $container.height(),
            frameWidth: width,
            frameHeight: height,
            scale,
            computedHeight: height * scale,
            computedWidth: width * scale
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
        // Ensure container has relative positioning for absolute positioning of frame
        $(elements.container).css('position', 'relative');
        
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