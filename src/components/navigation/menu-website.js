(function($) {
    'use strict';

    class MenuWebsiteModal {
        constructor() {
            this.modal = $('#menu-website-modal');
            this.overlay = this.modal.find('.menu-website-modal__overlay');
            this.closeButton = this.modal.find('.menu-website-modal__close');
            this.backButton = this.modal.find('.menu-website-modal__back');
            this.mainMenu = this.modal.find('.menu-website-modal__menu');
            this.menuStack = [];
            this.currentMenu = this.mainMenu;

            this.injectSubmenuToggles();
            this.bindEvents();
        } 

        injectSubmenuToggles() {
            // For each menu item with children, inject a toggle button if not present
            this.mainMenu.find('.menu-item-has-children').each(function() {
                const $item = $(this);
                if ($item.children('.menu-website-modal__submenu-toggle').length === 0) {
                    const $link = $item.children('a');
                    const $toggle = $('<button>', {
                        class: 'menu-website-modal__submenu-toggle',
                        'aria-expanded': 'false',
                        'aria-label': $link.text() + ' submenu',
                        html: '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7 5L12 10L7 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>'
                    });
                    $link.after($toggle);
                }
            });
        }

        bindEvents() {
            // Open modal
            $('.main-navigation__toggle').on('click', this.openModal.bind(this));

            // Close modal
            this.closeButton.on('click', this.closeModal.bind(this));
            this.overlay.on('click', this.closeModal.bind(this));

            // Handle submenu toggles
            this.modal.find('.menu-website-modal__submenu-toggle').on('click', this.showSubmenu.bind(this));

            // Handle back button
            this.backButton.on('click', this.goBack.bind(this));

            // Close on escape key
            $(document).on('keydown', this.handleKeydown.bind(this));

            // Reveal more items on first item arrow click
            $('.menu-item-first .menu-website-modal__submenu-toggle').on('click', function(e) {
                e.preventDefault();
                $('.menu-website-modal__more').show();
                $(this).hide(); // Optionally hide the arrow after click
            });
        }

        openModal() {
            this.modal.addClass('is-open');
            $('body').addClass('menu-website-modal-open');
            this.resetMenu();
        }

        closeModal() {
            this.modal.removeClass('is-open');
            $('body').removeClass('menu-website-modal-open');
            this.resetMenu();
        }

        resetMenu() {
            // Hide all submenus
            this.mainMenu.find('.sub-menu').hide().removeClass('is-open');
            this.mainMenu.show();
            this.currentMenu = this.mainMenu;
            this.menuStack = [];
            this.backButton.hide();
            this.mainMenu.find('.menu-website-modal__submenu-toggle').attr('aria-expanded', 'false');
        }

        showSubmenu(e) {
            e.preventDefault();
            e.stopPropagation();
            const toggle = $(e.currentTarget);
            const submenu = toggle.siblings('.sub-menu');
            if (submenu.length === 0) return;

            // Hide all siblings except the current li
            const parentLi = toggle.closest('li');
            parentLi.siblings().hide();
            // Hide the link and toggle in the current li, show only the submenu
            parentLi.children('a, .menu-website-modal__submenu-toggle').hide();
            submenu.css('display', 'block');
            toggle.attr('aria-expanded', 'true');

            // Push the parent <ul> and <li> to the stack for back navigation
            this.menuStack.push({
                parentUl: parentLi.parent(),
                parentLi: parentLi
            });
            this.currentMenu = submenu;
            this.backButton.show();
        }

        goBack() {
            if (this.menuStack.length > 0) {
                // Hide current submenu
                this.currentMenu.hide();

                // Pop the parent info
                const { parentUl, parentLi } = this.menuStack.pop();

                // Show all siblings in the parent menu
                parentUl.children('li').show();
                // Show the link and toggle in the parent li
                parentLi.children('a, .menu-website-modal__submenu-toggle').show();

                this.currentMenu = parentUl;
                // Set all toggles in the previous menu to collapsed
                this.currentMenu.find('> li > .menu-website-modal__submenu-toggle').attr('aria-expanded', 'false');
                if (this.menuStack.length === 0) {
                    this.backButton.hide();
                }
            }
        }

        handleKeydown(e) {
            if (e.key === 'Escape' && this.modal.hasClass('is-open')) {
                this.closeModal();
            }
        }
    }

    // Initialize when document is ready
    $(document).ready(function() {
        new MenuWebsiteModal();
    });

})(jQuery);
