(function($) {
    /**
     * Handles the visibility of the faculty field based on website type selection
     */
    function initFacultyFieldToggle() {
        const $websiteTypeSelect = $('select[name="faue_elemental_website_type"]');
        const $facultyRow = $('select[name="faue_elemental_faculty"]').closest('tr');
        
        // Wrap the contents of each cell in a div
        $facultyRow.find('th, td').each(function() {
            const $cell = $(this);
            const $wrapper = $('<div>', {
                'class': 'faue-faculty-field__content'
            });
            // Move all contents into the wrapper
            $cell.contents().appendTo($wrapper);
            $cell.append($wrapper);
        });

        // Add initial classes
        $facultyRow.addClass('faue-faculty-field');
        if ($websiteTypeSelect.val() !== 'faculty') {
            $facultyRow.addClass('faue-faculty-field--hidden');
        }

        function toggleFacultyField() {
            if ($websiteTypeSelect.val() === 'faculty') {
                $facultyRow.removeClass('faue-faculty-field--hidden');
            } else {
                $facultyRow.addClass('faue-faculty-field--hidden');
            }
        }

        $websiteTypeSelect.on('change', toggleFacultyField);
    }

    // Initialize when DOM is ready
    $(document).ready(initFacultyFieldToggle);
})(jQuery); 