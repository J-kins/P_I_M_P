/**
 * P.I.M.P - Login JavaScript
 * Handles login form validation and submission
 */

class LoginHandler {
    constructor(formId) {
        this.form = document.getElementById(formId);
        this.emailInput = document.getElementById('email');
        this.passwordInput = document.getElementById('password');
        this.rememberMeCheckbox = document.getElementById('remember_me');
        this.submitButton = this.form.querySelector('button[type="submit"]');
        this.loader = document.getElementById('loginLoader');

        this.init();
    }

    init() {
        if (!this.form) return;

        // Password toggle
        this.setupPasswordToggle();

        // Form validation
        this.emailInput.addEventListener('blur', () => this.validateEmail());
        this.passwordInput.addEventListener('blur', () => this.validatePassword());

        // Auto-clear errors on input
        this.emailInput.addEventListener('input', () => this.clearError(this.emailInput));
        this.passwordInput.addEventListener('input', () => this.clearError(this.passwordInput));

        // Form submission
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));

        // Social login buttons
        this.setupSocialLogin();
    }

    setupPasswordToggle() {
        const toggleButton = document.getElementById('passwordToggle');
        if (toggleButton) {
            toggleButton.addEventListener('click', () => {
                const type = this.passwordInput.type === 'password' ? 'text' : 'password';
                this.passwordInput.type = type;
                
                const icon = toggleButton.querySelector('i');
                icon.className = type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
            });
        }
    }

    validateEmail() {
        const email = this.emailInput.value.trim();
        const errorElement = document.getElementById('emailError');

        if (!email) {
            this.showError(this.emailInput, errorElement, 'Email is required');
            return false;
        }

        if (!this.isValidEmail(email)) {
            this.showError(this.emailInput, errorElement, 'Please enter a valid email address');
            return false;
        }

        this.clearError(this.emailInput, errorElement);
        return true;
    }

    validatePassword() {
        const password = this.passwordInput.value;
        const errorElement = document.getElementById('passwordError');

        if (!password) {
            this.showError(this.passwordInput, errorElement, 'Password is required');
            return false;
        }

        this.clearError(this.passwordInput, errorElement);
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

        const isEmailValid = this.validateEmail();
        const isPasswordValid = this.validatePassword();

        if (!isEmailValid || !isPasswordValid) {
            return;
        }

        // Show loading state
        this.setLoadingState(true);

        // In a real application, you might want to make an AJAX call here
        // For now, we'll just submit the form after a short delay
        setTimeout(() => {
            this.form.submit();
        }, 500);
    }

    setLoadingState(loading) {
        this.submitButton.disabled = loading;
        
        const buttonText = this.submitButton.querySelector('.button-text');
        if (loading) {
            if (buttonText) buttonText.textContent = 'Signing In...';
            if (this.loader) this.loader.style.display = 'inline-block';
        } else {
            if (buttonText) buttonText.textContent = 'Sign In';
            if (this.loader) this.loader.style.display = 'none';
        }
    }

    setupSocialLogin() {
        const googleButton = document.querySelector('.google-button');
        const facebookButton = document.querySelector('.facebook-button');

        if (googleButton) {
            googleButton.addEventListener('click', () => {
                this.handleSocialLogin('google');
            });
        }

        if (facebookButton) {
            facebookButton.addEventListener('click', () => {
                this.handleSocialLogin('facebook');
            });
        }
    }

    handleSocialLogin(provider) {
        // In a real application, this would redirect to OAuth flow
        console.log(`Initiating ${provider} OAuth flow...`);
        alert(`${provider.charAt(0).toUpperCase() + provider.slice(1)} OAuth would be implemented here`);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Initialize regular login
    if (document.getElementById('loginForm')) {
        new LoginHandler('loginForm');
    }

    // Initialize business login
    if (document.getElementById('businessLoginForm')) {
        new LoginHandler('businessLoginForm');
    }
});
