(function($) {
    'use strict';

    class MenuMetaNav {
        constructor() {
            this.servicesModal = $('#services-modal');
            this.structureModal = $('#structure-modal');
            this.openButtons = $('.menu-meta-nav__open-btn');
            this.closeButtons = $('.menu-meta-nav__close-btn');
            
            // Menu navigation state
            this.menuStack = [];
            this.currentMenu = null;
            
            // Ensure modals are hidden by default
            this.servicesModal.removeClass('is-open').hide();
            this.structureModal.removeClass('is-open').hide();
            
            // Hide all submenus by default
            this.servicesModal.find('.sub-menu').hide();
            this.structureModal.find('.sub-menu').hide();
            
            this.bindEvents();
        }

        bindEvents() {
            // Open modal
            this.openButtons.on('click', this.openModal.bind(this));

            // Close modal
            $('.menu-meta-nav__close-btn').on('click', this.closeModal.bind(this));

            // Close on overlay click
            $('.menu-meta-nav__overlay').on('click', this.closeModal.bind(this));

            // Close on escape key
            $(document).on('keydown', this.handleKeydown.bind(this));

            // Handle submenu toggles using event delegation
            $(document).on('click', '.menu-meta-nav__submenu-toggle', this.toggleSubmenu.bind(this));
            
            // Handle back button clicks
            $(document).on('click', '.menu-meta-nav__back-btn', this.goBack.bind(this));
        }

        openModal(e) {
            const button = $(e.currentTarget);
            const modalType = button.data('meta-modal');
            const modal = modalType === 'services' ? this.servicesModal : this.structureModal;

            // Hide other modal if open
            if (modalType === 'services') {
                this.structureModal.removeClass('is-open').hide();
            } else {
                this.servicesModal.removeClass('is-open').hide();
            }

            // Show selected modal
            modal.addClass('is-open').show();
            button.attr('aria-expanded', 'true');

            // Reset menu state
            this.resetMenu(modal);

            // Focus trap
            this.setupFocusTrap(modal);
        }

        closeModal(e) {
            // If called from a click event, find the closest modal
            let modal;
            if (e && e.currentTarget) {
                modal = $(e.currentTarget).closest('.menu-meta-nav__modal');
            } else {
                // fallback: close all modals
                modal = $('.menu-meta-nav__modal.is-open');
            }
            modal.removeClass('is-open').hide();
            // Reset menu state
            this.resetMenu(modal);
        }

        resetMenu(modal) {
            // Hide all submenus
            modal.find('.sub-menu').hide();
            // Show main menu
            modal.find('.menu-meta-nav__menu').show();
            // Show all menu items
            modal.find('.menu-item').show();
            // Show all links and toggles
            modal.find('.menu-item > a, .menu-item > .menu-meta-nav__submenu-toggle').show();
            // Hide back button instead of removing it
            modal.find('.menu-meta-nav__back-btn').css('display', 'none');
            // Reset state
            this.menuStack = [];
            this.currentMenu = modal.find('.menu-meta-nav__menu');
        }

        handleKeydown(e) {
            if (e.key === 'Escape') {
                if (this.servicesModal.is(':visible')) {
                    this.closeModal({ currentTarget: this.servicesModal.find('.menu-meta-nav__close-btn') });
                } else if (this.structureModal.is(':visible')) {
                    this.closeModal({ currentTarget: this.structureModal.find('.menu-meta-nav__close-btn') });
                }
            }
        }

        toggleSubmenu(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const button = $(e.currentTarget);
            const menuItem = button.closest('.menu-item');
            const submenu = menuItem.find('> .sub-menu');
            const modal = menuItem.closest('.menu-meta-nav__modal');
            
            // Show back button (use flex for correct layout)
            modal.find('.menu-meta-nav__back-btn').css('display', 'flex');
            
            // Hide all siblings except the current li
            menuItem.siblings().hide();
            // Hide the link and toggle in the current li
            menuItem.find('> a, > .menu-meta-nav__submenu-toggle').hide();
            // Show submenu
            submenu.show();
            
            // Push the parent menu and item to the stack
            this.menuStack.push({
                parentMenu: menuItem.parent(),
                parentItem: menuItem
            });
            
            this.currentMenu = submenu;
        }

        goBack() {
            if (this.menuStack.length > 0) {
                // Hide current submenu
                this.currentMenu.hide();
                
                // Pop the parent info from stack
                const { parentMenu, parentItem } = this.menuStack.pop();
                
                // Show all siblings in the parent menu
                parentMenu.children('li').show();
                // Show the link and toggle in the parent item
                parentItem.find('> a, > .menu-meta-nav__submenu-toggle').show();
                
                this.currentMenu = parentMenu;
                
                // Hide back button if we're back at the root
                if (this.menuStack.length === 0) {
                    this.currentMenu.closest('.menu-meta-nav__modal').find('.menu-meta-nav__back-btn').css('display', 'none');
                }
            }
        }

        setupFocusTrap(modal) {
            const focusableElements = modal.find('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
            const firstFocusable = focusableElements.first();
            const lastFocusable = focusableElements.last();

            modal.on('keydown', (e) => {
                if (e.key === 'Tab') {
                    if (e.shiftKey) {
                        if (document.activeElement === firstFocusable[0]) {
                            e.preventDefault();
                            lastFocusable.focus();
                        }
                    } else {
                        if (document.activeElement === lastFocusable[0]) {
                            e.preventDefault();
                            firstFocusable.focus();
                        }
                    }
                }
            });

            // Focus first element
            firstFocusable.focus();
        }
    }

    // Initialize when document is ready
    $(document).ready(function() {
        new MenuMetaNav();
    });

})(jQuery);
