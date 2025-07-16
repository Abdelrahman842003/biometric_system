/**
 * ========================================
 * Advanced Centered Toggle Navbar System
 * نظام الـ navbar المتقدم القابل للتحكم
 * ========================================
 */

class NavbarController {
    constructor() {
        this.navbarMenu = document.getElementById('navbarMenu');
        this.menuToggle = document.getElementById('menuToggle');
        this.overlay = document.getElementById('navbarOverlay');
        this.isOpen = false;
        this.isAnimating = false;
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.setActiveLink();
        this.addKeyboardSupport();
    }
    
    bindEvents() {
        // Toggle button click
        if (this.menuToggle) {
            this.menuToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggle();
            });
        }
        
        // Overlay click
        if (this.overlay) {
            this.overlay.addEventListener('click', () => {
                this.close();
            });
        }
        
        // Click outside to close
        document.addEventListener('click', (e) => {
            if (this.isOpen && !this.isClickInside(e.target)) {
                this.close();
            }
        });
        
        // Prevent menu click from closing
        if (this.navbarMenu) {
            this.navbarMenu.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        }
        
        // Escape key support
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                this.close();
            }
        });
        
        // Touch support for mobile
        this.addTouchSupport();
    }
    
    toggle() {
        if (this.isAnimating) return;
        
        this.isOpen ? this.close() : this.open();
    }
    
    open() {
        if (this.isAnimating || this.isOpen) return;
        
        this.isAnimating = true;
        this.isOpen = true;
        
        // Add classes with slight delay for smoother animation
        requestAnimationFrame(() => {
            this.overlay?.classList.add('show');
            this.menuToggle?.classList.add('active');
            
            setTimeout(() => {
                this.navbarMenu?.classList.add('show');
                this.animateMenuItems(true);
            }, 100);
        });
        
        // Reset animation flag
        setTimeout(() => {
            this.isAnimating = false;
        }, 800);
        
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
        
        // Focus management
        this.trapFocus();
    }
    
    close() {
        if (this.isAnimating || !this.isOpen) return;
        
        this.isAnimating = true;
        this.isOpen = false;
        
        // Remove classes in reverse order
        this.animateMenuItems(false);
        this.navbarMenu?.classList.remove('show');
        
        setTimeout(() => {
            this.menuToggle?.classList.remove('active');
            this.overlay?.classList.remove('show');
        }, 200);
        
        // Reset animation flag
        setTimeout(() => {
            this.isAnimating = false;
        }, 600);
        
        // Restore body scroll
        document.body.style.overflow = '';
        
        // Return focus to toggle button
        this.menuToggle?.focus();
    }
    
    animateMenuItems(show) {
        const items = this.navbarMenu?.querySelectorAll('.nav-item');
        if (!items) return;
        
        items.forEach((item, index) => {
            if (show) {
                setTimeout(() => {
                    item.style.animation = `slideInItem 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards`;
                }, index * 100);
            } else {
                item.style.animation = 'none';
            }
        });
    }
    
    isClickInside(target) {
        const navbar = document.querySelector('.navbar');
        return navbar?.contains(target) || false;
    }
    
    setActiveLink() {
        const currentPage = window.location.pathname.split('/').pop();
        const navLinks = document.querySelectorAll('.nav-link');
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            const href = link.getAttribute('href');
            if (href && href.includes(currentPage)) {
                link.classList.add('active');
            }
        });
    }
    
    addKeyboardSupport() {
        const navLinks = this.navbarMenu?.querySelectorAll('.nav-link');
        if (!navLinks) return;
        
        navLinks.forEach((link, index) => {
            link.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    const nextLink = navLinks[index + 1] || navLinks[0];
                    nextLink.focus();
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    const prevLink = navLinks[index - 1] || navLinks[navLinks.length - 1];
                    prevLink.focus();
                }
            });
        });
    }
    
    trapFocus() {
        const focusableElements = this.navbarMenu?.querySelectorAll(
            'a, button, [tabindex]:not([tabindex="-1"])'
        );
        
        if (!focusableElements || focusableElements.length === 0) return;
        
        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];
        
        // Focus first element
        setTimeout(() => {
            firstElement.focus();
        }, 300);
        
        document.addEventListener('keydown', this.handleFocusTrap.bind(this));
    }
    
    handleFocusTrap(e) {
        if (!this.isOpen) return;
        
        const focusableElements = this.navbarMenu?.querySelectorAll(
            'a, button, [tabindex]:not([tabindex="-1"])'
        );
        
        if (!focusableElements || focusableElements.length === 0) return;
        
        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];
        
        if (e.key === 'Tab') {
            if (e.shiftKey) {
                if (document.activeElement === firstElement) {
                    e.preventDefault();
                    lastElement.focus();
                }
            } else {
                if (document.activeElement === lastElement) {
                    e.preventDefault();
                    firstElement.focus();
                }
            }
        }
    }
    
    addTouchSupport() {
        let startY = 0;
        let startX = 0;
        
        if (this.navbarMenu) {
            this.navbarMenu.addEventListener('touchstart', (e) => {
                startY = e.touches[0].clientY;
                startX = e.touches[0].clientX;
            });
            
            this.navbarMenu.addEventListener('touchmove', (e) => {
                if (!this.isOpen) return;
                
                const currentY = e.touches[0].clientY;
                const currentX = e.touches[0].clientX;
                const diffY = startY - currentY;
                const diffX = startX - currentX;
                
                // Swipe up to close
                if (diffY > 50 && Math.abs(diffX) < 100) {
                    this.close();
                }
                
                // Swipe left/right to close
                if (Math.abs(diffX) > 100 && Math.abs(diffY) < 50) {
                    this.close();
                }
            });
        }
    }
}

// Initialize navbar controller when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.navbarController = new NavbarController();
});

// Legacy functions for compatibility
function toggleNavbar() {
    window.navbarController?.toggle();
}

function toggleMobileMenu() {
    toggleNavbar();
}

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = NavbarController;
}
