<?php
/**
 * P.I.M.P - Register Page
 * User registration with comprehensive validation
 */

use PIMP\Core\Config;
use PIMP\Views\Components;

$nav_items = [
    ['url' => '/', 'label' => 'Home', 'active' => false],
    ['url' => '/businesses', 'label' => 'Business Directory', 'active' => false],
    ['url' => '/reviews', 'label' => 'Reviews', 'active' => false],
    ['url' => '/categories', 'label' => 'Categories', 'active' => false],
    ['url' => '/scam-alerts', 'label' => 'Scam Alerts', 'active' => false],
    ['url' => '/resources', 'label' => 'Resources', 'active' => false],
];

// Get form data from session or POST
$form_data = [
    'first_name' => $_POST['first_name'] ?? '',
    'last_name' => $_POST['last_name'] ?? '',
    'email' => $_POST['email'] ?? '',
    'phone' => $_POST['phone'] ?? '',
    'user_type' => $_POST['user_type'] ?? 'consumer'
];
$errors = $_SESSION['register_errors'] ?? [];
$success = $_SESSION['register_success'] ?? '';

// Clear session messages after displaying
unset($_SESSION['register_errors']);
unset($_SESSION['register_success']);

$footer_config = [
    'logo' => Config::imageUrl('logo.png'),
    'logoAlt' => 'P.I.M.P Business Repository',
    'links' => [
        [
            'title' => 'For Consumers',
            'links' => [
                ['url' => '/businesses', 'label' => 'Find Businesses'],
                ['url' => '/reviews/write', 'label' => 'Write a Review'],
                ['url' => '/scam-alerts', 'label' => 'Scam Alerts'],
                ['url' => '/resources/tips', 'label' => 'Consumer Tips'],
            ]
        ],
    ],
    'theme' => 'light'
];

ob_start();
?>

<?php
echo Components::call('Headers', 'documentHead', [[
    'title' => 'Register - P.I.M.P Business Repository',
    'metaTags' => [
        'description' => 'Create your P.I.M.P account to write reviews, manage your business profile, and access exclusive features.',
        'keywords' => 'register, sign up, create account, PIMP account'
    ],
    'styles' => [
        'views/auth.css'
    ],
    'scripts' => [
        'js/auth.js'
    ]
]]);
?>

<body class="auth-page">
    <?php
    echo Components::call('Headers', 'businessHeader', [[
        'logo' => Config::imageUrl('logo.png'),
        'logoAlt' => 'P.I.M.P - Business Repository Platform',
        'mainNavItems' => $nav_items,
        'userActions' => [
            ['url' => '/login', 'label' => 'Login', 'separator' => false],
        ],
        'showSearch' => false,
    ]]);
    ?>

    <main class="auth-main">
        <div class="auth-container">
            <div class="auth-card">
                <!-- Registration Header -->
                <div class="auth-header">
                    <h1>Join P.I.M.P</h1>
                    <p>Create your account to get started</p>
                </div>

                <!-- Social Registration Options -->
                <div class="social-login">
                    <button type="button" class="social-button google-button">
                        <i class="fab fa-google"></i>
                        Sign up with Google
                    </button>
                    <button type="button" class="social-button facebook-button">
                        <i class="fab fa-facebook-f"></i>
                        Sign up with Facebook
                    </button>
                </div>

                <div class="divider">
                    <span>or</span>
                </div>

                <!-- Registration Form -->
                <form id="registerForm" action="<?= Config::url('/register') ?>" method="POST" class="auth-form" novalidate>
                    <?php if (!empty($errors['general'])): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($errors['general']) ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?= htmlspecialchars($success) ?>
                    </div>
                    <?php endif; ?>

                    <!-- User Type Selection -->
                    <div class="form-group">
                        <label class="form-label">I am a:</label>
                        <div class="user-type-selector">
                            <label class="user-type-option">
                                <input type="radio" name="user_type" value="consumer" <?= $form_data['user_type'] === 'consumer' ? 'checked' : '' ?>>
                                <div class="user-type-card">
                                    <i class="fas fa-user"></i>
                                    <span>Consumer</span>
                                    <small>I want to find and review businesses</small>
                                </div>
                            </label>
                            <label class="user-type-option">
                                <input type="radio" name="user_type" value="business" <?= $form_data['user_type'] === 'business' ? 'checked' : '' ?>>
                                <div class="user-type-card">
                                    <i class="fas fa-building"></i>
                                    <span>Business Owner</span>
                                    <small>I want to manage my business profile</small>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Personal Information -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name" class="form-label">First Name</label>
                            <div class="input-group">
                                <i class="fas fa-user input-icon"></i>
                                <input type="text" 
                                       id="first_name" 
                                       name="first_name" 
                                       value="<?= htmlspecialchars($form_data['first_name']) ?>" 
                                       class="form-input" 
                                       placeholder="Enter your first name"
                                       required
                                       autocomplete="given-name">
                            </div>
                            <div class="error-message" id="firstNameError">
                                <?= isset($errors['first_name']) ? htmlspecialchars($errors['first_name']) : '' ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="last_name" class="form-label">Last Name</label>
                            <div class="input-group">
                                <i class="fas fa-user input-icon"></i>
                                <input type="text" 
                                       id="last_name" 
                                       name="last_name" 
                                       value="<?= htmlspecialchars($form_data['last_name']) ?>" 
                                       class="form-input" 
                                       placeholder="Enter your last name"
                                       required
                                       autocomplete="family-name">
                            </div>
                            <div class="error-message" id="lastNameError">
                                <?= isset($errors['last_name']) ? htmlspecialchars($errors['last_name']) : '' ?>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   value="<?= htmlspecialchars($form_data['email']) ?>" 
                                   class="form-input" 
                                   placeholder="Enter your email"
                                   required
                                   autocomplete="email">
                        </div>
                        <div class="error-message" id="emailError">
                            <?= isset($errors['email']) ? htmlspecialchars($errors['email']) : '' ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="phone" class="form-label">Phone Number (Optional)</label>
                        <div class="input-group">
                            <i class="fas fa-phone input-icon"></i>
                            <input type="tel" 
                                   id="phone" 
                                   name="phone" 
                                   value="<?= htmlspecialchars($form_data['phone']) ?>" 
                                   class="form-input" 
                                   placeholder="Enter your phone number"
                                   autocomplete="tel">
                        </div>
                        <div class="error-message" id="phoneError"></div>
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   class="form-input" 
                                   placeholder="Create a password"
                                   required
                                   autocomplete="new-password">
                            <button type="button" class="password-toggle" id="passwordToggle">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength" id="passwordStrength">
                            <div class="strength-bar"></div>
                            <div class="strength-text"></div>
                        </div>
                        <div class="error-message" id="passwordError">
                            <?= isset($errors['password']) ? htmlspecialchars($errors['password']) : '' ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" 
                                   id="confirm_password" 
                                   name="confirm_password" 
                                   class="form-input" 
                                   placeholder="Confirm your password"
                                   required
                                   autocomplete="new-password">
                            <button type="button" class="password-toggle" id="confirmPasswordToggle">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="error-message" id="confirmPasswordError"></div>
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="terms" id="terms" required>
                            <span class="checkmark"></span>
                            I agree to the <a href="<?= Config::url('/terms') ?>" target="_blank">Terms of Service</a> 
                            and <a href="<?= Config::url('/privacy') ?>" target="_blank">Privacy Policy</a>
                        </label>
                        <div class="error-message" id="termsError"></div>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="newsletter" id="newsletter" checked>
                            <span class="checkmark"></span>
                            Send me updates about new features and special offers
                        </label>
                    </div>

                    <button type="submit" class="auth-button button-primary" id="registerButton">
                        <span class="button-text">Create Account</span>
                        <div class="button-loader" id="registerLoader">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                    </button>
                </form>

                <!-- Additional Links -->
                <div class="auth-footer">
                    <p>Already have an account? 
                        <a href="<?= Config::url('/login') ?>" class="auth-link">Sign in here</a>
                    </p>
                </div>
            </div>

            <!-- Benefits Section -->
            <div class="auth-benefits">
                <h2>Join Our Community</h2>
                <div class="benefits-list">
                    <div class="benefit-item">
                        <i class="fas fa-shield-alt benefit-icon"></i>
                        <div class="benefit-content">
                            <h3>Trust & Safety</h3>
                            <p>Your information is secure with our advanced security measures</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-comments benefit-icon"></i>
                        <div class="benefit-content">
                            <h3>Make Your Voice Heard</h3>
                            <p>Share authentic experiences and help others make better choices</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-award benefit-icon"></i>
                        <div class="benefit-content">
                            <h3>Earn Recognition</h3>
                            <p>Get badges and recognition for your helpful contributions</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-users benefit-icon"></i>
                        <div class="benefit-content">
                            <h3>Join Millions</h3>
                            <p>Be part of a community that values honest business reviews</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php
    echo Components::call('Footers', 'businessFooter', [$footer_config]);
    echo Components::call('Footers', 'documentClose');
    ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const registerForm = document.getElementById('registerForm');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const passwordToggle = document.getElementById('passwordToggle');
        const confirmPasswordToggle = document.getElementById('confirmPasswordToggle');
        const registerButton = document.getElementById('registerButton');
        const registerLoader = document.getElementById('registerLoader');

        // Password visibility toggle
        passwordToggle.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        });

        confirmPasswordToggle.addEventListener('click', function() {
            const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPasswordInput.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        });

        // Password strength indicator
        passwordInput.addEventListener('input', function() {
            checkPasswordStrength(this.value);
            validatePasswordMatch();
        });

        confirmPasswordInput.addEventListener('input', validatePasswordMatch);

        function checkPasswordStrength(password) {
            const strengthBar = document.querySelector('.strength-bar');
            const strengthText = document.querySelector('.strength-text');
            let strength = 0;
            let feedback = '';

            // Check password length
            if (password.length >= 8) strength += 25;
            if (password.length >= 12) strength += 25;

            // Check for character variety
            if (/[a-z]/.test(password)) strength += 15;
            if (/[A-Z]/.test(password)) strength += 15;
            if (/[0-9]/.test(password)) strength += 10;
            if (/[^a-zA-Z0-9]/.test(password)) strength += 10;

            // Determine strength level
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

        function validatePasswordMatch() {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            const errorElement = document.getElementById('confirmPasswordError');

            if (confirmPassword && password !== confirmPassword) {
                showError(confirmPasswordInput, errorElement, 'Passwords do not match');
                return false;
            } else if (confirmPassword) {
                clearError(confirmPasswordInput, errorElement);
                return true;
            }
            return true;
        }

        function showError(input, errorElement, message) {
            input.classList.add('error');
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }

        function clearError(input, errorElement) {
            input.classList.remove('error');
            errorElement.textContent = '';
            errorElement.style.display = 'none';
        }

        // Form submission
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Basic validation
            let isValid = true;
            
            // Validate required fields
            const requiredFields = registerForm.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    const errorElement = document.getElementById(field.id + 'Error');
                    showError(field, errorElement, 'This field is required');
                }
            });

            // Validate email format
            const email = document.getElementById('email').value;
            if (email && !isValidEmail(email)) {
                isValid = false;
                const errorElement = document.getElementById('emailError');
                showError(document.getElementById('email'), errorElement, 'Please enter a valid email address');
            }

            // Validate password match
            if (!validatePasswordMatch()) {
                isValid = false;
            }

            if (isValid) {
                // Show loading state
                registerButton.disabled = true;
                registerLoader.style.display = 'inline-block';
                registerButton.querySelector('.button-text').textContent = 'Creating Account...';
                
                // Simulate API call (replace with actual API call)
                setTimeout(() => {
                    // For demo purposes, always submit the form
                    // In real implementation, you would make an AJAX call here
                    this.submit();
                }, 2000);
            }
        });

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        // Auto-clear errors on input
        const formInputs = registerForm.querySelectorAll('input');
        formInputs.forEach(input => {
            input.addEventListener('input', function() {
                if (this.classList.contains('error')) {
                    const errorElement = document.getElementById(this.id + 'Error');
                    clearError(this, errorElement);
                }
            });
        });

        // User type selection styling
        const userTypeOptions = document.querySelectorAll('.user-type-option');
        userTypeOptions.forEach(option => {
            option.addEventListener('click', function() {
                userTypeOptions.forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
            });
        });
    });
    </script>
</body>
</html>

<?php echo ob_get_clean(); ?>