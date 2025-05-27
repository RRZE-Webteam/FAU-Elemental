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
            // Hide all submenus (both menu-children and page-children)
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
            const parentLi = toggle.closest('li');
            
            // Check if this is a child item (page child or menu child) being clicked
            if (parentLi.hasClass('page-child-item') || parentLi.hasClass('menu-child-item')) {
                // This is a child item with its own children - hide other types of children
                const submenu = toggle.siblings('.sub-menu');
                if (submenu.length === 0) return;

                // Hide all siblings except the current li
                parentLi.siblings().hide();
                
                // Find the parent container and hide other types of children
                const parentContainer = parentLi.closest('.sub-menu').parent();
                let hiddenChildren = $();
                
                if (parentLi.hasClass('page-child-item')) {
                    // Hide menu children if they exist
                    hiddenChildren = parentContainer.find('.sub-menu.menu-children');
                    hiddenChildren.hide();
                } else if (parentLi.hasClass('menu-child-item')) {
                    // Hide page children if they exist
                    hiddenChildren = parentContainer.find('.sub-menu.page-children');
                    hiddenChildren.hide();
                }
                
                // Hide the toggle, keep <a> visible and styled as label
                parentLi.children('.menu-website-modal__submenu-toggle').hide();
                parentLi.children('a').css({'display': '', 'font-weight': 'bold', 'pointer-events': 'none', 'color': '#333'});
                submenu.css('display', 'block');
                toggle.attr('aria-expanded', 'true');

                // Push to stack for back navigation
                this.menuStack.push({
                    parentUl: parentLi.parent(),
                    parentLi: parentLi,
                    activeSubmenu: submenu,
                    hiddenChildren: hiddenChildren
                });
                this.currentMenu = submenu;
                this.backButton.show();
            } else {
                // This is a main menu item - show both menu children and page children
                const allSubmenus = parentLi.children('.sub-menu');
                if (allSubmenus.length === 0) return;

                // Hide all siblings except the current li
                parentLi.siblings().hide();
                // Hide the toggle, keep <a> visible and styled as label
                parentLi.children('.menu-website-modal__submenu-toggle').hide();
                parentLi.children('a').css({'display': '', 'font-weight': 'bold', 'pointer-events': 'none', 'color': '#333'});
                
                // Show all submenus (both menu children and page children)
                allSubmenus.css('display', 'block');
                toggle.attr('aria-expanded', 'true');

                // For navigation purposes, use the first submenu as the current menu
                const firstSubmenu = allSubmenus.first();
                this.menuStack.push({
                    parentUl: parentLi.parent(),
                    parentLi: parentLi,
                    activeSubmenu: firstSubmenu,
                    allSubmenus: allSubmenus
                });
                this.currentMenu = firstSubmenu;
                this.backButton.show();
            }
        }

        goBack() {
            if (this.menuStack.length > 0) {
                // Hide current submenu
                this.currentMenu.hide();

                // Pop the parent info
                const stackItem = this.menuStack.pop();
                const { parentUl, parentLi, activeSubmenu, allSubmenus, hiddenChildren } = stackItem;

                // Restore <a> styles
                parentLi.children('a').css({'font-weight': '', 'pointer-events': '', 'color': ''});

                // Show all siblings in the parent menu
                parentUl.children('li').show();
                // Show the link and all toggles in the parent li
                parentLi.children('a, .menu-website-modal__submenu-toggle').show();

                // If we had hidden children (from a child item navigation), restore the mixed view
                if (hiddenChildren && hiddenChildren.length > 0) {
                    // We came from a child item navigation, so restore only the hidden children
                    // Don't show the current submenu we just came back from
                    hiddenChildren.show();
                    
                    // Hide any nested submenus within the restored children to ensure clean state
                    hiddenChildren.find('.sub-menu').hide();
                    
                    // Reset all toggles within the restored children to collapsed state
                    hiddenChildren.find('.menu-website-modal__submenu-toggle').attr('aria-expanded', 'false');
                } else {
                    // We came from a main item navigation, so hide all submenus
                    if (allSubmenus && allSubmenus.length > 0) {
                        allSubmenus.hide();
                    } else {
                        parentLi.children('.sub-menu').hide();
                    }
                }

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
