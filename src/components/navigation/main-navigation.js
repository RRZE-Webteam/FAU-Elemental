(function($) {
    'use strict';

    class MainNavigation {
        constructor() {
            this.toggleButton = $('.main-navigation__toggle');
            this.menu = $('#main-menu');
            this.isMenuOpen = false;

            this.init();
        }

        init() {
            this.toggleButton.on('click', this.toggleMenu.bind(this));
            $(document).on('click', this.handleDocumentClick.bind(this));
            $(document).on('keydown', this.handleEscapeKey.bind(this));
        }

        toggleMenu(e) {
            e.stopPropagation();
            this.isMenuOpen = !this.isMenuOpen;
            
            this.toggleButton.attr('aria-expanded', this.isMenuOpen);
            this.menu.toggleClass('active', this.isMenuOpen);
        }

        handleDocumentClick(e) {
            if (this.isMenuOpen && !$(e.target).closest('.main-navigation__menu-container').length) {
                this.isMenuOpen = false;
                this.toggleButton.attr('aria-expanded', false);
                this.menu.removeClass('active');
            }
        }

        handleEscapeKey(e) {
            if (e.key === 'Escape') {
                this.isMenuOpen = false;
                this.toggleButton.attr('aria-expanded', false);
                this.menu.removeClass('active');
            }
        }
    }

    // Initialize when document is ready
    $(document).ready(function() {
        new MainNavigation();
    });

})(jQuery); 