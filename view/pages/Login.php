<?php
/**
 * P.I.M.P - Login Page
 * User authentication with comprehensive validation
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
$email = $_POST['email'] ?? '';
$remember_me = isset($_POST['remember_me']);
$error = $_SESSION['login_error'] ?? '';
$success = $_SESSION['login_success'] ?? '';

// Clear session messages after displaying
unset($_SESSION['login_error']);
unset($_SESSION['login_success']);

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
    'title' => 'Login - P.I.M.P Business Repository',
    'metaTags' => [
        'description' => 'Login to your P.I.M.P account to write reviews, manage your business, and access exclusive features.',
        'keywords' => 'login, sign in, PIMP account, business repository'
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
            ['url' => '/register', 'label' => 'Register', 'separator' => false],
        ],
        'showSearch' => false,
    ]]);
    ?>

    <main class="auth-main">
        <div class="auth-container">
            <div class="auth-card">
                <!-- Login Header -->
                <div class="auth-header">
                    <h1>Welcome Back</h1>
                    <p>Sign in to your P.I.M.P account</p>
                </div>

                <!-- Social Login Options -->
                <div class="social-login">
                    <button type="button" class="social-button google-button">
                        <i class="fab fa-google"></i>
                        Continue with Google
                    </button>
                    <button type="button" class="social-button facebook-button">
                        <i class="fab fa-facebook-f"></i>
                        Continue with Facebook
                    </button>
                </div>

                <div class="divider">
                    <span>or</span>
                </div>

                <!-- Login Form -->
                <form id="loginForm" action="<?= Config::url('/login') ?>" method="POST" class="auth-form" novalidate>
                    <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?= htmlspecialchars($success) ?>
                    </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   value="<?= htmlspecialchars($email) ?>" 
                                   class="form-input" 
                                   placeholder="Enter your email"
                                   required
                                   autocomplete="email">
                        </div>
                        <div class="error-message" id="emailError"></div>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" 
                                   id="password" 
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
                            <input type="checkbox" 
                                   name="remember_me" 
                                   id="remember_me" 
                                   <?= $remember_me ? 'checked' : '' ?>>
                            <span class="checkmark"></span>
                            Remember me
                        </label>
                        <a href="<?= Config::url('/forgot-password') ?>" class="forgot-password">
                            Forgot password?
                        </a>
                    </div>

                    <button type="submit" class="auth-button button-primary" id="loginButton">
                        <span class="button-text">Sign In</span>
                        <div class="button-loader" id="loginLoader">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                    </button>
                </form>

                <!-- Additional Links -->
                <div class="auth-footer">
                    <p>Don't have an account? 
                        <a href="<?= Config::url('/register') ?>" class="auth-link">Sign up here</a>
                    </p>
                    <p>Are you a business owner? 
                        <a href="<?= Config::url('/business/login') ?>" class="auth-link">Business login</a>
                    </p>
                </div>
            </div>

            <!-- Benefits Section -->
            <div class="auth-benefits">
                <h2>Why Sign In?</h2>
                <div class="benefits-list">
                    <div class="benefit-item">
                        <i class="fas fa-pencil-alt benefit-icon"></i>
                        <div class="benefit-content">
                            <h3>Write Reviews</h3>
                            <p>Share your experiences and help others make informed decisions</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-heart benefit-icon"></i>
                        <div class="benefit-content">
                            <h3>Save Favorites</h3>
                            <p>Bookmark businesses you love and want to revisit</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-bell benefit-icon"></i>
                        <div class="benefit-content">
                            <h3>Get Notifications</h3>
                            <p>Stay updated on new reviews and business responses</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-star benefit-icon"></i>
                        <div class="benefit-content">
                            <h3>Earn Rewards</h3>
                            <p>Get recognized for your helpful contributions</p>
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
        const loginForm = document.getElementById('loginForm');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const passwordToggle = document.getElementById('passwordToggle');
        const loginButton = document.getElementById('loginButton');
        const loginLoader = document.getElementById('loginLoader');

        // Password visibility toggle
        passwordToggle.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        });

        // Real-time validation
        emailInput.addEventListener('blur', validateEmail);
        passwordInput.addEventListener('blur', validatePassword);

        function validateEmail() {
            const email = emailInput.value.trim();
            const errorElement = document.getElementById('emailError');
            
            if (!email) {
                showError(emailInput, errorElement, 'Email is required');
                return false;
            }
            
            if (!isValidEmail(email)) {
                showError(emailInput, errorElement, 'Please enter a valid email address');
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
                
                // Simulate API call (replace with actual API call)
                setTimeout(() => {
                    // For demo purposes, always submit the form
                    // In real implementation, you would make an AJAX call here
                    this.submit();
                }, 1500);
            }
        });

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

        // Social login handlers
        document.querySelector('.google-button').addEventListener('click', function() {
            // Implement Google OAuth
            alert('Google OAuth would be implemented here');
        });

        document.querySelector('.facebook-button').addEventListener('click', function() {
            // Implement Facebook OAuth
            alert('Facebook OAuth would be implemented here');
        });
    });
    </script>
</body>
</html>

<?php echo ob_get_clean(); ?>