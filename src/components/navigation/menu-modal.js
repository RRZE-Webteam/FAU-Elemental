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

            // Submenu toggles (including new row-style toggles)
            $(document).on('click', '.menu-modal__submenu-toggle, .menu-website-modal__submenu-toggle, .menu-modal__submenu-row', (e) => {
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
            
            // Hide all submenus initially
            $menu.find('.sub-menu').hide();
            
            // Remove all overview links
            $menu.find('.menu-item-overview').remove();
            
            // Remove all level headings
            $menu.find('.menu-modal__level-heading').remove();
            
            // Reset all toggle states
            $menu.find('.menu-modal__submenu-toggle, .menu-website-modal__submenu-toggle').attr('aria-expanded', 'false').removeClass('expanded').show();
            
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
            
            // Remove all overview links
            $menu.find('.menu-item-overview').remove();
            
            // Remove all level headings
            $menu.find('.menu-modal__level-heading').remove();
            
            // Reset all toggle states
            $menu.find('.menu-modal__submenu-toggle').attr('aria-expanded', 'false').removeClass('expanded').show();
            
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
                const $submenu = parentLi.find('.sub-menu');
                $submenu.hide();
                
                // Remove overview link if it exists
                $submenu.find('.menu-item-overview').remove();
                
                // Remove level heading if it exists
                parentLi.find('.menu-modal__level-heading').remove();
                
                // Show all siblings in the parent menu
                parentUl.children('li').show();
                
                // Show the parent toggle button
                parentLi.children('.menu-modal__submenu-toggle').show();
                
                // Set all toggles in the current menu to collapsed
                parentUl.find('> li > .menu-modal__submenu-toggle').attr('aria-expanded', 'false');
                
                // Update navigation stack
                $modal.data('navigation-stack', navigationStack);
                
                // Check if there's still a parent level to show heading for
                if (navigationStack.length > 0) {
                    // Get the parent level info from the remaining stack
                    const currentLevel = navigationStack[navigationStack.length - 1];
                    if (currentLevel.parentTitle) {
                        // Remove any existing headings first
                        $modal.find('.menu-modal__level-heading').remove();
                        // Add heading for the level we're going back to
                        parentUl.prepend('<h2 class="menu-modal__level-heading">' + currentLevel.parentTitle + '</h2>');
                    }
                } else {
                    // We're back to the root level, remove all headings
                    $modal.find('.menu-modal__level-heading').remove();
                    $backButton.hide();
                }
            } else {
                // If no navigation stack, just close the modal
                this.closeCurrentModal();
            }
        }

        toggleSubmenu($toggle) {
            console.log('toggleSubmenu called'); // Debug
            const $parentLi = $toggle.closest('.menu-item');
            const $submenu = $toggle.siblings('.sub-menu');
            const $modal = $toggle.closest('.menu-modal, .menu-meta-nav__modal, .menu-website-modal');
            const $backButton = $modal.find('.menu-modal__back-btn, .menu-meta-nav__modal__back-btn, .menu-website-modal__back-btn');
            
            console.log('Parent LI:', $parentLi.length); // Debug
            console.log('Submenu found:', $submenu.length); // Debug
            console.log('Modal found:', $modal.length); // Debug
            console.log('Back button found:', $backButton.length); // Debug
            
            if ($submenu.length === 0) {
                console.log('No submenu found, returning'); // Debug
                return;
            }
            
            // Get parent information from data attributes
            const parentUrl = $toggle.data('parent-url');
            const parentTitle = $toggle.data('parent-title');
            
            console.log('Parent URL:', parentUrl); // Debug
            console.log('Parent Title:', parentTitle); // Debug
            
            // Use drill-down navigation for all menus (both global and local)
            // Hide all siblings of the current item
            console.log('Hiding siblings of parent LI'); // Debug
            $parentLi.siblings().hide();
            
            // Hide the submenu toggle and add heading for current level
            console.log('Hiding toggle and adding level heading'); // Debug
            $toggle.hide();
            
            // Remove any existing level headings in the modal
            $modal.find('.menu-modal__level-heading').remove();
            
            // Add new level heading for current level
            $toggle.after('<h2 class="menu-modal__level-heading">' + parentTitle + '</h2>');
            
            // Add overview link at the beginning of submenu
            if (parentUrl && parentTitle) {
                console.log('Adding overview link'); // Debug
                const overviewLink = `<li class="menu-item menu-item-overview"><a href="${parentUrl}">Übersicht: ${parentTitle}</a></li>`;
                $submenu.prepend(overviewLink);
            }
            
            // Show the submenu
            console.log('Showing submenu'); // Debug
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
                parentLi: $parentLi,
                parentUrl: parentUrl,
                parentTitle: parentTitle
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

    // Add CSS class to body when modal is open and styles for new menu structure
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
        
        /* New submenu row button styles */
        .menu-modal__submenu-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            padding: 12px 16px;
            background: none;
            border: none;
            text-align: left;
            cursor: pointer;
            font-size: inherit;
            font-family: inherit;
            color: inherit;
            transition: background-color 0.2s ease;
        }
        
        .menu-modal__submenu-row:hover,
        .menu-modal__submenu-row:focus {
            background-color: rgba(0, 0, 0, 0.05);
        }
        
        .menu-modal__item-title {
            flex: 1;
        }
        
        .menu-modal__submenu-arrow {
            flex-shrink: 0;
            margin-left: 8px;
        }
        
        /* Level heading styles */
        .menu-modal__level-heading {
            padding: 16px 16px 8px 16px;
            margin: 0 0 8px 0;
            font-size: 1.2em;
            font-weight: bold;
            color: #333;
            border-bottom: 1px solid #eee;
        }
        
        /* Overview link special styling */
        .menu-item-overview a {
            font-weight: 600;
            color: #0066cc;
        }
        
        .menu-item-overview a:hover {
            color: #0052a3;
        }
    `;
    document.head.appendChild(style);

})(jQuery); 