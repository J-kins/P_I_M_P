<?php
/**
 * P.I.M.P - Email Verification Page
 * Email verification and account activation
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

$status = $_GET['status'] ?? 'pending'; // pending, success, expired, invalid
$email = $_SESSION['verification_email'] ?? '';
$resend_cooldown = $_SESSION['resend_cooldown'] ?? 0;

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
    'title' => 'Email Verification - P.I.M.P Business Repository',
    'metaTags' => [
        'description' => 'Verify your email address to activate your P.I.M.P account and access all features.',
        'keywords' => 'email verification, account activation, PIMP verification'
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
                <?php if ($status === 'pending'): ?>
                <!-- Verification Pending -->
                <div class="auth-header text-center">
                    <div class="verification-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h1>Verify Your Email</h1>
                    <p>We've sent a verification link to:</p>
                    <div class="verification-email">
                        <strong><?= htmlspecialchars($email) ?></strong>
                    </div>
                </div>

                <div class="verification-instructions">
                    <h3>Next Steps:</h3>
                    <ol>
                        <li>Check your email inbox</li>
                        <li>Click the verification link in the email</li>
                        <li>Your account will be activated automatically</li>
                    </ol>
                </div>

                <div class="verification-actions">
                    <button type="button" class="auth-button button-outline" id="resendButton" 
                            <?= $resend_cooldown > 0 ? 'disabled' : '' ?>>
                        <span class="button-text">
                            <?= $resend_cooldown > 0 ? "Resend in {$resend_cooldown}s" : 'Resend Email' ?>
                        </span>
                    </button>
                    <a href="<?= Config::url('/login') ?>" class="auth-link">
                        Already verified? Sign in
                    </a>
                </div>

                <?php elseif ($status === 'success'): ?>
                <!-- Verification Success -->
                <div class="auth-header text-center">
                    <div class="success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h1>Email Verified!</h1>
                    <p>Your email has been successfully verified. Your account is now active.</p>
                </div>

                <div class="verification-actions">
                    <a href="<?= Config::url('/login') ?>" class="auth-button button-primary">
                        Continue to Login
                    </a>
                </div>

                <?php elseif ($status === 'expired'): ?>
                <!-- Verification Expired -->
                <div class="auth-header text-center">
                    <div class="error-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h1>Verification Link Expired</h1>
                    <p>This verification link has expired. Please request a new one.</p>
                </div>

                <div class="verification-actions">
                    <button type="button" class="auth-button button-primary" id="resendExpiredButton">
                        Send New Verification Email
                    </button>
                </div>

                <?php else: ?>
                <!-- Invalid Verification -->
                <div class="auth-header text-center">
                    <div class="error-icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <h1>Invalid Verification Link</h1>
                    <p>This verification link is invalid. Please check the link or request a new one.</p>
                </div>

                <div class="verification-actions">
                    <a href="<?= Config::url('/register') ?>" class="auth-button button-outline">
                        Back to Registration
                    </a>
                    <button type="button" class="auth-button button-primary" id="resendInvalidButton">
                        Request New Verification
                    </button>
                </div>
                <?php endif; ?>
            </div>

            <!-- Benefits of Verification -->
            <div class="auth-benefits">
                <h2>Why Verify Your Email?</h2>
                <div class="benefits-list">
                    <div class="benefit-item">
                        <i class="fas fa-check-circle benefit-icon"></i>
                        <div class="benefit-content">
                            <h3>Account Security</h3>
                            <p>Protect your account with verified email authentication</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-bell benefit-icon"></i>
                        <div class="benefit-content">
                            <h3>Important Notifications</h3>
                            <p>Receive alerts about your reviews and account activity</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-star benefit-icon"></i>
                        <div class="benefit-content">
                            <h3>Full Access</h3>
                            <p>Access all features including writing reviews and saving favorites</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-shield-alt benefit-icon"></i>
                        <div class="benefit-content">
                            <h3>Trust & Credibility</h3>
                            <p>Verified accounts help build trust in the community</p>
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
        // Resend verification email functionality
        const resendButton = document.getElementById('resendButton');
        const resendExpiredButton = document.getElementById('resendExpiredButton');
        const resendInvalidButton = document.getElementById('resendInvalidButton');

        function handleResendClick(button) {
            if (button) {
                button.addEventListener('click', function() {
                    const originalText = button.querySelector('.button-text').textContent;
                    button.disabled = true;
                    button.querySelector('.button-text').textContent = 'Sending...';

                    // Simulate API call
                    setTimeout(() => {
                        // In real implementation, make AJAX call to resend verification
                        alert('Verification email has been resent!');
                        button.querySelector('.button-text').textContent = 'Email Sent!';
                        
                        // Re-enable after a delay
                        setTimeout(() => {
                            button.disabled = false;
                            button.querySelector('.button-text').textContent = originalText;
                        }, 3000);
                    }, 1500);
                });
            }
        }

        handleResendClick(resendButton);
        handleResendClick(resendExpiredButton);
        handleResendClick(resendInvalidButton);

        // Countdown timer for resend cooldown
        <?php if ($resend_cooldown > 0): ?>
        let cooldown = <?= $resend_cooldown ?>;
        const countdownInterval = setInterval(() => {
            cooldown--;
            if (resendButton) {
                resendButton.querySelector('.button-text').textContent = `Resend in ${cooldown}s`;
            }
            
            if (cooldown <= 0) {
                clearInterval(countdownInterval);
                if (resendButton) {
                    resendButton.disabled = false;
                    resendButton.querySelector('.button-text').textContent = 'Resend Email';
                }
            }
        }, 1000);
        <?php endif; ?>
    });
    </script>
</body>
</html>

<?php echo ob_get_clean(); ?>