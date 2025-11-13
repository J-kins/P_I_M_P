/**
 * P.I.M.P - Email Verification JavaScript
 * Handles email verification and resend functionality
 */

class EmailVerificationHandler {
    constructor() {
        this.resendButton = document.getElementById('resendButton');
        this.resendExpiredButton = document.getElementById('resendExpiredButton');
        this.resendInvalidButton = document.getElementById('resendInvalidButton');
        this.cooldownInterval = null;

        this.init();
    }

    init() {
        // Setup resend buttons
        this.setupResendButton(this.resendButton);
        this.setupResendButton(this.resendExpiredButton);
        this.setupResendButton(this.resendInvalidButton);

        // Start countdown if there's a cooldown
        this.startCooldownTimer();

        // Auto-redirect on success
        this.checkAutoRedirect();
    }

    setupResendButton(button) {
        if (!button) return;

        button.addEventListener('click', async () => {
            await this.handleResend(button);
        });
    }

    async handleResend(button) {
        const originalText = button.querySelector('.button-text').textContent;
        
        // Disable button and show loading
        button.disabled = true;
        button.querySelector('.button-text').textContent = 'Sending...';

        try {
            // Make API call to resend verification email
            const response = await fetch('/api/resend-verification', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    email: this.getEmailFromSession()
                })
            });

            const result = await response.json();

            if (result.success) {
                // Show success message
                this.showNotification('Verification email sent successfully!', 'success');
                button.querySelector('.button-text').textContent = 'Email Sent!';

                // Start cooldown
                if (result.cooldown) {
                    this.startCooldown(button, result.cooldown);
                } else {
                    this.startCooldown(button, 60); // Default 60 seconds
                }
            } else {
                // Show error message
                this.showNotification(result.error || 'Failed to send email', 'error');
                button.disabled = false;
                button.querySelector('.button-text').textContent = originalText;
            }
        } catch (error) {
            console.error('Resend error:', error);
            this.showNotification('An error occurred. Please try again.', 'error');
            button.disabled = false;
            button.querySelector('.button-text').textContent = originalText;
        }
    }

    startCooldown(button, seconds) {
        let remaining = seconds;
        
        const updateButton = () => {
            if (remaining > 0) {
                button.querySelector('.button-text').textContent = `Resend in ${remaining}s`;
                remaining--;
                setTimeout(updateButton, 1000);
            } else {
                button.disabled = false;
                button.querySelector('.button-text').textContent = 'Resend Email';
            }
        };

        updateButton();
    }

    startCooldownTimer() {
        const buttonText = this.resendButton?.querySelector('.button-text');
        if (!buttonText) return;

        const text = buttonText.textContent;
        const match = text.match(/Resend in (\d+)s/);
        
        if (match) {
            const seconds = parseInt(match[1]);
            this.startCooldown(this.resendButton, seconds);
        }
    }

    getEmailFromSession() {
        // This would typically come from a session or data attribute
        const emailElement = document.querySelector('.verification-email strong');
        return emailElement ? emailElement.textContent : '';
    }

    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            <span>${message}</span>
        `;

        // Add to page
        document.body.appendChild(notification);

        // Animate in
        setTimeout(() => notification.classList.add('show'), 10);

        // Remove after 5 seconds
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 5000);
    }

    checkAutoRedirect() {
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');

        if (status === 'success') {
            // Auto-redirect to login after 3 seconds
            setTimeout(() => {
                window.location.href = '/login';
            }, 3000);

            // Show countdown
            this.showRedirectCountdown(3);
        }
    }

    showRedirectCountdown(seconds) {
        const countdownElement = document.createElement('p');
        countdownElement.className = 'redirect-countdown';
        countdownElement.textContent = `Redirecting to login in ${seconds} seconds...`;

        const actions = document.querySelector('.verification-actions');
        if (actions) {
            actions.appendChild(countdownElement);

            let remaining = seconds - 1;
            const interval = setInterval(() => {
                if (remaining > 0) {
                    countdownElement.textContent = `Redirecting to login in ${remaining} seconds...`;
                    remaining--;
                } else {
                    clearInterval(interval);
                }
            }, 1000);
        }
    }
}

// Email check functionality for expired/invalid tokens
class EmailInputHandler {
    constructor() {
        this.emailInput = document.getElementById('verificationEmail');
        this.submitButton = document.getElementById('submitEmailButton');

        if (this.emailInput && this.submitButton) {
            this.init();
        }
    }

    init() {
        this.submitButton.addEventListener('click', async () => {
            const email = this.emailInput.value.trim();

            if (!this.validateEmail(email)) {
                this.showError('Please enter a valid email address');
                return;
            }

            await this.requestNewVerification(email);
        });

        this.emailInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.submitButton.click();
            }
        });
    }

    validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    showError(message) {
        const errorElement = document.getElementById('emailInputError');
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }
    }

    async requestNewVerification(email) {
        this.submitButton.disabled = true;
        this.submitButton.textContent = 'Sending...';

        try {
            const response = await fetch('/api/resend-verification', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email })
            });

            const result = await response.json();

            if (result.success) {
                window.location.href = '/verify-email?status=pending';
            } else {
                this.showError(result.error || 'Failed to send verification email');
                this.submitButton.disabled = false;
                this.submitButton.textContent = 'Send Verification Email';
            }
        } catch (error) {
            console.error('Verification request error:', error);
            this.showError('An error occurred. Please try again.');
            this.submitButton.disabled = false;
            this.submitButton.textContent = 'Send Verification Email';
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new EmailVerificationHandler();
    new EmailInputHandler();
});

// Add notification styles dynamically
const style = document.createElement('style');
style.textContent = `
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        display: flex;
        align-items: center;
        gap: 10px;
        transform: translateX(400px);
        transition: transform 0.3s ease;
        z-index: 10000;
    }

    .notification.show {
        transform: translateX(0);
    }

    .notification-success {
        border-left: 4px solid #10b981;
    }

    .notification-success i {
        color: #10b981;
    }

    .notification-error {
        border-left: 4px solid #ef4444;
    }

    .notification-error i {
        color: #ef4444;
    }

    .redirect-countdown {
        margin-top: 15px;
        text-align: center;
        color: #666;
        font-size: 14px;
    }
`;
document.head.appendChild(style);
