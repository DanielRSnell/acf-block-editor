const ClientBlocksPreview = (function($) {
    // Configuration
    const config = {
        defaultWidth: 1024,
        aspectRatio: 9/16 // height/width ratio
    };
    
    // DOM Elements
    const elements = {
        container: '.preview-container',
        frameContainer: '.preview-frame-container',
        frame: '#preview-frame'
    };
    
    const calculatePreviewDimensions = () => {
        const $container = $(elements.container);
        const $frameContainer = $(elements.frameContainer);
        
        // Get exact computed dimensions of container
        const containerWidth = $container[0].getBoundingClientRect().width;
        const containerHeight = $container[0].getBoundingClientRect().height;
        
        // Get target width (either from breakpoint or default)
        const targetWidth = parseInt($frameContainer.data('width')) || config.defaultWidth;
        const targetHeight = Math.round(targetWidth * config.aspectRatio);
        
        // Calculate scale based on container constraints
        const widthScale = containerWidth / targetWidth;
        const heightScale = containerHeight / targetHeight;
        const scale = Math.min(widthScale, heightScale);
        
        return {
            width: targetWidth,
            height: targetHeight,
            scale: scale
        };
    };
    
    const updatePreviewSize = () => {
        const $frameContainer = $(elements.frameContainer);
        const dimensions = calculatePreviewDimensions();
        
        // Apply the calculated dimensions and scale
        $frameContainer.css({
            width: `${dimensions.width}px`,
            height: `${dimensions.height}px`,
            transform: `scale(${dimensions.scale})`,
            transformOrigin: 'center top',
            position: 'relative',
            left: '50%',
            marginLeft: `-${dimensions.width / 2}px`
        });
        
        // Log calculations for debugging
        console.log('Preview dimensions:', {
            container: {
                width: $(elements.container)[0].getBoundingClientRect().width,
                height: $(elements.container)[0].getBoundingClientRect().height
            },
            frame: dimensions,
            scaled: {
                width: dimensions.width * dimensions.scale,
                height: dimensions.height * dimensions.scale
            }
        });
    };
    
    // Handle window resize
    const handleResize = _.debounce(() => {
        updatePreviewSize();
    }, 250);
    
    // Initialize
    const init = () => {
        // Initial update
        updatePreviewSize();
        
        // Set up event listeners
        $(window).on('resize', handleResize);
        
        // Handle iframe load
        $(elements.frame).on('load', updatePreviewSize);
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