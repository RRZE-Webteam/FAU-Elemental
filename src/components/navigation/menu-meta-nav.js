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
            this.closeButtons.on('click', this.closeModal.bind(this));

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
            const button = $(e.currentTarget);
            const modalType = button.data('meta-modal-close');
            const modal = modalType === 'services' ? this.servicesModal : this.structureModal;

            modal.removeClass('is-open').hide();
            $(`.menu-meta-nav__open-btn[data-meta-modal="${modalType}"]`).attr('aria-expanded', 'false');
            
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
            // Remove back button
            modal.find('.menu-meta-nav__back-btn').remove();
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
            
            // Add back button to modal header if it doesn't exist
            if (!modal.find('.menu-meta-nav__back-btn').length) {
                modal.find('.menu-meta-nav__modal-content').prepend(`
                    <button class="menu-meta-nav__back-btn" aria-label="Back">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 4L6 10L12 16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Back
                    </button>
                `);
            }
            
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
                
                // Remove back button if we're back at the root
                if (this.menuStack.length === 0) {
                    this.currentMenu.closest('.menu-meta-nav__modal').find('.menu-meta-nav__back-btn').remove();
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
