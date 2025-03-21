/******/ (() => { // webpackBootstrap
/*!****************************!*\
  !*** ./src/admin/admin.js ***!
  \****************************/
(function ($) {
  console.log('FAU Elemental Admin script loaded');

  /**
   * Handles the visibility of the faculty field based on website type selection
   */
  function initFacultyFieldToggle() {
    console.log('Initializing faculty field toggle');
    const $websiteTypeSelect = $('select[name="faue_website_type"]');
    console.log('Website type select element:', $websiteTypeSelect);
    const $facultyRow = $('select[name="faue_faculty"]').closest('tr');
    console.log('Faculty row element:', $facultyRow);

    // Wrap the contents of each cell in a div
    $facultyRow.find('th, td').each(function () {
      const $cell = $(this);
      console.log('Processing cell:', $cell);
      const $wrapper = $('<div>', {
        'class': 'faue-faculty-field__content'
      });
      console.log('Created wrapper element:', $wrapper);

      // Move all contents into the wrapper
      $cell.contents().appendTo($wrapper);
      $cell.append($wrapper);
      console.log('Moved contents to wrapper');
    });

    // Add initial classes
    $facultyRow.addClass('faue-faculty-field');
    console.log('Added base faculty field class');
    if ($websiteTypeSelect.val() !== 'faculty') {
      $facultyRow.addClass('faue-faculty-field--hidden');
      console.log('Initially hiding faculty field');
    }
    function toggleFacultyField() {
      const currentValue = $websiteTypeSelect.val();
      console.log('Website type changed to:', currentValue);
      if (currentValue === 'faculty') {
        $facultyRow.removeClass('faue-faculty-field--hidden');
        console.log('Showing faculty field');
      } else {
        $facultyRow.addClass('faue-faculty-field--hidden');
        console.log('Hiding faculty field');
      }
    }
    $websiteTypeSelect.on('change', toggleFacultyField);
    console.log('Added change event listener');
  }

  // Initialize when DOM is ready
  $(document).ready(initFacultyFieldToggle);
})(jQuery);
/******/ })()
;
//# sourceMappingURL=admin.js.map