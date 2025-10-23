/**
 * P.I.M.P - Register JavaScript
 * Handles registration form validation and submission
 */

class RegisterHandler {
    constructor(formId) {
        this.form = document.getElementById(formId);
        if (!this.form) return;

        this.inputs = {
            firstName: document.getElementById('first_name'),
            lastName: document.getElementById('last_name'),
            email: document.getElementById('email'),
            phone: document.getElementById('phone'),
            password: document.getElementById('password'),
            confirmPassword: document.getElementById('confirm_password'),
            terms: document.getElementById('terms')
        };

        this.submitButton = this.form.querySelector('button[type="submit"]');
        this.loader = document.getElementById('registerLoader');

        this.init();
    }

    init() {
        // Password toggles
        this.setupPasswordToggles();

        // Password strength indicator
        this.setupPasswordStrength();

        // User type selection
        this.setupUserTypeSelection();

        // Real-time validation
        this.setupValidation();

        // Form submission
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));

        // Social registration
        this.setupSocialRegistration();
    }

    setupPasswordToggles() {
        const passwordToggle = document.getElementById('passwordToggle');
        const confirmPasswordToggle = document.getElementById('confirmPasswordToggle');

        if (passwordToggle) {
            passwordToggle.addEventListener('click', () => {
                this.togglePasswordVisibility(this.inputs.password, passwordToggle);
            });
        }

        if (confirmPasswordToggle) {
            confirmPasswordToggle.addEventListener('click', () => {
                this.togglePasswordVisibility(this.inputs.confirmPassword, confirmPasswordToggle);
            });
        }
    }

    togglePasswordVisibility(input, button) {
        const type = input.type === 'password' ? 'text' : 'password';
        input.type = type;
        
        const icon = button.querySelector('i');
        icon.className = type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
    }

    setupPasswordStrength() {
        if (this.inputs.password) {
            this.inputs.password.addEventListener('input', () => {
                this.checkPasswordStrength(this.inputs.password.value);
                this.validatePasswordMatch();
            });
        }

        if (this.inputs.confirmPassword) {
            this.inputs.confirmPassword.addEventListener('input', () => {
                this.validatePasswordMatch();
            });
        }
    }

    checkPasswordStrength(password) {
        const strengthBar = document.querySelector('.strength-bar');
        const strengthText = document.querySelector('.strength-text');
        
        if (!strengthBar || !strengthText) return;

        let strength = 0;

        // Length checks
        if (password.length >= 8) strength += 25;
        if (password.length >= 12) strength += 25;

        // Character variety
        if (/[a-z]/.test(password)) strength += 15;
        if (/[A-Z]/.test(password)) strength += 15;
        if (/[0-9]/.test(password)) strength += 10;
        if (/[^a-zA-Z0-9]/.test(password)) strength += 10;

        // Update UI
        if (strength >= 80) {
            strengthBar.style.width = '100%';
            strengthBar.style.backgroundColor = '#10b981';
            strengthText.textContent = 'Strong password';
            strengthText.style.color = '#10b981';
        } else if (strength >= 60) {
            strengthBar.style.width = '75%';
            strengthBar.style.backgroundColor = '#f59e0b';
            strengthText.textContent = 'Good password';
            strengthText.style.color = '#f59e0b';
        } else if (strength >= 40) {
            strengthBar.style.width = '50%';
            strengthBar.style.backgroundColor = '#f59e0b';
            strengthText.textContent = 'Fair password';
            strengthText.style.color = '#f59e0b';
        } else if (password.length > 0) {
            strengthBar.style.width = '25%';
            strengthBar.style.backgroundColor = '#ef4444';
            strengthText.textContent = 'Weak password';
            strengthText.style.color = '#ef4444';
        } else {
            strengthBar.style.width = '0%';
            strengthText.textContent = '';
        }
    }

    validatePasswordMatch() {
        const password = this.inputs.password.value;
        const confirmPassword = this.inputs.confirmPassword.value;
        const errorElement = document.getElementById('confirmPasswordError');

        if (confirmPassword && password !== confirmPassword) {
            this.showError(this.inputs.confirmPassword, errorElement, 'Passwords do not match');
            return false;
        } else if (confirmPassword) {
            this.clearError(this.inputs.confirmPassword, errorElement);
            return true;
        }
        return true;
    }

    setupUserTypeSelection() {
        const userTypeOptions = document.querySelectorAll('.user-type-option');
        
        userTypeOptions.forEach(option => {
            option.addEventListener('click', function() {
                userTypeOptions.forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
            });
        });
    }

    setupValidation() {
        // Auto-clear errors on input
        Object.values(this.inputs).forEach(input => {
            if (input) {
                input.addEventListener('input', () => {
                    if (input.classList.contains('error')) {
                        const errorId = input.id + 'Error';
                        this.clearError(input, document.getElementById(errorId));
                    }
                });
            }
        });

        // Blur validation
        if (this.inputs.email) {
            this.inputs.email.addEventListener('blur', () => this.validateEmail());
        }
    }

    validateForm() {
        let isValid = true;
        const errors = {};

        // First name
        if (!this.inputs.firstName.value.trim()) {
            errors.firstName = 'First name is required';
            isValid = false;
        } else if (this.inputs.firstName.value.trim().length < 2) {
            errors.firstName = 'First name must be at least 2 characters';
            isValid = false;
        }

        // Last name
        if (!this.inputs.lastName.value.trim()) {
            errors.lastName = 'Last name is required';
            isValid = false;
        } else if (this.inputs.lastName.value.trim().length < 2) {
            errors.lastName = 'Last name must be at least 2 characters';
            isValid = false;
        }

        // Email
        if (!this.inputs.email.value.trim()) {
            errors.email = 'Email is required';
            isValid = false;
        } else if (!this.isValidEmail(this.inputs.email.value)) {
            errors.email = 'Please enter a valid email address';
            isValid = false;
        }

        // Password
        if (!this.inputs.password.value) {
            errors.password = 'Password is required';
            isValid = false;
        } else if (this.inputs.password.value.length < 8) {
            errors.password = 'Password must be at least 8 characters';
            isValid = false;
        }

        // Confirm password
        if (!this.inputs.confirmPassword.value) {
            errors.confirmPassword = 'Please confirm your password';
            isValid = false;
        } else if (this.inputs.password.value !== this.inputs.confirmPassword.value) {
            errors.confirmPassword = 'Passwords do not match';
            isValid = false;
        }

        // Terms
        if (!this.inputs.terms.checked) {
            errors.terms = 'You must accept the terms and conditions';
            isValid = false;
        }

        // Show all errors
        Object.keys(errors).forEach(field => {
            const input = this.inputs[field] || document.getElementById(field);
            const errorElement = document.getElementById(field + 'Error');
            if (input && errorElement) {
                this.showError(input, errorElement, errors[field]);
            }
        });

        return isValid;
    }

    validateEmail() {
        const email = this.inputs.email.value.trim();
        const errorElement = document.getElementById('emailError');

        if (!email) {
            this.showError(this.inputs.email, errorElement, 'Email is required');
            return false;
        }

        if (!this.isValidEmail(email)) {
            this.showError(this.inputs.email, errorElement, 'Please enter a valid email address');
            return false;
        }

        this.clearError(this.inputs.email, errorElement);
        return true;
    }

    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    showError(input, errorElement, message) {
        if (input) input.classList.add('error');
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }
    }

    clearError(input, errorElement) {
        if (input) input.classList.remove('error');
        if (errorElement) {
            if (typeof errorElement === 'string') {
                errorElement = document.getElementById(errorElement);
            }
            if (errorElement) {
                errorElement.textContent = '';
                errorElement.style.display = 'none';
            }
        }
    }

    async handleSubmit(e) {
        e.preventDefault();

        if (!this.validateForm()) {
            return;
        }

        // Show loading state
        this.setLoadingState(true);

        // In a real application, you might want to make an AJAX call here
        // For now, we'll just submit the form after a short delay
        setTimeout(() => {
            this.form.submit();
        }, 1000);
    }

    setLoadingState(loading) {
        this.submitButton.disabled = loading;
        
        const buttonText = this.submitButton.querySelector('.button-text');
        if (loading) {
            if (buttonText) buttonText.textContent = 'Creating Account...';
            if (this.loader) this.loader.style.display = 'inline-block';
        } else {
            if (buttonText) buttonText.textContent = 'Create Account';
            if (this.loader) this.loader.style.display = 'none';
        }
    }

    setupSocialRegistration() {
        const googleButton = document.querySelector('.google-button');
        const facebookButton = document.querySelector('.facebook-button');

        if (googleButton) {
            googleButton.addEventListener('click', () => {
                this.handleSocialRegistration('google');
            });
        }

        if (facebookButton) {
            facebookButton.addEventListener('click', () => {
                this.handleSocialRegistration('facebook');
            });
        }
    }

    handleSocialRegistration(provider) {
        console.log(`Initiating ${provider} OAuth registration...`);
        alert(`${provider.charAt(0).toUpperCase() + provider.slice(1)} OAuth would be implemented here`);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Initialize regular registration
    if (document.getElementById('registerForm')) {
        new RegisterHandler('registerForm');
    }

    // Initialize business registration
    if (document.getElementById('businessRegisterForm')) {
        new RegisterHandler('businessRegisterForm');
    }
});
