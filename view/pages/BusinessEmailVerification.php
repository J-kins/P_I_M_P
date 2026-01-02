<?php
/**
 * P.I.M.P - Business Email Verification
 * Business email verification and account activation system
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

// Get verification status from query parameters or session
$verification_status = $_GET['status'] ?? 'pending'; // pending, sent, verified, expired, invalid
$email = $_GET['email'] ?? 'business@example.com';

ob_start();
?>

<?php
echo Components::call('Headers', 'documentHead', [[
    'title' => 'Verify Your Email - P.I.M.P Business',
    'metaTags' => [
        'description' => 'Verify your business email address to activate your P.I.M.P business account',
        'keywords' => 'email verification, business verification, account activation, PIMP verification'
    ],
    'styles' => [
        'views/business-email-verification.css'
    ],
    'scripts' => [
        'js/business-email-verification.js'
    ]
]]);
?>

<body class="business-email-verification-page">
    <?php
    echo Components::call('Headers', 'businessHeader', [[
        'logo' => Config::imageUrl('logo.png'),
        'logoAlt' => 'P.I.M.P - Business Repository Platform',
        'mainNavItems' => $nav_items,
        'userActions' => [
            ['url' => '/business/login', 'label' => 'Business Login', 'separator' => false],
        ],
        'showSearch' => false,
    ]]);
    ?>

    <main class="business-email-verification-main">
        <!-- Verification Container -->
        <section class="verification-section">
            <div class="container">
                <div class="verification-card">
                    <!-- Verification Header -->
                    <div class="verification-header">
                        <div class="verification-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h1>Verify Your Email Address</h1>
                        <p>We've sent a verification link to your email</p>
                    </div>

                    <!-- Dynamic Content Based on Verification Status -->
                    <div class="verification-content">
                        <!-- Pending Verification -->
                        <div class="verification-state <?= $verification_status === 'pending' ? 'active' : '' ?>" id="pendingState">
                            <div class="state-content">
                                <h2>Check Your Email</h2>
                                <p>We've sent a verification link to:</p>
                                <div class="email-display">
                                    <i class="fas fa-envelope"></i>
                                    <span id="verificationEmail"><?= htmlspecialchars($email) ?></span>
                                </div>
                                <p class="instruction-text">
                                    Click the link in the email to verify your business account and complete your registration.
                                </p>
                            </div>

                            <div class="verification-actions">
                                <button class="resend-button button-primary" id="resendVerification">
                                    <i class="fas fa-paper-plane"></i>
                                    Resend Verification Email
                                </button>
                                <button class="change-email-button button-secondary" id="changeEmail">
                                    <i class="fas fa-edit"></i>
                                    Change Email Address
                                </button>
                            </div>

                            <div class="verification-help">
                                <h3>Didn't receive the email?</h3>
                                <ul>
                                    <li>Check your spam or junk folder</li>
                                    <li>Make sure you entered the correct email address</li>
                                    <li>Wait a few minutes - it may take up to 5 minutes to arrive</li>
                                    <li>Add <strong>noreply@pimp-business.com</strong> to your contacts</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Verification Sent -->
                        <div class="verification-state <?= $verification_status === 'sent' ? 'active' : '' ?>" id="sentState">
                            <div class="state-content">
                                <div class="success-animation">
                                    <i class="fas fa-paper-plane"></i>
                                </div>
                                <h2>Verification Email Sent!</h2>
                                <p>We've sent a new verification link to:</p>
                                <div class="email-display">
                                    <i class="fas fa-envelope"></i>
                                    <span id="sentEmail"><?= htmlspecialchars($email) ?></span>
                                </div>
                                <p class="instruction-text">
                                    Please check your inbox and click the verification link to activate your account.
                                </p>
                            </div>

                            <div class="verification-actions">
                                <button class="resend-button button-secondary" id="resendAgain">
                                    <i class="fas fa-redo"></i>
                                    Send Another Email
                                </button>
                                <button class="change-email-button button-secondary" id="changeEmailSent">
                                    <i class="fas fa-edit"></i>
                                    Change Email Address
                                </button>
                            </div>
                        </div>

                        <!-- Email Change Form -->
                        <div class="verification-state" id="changeEmailState">
                            <div class="state-content">
                                <h2>Change Email Address</h2>
                                <p>Enter your new email address below</p>
                                
                                <form id="changeEmailForm" class="email-change-form">
                                    <div class="form-group">
                                        <label for="newEmail" class="form-label">New Email Address</label>
                                        <input type="email" id="newEmail" name="new_email" class="form-input" required placeholder="Enter your new email address">
                                        <div class="error-message" id="newEmailError"></div>
                                    </div>
                                    <div class="form-group">
                                        <label for="confirmNewEmail" class="form-label">Confirm New Email Address</label>
                                        <input type="email" id="confirmNewEmail" name="confirm_new_email" class="form-input" required placeholder="Confirm your new email address">
                                        <div class="error-message" id="confirmNewEmailError"></div>
                                    </div>
                                    
                                    <div class="form-actions">
                                        <button type="button" class="cancel-change button-secondary" id="cancelChangeEmail">
                                            Cancel
                                        </button>
                                        <button type="submit" class="update-email-button button-primary">
                                            <i class="fas fa-save"></i>
                                            Update Email Address
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Verification Success -->
                        <div class="verification-state <?= $verification_status === 'verified' ? 'active' : '' ?>" id="successState">
                            <div class="state-content">
                                <div class="success-animation">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <h2>Email Verified Successfully!</h2>
                                <p>Your business email has been verified and your account is now active.</p>
                                
                                <div class="success-details">
                                    <div class="detail-item">
                                        <i class="fas fa-check"></i>
                                        <span>Email address verified</span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-check"></i>
                                        <span>Business account activated</span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-check"></i>
                                        <span>Full platform access granted</span>
                                    </div>
                                </div>
                            </div>

                            <div class="verification-actions">
                                <a href="/business/dashboard" class="button-primary">
                                    <i class="fas fa-tachometer-alt"></i>
                                    Go to Dashboard
                                </a>
                                <a href="/business/profile" class="button-secondary">
                                    <i class="fas fa-user-circle"></i>
                                    Complete Your Profile
                                </a>
                            </div>
                        </div>

                        <!-- Verification Expired -->
                        <div class="verification-state <?= $verification_status === 'expired' ? 'active' : '' ?>" id="expiredState">
                            <div class="state-content">
                                <div class="error-animation">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <h2>Verification Link Expired</h2>
                                <p>The verification link has expired. Please request a new verification email.</p>
                                <p class="instruction-text">
                                    Verification links are valid for 24 hours. For security reasons, you'll need to request a new one.
                                </p>
                            </div>

                            <div class="verification-actions">
                                <button class="resend-button button-primary" id="resendExpired">
                                    <i class="fas fa-paper-plane"></i>
                                    Send New Verification Email
                                </button>
                                <button class="change-email-button button-secondary" id="changeEmailExpired">
                                    <i class="fas fa-edit"></i>
                                    Change Email Address
                                </button>
                            </div>
                        </div>

                        <!-- Invalid Verification -->
                        <div class="verification-state <?= $verification_status === 'invalid' ? 'active' : '' ?>" id="invalidState">
                            <div class="state-content">
                                <div class="error-animation">
                                    <i class="fas fa-times-circle"></i>
                                </div>
                                <h2>Invalid Verification Link</h2>
                                <p>The verification link is invalid or has already been used.</p>
                                <p class="instruction-text">
                                    If you believe this is an error, please request a new verification email or contact support.
                                </p>
                            </div>

                            <div class="verification-actions">
                                <button class="resend-button button-primary" id="resendInvalid">
                                    <i class="fas fa-paper-plane"></i>
                                    Send New Verification Email
                                </button>
                                <a href="/contact" class="button-secondary">
                                    <i class="fas fa-headset"></i>
                                    Contact Support
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Countdown Timer -->
                    <div class="countdown-section" id="countdownSection">
                        <div class="countdown-header">
                            <i class="fas fa-clock"></i>
                            <span>Resend available in:</span>
                        </div>
                        <div class="countdown-timer" id="countdownTimer">
                            <span class="countdown-minutes">05</span>:<span class="countdown-seconds">00</span>
                        </div>
                    </div>

                    <!-- Support Section -->
                    <div class="support-section">
                        <div class="support-header">
                            <i class="fas fa-question-circle"></i>
                            <h3>Need Help?</h3>
                        </div>
                        <div class="support-options">
                            <div class="support-option">
                                <i class="fas fa-envelope-open-text"></i>
                                <div class="support-info">
                                    <h4>Check Spam Folder</h4>
                                    <p>Sometimes verification emails end up in spam or junk folders</p>
                                </div>
                            </div>
                            <div class="support-option">
                                <i class="fas fa-sync-alt"></i>
                                <div class="support-info">
                                    <h4>Wait a Few Minutes</h4>
                                    <p>Email delivery can take 2-5 minutes depending on your provider</p>
                                </div>
                            </div>
                            <div class="support-option">
                                <i class="fas fa-headset"></i>
                                <div class="support-info">
                                    <h4>Contact Support</h4>
                                    <p>Our team is here to help you get verified</p>
                                    <a href="/contact" class="support-link">Get Help</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Progress Steps -->
        <section class="progress-section">
            <div class="container">
                <div class="progress-steps">
                    <div class="progress-step completed">
                        <div class="step-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="step-content">
                            <h3>Account Created</h3>
                            <p>Your business account has been registered</p>
                        </div>
                    </div>
                    <div class="progress-step active">
                        <div class="step-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="step-content">
                            <h3>Email Verification</h3>
                            <p>Verify your email to activate your account</p>
                        </div>
                    </div>
                    <div class="progress-step">
                        <div class="step-icon">
                            <i class="fas fa-tachometer-alt"></i>
                        </div>
                        <div class="step-content">
                            <h3>Dashboard Access</h3>
                            <p>Access your business dashboard and tools</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php
    echo Components::call('Footers', 'businessFooter', [$footer_config]);
    echo Components::call('Footers', 'documentClose');
    ?>
</body>
</html>

<?php echo ob_get_clean(); ?>
