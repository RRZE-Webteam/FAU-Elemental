(function($) {
    /**
     * Handles the visibility of the faculty field based on website type selection
     */
    function initFacultyFieldToggle() {
        const $websiteTypeSelect = $('select[name="fau_elemental_website_type"]');
        const $facultyRow = $('select[name="fau_elemental_faculty"]').closest('tr');
        
        // Wrap the contents of each cell in a div
        $facultyRow.find('th, td').each(function() {
            const $cell = $(this);
            const $wrapper = $('<div>', {
                'class': 'fau-faculty-field__content'
            });
            // Move all contents into the wrapper
            $cell.contents().appendTo($wrapper);
            $cell.append($wrapper);
        });

        // Add initial classes
        $facultyRow.addClass('fau-faculty-field');
        if ($websiteTypeSelect.val() !== 'faculty') {
            $facultyRow.addClass('fau-faculty-field--hidden');
        }

        function toggleFacultyField() {
            if ($websiteTypeSelect.val() === 'faculty') {
                $facultyRow.removeClass('fau-faculty-field--hidden');
            } else {
                $facultyRow.addClass('fau-faculty-field--hidden');
            }
        }

        $websiteTypeSelect.on('change', toggleFacultyField);
    }

    // Initialize when DOM is ready
    $(document).ready(initFacultyFieldToggle);
})(jQuery); 