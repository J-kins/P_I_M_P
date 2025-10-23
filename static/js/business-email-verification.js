/**
 * P.I.M.P - Business Email Verification JavaScript
 * Handles email verification process, countdown timer, and resend functionality
 */

class BusinessEmailVerification {
    constructor() {
        this.currentState = 'pending'; // pending, sent, verified, expired, invalid
        this.email = '';
        this.resendCooldown = 300; // 5 minutes in seconds
        this.countdownInterval = null;
        this.initialize();
    }

    initialize() {
        this.bindEventListeners();
        this.loadInitialState();
        this.initializeCountdown();
        this.updateUI();
    }

    // Event listeners
    bindEventListeners() {
        // Resend verification buttons
        document.querySelectorAll('#resendVerification, #resendAgain, #resendExpired, #resendInvalid').forEach(button => {
            button.addEventListener('click', () => {
                this.resendVerification();
            });
        });

        // Change email buttons
        document.querySelectorAll('#changeEmail, #changeEmailSent, #changeEmailExpired').forEach(button => {
            button.addEventListener('click', () => {
                this.showChangeEmailForm();
            });
        });

        // Cancel change email
        const cancelChangeButton = document.getElementById('cancelChangeEmail');
        if (cancelChangeButton) {
            cancelChangeButton.addEventListener('click', () => {
                this.hideChangeEmailForm();
            });
        }

        // Change email form submission
        const changeEmailForm = document.getElementById('changeEmailForm');
        if (changeEmailForm) {
            changeEmailForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitEmailChange();
            });
        }

        // Real-time email validation
        const emailInputs = document.querySelectorAll('input[type="email"]');
        emailInputs.forEach(input => {
            input.addEventListener('blur', () => {
                this.validateEmail(input);
            });
        });

        // Check verification status periodically
        this.startStatusPolling();
    }

    // Initial state loading
    loadInitialState() {
        // Get initial state from URL parameters or default to pending
        const urlParams = new URLSearchParams(window.location.search);
        this.currentState = urlParams.get('status') || 'pending';
        this.email = urlParams.get('email') || this.getStoredEmail() || 'business@example.com';

        // Store email for persistence
        this.storeEmail(this.email);
    }

    // UI management
    updateUI() {
        // Hide all states first
        document.querySelectorAll('.verification-state').forEach(state => {
            state.classList.remove('active');
        });

        // Show current state
        const currentStateElement = document.getElementById(`${this.currentState}State`);
        if (currentStateElement) {
            currentStateElement.classList.add('active');
        }

        // Update email displays
        this.updateEmailDisplays();

        // Update progress steps
        this.updateProgressSteps();

        // Show/hide countdown based on state
        this.updateCountdownVisibility();
    }

    updateEmailDisplays() {
        const emailDisplays = document.querySelectorAll('#verificationEmail, #sentEmail');
        emailDisplays.forEach(display => {
            if (display) {
                display.textContent = this.email;
            }
        });
    }

    updateProgressSteps() {
        const steps = document.querySelectorAll('.progress-step');
        steps.forEach(step => {
            step.classList.remove('completed', 'active');
        });

        // Mark first step as completed
        steps[0].classList.add('completed');

        // Mark second step based on verification status
        if (this.currentState === 'verified') {
            steps[1].classList.add('completed');
            steps[2].classList.add('active');
        } else {
            steps[1].classList.add('active');
        }
    }

    updateCountdownVisibility() {
        const countdownSection = document.getElementById('countdownSection');
        if (this.shouldShowCountdown()) {
            countdownSection.classList.add('active');
        } else {
            countdownSection.classList.remove('active');
        }
    }

    shouldShowCountdown() {
        return ['pending', 'sent', 'expired', 'invalid'].includes(this.currentState) && 
               this.getRemainingCooldown() > 0;
    }

    // Countdown timer
    initializeCountdown() {
        const remainingTime = this.getRemainingCooldown();
        if (remainingTime > 0) {
            this.startCountdown(remainingTime);
        }
    }

    startCountdown(seconds) {
        this.updateCountdownDisplay(seconds);

        this.countdownInterval = setInterval(() => {
            seconds--;
            this.updateCountdownDisplay(seconds);

            if (seconds <= 0) {
                this.stopCountdown();
                this.updateCountdownVisibility();
            }
        }, 1000);
    }

    stopCountdown() {
        if (this.countdownInterval) {
            clearInterval(this.countdownInterval);
            this.countdownInterval = null;
        }
    }

    updateCountdownDisplay(seconds) {
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = seconds % 60;

        const minutesElement = document.querySelector('.countdown-minutes');
        const secondsElement = document.querySelector('.countdown-seconds');

        if (minutesElement && secondsElement) {
            minutesElement.textContent = minutes.toString().padStart(2, '0');
            secondsElement.textContent = remainingSeconds.toString().padStart(2, '0');
        }

        // Store remaining time for page refreshes
        localStorage.setItem('verificationCooldown', seconds.toString());
        localStorage.setItem('verificationCooldownStart', Date.now().toString());
    }

    getRemainingCooldown() {
        const storedCooldown = localStorage.getItem('verificationCooldown');
        const storedStart = localStorage.getItem('verificationCooldownStart');

        if (storedCooldown && storedStart) {
            const elapsed = Math.floor((Date.now() - parseInt(storedStart)) / 1000);
            const remaining = parseInt(storedCooldown) - elapsed;
            return Math.max(0, remaining);
        }

        return 0;
    }

    // Email change functionality
    showChangeEmailForm() {
        this.hideAllStates();
        document.getElementById('changeEmailState').classList.add('active');
    }

    hideChangeEmailForm() {
        document.getElementById('changeEmailState').classList.remove('active');
        this.showCurrentState();
    }

    hideAllStates() {
        document.querySelectorAll('.verification-state').forEach(state => {
            state.classList.remove('active');
        });
    }

    showCurrentState() {
        this.updateUI();
    }

    async submitEmailChange() {
        const newEmail = document.getElementById('newEmail').value.trim();
        const confirmEmail = document.getElementById('confirmNewEmail').value.trim();

        // Validate form
        if (!this.validateEmailChange(newEmail, confirmEmail)) {
            return;
        }

        // Show loading state
        const submitButton = document.querySelector('.update-email-button');
        const originalText = submitButton.innerHTML;
        submitButton.innerHTML = '<span class="loading-spinner"></span> Updating...';
        submitButton.disabled = true;

        try {
            // Simulate API call to update email
            await this.updateEmailAddress(newEmail);
            
            // Update local email
            this.email = newEmail;
            this.storeEmail(newEmail);
            
            // Show success message and return to verification
            this.showSuccessMessage('Email updated successfully! Sending new verification...');
            
            // Wait a moment then resend verification
            setTimeout(() => {
                this.hideChangeEmailForm();
                this.resendVerification();
            }, 1500);
            
        } catch (error) {
            console.error('Email change error:', error);
            this.showError('newEmailError', 'Failed to update email. Please try again.');
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
        }
    }

    validateEmailChange(newEmail, confirmEmail) {
        let isValid = true;

        // Clear previous errors
        this.clearErrors();

        // Validate new email
        if (!newEmail) {
            this.showError('newEmailError', 'New email address is required');
            isValid = false;
        } else if (!this.isValidEmail(newEmail)) {
            this.showError('newEmailError', 'Please enter a valid email address');
            isValid = false;
        }

        // Validate email confirmation
        if (!confirmEmail) {
            this.showError('confirmNewEmailError', 'Please confirm your email address');
            isValid = false;
        } else if (newEmail !== confirmEmail) {
            this.showError('confirmNewEmailError', 'Email addresses do not match');
            isValid = false;
        }

        return isValid;
    }

    // Verification resend functionality
    async resendVerification() {
        const remainingCooldown = this.getRemainingCooldown();
        
        if (remainingCooldown > 0) {
            this.showError('resendError', `Please wait ${this.formatTime(remainingCooldown)} before resending`);
            return;
        }

        // Show loading state
        const resendButtons = document.querySelectorAll('.resend-button');
        resendButtons.forEach(button => {
            const originalText = button.innerHTML;
            button.innerHTML = '<span class="loading-spinner"></span> Sending...';
            button.disabled = true;

            // Restore button after a delay (in case of error)
            setTimeout(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            }, 3000);
        });

        try {
            // Simulate API call to resend verification
            await this.sendVerificationEmail();
            
            // Update state to show sent confirmation
            this.currentState = 'sent';
            this.updateUI();
            
            // Start cooldown period
            this.startCountdown(this.resendCooldown);
            this.updateCountdownVisibility();
            
            // Show success message
            this.showSuccessMessage('Verification email sent successfully!');
            
        } catch (error) {
            console.error('Resend error:', error);
            this.showError('resendError', 'Failed to send verification email. Please try again.');
        }
    }

    // Status polling
    startStatusPolling() {
        // Only poll if verification is pending
        if (this.currentState === 'pending' || this.currentState === 'sent') {
            setInterval(() => {
                this.checkVerificationStatus();
            }, 10000); // Check every 10 seconds
        }
    }

    async checkVerificationStatus() {
        try {
            const status = await this.fetchVerificationStatus();
            
            if (status !== this.currentState) {
                this.currentState = status;
                this.updateUI();
                
                if (status === 'verified') {
                    this.stopCountdown();
                    this.showSuccessMessage('Email verified successfully!');
                }
            }
        } catch (error) {
            console.error('Status check error:', error);
        }
    }

    // API simulation methods
    async sendVerificationEmail() {
        // Simulate API call
        return new Promise((resolve, reject) => {
            setTimeout(() => {
                // Simulate random failures (10% chance)
                if (Math.random() < 0.1) {
                    reject(new Error('Network error'));
                } else {
                    resolve({ success: true });
                }
            }, 1500);
        });
    }

    async updateEmailAddress(newEmail) {
        // Simulate API call
        return new Promise((resolve, reject) => {
            setTimeout(() => {
                // Simulate random failures (5% chance)
                if (Math.random() < 0.05) {
                    reject(new Error('Update failed'));
                } else {
                    resolve({ success: true });
                }
            }, 2000);
        });
    }

    async fetchVerificationStatus() {
        // Simulate API call to check verification status
        return new Promise((resolve) => {
            setTimeout(() => {
                // In a real app, this would check the actual verification status
                // For demo purposes, we'll randomly verify after some time
                if (this.currentState === 'pending' || this.currentState === 'sent') {
                    const shouldVerify = Math.random() < 0.1; // 10% chance per check
                    resolve(shouldVerify ? 'verified' : this.currentState);
                } else {
                    resolve(this.currentState);
                }
            }, 1000);
        });
    }

    // Utility methods
    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    validateEmail(input) {
        const value = input.value.trim();
        const errorId = `${input.id}Error`;
        
        if (!value) {
            this.showError(errorId, 'Email address is required');
            return false;
        } else if (!this.isValidEmail(value)) {
            this.showError(errorId, 'Please enter a valid email address');
            return false;
        } else {
            this.hideError(errorId);
            return true;
        }
    }

    showError(elementId, message) {
        const element = document.getElementById(elementId);
        if (element) {
            element.textContent = message;
            element.classList.add('show');
        }
    }

    hideError(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.classList.remove('show');
        }
    }

    clearErrors() {
        const errorElements = document.querySelectorAll('.error-message');
        errorElements.forEach(element => {
            element.classList.remove('show');
        });
    }

    showSuccessMessage(message) {
        // Create temporary success message
        const successElement = document.createElement('div');
        successElement.className = 'success-message show';
        successElement.textContent = message;
        successElement.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #10b981;
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            animation: slideInRight 0.3s ease;
        `;

        document.body.appendChild(successElement);

        // Remove after 5 seconds
        setTimeout(() => {
            successElement.remove();
        }, 5000);
    }

    formatTime(seconds) {
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = seconds % 60;
        return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
    }

    storeEmail(email) {
        localStorage.setItem('businessVerificationEmail', email);
    }

    getStoredEmail() {
        return localStorage.getItem('businessVerificationEmail');
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.businessEmailVerification = new BusinessEmailVerification();
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = BusinessEmailVerification;
}
