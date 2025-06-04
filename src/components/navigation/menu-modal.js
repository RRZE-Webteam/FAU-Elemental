/**
 * Unified Menu Modal JavaScript
 * Handles both global menus (meta-nav) and local menus (website)
 */

(function($) {
    'use strict';

    class MenuModal {
        constructor() {
            this.modals = [];
            this.currentModal = null;
            this.init();
        }

        init() {
            this.bindEvents();
            this.setupAccessibility();
        }

        bindEvents() {
            // Open modal buttons
            $(document).on('click', '.menu-modal__open-btn', (e) => {
                e.preventDefault();
                console.log('FAU Debug: Open button clicked');
                const modalTarget = $(e.currentTarget).data('modal-target') || $(e.currentTarget).data('meta-modal');
                if (modalTarget) {
                    this.openModal(modalTarget);
                }
            });

            // Close modal buttons - Updated to match actual generated HTML structure
            $(document).on('click', '.menu-modal__close-btn, .menu-meta-nav__modal__close-btn, .menu-website-modal__close-btn', (e) => {
                e.preventDefault();
                console.log('FAU Debug: Close button clicked');
                this.closeCurrentModal();
            });

            // Back buttons
            $(document).on('click', '.menu-modal__back-btn, .menu-meta-nav__modal__back-btn, .menu-website-modal__back-btn', (e) => {
                e.preventDefault();
                console.log('FAU Debug: Back button clicked');
                this.goBack();
            });

            // Overlay click to close
            $(document).on('click', '.menu-modal__overlay, .menu-meta-nav__modal__overlay, .menu-website-modal__overlay', (e) => {
                if (e.target === e.currentTarget) {
                    console.log('FAU Debug: Overlay clicked');
                    this.closeCurrentModal();
                }
            });

            // Submenu toggles
            $(document).on('click', '.menu-modal__submenu-toggle, .menu-website-modal__submenu-toggle', (e) => {
                e.preventDefault();
                console.log('FAU Debug: Submenu toggle clicked');
                this.toggleSubmenu($(e.currentTarget));
            });

            // Escape key to close
            $(document).on('keydown', (e) => {
                if (e.key === 'Escape' && this.currentModal) {
                    console.log('FAU Debug: Escape key pressed');
                    this.closeCurrentModal();
                }
            });

            // Prevent body scroll when modal is open
            $(document).on('touchmove', (e) => {
                if (this.currentModal && !$(e.target).closest('.menu-modal__content, .menu-meta-nav__modal-content, .menu-website-modal__content').length) {
                    e.preventDefault();
                }
            });
        }

        setupAccessibility() {
            // Ensure modals are properly hidden from screen readers initially
            $('.menu-modal, .menu-meta-nav__modal, .menu-website-modal').each(function() {
                $(this).attr('aria-hidden', 'true');
            });
        }

        openModal(modalId) {
            console.log('FAU Debug: === OPENING MODAL ===');
            console.log('FAU Debug: modalId requested:', modalId);
            
            // Close any currently open modal
            this.closeCurrentModal();

            // Find the modal (support different ID formats)
            let $modal = $(`#${modalId}`);
            console.log('FAU Debug: First search for #' + modalId + ':', $modal.length, $modal.get(0));
            
            if (!$modal.length) {
                $modal = $(`#${modalId}-modal`);
                console.log('FAU Debug: Second search for #' + modalId + '-modal:', $modal.length, $modal.get(0));
            }
            
            if (!$modal.length) {
                console.warn(`Modal not found: ${modalId}`);
                console.log('FAU Debug: Available modals on page:');
                $('.menu-modal, .menu-meta-nav__modal, .menu-website-modal').each(function(i) {
                    console.log('  Modal ' + i + ':', this.id, this.className);
                });
                return;
            }

            console.log('FAU Debug: Modal found successfully:', $modal.attr('id'), $modal.attr('class'));
            this.currentModal = $modal;

            // Reset modal to initial state
            this.resetModalState($modal);

            // Remove inline display:none so CSS can take over
            $modal.removeAttr('style');
            
            // Show modal using CSS classes only
            $modal.addClass('is-open');
            $modal.attr('aria-hidden', 'false');
            
            // Debug: Log the actual HTML structure of menu items with toggle buttons
            console.log('FAU Debug: === MENU STRUCTURE ANALYSIS ===');
            $modal.find('.menu-item').each(function(i) {
                const $item = $(this);
                const hasToggle = $item.find('.menu-modal__submenu-toggle').length > 0;
                if (hasToggle) {
                    console.log('FAU Debug: Menu item ' + i + ' with toggle:');
                    console.log('  Classes:', $item.attr('class'));
                    console.log('  HTML structure:', $item.get(0).outerHTML.substring(0, 200) + '...');
                    console.log('  Toggle button position:', $item.find('.menu-modal__submenu-toggle').get(0));
                }
            });

            // Focus management
            const $firstFocusable = $modal.find('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])').first();
            if ($firstFocusable.length) {
                $firstFocusable.focus();
            }

            // Store the previously focused element
            this.previouslyFocused = document.activeElement;

            // Prevent body scroll
            $('body').addClass('modal-open');

            // Trap focus within modal
            this.trapFocus($modal);
        }

        resetModalState($modal) {
            // Reset drill-down navigation state completely
            const $menu = $modal.find('.menu-modal__menu, .menu-meta-nav__menu, .menu-website-modal__menu');
            const $backButton = $modal.find('.menu-modal__back-btn, .menu-meta-nav__modal__back-btn, .menu-website-modal__back-btn');
            
            // Show all menu items
            $menu.find('.menu-item').show();
            
            // Hide all submenus
            $menu.find('.sub-menu').hide();
            
            // Reset all toggle states to collapsed
            $menu.find('.menu-modal__submenu-toggle, .menu-website-modal__submenu-toggle').attr('aria-expanded', 'false').show();
            
            // Restore all link styles
            $menu.find('a').css({
                'font-weight': '', 
                'pointer-events': '', 
                'color': ''
            });
            
            // Hide back button
            $backButton.hide();
            
            // Clear navigation stack
            $modal.data('navigation-stack', []);
            
            console.log('FAU Debug: Modal state reset to initial state');
        }

        closeCurrentModal() {
            if (!this.currentModal) return;

            const $modal = this.currentModal;

            // Reset drill-down navigation state completely
            const $menu = $modal.find('.menu-modal__menu, .menu-meta-nav__menu, .menu-website-modal__menu');
            const $backButton = $modal.find('.menu-modal__back-btn, .menu-meta-nav__modal__back-btn, .menu-website-modal__back-btn');
            
            // Show all menu items
            $menu.find('.menu-item').show();
            
            // Hide all submenus
            $menu.find('.sub-menu').hide();
            
            // Reset all toggle states
            $menu.find('.menu-modal__submenu-toggle').attr('aria-expanded', 'false').show();
            
            // Restore all link styles
            $menu.find('a').css({
                'font-weight': '', 
                'pointer-events': '', 
                'color': ''
            });
            
            // Hide back button
            $backButton.hide();
            
            // Clear navigation stack
            $modal.data('navigation-stack', []);

            // Hide modal
            $modal.removeClass('is-open');
            $modal.attr('aria-hidden', 'true');

            // Wait for animation then hide
            setTimeout(() => {
                $modal.hide();
            }, 300);

            // Restore focus
            if (this.previouslyFocused) {
                $(this.previouslyFocused).focus();
            }

            // Allow body scroll
            $('body').removeClass('modal-open');

            this.currentModal = null;
            this.previouslyFocused = null;
        }

        goBack() {
            if (!this.currentModal) return;
            
            const $modal = this.currentModal;
            const $backButton = $modal.find('.menu-modal__back-btn, .menu-meta-nav__modal__back-btn, .menu-website-modal__back-btn');
            const navigationStack = $modal.data('navigation-stack') || [];
            
            console.log('FAU Debug: Going back from drill-down navigation. Stack depth:', navigationStack.length);
            
            if (navigationStack.length > 0) {
                // Pop the most recent navigation state
                const { parentUl, parentLi } = navigationStack.pop();
                
                // Hide current submenu
                parentLi.find('.sub-menu').hide();
                
                // Restore parent link styles
                parentLi.children('a').css({
                    'font-weight': '', 
                    'pointer-events': '', 
                    'color': ''
                });
                
                // Show all siblings in the parent menu
                parentUl.children('li').show();
                
                // Show the parent link and toggle
                parentLi.children('a, .menu-modal__submenu-toggle').show();
                
                // Set all toggles in the current menu to collapsed
                parentUl.find('> li > .menu-modal__submenu-toggle').attr('aria-expanded', 'false');
                
                // Update navigation stack
                $modal.data('navigation-stack', navigationStack);
                
                // Hide back button if we're back to the root
                if (navigationStack.length === 0) {
                    $backButton.hide();
                }
                
                console.log('FAU Debug: Restored to menu level. Remaining stack depth:', navigationStack.length);
            } else {
                // If no navigation stack, just close the modal
                this.closeCurrentModal();
            }
        }

        toggleSubmenu($toggle) {
            const $parentLi = $toggle.closest('.menu-item');
            const $submenu = $toggle.siblings('.sub-menu');
            const $modal = $toggle.closest('.menu-modal, .menu-meta-nav__modal, .menu-website-modal');
            const $backButton = $modal.find('.menu-modal__back-btn, .menu-meta-nav__modal__back-btn, .menu-website-modal__back-btn');
            
            if ($submenu.length === 0) return;
            
            console.log('FAU Debug: Drill-down navigation activated');
            
            // Use drill-down navigation for all menus (both global and local)
            // Hide all siblings of the current item
            $parentLi.siblings().hide();
            
            // Hide the submenu toggle, keep parent link visible as styled label
            $toggle.hide();
            $parentLi.children('a').css({
                'display': '', 
                'font-weight': 'bold', 
                'pointer-events': 'none', 
                'color': '#333'
            });
            
            // Show the submenu
            $submenu.css('display', 'block');
            $toggle.attr('aria-expanded', 'true');
            
            // Initialize or get navigation stack
            if (!$modal.data('navigation-stack')) {
                $modal.data('navigation-stack', []);
            }
            
            // Push current state to navigation stack
            const navigationStack = $modal.data('navigation-stack');
            navigationStack.push({
                parentUl: $parentLi.parent(),
                parentLi: $parentLi
            });
            
            // Show back button
            $backButton.show();
            
            console.log('FAU Debug: Navigation stack depth:', navigationStack.length);
        }

        trapFocus($modal) {
            const $focusableElements = $modal.find('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
            const $firstFocusable = $focusableElements.first();
            const $lastFocusable = $focusableElements.last();

            $modal.on('keydown.menu-modal', (e) => {
                if (e.key !== 'Tab') return;

                if (e.shiftKey) {
                    // Shift + Tab
                    if (document.activeElement === $firstFocusable[0]) {
                        e.preventDefault();
                        $lastFocusable.focus();
                    }
                } else {
                    // Tab
                    if (document.activeElement === $lastFocusable[0]) {
                        e.preventDefault();
                        $firstFocusable.focus();
                    }
                }
            });
        }
    }

    // Initialize when DOM is ready
    $(document).ready(function() {
        console.log('FAU Debug: MenuModal JavaScript initializing');
        
        // Check if buttons exist
        const openButtons = $('.menu-modal__open-btn');
        console.log('FAU Debug: Found ' + openButtons.length + ' open buttons');
        
        // Check if modals exist
        const modals = $('.menu-modal, .menu-meta-nav__modal, .menu-website-modal');
        console.log('FAU Debug: Found ' + modals.length + ' modals');
        
        new MenuModal();
        console.log('FAU Debug: MenuModal initialized');
    });

    // Add CSS class to body when modal is open
    const style = document.createElement('style');
    style.textContent = `
        body.modal-open {
            overflow: hidden;
        }
        
        @media (max-width: 768px) {
            body.modal-open {
                position: fixed;
                width: 100%;
            }
        }
    `;
    document.head.appendChild(style);

})(jQuery); 