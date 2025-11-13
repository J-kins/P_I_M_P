/**
 * P.I.M.P - Business Registration JavaScript
 * Handles multi-step registration form with validation
 */

class BusinessRegistration {
    constructor() {
        this.currentStep = 1;
        this.totalSteps = 5;
        this.formData = {};
        this.categories = [];
        this.services = [];
        this.businessHours = {};
        this.initialize();
    }

    initialize() {
        this.bindEventListeners();
        this.loadCategories();
        this.initializeBusinessHours();
        this.updateProgress();
    }

    // Event listeners
    bindEventListeners() {
        // Step navigation
        document.querySelectorAll('.next-step').forEach(button => {
            button.addEventListener('click', (e) => {
                const nextStep = parseInt(e.target.getAttribute('data-next'));
                this.nextStep(nextStep);
            });
        });

        document.querySelectorAll('.prev-step').forEach(button => {
            button.addEventListener('click', (e) => {
                const prevStep = parseInt(e.target.getAttribute('data-prev'));
                this.previousStep(prevStep);
            });
        });

        // Form submission
        const registrationForm = document.getElementById('businessRegistrationForm');
        if (registrationForm) {
            registrationForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitRegistration();
            });
        }

        // Real-time validation
        this.bindRealTimeValidation();

        // Categories selection
        this.bindCategoriesSelection();

        // Services management
        this.bindServicesManagement();

        // Password strength
        this.bindPasswordStrength();

        // Password visibility toggle
        this.bindPasswordToggle();

        // Character counters
        this.bindCharacterCounters();
    }

    // Step navigation
    nextStep(nextStep) {
        if (this.validateStep(this.currentStep)) {
            this.saveStepData(this.currentStep);
            this.showStep(nextStep);
            this.updateProgress();
        }
    }

    previousStep(prevStep) {
        this.showStep(prevStep);
        this.updateProgress();
    }

    showStep(step) {
        // Hide all steps
        document.querySelectorAll('.form-step').forEach(stepElement => {
            stepElement.classList.remove('active');
        });

        // Show target step
        const targetStep = document.querySelector(`[data-step="${step}"]`);
        if (targetStep) {
            targetStep.classList.add('active');
            this.currentStep = step;
        }

        // Update review section if on last step
        if (step === 5) {
            this.updateReviewSection();
        }
    }

    updateProgress() {
        // Update progress steps
        document.querySelectorAll('.step').forEach(step => {
            const stepNumber = parseInt(step.getAttribute('data-step'));
            
            step.classList.remove('active', 'completed');
            
            if (stepNumber === this.currentStep) {
                step.classList.add('active');
            } else if (stepNumber < this.currentStep) {
                step.classList.add('completed');
            }
        });
    }

    // Step validation
    validateStep(step) {
        let isValid = true;

        switch (step) {
            case 1:
                isValid = this.validateStep1();
                break;
            case 2:
                isValid = this.validateStep2();
                break;
            case 3:
                isValid = this.validateStep3();
                break;
            case 4:
                isValid = this.validateStep4();
                break;
        }

        return isValid;
    }

    validateStep1() {
        let isValid = true;

        // Business name
        const businessName = document.getElementById('businessName');
        if (!businessName.value.trim()) {
            this.showError('businessNameError', 'Business name is required');
            isValid = false;
        } else {
            this.hideError('businessNameError');
        }

        // Business type
        const businessType = document.getElementById('businessType');
        if (!businessType.value) {
            this.showError('businessTypeError', 'Please select a business type');
            isValid = false;
        } else {
            this.hideError('businessTypeError');
        }

        // Business description
        const businessDescription = document.getElementById('businessDescription');
        if (!businessDescription.value.trim()) {
            this.showError('businessDescriptionError', 'Business description is required');
            isValid = false;
        } else if (businessDescription.value.length < 50) {
            this.showError('businessDescriptionError', 'Description should be at least 50 characters');
            isValid = false;
        } else {
            this.hideError('businessDescriptionError');
        }

        // Year established
        const yearEstablished = document.getElementById('yearEstablished');
        if (!yearEstablished.value) {
            this.showError('yearEstablishedError', 'Year established is required');
            isValid = false;
        } else {
            const year = parseInt(yearEstablished.value);
            const currentYear = new Date().getFullYear();
            if (year < 1900 || year > currentYear) {
                this.showError('yearEstablishedError', 'Please enter a valid year');
                isValid = false;
            } else {
                this.hideError('yearEstablishedError');
            }
        }

        return isValid;
    }

    validateStep2() {
        let isValid = true;

        // Contact name
        const contactName = document.getElementById('contactName');
        if (!contactName.value.trim()) {
            this.showError('contactNameError', 'Contact name is required');
            isValid = false;
        } else {
            this.hideError('contactNameError');
        }

        // Contact email
        const contactEmail = document.getElementById('contactEmail');
        if (!contactEmail.value.trim()) {
            this.showError('contactEmailError', 'Contact email is required');
            isValid = false;
        } else if (!this.isValidEmail(contactEmail.value)) {
            this.showError('contactEmailError', 'Please enter a valid email address');
            isValid = false;
        } else {
            this.hideError('contactEmailError');
        }

        // Contact phone
        const contactPhone = document.getElementById('contactPhone');
        if (!contactPhone.value.trim()) {
            this.showError('contactPhoneError', 'Contact phone is required');
            isValid = false;
        } else {
            this.hideError('contactPhoneError');
        }

        // Address
        const addressFields = ['addressLine1', 'city', 'state', 'zipCode', 'country'];
        addressFields.forEach(field => {
            const element = document.getElementById(field);
            if (!element.value.trim()) {
                this.showError(`${field}Error`, `${this.formatFieldName(field)} is required`);
                isValid = false;
            } else {
                this.hideError(`${field}Error`);
            }
        });

        return isValid;
    }

    validateStep3() {
        let isValid = true;

        // Categories
        const selectedCategories = this.categories.filter(cat => cat.selected);
        if (selectedCategories.length === 0) {
            this.showError('categoriesError', 'Please select at least one category');
            isValid = false;
        } else if (selectedCategories.length > 3) {
            this.showError('categoriesError', 'Please select no more than 3 categories');
            isValid = false;
        } else {
            this.hideError('categoriesError');
        }

        // Services
        if (this.services.length === 0) {
            this.showError('servicesError', 'Please add at least one service');
            isValid = false;
        } else {
            this.hideError('servicesError');
        }

        return isValid;
    }

    validateStep4() {
        let isValid = true;

        // Account email
        const accountEmail = document.getElementById('accountEmail');
        if (!accountEmail.value.trim()) {
            this.showError('accountEmailError', 'Account email is required');
            isValid = false;
        } else if (!this.isValidEmail(accountEmail.value)) {
            this.showError('accountEmailError', 'Please enter a valid email address');
            isValid = false;
        } else {
            this.hideError('accountEmailError');
        }

        // Password
        const password = document.getElementById('password');
        if (!password.value) {
            this.showError('passwordError', 'Password is required');
            isValid = false;
        } else if (password.value.length < 8) {
            this.showError('passwordError', 'Password must be at least 8 characters');
            isValid = false;
        } else {
            this.hideError('passwordError');
        }

        // Confirm password
        const confirmPassword = document.getElementById('confirmPassword');
        if (!confirmPassword.value) {
            this.showError('confirmPasswordError', 'Please confirm your password');
            isValid = false;
        } else if (password.value !== confirmPassword.value) {
            this.showError('confirmPasswordError', 'Passwords do not match');
            isValid = false;
        } else {
            this.hideError('confirmPasswordError');
        }

        // Terms agreement
        const termsAgreement = document.querySelector('input[name="terms_agreement"]');
        const businessVerification = document.querySelector('input[name="business_verification"]');
        
        if (!termsAgreement.checked || !businessVerification.checked) {
            this.showError('termsError', 'Please accept all required terms');
            isValid = false;
        } else {
            this.hideError('termsError');
        }

        return isValid;
    }

    // Data management
    saveStepData(step) {
        switch (step) {
            case 1:
                this.formData.businessInfo = this.getStep1Data();
                break;
            case 2:
                this.formData.contactDetails = this.getStep2Data();
                break;
            case 3:
                this.formData.services = this.getStep3Data();
                break;
            case 4:
                this.formData.account = this.getStep4Data();
                break;
        }

        // Save to localStorage for persistence
        localStorage.setItem('businessRegistrationData', JSON.stringify(this.formData));
    }

    getStep1Data() {
        return {
            businessName: document.getElementById('businessName').value,
            tradingName: document.getElementById('tradingName').value,
            businessType: document.getElementById('businessType').value,
            registrationNumber: document.getElementById('registrationNumber').value,
            businessDescription: document.getElementById('businessDescription').value,
            yearEstablished: document.getElementById('yearEstablished').value
        };
    }

    getStep2Data() {
        return {
            contactName: document.getElementById('contactName').value,
            contactEmail: document.getElementById('contactEmail').value,
            contactPhone: document.getElementById('contactPhone').value,
            address: {
                line1: document.getElementById('addressLine1').value,
                line2: document.getElementById('addressLine2').value,
                city: document.getElementById('city').value,
                state: document.getElementById('state').value,
                zipCode: document.getElementById('zipCode').value,
                country: document.getElementById('country').value
            },
            onlinePresence: {
                website: document.getElementById('website').value,
                facebook: document.getElementById('facebook').value,
                instagram: document.getElementById('instagram').value,
                linkedin: document.getElementById('linkedin').value
            }
        };
    }

    getStep3Data() {
        return {
            categories: this.categories.filter(cat => cat.selected).map(cat => cat.name),
            services: this.services,
            businessHours: this.businessHours
        };
    }

    getStep4Data() {
        return {
            accountEmail: document.getElementById('accountEmail').value,
            preferences: {
                emailNotifications: document.querySelector('input[name="email_notifications"]').checked,
                marketingEmails: document.querySelector('input[name="marketing_emails"]').checked,
                smsNotifications: document.querySelector('input[name="sms_notifications"]').checked
            }
        };
    }

    // Categories management
    loadCategories() {
        // Sample categories - in real app, this would come from an API
        this.categories = [
            { id: 'tech', name: 'Technology', selected: false },
            { id: 'consulting', name: 'Consulting', selected: false },
            { id: 'healthcare', name: 'Healthcare', selected: false },
            { id: 'retail', name: 'Retail', selected: false },
            { id: 'restaurant', name: 'Restaurant', selected: false },
            { id: 'construction', name: 'Construction', selected: false },
            { id: 'education', name: 'Education', selected: false },
            { id: 'finance', name: 'Finance', selected: false },
            { id: 'real_estate', name: 'Real Estate', selected: false },
            { id: 'legal', name: 'Legal Services', selected: false },
            { id: 'marketing', name: 'Marketing', selected: false },
            { id: 'transportation', name: 'Transportation', selected: false }
        ];

        this.renderCategories();
    }

    renderCategories() {
        const categoriesGrid = document.getElementById('categoriesGrid');
        if (!categoriesGrid) return;

        categoriesGrid.innerHTML = this.categories.map(category => `
            <label class="category-option ${category.selected ? 'selected' : ''}">
                <input type="checkbox" value="${category.id}" ${category.selected ? 'checked' : ''}>
                <span class="category-checkmark"></span>
                <span class="category-name">${category.name}</span>
            </label>
        `).join('');

        this.bindCategoriesSelection();
    }

    bindCategoriesSelection() {
        document.querySelectorAll('.category-option').forEach(option => {
            option.addEventListener('click', (e) => {
                const checkbox = option.querySelector('input[type="checkbox"]');
                const categoryId = checkbox.value;
                
                // Toggle selection
                const category = this.categories.find(cat => cat.id === categoryId);
                if (category) {
                    // Check if we can select more categories
                    const selectedCount = this.categories.filter(cat => cat.selected).length;
                    if (!category.selected && selectedCount >= 3) {
                        this.showError('categoriesError', 'Maximum 3 categories allowed');
                        return;
                    }
                    
                    category.selected = !category.selected;
                    checkbox.checked = category.selected;
                    option.classList.toggle('selected', category.selected);
                    this.hideError('categoriesError');
                }
            });
        });
    }

    // Services management
    bindServicesManagement() {
        const serviceInput = document.getElementById('serviceInput');
        const addServiceButton = document.querySelector('.add-service');
        const servicesList = document.getElementById('servicesList');

        if (addServiceButton && serviceInput) {
            addServiceButton.addEventListener('click', () => {
                this.addService(serviceInput.value.trim());
                serviceInput.value = '';
            });

            serviceInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.addService(serviceInput.value.trim());
                    serviceInput.value = '';
                }
            });
        }
    }

    addService(serviceName) {
        if (!serviceName) return;

        // Check if service already exists
        if (this.services.includes(serviceName)) {
            this.showError('servicesError', 'Service already added');
            return;
        }

        this.services.push(serviceName);
        this.renderServices();
        this.hideError('servicesError');
    }

    removeService(serviceName) {
        this.services = this.services.filter(service => service !== serviceName);
        this.renderServices();
    }

    renderServices() {
        const servicesList = document.getElementById('servicesList');
        if (!servicesList) return;

        if (this.services.length === 0) {
            servicesList.innerHTML = '<div class="no-services">No services added yet</div>';
            return;
        }

        servicesList.innerHTML = this.services.map(service => `
            <div class="service-tag">
                <span>${service}</span>
                <button type="button" class="remove-service" data-service="${service}">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `).join('');

        // Bind remove buttons
        servicesList.querySelectorAll('.remove-service').forEach(button => {
            button.addEventListener('click', () => {
                const serviceName = button.getAttribute('data-service');
                this.removeService(serviceName);
            });
        });
    }

    // Business hours management
    initializeBusinessHours() {
        const days = [
            'Monday', 'Tuesday', 'Wednesday', 'Thursday', 
            'Friday', 'Saturday', 'Sunday'
        ];

        const hoursList = document.getElementById('businessHours');
        if (!hoursList) return;

        hoursList.innerHTML = days.map(day => `
            <div class="hours-item" data-day="${day.toLowerCase()}">
                <span class="day-label">${day}</span>
                <input type="time" class="time-input opening-time" value="09:00">
                <input type="time" class="time-input closing-time" value="17:00">
                <label class="closed-checkbox">
                    <input type="checkbox" class="closed-input">
                    <span>Closed</span>
                </label>
            </div>
        `).join('');

        // Bind business hours changes
        this.bindBusinessHours();
    }

    bindBusinessHours() {
        document.querySelectorAll('.hours-item').forEach(item => {
            const day = item.getAttribute('data-day');
            const openingTime = item.querySelector('.opening-time');
            const closingTime = item.querySelector('.closing-time');
            const closedInput = item.querySelector('.closed-input');

            // Initialize data
            this.businessHours[day] = {
                open: openingTime.value,
                close: closingTime.value,
                closed: closedInput.checked
            };

            // Add event listeners
            [openingTime, closingTime, closedInput].forEach(input => {
                input.addEventListener('change', () => {
                    this.businessHours[day] = {
                        open: openingTime.value,
                        close: closingTime.value,
                        closed: closedInput.checked
                    };

                    // Disable time inputs when closed
                    openingTime.disabled = closedInput.checked;
                    closingTime.disabled = closedInput.checked;
                });
            });
        });
    }

    // Password strength
    bindPasswordStrength() {
        const passwordInput = document.getElementById('password');
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
        strengthText.style.color = strength.color;
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

    // Password toggle
    bindPasswordToggle() {
        document.querySelectorAll('.password-toggle').forEach(toggle => {
            toggle.addEventListener('click', () => {
                const input = toggle.closest('.password-input-group').querySelector('input');
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                toggle.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
            });
        });
    }

    // Character counters
    bindCharacterCounters() {
        const textareas = document.querySelectorAll('textarea');
        textareas.forEach(textarea => {
            textarea.addEventListener('input', () => {
                this.updateCharCount(textarea);
            });
            // Initialize count
            this.updateCharCount(textarea);
        });
    }

    updateCharCount(textarea) {
        const charCount = textarea.parentNode.querySelector('.current-chars');
        if (charCount) {
            charCount.textContent = textarea.value.length;
        }
    }

    // Real-time validation
    bindRealTimeValidation() {
        // Add input event listeners for real-time validation
        const inputs = document.querySelectorAll('input[required], select[required], textarea[required]');
        inputs.forEach(input => {
            input.addEventListener('blur', () => {
                this.validateField(input);
            });
        });
    }

    validateField(field) {
        const fieldName = field.getAttribute('name');
        const value = field.value.trim();

        // Basic required validation
        if (!value) {
            this.markFieldError(field, 'This field is required');
            return false;
        }

        // Email validation
        if (field.type === 'email' && !this.isValidEmail(value)) {
            this.markFieldError(field, 'Please enter a valid email address');
            return false;
        }

        // URL validation
        if (field.type === 'url' && value && !this.isValidUrl(value)) {
            this.markFieldError(field, 'Please enter a valid URL');
            return false;
        }

        // Clear any existing errors
        this.clearFieldError(field);
        return true;
    }

    markFieldError(field, message) {
        field.classList.add('error');
        
        const errorId = `${field.id}Error`;
        const errorElement = document.getElementById(errorId);
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.classList.add('show');
        }
    }

    clearFieldError(field) {
        field.classList.remove('error');
        
        const errorId = `${field.id}Error`;
        const errorElement = document.getElementById(errorId);
        if (errorElement) {
            errorElement.classList.remove('show');
        }
    }

    // Review section
    updateReviewSection() {
        // Business Information
        if (this.formData.businessInfo) {
            document.getElementById('reviewBusinessName').textContent = this.formData.businessInfo.businessName;
            document.getElementById('reviewBusinessType').textContent = this.formatBusinessType(this.formData.businessInfo.businessType);
            document.getElementById('reviewDescription').textContent = this.formData.businessInfo.businessDescription;
            document.getElementById('reviewYearEstablished').textContent = this.formData.businessInfo.yearEstablished;
        }

        // Contact Details
        if (this.formData.contactDetails) {
            document.getElementById('reviewContactName').textContent = this.formData.contactDetails.contactName;
            document.getElementById('reviewContactEmail').textContent = this.formData.contactDetails.contactEmail;
            document.getElementById('reviewContactPhone').textContent = this.formData.contactDetails.contactPhone;
            
            const address = this.formData.contactDetails.address;
            document.getElementById('reviewAddress').textContent = 
                `${address.line1}, ${address.city}, ${address.state} ${address.zipCode}`;
        }

        // Services & Categories
        if (this.formData.services) {
            document.getElementById('reviewCategories').textContent = this.formData.services.categories.join(', ');
            document.getElementById('reviewServices').textContent = this.formData.services.services.join(', ');
            document.getElementById('reviewHours').textContent = this.formatBusinessHours(this.formData.services.businessHours);
        }

        // Account Details
        if (this.formData.account) {
            document.getElementById('reviewAccountEmail').textContent = this.formData.account.accountEmail;
            
            const prefs = this.formData.account.preferences;
            const notifications = [];
            if (prefs.emailNotifications) notifications.push('Email');
            if (prefs.smsNotifications) notifications.push('SMS');
            document.getElementById('reviewNotifications').textContent = notifications.join(', ') || 'None';
        }
    }

    formatBusinessType(type) {
        const types = {
            'sole_proprietorship': 'Sole Proprietorship',
            'partnership': 'Partnership',
            'corporation': 'Corporation',
            'llc': 'Limited Liability Company',
            'nonprofit': 'Non-Profit Organization'
        };
        return types[type] || type;
    }

    formatBusinessHours(hours) {
        const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        const formattedHours = days.map(day => {
            const dayHours = hours[day];
            if (dayHours.closed) {
                return `${day.charAt(0).toUpperCase() + day.slice(1)}: Closed`;
            } else {
                return `${day.charAt(0).toUpperCase() + day.slice(1)}: ${dayHours.open} - ${dayHours.close}`;
            }
        });
        return formattedHours.join('; ');
    }

    // Form submission
    async submitRegistration() {
        if (!this.validateStep(4)) {
            this.showStep(4);
            return;
        }

        // Save final step data
        this.saveStepData(4);

        // Show loading state
        const submitButton = document.querySelector('.submit-registration');
        const originalText = submitButton.innerHTML;
        submitButton.innerHTML = '<span class="loading-spinner"></span> Submitting...';
        submitButton.disabled = true;

        try {
            // Simulate API call
            await this.submitToApi();
            
            // Show success message
            this.showSuccessMessage();
            
            // Clear stored data
            localStorage.removeItem('businessRegistrationData');
            
        } catch (error) {
            console.error('Registration error:', error);
            this.showError('Registration failed. Please try again.');
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
        }
    }

    async submitToApi() {
        // Simulate API call
        return new Promise((resolve) => {
            setTimeout(() => {
                // In real app, this would submit to your backend
                console.log('Registration data:', this.formData);
                resolve({ success: true });
            }, 2000);
        });
    }

    showSuccessMessage() {
        // Create success modal
        const modal = document.createElement('div');
        modal.className = 'success-modal active';
        modal.innerHTML = `
            <div class="success-modal-content">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h2>Registration Successful!</h2>
                <p>Your business has been registered successfully. You will receive a confirmation email shortly.</p>
                <div class="success-actions">
                    <a href="/business/dashboard" class="button-primary">Go to Dashboard</a>
                    <a href="/business/login" class="button-secondary">Business Login</a>
                </div>
            </div>
        `;

        // Add styles
        const styles = `
            <style>
                .success-modal {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.5);
                    display: none;
                    align-items: center;
                    justify-content: center;
                    z-index: 1000;
                }
                .success-modal.active {
                    display: flex;
                }
                .success-modal-content {
                    background: white;
                    border-radius: 12px;
                    padding: 3rem;
                    text-align: center;
                    max-width: 500px;
                    width: 90%;
                }
                .success-icon {
                    font-size: 4rem;
                    color: #10b981;
                    margin-bottom: 1.5rem;
                }
                .success-modal-content h2 {
                    font-size: 1.75rem;
                    font-weight: 600;
                    color: var(--text-primary);
                    margin-bottom: 1rem;
                }
                .success-modal-content p {
                    color: var(--text-secondary);
                    margin-bottom: 2rem;
                    line-height: 1.5;
                }
                .success-actions {
                    display: flex;
                    gap: 1rem;
                    justify-content: center;
                }
            </style>
        `;

        document.head.insertAdjacentHTML('beforeend', styles);
        document.body.appendChild(modal);
    }

    // Utility methods
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

    formatFieldName(fieldName) {
        return fieldName
            .replace(/([A-Z])/g, ' $1')
            .replace(/([A-Z][a-z])/g, ' $1')
            .replace(/^./, str => str.toUpperCase())
            .replace(/(Line|Code)$/, ' $1')
            .trim();
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
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.businessRegistration = new BusinessRegistration();
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = BusinessRegistration;
}
