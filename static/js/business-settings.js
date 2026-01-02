/**
 * P.I.M.P - Business Settings JavaScript
 * Handles settings navigation, form management, and interactions
 */

class BusinessSettings {
    constructor() {
        this.currentTab = 'profile';
        this.forms = {};
        this.initialize();
    }

    initialize() {
        this.bindNavigation();
        this.bindFormSubmissions();
        this.bindFileUploads();
        this.bindPasswordStrength();
        this.bindSecurityActions();
        this.loadFormData();
    }

    // Navigation handling
    bindNavigation() {
        const navLinks = document.querySelectorAll('.nav-link');
        const tabs = document.querySelectorAll('.settings-tab');

        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                
                const targetTab = link.getAttribute('data-tab');
                this.switchTab(targetTab);
            });
        });

        // Handle URL hash changes
        window.addEventListener('hashchange', () => {
            const hash = window.location.hash.replace('#', '');
            if (hash && this.isValidTab(hash)) {
                this.switchTab(hash);
            }
        });

        // Initial tab from URL hash
        const initialHash = window.location.hash.replace('#', '');
        if (initialHash && this.isValidTab(initialHash)) {
            this.switchTab(initialHash);
        }
    }

    isValidTab(tabName) {
        const validTabs = ['profile', 'contact', 'notifications', 'security', 'billing', 'integrations'];
        return validTabs.includes(tabName);
    }

    switchTab(tabName) {
        // Update navigation
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('active');
        });
        document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');

        // Update tabs
        document.querySelectorAll('.settings-tab').forEach(tab => {
            tab.classList.remove('active');
        });
        document.getElementById(`${tabName}Tab`).classList.add('active');

        // Update URL
        window.history.replaceState(null, null, `#${tabName}`);
        this.currentTab = tabName;

        // Dispatch custom event
        window.dispatchEvent(new CustomEvent('settingsTabChanged', {
            detail: { tab: tabName }
        }));
    }

    // Form handling
    bindFormSubmissions() {
        const forms = document.querySelectorAll('.settings-form, .security-form');
        
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleFormSubmit(form);
            });
        });

        // Cancel buttons
        document.querySelectorAll('.cancel-button').forEach(button => {
            button.addEventListener('click', () => {
                this.resetForm(button.closest('form'));
            });
        });
    }

    async handleFormSubmit(form) {
        const formId = form.id;
        const formData = new FormData(form);
        const submitButton = form.querySelector('.save-button');
        
        // Show loading state
        this.setButtonLoading(submitButton, true);

        try {
            // Validate form
            if (!this.validateForm(form)) {
                throw new Error('Please fix form errors before submitting.');
            }

            // Simulate API call
            await this.simulateApiCall(formData);
            
            // Show success message
            this.showMessage('Settings saved successfully!', 'success', form);
            
            // Save to local storage for demo purposes
            this.saveFormData(formId, formData);
            
        } catch (error) {
            this.showMessage(error.message, 'error', form);
        } finally {
            this.setButtonLoading(submitButton, false);
        }
    }

    validateForm(form) {
        let isValid = true;
        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
        
        inputs.forEach(input => {
            if (!input.value.trim()) {
                this.markFieldError(input, 'This field is required');
                isValid = false;
            } else {
                this.clearFieldError(input);
            }

            // Additional validation for specific fields
            if (input.type === 'email' && input.value) {
                if (!this.isValidEmail(input.value)) {
                    this.markFieldError(input, 'Please enter a valid email address');
                    isValid = false;
                }
            }

            if (input.type === 'url' && input.value) {
                if (!this.isValidUrl(input.value)) {
                    this.markFieldError(input, 'Please enter a valid URL');
                    isValid = false;
                }
            }
        });

        return isValid;
    }

    markFieldError(field, message) {
        field.classList.add('error');
        
        let errorElement = field.parentNode.querySelector('.error-message');
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.className = 'error-message';
            field.parentNode.appendChild(errorElement);
        }
        errorElement.textContent = message;
    }

    clearFieldError(field) {
        field.classList.remove('error');
        const errorElement = field.parentNode.querySelector('.error-message');
        if (errorElement) {
            errorElement.remove();
        }
    }

    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    isValidUrl(url) {
        try {
            new URL(url);
            return true;
        } catch {
            return false;
        }
    }

    setButtonLoading(button, isLoading) {
        if (isLoading) {
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        } else {
            button.disabled = false;
            button.innerHTML = 'Save Changes';
        }
    }

    showMessage(message, type, context) {
        // Remove existing messages
        const existingMessages = context.querySelectorAll('.form-message');
        existingMessages.forEach(msg => msg.remove());

        // Create new message
        const messageElement = document.createElement('div');
        messageElement.className = `form-message ${type}-message`;
        messageElement.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            ${message}
        `;

        // Insert message
        context.insertBefore(messageElement, context.firstChild);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            messageElement.remove();
        }, 5000);
    }

    resetForm(form) {
        form.reset();
        this.clearAllFormErrors(form);
        this.loadFormData(form.id); // Reload saved data
    }

    clearAllFormErrors(form) {
        form.querySelectorAll('.error').forEach(field => {
            this.clearFieldError(field);
        });
    }

    // File upload handling
    bindFileUploads() {
        const fileInputs = document.querySelectorAll('.file-input');
        
        fileInputs.forEach(input => {
            input.addEventListener('change', (e) => {
                this.handleFileUpload(e.target);
            });
        });

        // Remove buttons
        document.querySelectorAll('.remove-button').forEach(button => {
            button.addEventListener('click', (e) => {
                this.handleFileRemove(e.target);
            });
        });
    }

    handleFileUpload(input) {
        const file = input.files[0];
        if (!file) return;

        // Validate file type and size
        if (!this.isValidFile(file)) {
            alert('Please select a valid image file (JPG, PNG, GIF) under 5MB.');
            input.value = '';
            return;
        }

        // Show preview
        this.showFilePreview(file, input);

        // Show remove button
        const removeButton = input.closest('.upload-controls').querySelector('.remove-button');
        removeButton.style.display = 'block';
    }

    isValidFile(file) {
        const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
        const maxSize = 5 * 1024 * 1024; // 5MB
        
        return validTypes.includes(file.type) && file.size <= maxSize;
    }

    showFilePreview(file, input) {
        const reader = new FileReader();
        const preview = input.closest('.upload-item').querySelector('.logo-preview');
        const placeholder = input.closest('.upload-item').querySelector('.upload-placeholder');

        reader.onload = (e) => {
            preview.src = e.target.result;
            preview.style.display = 'block';
            placeholder.style.display = 'none';
        };

        reader.readAsDataURL(file);
    }

    handleFileRemove(button) {
        const uploadItem = button.closest('.upload-item');
        const fileInput = uploadItem.querySelector('.file-input');
        const preview = uploadItem.querySelector('.logo-preview');
        const placeholder = uploadItem.querySelector('.upload-placeholder');

        fileInput.value = '';
        preview.style.display = 'none';
        placeholder.style.display = 'flex';
        button.style.display = 'none';
    }

    // Password strength meter
    bindPasswordStrength() {
        const passwordInput = document.getElementById('newPassword');
        if (!passwordInput) return;

        passwordInput.addEventListener('input', () => {
            this.updatePasswordStrength(passwordInput.value);
        });
    }

    updatePasswordStrength(password) {
        const strengthBar = document.querySelector('.strength-bar');
        const strengthText = document.querySelector('.strength-text');
        
        if (!strengthBar || !strengthText) return;

        const strength = this.calculatePasswordStrength(password);
        
        // Update bar width and color
        strengthBar.style.width = `${strength.percentage}%`;
        strengthBar.style.backgroundColor = strength.color;
        
        // Update text
        strengthText.textContent = strength.text;
    }

    calculatePasswordStrength(password) {
        let score = 0;
        
        if (password.length >= 8) score++;
        if (password.match(/[a-z]/) && password.match(/[A-Z]/)) score++;
        if (password.match(/\d/)) score++;
        if (password.match(/[^a-zA-Z\d]/)) score++;

        const strengths = {
            0: { percentage: 0, color: '#ef4444', text: 'Very weak' },
            1: { percentage: 25, color: '#f97316', text: 'Weak' },
            2: { percentage: 50, color: '#eab308', text: 'Fair' },
            3: { percentage: 75, color: '#84cc16', text: 'Good' },
            4: { percentage: 100, color: '#10b981', text: 'Strong' }
        };

        return strengths[score] || strengths[0];
    }

    // Security actions
    bindSecurityActions() {
        // Revoke session buttons
        document.querySelectorAll('.revoke-button').forEach(button => {
            button.addEventListener('click', (e) => {
                this.revokeSession(e.target);
            });
        });

        // Revoke all sessions button
        const revokeAllButton = document.querySelector('.revoke-all-button');
        if (revokeAllButton) {
            revokeAllButton.addEventListener('click', () => {
                this.revokeAllSessions();
            });
        }

        // 2FA toggle
        const twoFactorToggle = document.querySelector('input[name="two_factor_auth"]');
        if (twoFactorToggle) {
            twoFactorToggle.addEventListener('change', (e) => {
                this.handleTwoFactorToggle(e.target.checked);
            });
        }
    }

    revokeSession(button) {
        const sessionItem = button.closest('.session-item');
        if (confirm('Are you sure you want to revoke this session?')) {
            sessionItem.style.opacity = '0.5';
            setTimeout(() => {
                sessionItem.remove();
            }, 300);
        }
    }

    revokeAllSessions() {
        if (confirm('Are you sure you want to revoke all other sessions?')) {
            const sessions = document.querySelectorAll('.session-item:not(:first-child)');
            sessions.forEach(session => {
                session.style.opacity = '0.5';
                setTimeout(() => {
                    session.remove();
                }, 300);
            });
        }
    }

    handleTwoFactorToggle(enabled) {
        if (enabled) {
            // In a real app, this would initiate 2FA setup
            alert('Two-factor authentication setup would be initiated here.');
        } else {
            if (confirm('Are you sure you want to disable two-factor authentication?')) {
                // In a real app, this would disable 2FA
                alert('Two-factor authentication has been disabled.');
            } else {
                // Revert the toggle
                document.querySelector('input[name="two_factor_auth"]').checked = false;
            }
        }
    }

    // Data persistence (for demo purposes)
    saveFormData(formId, formData) {
        const data = {};
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }
        localStorage.setItem(`businessSettings_${formId}`, JSON.stringify(data));
    }

    loadFormData() {
        const forms = ['profileForm', 'contactForm', 'notificationsForm'];
        
        forms.forEach(formId => {
            const savedData = localStorage.getItem(`businessSettings_${formId}`);
            if (savedData) {
                const data = JSON.parse(savedData);
                this.populateForm(formId, data);
            }
        });
    }

    populateForm(formId, data) {
        const form = document.getElementById(formId);
        if (!form) return;

        Object.keys(data).forEach(key => {
            const input = form.querySelector(`[name="${key}"]`);
            if (input) {
                if (input.type === 'checkbox' || input.type === 'radio') {
                    input.checked = data[key] === 'on' || data[key] === true;
                } else {
                    input.value = data[key];
                }
            }
        });

        // Update character counts
        const textareas = form.querySelectorAll('textarea');
        textareas.forEach(textarea => {
            this.updateCharCount(textarea);
        });
    }

    updateCharCount(textarea) {
        const charCount = textarea.parentNode.querySelector('.current-chars');
        if (charCount) {
            charCount.textContent = textarea.value.length;
        }
    }

    // Utility methods
    async simulateApiCall(formData) {
        // Simulate network delay
        return new Promise((resolve) => {
            setTimeout(() => {
                resolve({ success: true });
            }, 1500);
        });
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.businessSettings = new BusinessSettings();
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = BusinessSettings;
}
