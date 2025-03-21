/******/ (() => { // webpackBootstrap
/*!***************************************************!*\
  !*** ./src/blocks/core-image/image-fullscreen.js ***!
  \***************************************************/
(function ($) {
  /**
   * Image fullscreen functionality
   * @param {string} imgSrc - The source URL of the image to display in fullscreen
   */
  function openImageFullscreen(imgSrc) {
    const fullscreenContainer = $("<div class='image-fullscreen-container'></div>");
    const img = $("<img src='" + imgSrc + "'></img>");
    const closeBtn = $("<button class='image-fullscreen-close'>×</button>");
    closeBtn.click(function () {
      fullscreenContainer.remove();
    });
    fullscreenContainer.append(img).append(closeBtn).appendTo('body');
    fullscreenContainer.click(function (e) {
      if (e.target === this) {
        $(this).remove();
      }
    });
  }

  // Make the function available globally
  window.openImageFullscreen = openImageFullscreen;
})(jQuery);
/******/ })()
;
//# sourceMappingURL=image-fullscreen.js.map