(function($) {
    'use strict';

    class MainNavigation {
        constructor() {
            this.nav = document.querySelector('.main-navigation');
            if (!this.nav) return;

            this.toggle = this.nav.querySelector('.main-navigation__toggle');
            this.menu = this.nav.querySelector('.main-navigation__menu');
            this.submenus = this.nav.querySelectorAll('.sub-menu');

            if (!this.toggle || !this.menu) return;

            this.init();
        }

        init() {
            this.toggle.addEventListener('click', () => this.toggleMenu());
            
            // Handle submenu toggles on mobile
            if (window.innerWidth <= 768) {
                const menuItems = this.nav.querySelectorAll('.menu-item-has-children > a');
                menuItems.forEach(link => {
                    link.addEventListener('click', (e) => {
                        if (window.innerWidth <= 768) {
                            e.preventDefault();
                            const submenu = link.parentElement.querySelector('.sub-menu');
                            if (submenu) {
                                submenu.style.display = submenu.style.display === 'block' ? 'none' : 'block';
                            }
                        }
                    });
                });
            }

            // Handle window resize
            window.addEventListener('resize', () => this.handleResize());
        }

        toggleMenu() {
            const isExpanded = this.toggle.getAttribute('aria-expanded') === 'true';
            this.toggle.setAttribute('aria-expanded', !isExpanded);
            this.menu.classList.toggle('is-active');
            
            // Toggle hamburger icon
            const icon = this.toggle.querySelector('.main-navigation__toggle-icon');
            if (icon) {
                icon.style.transform = isExpanded ? 'rotate(0deg)' : 'rotate(45deg)';
                icon.style.transformOrigin = 'center';
            }
        }

        handleResize() {
            if (!this.menu || !this.toggle) return;

            if (window.innerWidth > 768) {
                this.menu.style.display = 'flex';
                this.toggle.setAttribute('aria-expanded', 'false');
                this.submenus.forEach(submenu => {
                    if (submenu) {
                        submenu.style.display = 'none';
                    }
                });
            } else {
                this.menu.style.display = this.menu.classList.contains('is-active') ? 'flex' : 'none';
            }
        }
    }

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', () => {
        new MainNavigation();
    });

})(jQuery); 