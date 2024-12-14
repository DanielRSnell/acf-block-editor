// Breakpoints Module
const ClientBlocksBreakpoints = (function($) {
    // State
    let breakpoints = [];
    
    // DOM Elements
    const elements = {
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
                        title="${breakpoint.name} (${breakpoint.width}px)">
                    <ion-icon name="${breakpoint.icon}"></ion-icon>
                </button>
            `);
            
            $controls.append($button);
        });
        
        // Add settings button
        $controls.append(`
            <button type="button" class="breakpoint-settings" title="Breakpoint Settings">
                <ion-icon name="settings-outline"></ion-icon>
            </button>
        `);
    };
    
    const renderBreakpointsList = () => {
        const $list = $('.breakpoints-list');
        $list.empty();
        
        breakpoints.forEach((breakpoint, index) => {
            $list.append(`
                <div class="breakpoint-item">
                    <input type="text" name="name" value="${breakpoint.name}" placeholder="Name">
                    <input type="number" name="width" value="${breakpoint.width || ''}" placeholder="Width">
                    <select name="icon">
                        ${getIconOptions(breakpoint.icon)}
                    </select>
                    <button type="button" class="remove-breakpoint" data-index="${index}">Remove</button>
                </div>
            `);
        });
    };
    
    const getIconOptions = (selectedIcon) => {
        const icons = ['phone-portrait-outline', 'phone-landscape-outline', 'tablet-landscape-outline', 'laptop-outline', 'desktop-outline', 'expand-outline'];
        return icons.map(icon => `<option value="${icon}" ${icon === selectedIcon ? 'selected' : ''}>${icon}</option>`).join('');
    };
    
    // Event Handlers
    const openBreakpointSettings = () => {
        const $modal = $(elements.modal);
        if (!$modal.length) {
            createSettingsModal();
            console.log('Modal created successfully');
        } else {
            renderBreakpointsList();
            console.log('Modal already exists, updated content');
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
    
    const attachModalEvents = () => {
        $(document).on('click', '.close-modal', closeModal);
        $(document).on('click', '.add-breakpoint', addBreakpoint);
        $(document).on('click', '.remove-breakpoint', removeBreakpoint);
        $(document).on('click', '.save-breakpoints', saveBreakpoints);
    };
    
    const closeModal = () => {
        $(elements.modal).hide();
    };
    
    const addBreakpoint = () => {
        breakpoints.push({
            id: `breakpoint_${Date.now()}`,
            name: 'New Breakpoint',
            width: '',
            icon: 'expand-outline'
        });
        renderBreakpointsList();
    };
    
    const removeBreakpoint = function() {
        const index = $(this).data('index');
        breakpoints.splice(index, 1);
        renderBreakpointsList();
    };
    
    const saveBreakpoints = () => {
        const updatedBreakpoints = [];
        $('.breakpoint-item').each(function() {
            const $item = $(this);
            updatedBreakpoints.push({
                id: $item.find('[name="name"]').val().toLowerCase().replace(/\s+/g, '_'),
                name: $item.find('[name="name"]').val(),
                width: parseInt($item.find('[name="width"]').val()) || null,
                icon: $item.find('[name="icon"]').val()
            });
        });
        
        api.saveBreakpoints(updatedBreakpoints);
        closeModal();
    };
    
    // Initialize
    const init = () => {
        api.loadBreakpoints();
    };
    
    return {
        init: init,
        openBreakpointSettings: openBreakpointSettings
    };
    
})(jQuery);

jQuery(document).ready(function() {
    ClientBlocksBreakpoints.init();
});
