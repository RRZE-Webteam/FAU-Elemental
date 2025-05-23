(function($) {
    'use strict';

    class MainNavigation {
        constructor() {
            this.toggleButton = $('.main-navigation__toggle');
            this.init();
        }

        init() {
            this.toggleButton.on('click', this.handleToggleClick.bind(this));
        }

        handleToggleClick(e) {
            e.stopPropagation();
            const isExpanded = this.toggleButton.attr('aria-expanded') === 'true';
            this.toggleButton.attr('aria-expanded', !isExpanded);
        }
    }

    // Initialize when document is ready
    $(document).ready(function() {
        new MainNavigation();
    });

})(jQuery); 