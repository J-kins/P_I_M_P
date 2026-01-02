<?php
/**
 * P.I.M.P - Business Login
 * Business authentication with comprehensive validation
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

$footer_config = [
    'logo' => Config::imageUrl('logo.png'),
    'logoAlt' => 'P.I.M.P Business Repository',
    'theme' => 'light'
];

ob_start();
?>

<?php
echo Components::call('Headers', 'documentHead', [[
    'title' => 'Business Login - P.I.M.P',
    'metaTags' => [
        'description' => 'Login to your P.I.M.P business account to manage your profile, respond to reviews, and access business tools.',
        'keywords' => 'business login, business account, PIMP business portal'
    ],
    'styles' => [
        'views/business-auth.css'
    ],
    'scripts' => [
        'js/business-auth.js'
    ]
]]);
?>

<body class="business-auth-page">
    <?php
    echo Components::call('Headers', 'businessHeader', [[
        'logo' => Config::imageUrl('logo.png'),
        'logoAlt' => 'P.I.M.P - Business Repository Platform',
        'mainNavItems' => $nav_items,
        'userActions' => [
            ['url' => '/business/register', 'label' => 'Business Register', 'separator' => false],
        ],
        'showSearch' => false,
    ]]);
    ?>

    <main class="auth-main">
        <div class="auth-container">
            <div class="auth-card">
                <!-- Login Header -->
                <div class="auth-header">
                    <h1>Business Login</h1>
                    <p>Access your business dashboard</p>
                </div>

                <!-- Login Form -->
                <form id="businessLoginForm" action="<?= Config::url('/business/login') ?>" method="POST" class="auth-form" novalidate>
                    <div class="form-group">
                        <label for="businessEmail" class="form-label">Business Email</label>
                        <div class="input-group">
                            <i class="fas fa-building input-icon"></i>
                            <input type="email" 
                                   id="businessEmail" 
                                   name="email" 
                                   class="form-input" 
                                   placeholder="Enter your business email"
                                   required
                                   autocomplete="email">
                        </div>
                        <div class="error-message" id="emailError"></div>
                    </div>

                    <div class="form-group">
                        <label for="businessPassword" class="form-label">Password</label>
                        <div class="input-group">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" 
                                   id="businessPassword" 
                                   name="password" 
                                   class="form-input" 
                                   placeholder="Enter your password"
                                   required
                                   autocomplete="current-password">
                            <button type="button" class="password-toggle" id="passwordToggle">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="error-message" id="passwordError"></div>
                    </div>

                    <div class="form-options">
                        <label class="checkbox-label">
                            <input type="checkbox" name="remember_me" id="remember_me">
                            <span class="checkmark"></span>
                            Remember me
                        </label>
                        <a href="<?= Config::url('/business/forgot-password') ?>" class="forgot-password">
                            Forgot password?
                        </a>
                    </div>

                    <button type="submit" class="auth-button button-primary" id="loginButton">
                        <span class="button-text">Sign In to Dashboard</span>
                        <div class="button-loader" id="loginLoader">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                    </button>
                </form>

                <!-- Additional Links -->
                <div class="auth-footer">
                    <p>Don't have a business account? 
                        <a href="<?= Config::url('/business/register') ?>" class="auth-link">Register your business</a>
                    </p>
                    <p>Need to claim your business? 
                        <a href="<?= Config::url('/business/claim') ?>" class="auth-link">Claim your business</a>
                    </p>
                </div>
            </div>

            <!-- Benefits Section -->
            <div class="auth-benefits">
                <h2>Business Dashboard Features</h2>
                <div class="benefits-list">
                    <div class="benefit-item">
                        <i class="fas fa-star benefit-icon"></i>
                        <div class="benefit-content">
                            <h3>Review Management</h3>
                            <p>Respond to customer reviews and manage your online reputation</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-chart-line benefit-icon"></i>
                        <div class="benefit-content">
                            <h3>Business Analytics</h3>
                            <p>Track profile views, customer engagement, and performance metrics</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-shield-alt benefit-icon"></i>
                        <div class="benefit-content">
                            <h3>Accreditation Tools</h3>
                            <p>Apply for and maintain P.I.M.P accreditation status</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-cog benefit-icon"></i>
                        <div class="benefit-content">
                            <h3>Profile Management</h3>
                            <p>Update business information, photos, and service details</p>
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
        const loginForm = document.getElementById('businessLoginForm');
        const emailInput = document.getElementById('businessEmail');
        const passwordInput = document.getElementById('businessPassword');
        const passwordToggle = document.getElementById('passwordToggle');
        const loginButton = document.getElementById('loginButton');
        const loginLoader = document.getElementById('loginLoader');

        // Password visibility toggle
        passwordToggle.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        });

        // Form submission
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const isEmailValid = validateEmail();
            const isPasswordValid = validatePassword();
            
            if (isEmailValid && isPasswordValid) {
                // Show loading state
                loginButton.disabled = true;
                loginLoader.style.display = 'inline-block';
                loginButton.querySelector('.button-text').textContent = 'Signing In...';
                
                // Simulate API call
                setTimeout(() => {
                    // For demo purposes, always submit the form
                    this.submit();
                }, 1500);
            }
        });

        function validateEmail() {
            const email = emailInput.value.trim();
            const errorElement = document.getElementById('emailError');
            
            if (!email) {
                showError(emailInput, errorElement, 'Business email is required');
                return false;
            }
            
            if (!isValidEmail(email)) {
                showError(emailInput, errorElement, 'Please enter a valid business email');
                return false;
            }
            
            clearError(emailInput, errorElement);
            return true;
        }

        function validatePassword() {
            const password = passwordInput.value;
            const errorElement = document.getElementById('passwordError');
            
            if (!password) {
                showError(passwordInput, errorElement, 'Password is required');
                return false;
            }
            
            clearError(passwordInput, errorElement);
            return true;
        }

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
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

        // Auto-clear errors on input
        emailInput.addEventListener('input', function() {
            if (this.classList.contains('error')) {
                clearError(this, document.getElementById('emailError'));
            }
        });

        passwordInput.addEventListener('input', function() {
            if (this.classList.contains('error')) {
                clearError(this, document.getElementById('passwordError'));
            }
        });
    });
    </script>
</body>
</html>

<?php echo ob_get_clean(); ?>