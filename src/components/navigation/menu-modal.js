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
                const modalTarget = $(e.currentTarget).data('modal-target') || $(e.currentTarget).data('meta-modal');
                if (modalTarget) {
                    this.openModal(modalTarget);
                }
            });

            // Close modal buttons - Updated to match actual generated HTML structure
            $(document).on('click', '.menu-modal__close-btn, .menu-meta-nav__modal__close-btn, .menu-website-modal__close-btn', (e) => {
                e.preventDefault();
                this.closeCurrentModal();
            });

            // Back buttons
            $(document).on('click', '.menu-modal__back-btn, .menu-meta-nav__modal__back-btn, .menu-website-modal__back-btn', (e) => {
                e.preventDefault();
                this.goBack();
            });

            // Overlay click to close
            $(document).on('click', '.menu-modal__overlay, .menu-meta-nav__modal__overlay, .menu-website-modal__overlay', (e) => {
                if (e.target === e.currentTarget) {
                    this.closeCurrentModal();
                }
            });

            // Submenu toggles
            $(document).on('click', '.menu-modal__submenu-toggle, .menu-website-modal__submenu-toggle', (e) => {
                e.preventDefault();
                this.toggleSubmenu($(e.currentTarget));
            });

            // Escape key to close
            $(document).on('keydown', (e) => {
                if (e.key === 'Escape' && this.currentModal) {
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
            // Close any currently open modal
            this.closeCurrentModal();

            // Find the modal (support different ID formats)
            let $modal = $(`#${modalId}`);
            
            if (!$modal.length) {
                $modal = $(`#${modalId}-modal`);
            }
            
            if (!$modal.length) {
                console.warn(`Modal not found: ${modalId}`);
                return;
            }

            this.currentModal = $modal;

            // Reset modal to initial state
            this.resetModalState($modal);

            // Remove inline display:none so CSS can take over
            $modal.removeAttr('style');
            
            // Show modal using CSS classes only
            $modal.addClass('is-open');
            $modal.attr('aria-hidden', 'false');

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
        new MenuModal();
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