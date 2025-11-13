/**
 * P.I.M.P - Modal Component
 * Handles modal dialogs and popups
 */

class ModalComponent {
    constructor(element, pimp) {
        this.element = element;
        this.pimp = pimp || window.PIMP;
        this.isOpen = false;
        this.backdrop = null;
        this.originalBodyOverflow = '';
        this.init();
    }

    init() {
        if (!this.element) {
            console.error('ModalComponent: Element not found');
            return;
        }

        this.setupEventHandlers();
        this.createBackdrop();
        this.setupAccessibility();
    }

    setupEventHandlers() {
        // Close buttons
        const closeButtons = this.element.querySelectorAll('[data-modal-close], .modal-close, .close-modal');
        closeButtons.forEach(btn => {
            btn.addEventListener('click', () => this.hide());
        });

        // Backdrop click
        if (this.backdrop) {
            this.backdrop.addEventListener('click', (e) => {
                if (e.target === this.backdrop) {
                    const allowBackdropClose = this.element.getAttribute('data-backdrop-close') !== 'false';
                    if (allowBackdropClose) {
                        this.hide();
                    }
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

        // Prevent body scroll when modal is open
        this.element.addEventListener('wheel', (e) => {
            if (this.isOpen) {
                e.stopPropagation();
            }
        }, { passive: false });
    }

    createBackdrop() {
        this.backdrop = document.createElement('div');
        this.backdrop.className = 'modal-backdrop';
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

        // Trap focus within modal
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
        this.emitEvent('modal:show', { modal: this.element });

        // Animation
        requestAnimationFrame(() => {
            this.element.classList.add('fade-in');
        });
    }

    hide() {
        if (!this.isOpen) return;

        this.isOpen = false;
        this.element.classList.remove('show', 'active', 'fade-in');
        this.element.setAttribute('aria-hidden', 'true');

        if (this.backdrop) {
            this.backdrop.classList.remove('show');
            this.backdrop.setAttribute('aria-hidden', 'true');
        }

        // Restore body scroll
        document.body.style.overflow = this.originalBodyOverflow;

        // Trigger event
        this.emitEvent('modal:hide', { modal: this.element });
    }

    toggle() {
        if (this.isOpen) {
            this.hide();
        } else {
            this.show();
        }
    }

    setContent(content) {
        const contentArea = this.element.querySelector('.modal-content, .modal-body');
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
        const titleElement = this.element.querySelector('.modal-title, .modal-header h1, .modal-header h2, .modal-header h3');
        if (titleElement) {
            titleElement.textContent = title;
        }
    }

    setSize(size) {
        // Remove existing size classes
        this.element.classList.remove('modal-sm', 'modal-md', 'modal-lg', 'modal-xl');
        
        // Add new size class
        if (size) {
            this.element.classList.add(`modal-${size}`);
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

// Auto-initialize modals on page load
document.addEventListener('DOMContentLoaded', () => {
    // Initialize all modals with data-modal attribute
    document.querySelectorAll('[data-modal]').forEach(element => {
        if (!element.modalComponent) {
            element.modalComponent = new ModalComponent(element);
        }
    });

    // Handle modal triggers
    document.querySelectorAll('[data-modal-target]').forEach(trigger => {
        trigger.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = trigger.getAttribute('data-modal-target');
            const modalElement = document.querySelector(targetId);
            
            if (modalElement) {
                if (!modalElement.modalComponent) {
                    modalElement.modalComponent = new ModalComponent(modalElement);
                }
                modalElement.modalComponent.show();
            }
        });
    });
});

// Export for module usage
if (typeof window !== 'undefined') {
    window.ModalComponent = ModalComponent;
}

if (typeof module !== 'undefined' && module.exports) {
    module.exports = ModalComponent;
}
