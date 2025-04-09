document.addEventListener('DOMContentLoaded', function() {
    const menuItems = document.querySelectorAll('.fau-navigation .menu-item.has-children');
    const overlay = document.createElement('div');
    overlay.className = 'fau-navigation__overlay';
    document.body.appendChild(overlay);

    // Add overlay styles
    const style = document.createElement('style');
    style.textContent = `
        .fau-navigation__overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
        }
    `;
    document.head.appendChild(style);

    menuItems.forEach(item => {
        const button = item.querySelector('.fau-navigation__button');
        const submenu = item.querySelector('.sub-menu');

        if (button && submenu) {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();

                // Close other open submenus
                document.querySelectorAll('.fau-navigation .sub-menu').forEach(menu => {
                    if (menu !== submenu) {
                        menu.classList.remove('is-open');
                        const parentButton = menu.closest('.menu-item').querySelector('.fau-navigation__button');
                        if (parentButton) {
                            parentButton.setAttribute('aria-expanded', 'false');
                        }
                    }
                });

                // Toggle current submenu
                const isExpanded = button.getAttribute('aria-expanded') === 'true';
                button.setAttribute('aria-expanded', !isExpanded);
                submenu.classList.toggle('is-open');
                overlay.style.display = isExpanded ? 'none' : 'block';
            });
        }
    });

    // Handle nested submenu clicks
    document.querySelectorAll('.fau-navigation .sub-menu .menu-item.has-children > .fau-navigation__link').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const submenu = link.nextElementSibling;
            if (submenu && submenu.classList.contains('sub-menu')) {
                submenu.classList.toggle('is-open');
            }
        });
    });

    // Close submenus when clicking overlay
    overlay.addEventListener('click', () => {
        document.querySelectorAll('.fau-navigation .sub-menu').forEach(menu => {
            menu.classList.remove('is-open');
            const button = menu.closest('.menu-item').querySelector('.fau-navigation__button');
            if (button) {
                button.setAttribute('aria-expanded', 'false');
            }
        });
        overlay.style.display = 'none';
    });

    // Close submenus when pressing Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.fau-navigation .sub-menu').forEach(menu => {
                menu.classList.remove('is-open');
                const button = menu.closest('.menu-item').querySelector('.fau-navigation__button');
                if (button) {
                    button.setAttribute('aria-expanded', 'false');
                }
            });
            overlay.style.display = 'none';
        }
    });

    // Handle window resize
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            if (window.innerWidth > 768) {
                document.querySelectorAll('.fau-navigation .sub-menu.is-open').forEach(submenu => {
                    const button = submenu.closest('.menu-item').querySelector('.fau-navigation__button');
                    if (button) {
                        const buttonRect = button.getBoundingClientRect();
                        const submenuRect = submenu.getBoundingClientRect();
                        
                        const rightEdge = buttonRect.right + submenuRect.width;
                        if (rightEdge > window.innerWidth) {
                            submenu.style.right = '0';
                        } else {
                            submenu.style.right = 'auto';
                            submenu.style.left = buttonRect.left + 'px';
                        }
                    }
                });
            }
        }, 250);
    });
}); 