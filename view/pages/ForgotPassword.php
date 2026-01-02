<?php
/**
 * P.I.M.P - Forgot Password Page
 * Password recovery functionality
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

// Get form data
$email = $_POST['email'] ?? '';
$step = $_GET['step'] ?? 'request'; // request, verify, reset
$token = $_GET['token'] ?? '';
$error = $_SESSION['password_error'] ?? '';
$success = $_SESSION['password_success'] ?? '';

// Clear session messages
unset($_SESSION['password_error']);
unset($_SESSION['password_success']);

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
    'title' => 'Forgot Password - P.I.M.P Business Repository',
    'metaTags' => [
        'description' => 'Reset your P.I.M.P account password. Enter your email to receive password reset instructions.',
        'keywords' => 'forgot password, reset password, PIMP account recovery'
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
                <?php if ($step === 'request'): ?>
                <!-- Request Password Reset -->
                <div class="auth-header">
                    <h1>Reset Your Password</h1>
                    <p>Enter your email to receive reset instructions</p>
                </div>

                <form id="forgotPasswordForm" action="<?= Config::url('/forgot-password') ?>" method="POST" class="auth-form">
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
                                   placeholder="Enter your email address"
                                   required
                                   autocomplete="email">
                        </div>
                        <div class="error-message" id="emailError"></div>
                    </div>

                    <button type="submit" class="auth-button button-primary" id="submitButton">
                        <span class="button-text">Send Reset Instructions</span>
                        <div class="button-loader" id="submitLoader">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                    </button>
                </form>

                <div class="auth-footer">
                    <p>Remember your password? 
                        <a href="<?= Config::url('/login') ?>" class="auth-link">Back to login</a>
                    </p>
                </div>

                <?php elseif ($step === 'verify' && $token): ?>
                <!-- Verify Token and Reset Password -->
                <div class="auth-header">
                    <h1>Create New Password</h1>
                    <p>Enter your new password below</p>
                </div>

                <form id="resetPasswordForm" action="<?= Config::url('/forgot-password?step=reset&token=' . urlencode($token)) ?>" method="POST" class="auth-form">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                    <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="new_password" class="form-label">New Password</label>
                        <div class="input-group">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" 
                                   id="new_password" 
                                   name="new_password" 
                                   class="form-input" 
                                   placeholder="Enter new password"
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
                        <div class="error-message" id="passwordError"></div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <div class="input-group">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" 
                                   id="confirm_password" 
                                   name="confirm_password" 
                                   class="form-input" 
                                   placeholder="Confirm new password"
                                   required
                                   autocomplete="new-password">
                            <button type="button" class="password-toggle" id="confirmPasswordToggle">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="error-message" id="confirmPasswordError"></div>
                    </div>

                    <button type="submit" class="auth-button button-primary" id="resetButton">
                        <span class="button-text">Reset Password</span>
                        <div class="button-loader" id="resetLoader">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                    </button>
                </form>

                <?php elseif ($step === 'reset'): ?>
                <!-- Password Reset Success -->
                <div class="auth-header text-center">
                    <div class="success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h1>Password Reset Successful</h1>
                    <p>Your password has been successfully reset. You can now login with your new password.</p>
                </div>

                <div class="auth-actions">
                    <a href="<?= Config::url('/login') ?>" class="auth-button button-primary">
                        Continue to Login
                    </a>
                </div>

                <?php else: ?>
                <!-- Invalid or Expired Token -->
                <div class="auth-header text-center">
                    <div class="error-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h1>Invalid Reset Link</h1>
                    <p>This password reset link is invalid or has expired. Please request a new one.</p>
                </div>

                <div class="auth-actions">
                    <a href="<?= Config::url('/forgot-password') ?>" class="auth-button button-primary">
                        Request New Reset Link
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Security Information -->
            <div class="auth-benefits">
                <h2>Account Security</h2>
                <div class="benefits-list">
                    <div class="benefit-item">
                        <i class="fas fa-shield-alt benefit-icon"></i>
                        <div class="benefit-content">
                            <h3>Secure Process</h3>
                            <p>Your password reset request is encrypted and secure</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-clock benefit-icon"></i>
                        <div class="benefit-content">
                            <h3>Time-Sensitive</h3>
                            <p>Reset links expire after 1 hour for your security</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-envelope benefit-icon"></i>
                        <div class="benefit-content">
                            <h3>Email Verification</h3>
                            <p>We'll send instructions to your registered email</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-user-shield benefit-icon"></i>
                        <div class="benefit-content">
                            <h3>Account Protection</h3>
                            <p>We verify your identity before allowing password changes</p>
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
        // Password reset functionality
        const forgotPasswordForm = document.getElementById('forgotPasswordForm');
        const resetPasswordForm = document.getElementById('resetPasswordForm');
        
        if (forgotPasswordForm) {
            forgotPasswordForm.addEventListener('submit', function(e) {
                e.preventDefault();
                // Add validation and submission logic here
                this.submit();
            });
        }

        if (resetPasswordForm) {
            // Add password strength and validation logic here
            // Similar to register page functionality
        }
    });
    </script>
</body>
</html>

<?php echo ob_get_clean(); ?>