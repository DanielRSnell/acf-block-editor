// Breakpoints Module
const ClientBlocksBreakpoints = (function($) {
    // State
    let breakpoints = [];
    let currentBreakpoint = 'full';
    
    // DOM Elements
    const elements = {
        container: '.preview-frame-container',
        controls: '.breakpoint-controls',
        settingsButton: '.breakpoint-settings',
        modal: '#breakpoint-settings-modal'
    };
    
    // API Methods
    const api = {
        loadBreakpoints: async () => {
            try {
                const response = await $.ajax({
                    url: `${clientBlocksEditor.restUrl}/breakpoints`,
                    headers: { 'X-WP-Nonce': clientBlocksEditor.nonce }
                });
                
                breakpoints = response;
                renderBreakpointButtons();
                
            } catch (error) {
                console.error('Error loading breakpoints:', error);
            }
        },
        
        saveBreakpoints: async (updatedBreakpoints) => {
            try {
                await $.ajax({
                    url: `${clientBlocksEditor.restUrl}/breakpoints`,
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': clientBlocksEditor.nonce
                    },
                    data: JSON.stringify(updatedBreakpoints)
                });
                
                breakpoints = updatedBreakpoints;
                renderBreakpointButtons();
                
            } catch (error) {
                console.error('Error saving breakpoints:', error);
            }
        }
    };
    
    // Render Methods
    const renderBreakpointButtons = () => {
        const $controls = $(elements.controls);
        $controls.empty();
        
        breakpoints.forEach(breakpoint => {
            const $button = $(`
                <button type="button" class="breakpoint-button" 
                        data-breakpoint="${breakpoint.id}" 
                        title="${breakpoint.name} ${breakpoint.width ? `(${breakpoint.width}px)` : ''}">
                    <ion-icon name="${breakpoint.icon}"></ion-icon>
                    <span>${breakpoint.id.toUpperCase()}</span>
                </button>
            `);
            
            if (breakpoint.id === currentBreakpoint) {
                $button.addClass('active');
            }
            
            $controls.append($button);
        });
        
        // Add settings button
        $controls.append(`
            <button type="button" class="breakpoint-settings" title="Breakpoint Settings">
                <ion-icon name="settings-outline"></ion-icon>
            </button>
        `);
    };
    
    // Event Handlers
    const handleBreakpointClick = function(e) {
        e.preventDefault();
        const $button = $(this);
        const breakpoint = $button.data('breakpoint');
        
        if (currentBreakpoint === breakpoint) return;
        
        $('.breakpoint-button').removeClass('active');
        $button.addClass('active');
        
        $(elements.container).attr('data-breakpoint', breakpoint);
        currentBreakpoint = breakpoint;
    };
    
    const handleSettingsClick = function(e) {
        e.preventDefault();
        openBreakpointSettings();
    };
    
    const openBreakpointSettings = () => {
        const $modal = $(elements.modal);
        if (!$modal.length) {
            createSettingsModal();
        }
        $(elements.modal).show();
    };
    
    const createSettingsModal = () => {
        const $modal = $(`
            <div id="breakpoint-settings-modal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Breakpoint Settings</h2>
                        <button type="button" class="close-modal">×</button>
                    </div>
                    <div class="modal-body">
                        <div class="breakpoints-list"></div>
                        <button type="button" class="add-breakpoint">Add Breakpoint</button>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="save-breakpoints">Save Changes</button>
                    </div>
                </div>
            </div>
        `).appendTo('body');
        
        renderBreakpointsList();
        attachModalEvents();
    };
    
    // Initialize
    const init = () => {
        api.loadBreakpoints();
        
        $(document).on('click', '.breakpoint-button', handleBreakpointClick);
        $(document).on('click', '.breakpoint-settings', handleSettingsClick);
    };
    
    return {
        init: init
    };
    
})(jQuery);

jQuery(document).ready(function() {
    ClientBlocksBreakpoints.init();
});