jQuery(document).ready(function($) {
    'use strict';
    
    // Toggle custom date field visibility
    $('input[name="faue_use_custom_last_updated"]').on('change', function() {
        $('.custom-date-field').toggle(this.checked);
    });
}); 