/**
 * P.I.M.P - OffCanvas Components
 * Handles off-canvas sidebars, drawers, and slide-out panels
 */

class OffCanvasComponent {
    constructor(element, pimp) {
        this.element = element;
        this.pimp = pimp || window.PIMP;
        this.isOpen = false;
        this.backdrop = null;
        this.originalBodyOverflow = '';
        this.position = this.element.getAttribute('data-position') || 'right';
        this.init();
    }

    init() {
        if (!this.element) {
            console.error('OffCanvasComponent: Element not found');
            return;
        }

        this.setupEventHandlers();
        this.createBackdrop();
        this.setupAccessibility();
        this.setPosition(this.position);
    }

    setupEventHandlers() {
        // Close buttons
        const closeButtons = this.element.querySelectorAll('[data-offcanvas-close], .offcanvas-close, .close-offcanvas');
        closeButtons.forEach(btn => {
            btn.addEventListener('click', () => this.hide());
        });

        // Backdrop click
        if (this.backdrop) {
            this.backdrop.addEventListener('click', () => {
                const allowBackdropClose = this.element.getAttribute('data-backdrop-close') !== 'false';
                if (allowBackdropClose) {
                    this.hide();
                }
            });
        }

        // Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                const allowEscapeClose = this.element.getAttribute('data-escape-close') !== 'false';
                if (allowEscapeClose) {
                    this.hide();
                }
            }
        });

        // Prevent body scroll when offcanvas is open
        this.element.addEventListener('wheel', (e) => {
            if (this.isOpen) {
                e.stopPropagation();
            }
        }, { passive: false });
    }

    createBackdrop() {
        this.backdrop = document.createElement('div');
        this.backdrop.className = 'offcanvas-backdrop';
        this.backdrop.setAttribute('aria-hidden', 'true');
        this.element.parentNode.insertBefore(this.backdrop, this.element);
    }

    setupAccessibility() {
        // Set ARIA attributes
        this.element.setAttribute('role', 'dialog');
        this.element.setAttribute('aria-modal', 'true');
        this.element.setAttribute('aria-hidden', 'true');

        // Find focusable elements
        this.focusableElements = this.element.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        this.firstFocusable = this.focusableElements[0];
        this.lastFocusable = this.focusableElements[this.focusableElements.length - 1];

        // Trap focus within offcanvas
        this.element.addEventListener('keydown', (e) => {
            if (e.key === 'Tab' && this.isOpen) {
                if (e.shiftKey) {
                    if (document.activeElement === this.firstFocusable) {
                        e.preventDefault();
                        this.lastFocusable?.focus();
                    }
                } else {
                    if (document.activeElement === this.lastFocusable) {
                        e.preventDefault();
                        this.firstFocusable?.focus();
                    }
                }
            }
        });
    }

    setPosition(position) {
        // Remove existing position classes
        this.element.classList.remove('offcanvas-left', 'offcanvas-right', 'offcanvas-top', 'offcanvas-bottom');
        
        // Add new position class
        this.position = position;
        this.element.classList.add(`offcanvas-${position}`);
        this.element.setAttribute('data-position', position);
    }

    show() {
        if (this.isOpen) return;

        this.isOpen = true;
        this.element.classList.add('show', 'active');
        this.element.setAttribute('aria-hidden', 'false');

        if (this.backdrop) {
            this.backdrop.classList.add('show');
            this.backdrop.setAttribute('aria-hidden', 'false');
        }

        // Prevent body scroll
        this.originalBodyOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';

        // Focus first element
        setTimeout(() => {
            this.firstFocusable?.focus() || this.element.focus();
        }, 100);

        // Trigger event
        this.emitEvent('offcanvas:show', { offcanvas: this.element });

        // Animation
        requestAnimationFrame(() => {
            this.element.classList.add('slide-in');
        });
    }

    hide() {
        if (!this.isOpen) return;

        this.isOpen = false;
        this.element.classList.remove('show', 'active', 'slide-in');
        this.element.setAttribute('aria-hidden', 'true');

        if (this.backdrop) {
            this.backdrop.classList.remove('show');
            this.backdrop.setAttribute('aria-hidden', 'true');
        }

        // Restore body scroll
        document.body.style.overflow = this.originalBodyOverflow;

        // Trigger event
        this.emitEvent('offcanvas:hide', { offcanvas: this.element });
    }

    toggle() {
        if (this.isOpen) {
            this.hide();
        } else {
            this.show();
        }
    }

    setContent(content) {
        const contentArea = this.element.querySelector('.offcanvas-content, .offcanvas-body');
        if (contentArea) {
            if (typeof content === 'string') {
                contentArea.innerHTML = content;
            } else if (content instanceof HTMLElement) {
                contentArea.innerHTML = '';
                contentArea.appendChild(content);
            }
        } else {
            this.element.innerHTML = content;
        }

        // Reinitialize focusable elements
        this.focusableElements = this.element.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        this.firstFocusable = this.focusableElements[0];
        this.lastFocusable = this.focusableElements[this.focusableElements.length - 1];
    }

    setTitle(title) {
        const titleElement = this.element.querySelector('.offcanvas-title, .offcanvas-header h1, .offcanvas-header h2, .offcanvas-header h3');
        if (titleElement) {
            titleElement.textContent = title;
        }
    }

    emitEvent(eventName, data) {
        const event = new CustomEvent(eventName, {
            detail: data,
            bubbles: true
        });
        this.element.dispatchEvent(event);

        if (this.pimp?.emitEvent) {
            this.pimp.emitEvent(eventName, data);
        }
    }

    destroy() {
        this.hide();
        if (this.backdrop) {
            this.backdrop.remove();
        }
        this.element.remove();
    }
}

// Auto-initialize offcanvas components on page load
document.addEventListener('DOMContentLoaded', () => {
    // Initialize all offcanvas with data-offcanvas attribute
    document.querySelectorAll('[data-offcanvas]').forEach(element => {
        if (!element.offcanvasComponent) {
            element.offcanvasComponent = new OffCanvasComponent(element);
        }
    });

    // Handle offcanvas triggers
    document.querySelectorAll('[data-offcanvas-target]').forEach(trigger => {
        trigger.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = trigger.getAttribute('data-offcanvas-target');
            const offcanvasElement = document.querySelector(targetId);
            
            if (offcanvasElement) {
                if (!offcanvasElement.offcanvasComponent) {
                    offcanvasElement.offcanvasComponent = new OffCanvasComponent(offcanvasElement);
                }
                offcanvasElement.offcanvasComponent.show();
            }
        });
    });

    // Handle toggle buttons
    document.querySelectorAll('[data-offcanvas-toggle]').forEach(trigger => {
        trigger.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = trigger.getAttribute('data-offcanvas-toggle');
            const offcanvasElement = document.querySelector(targetId);
            
            if (offcanvasElement) {
                if (!offcanvasElement.offcanvasComponent) {
                    offcanvasElement.offcanvasComponent = new OffCanvasComponent(offcanvasElement);
                }
                offcanvasElement.offcanvasComponent.toggle();
            }
        });
    });
});

// Export for module usage
if (typeof window !== 'undefined') {
    window.OffCanvasComponent = OffCanvasComponent;
}

if (typeof module !== 'undefined' && module.exports) {
    module.exports = OffCanvasComponent;
}


