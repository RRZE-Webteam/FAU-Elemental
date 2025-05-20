import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import save from './save';
import metadata from './block.json'; // Import block.json

const { name } = metadata; // Get the block name from block.json

// Add event listeners for search scope selection
document.addEventListener('DOMContentLoaded', function() {
    const scopeOptions = document.querySelectorAll('.fau-search-scope-option');
    
    scopeOptions.forEach(option => {
        option.addEventListener('change', function() {
            const instanceId = this.dataset.instanceId;
            const scopeValue = this.value;
            const hiddenInput = document.querySelector(`#${instanceId}-wrapper .fau-search-scope-hidden`);
            
            if (hiddenInput) {
                hiddenInput.value = scopeValue;
            }
        });
    });
});

registerBlockType( name, {
    // The block's 'title', 'icon', 'category', and 'attributes' are defined in block.json
    // and will be used automatically when the block is registered with PHP.
    // The JavaScript registration primarily needs the 'edit' and 'save' components.
    edit: Edit,
    save: save,
} );