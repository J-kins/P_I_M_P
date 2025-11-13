<?php
/**
 * P.I.M.P - Contact Page
 * Contact form and information page
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

// Get form data and messages
$form_data = [
    'name' => $_POST['name'] ?? '',
    'email' => $_POST['email'] ?? '',
    'subject' => $_POST['subject'] ?? '',
    'message' => $_POST['message'] ?? '',
    'inquiry_type' => $_POST['inquiry_type'] ?? 'general'
];
$errors = $_SESSION['contact_errors'] ?? [];
$success = $_SESSION['contact_success'] ?? '';

// Clear session messages
unset($_SESSION['contact_errors']);
unset($_SESSION['contact_success']);

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
        [
            'title' => 'For Businesses',
            'links' => [
                ['url' => '/business/claim', 'label' => 'Claim Your Business'],
                ['url' => '/business/advertise', 'label' => 'Advertise With Us'],
                ['url' => '/business/resources', 'label' => 'Business Resources'],
                ['url' => '/for-business', 'label' => 'For Business Home'],
            ]
        ],
        [
            'title' => 'Company',
            'links' => [
                ['url' => '/about', 'label' => 'About Us'],
                ['url' => '/news', 'label' => 'News & Updates'],
                ['url' => '/careers', 'label' => 'Careers'],
                ['url' => '/contact', 'label' => 'Contact Us', 'active' => true],
            ]
        ]
    ],
    'social' => [
        [
            'platform' => 'facebook',
            'url' => 'https://facebook.com/pimpbusiness',
            'icon' => '<i class="fab fa-facebook-f"></i>',
            'name' => 'Facebook',
            'newTab' => true
        ],
        [
            'platform' => 'twitter',
            'url' => 'https://twitter.com/pimpbusiness',
            'icon' => '<i class="fab fa-twitter"></i>',
            'name' => 'Twitter',
            'newTab' => true
        ],
        [
            'platform' => 'linkedin',
            'url' => 'https://linkedin.com/company/pimp-business',
            'icon' => '<i class="fab fa-linkedin-in"></i>',
            'name' => 'LinkedIn',
            'newTab' => true
        ],
        [
            'platform' => 'instagram',
            'url' => 'https://instagram.com/pimpbusiness',
            'icon' => '<i class="fab fa-instagram"></i>',
            'name' => 'Instagram',
            'newTab' => true
        ]
    ],
    'contact' => [
        ['label' => 'Phone', 'value' => '1-800-PIMP-HELP'],
        ['label' => 'Email', 'value' => 'support@pimp-business.com'],
        ['label' => 'Address', 'value' => '123 Business Ave, Suite 100, New York, NY 10001']
    ],
    'theme' => 'light'
];

ob_start();
?>

<?php
echo Components::call('Headers', 'documentHead', [[
    'title' => 'Contact Us - P.I.M.P Business Repository',
    'metaTags' => [
        'description' => 'Get in touch with P.I.M.P Business Repository. We\'re here to help with questions, support, and feedback.',
        'keywords' => 'contact PIMP, customer support, help, inquiry',
        'author' => 'P.I.M.P Business Repository'
    ],
    'canonical' => Config::url('/contact'),
    'styles' => [
        'views/contact.css'
    ],
    'scripts' => [
        'static/js/contact.js'
    ]
]]);
?>

<body>
    <?php
    echo Components::call('Headers', 'businessHeader', [[
        'logo' => Config::imageUrl('logo.png'),
        'logoAlt' => 'P.I.M.P - Business Repository Platform',
        'topBarItems' => [
            ['url' => '/about', 'label' => 'About PIMP'],
            ['url' => '/news', 'label' => 'News & Updates'],
            ['url' => '/careers', 'label' => 'Careers'],
            ['url' => '/contact', 'label' => 'Contact Us', 'active' => true],
        ],
        'mainNavItems' => $nav_items,
        'userActions' => [
            ['url' => '/login', 'label' => 'Log In'],
            ['url' => '/register', 'label' => 'Register', 'separator' => true],
            ['url' => '/for-business', 'label' => 'For Business'],
        ],
        'showSearch' => true,
        'showPhone' => true,
    ]]);
    ?>

    <!-- Hero Section -->
    <?php
    echo Components::call('Headers', 'heroHeader', [[
        'title' => 'Contact Us',
        'subtitle' => 'We\'re here to help. Get in touch with our team.',
        'bgImage' => Config::imageUrl('hero-bg.jpg'),
        'overlay' => 'dark',
        'size' => 'md',
        'align' => 'center'
    ]]);
    ?>

    <main class="main-content">
        <div class="contact-container">
            <div class="container">
                <div class="contact-grid">
                    <!-- Contact Information -->
                    <div class="contact-info">
                        <h2>Get in Touch</h2>
                        <p class="contact-intro">
                            Have a question or need assistance? We're here to help. Reach out to us through any of the following methods.
                        </p>

                        <div class="contact-methods">
                            <div class="contact-method">
                                <div class="method-icon">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="method-content">
                                    <h3>Phone</h3>
                                    <p><a href="tel:1-800-PIMP-HELP">1-800-PIMP-HELP</a></p>
                                    <p class="method-note">Monday - Friday, 9 AM - 6 PM EST</p>
                                </div>
                            </div>

                            <div class="contact-method">
                                <div class="method-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="method-content">
                                    <h3>Email</h3>
                                    <p><a href="mailto:support@pimp-business.com">support@pimp-business.com</a></p>
                                    <p class="method-note">We typically respond within 24 hours</p>
                                </div>
                            </div>

                            <div class="contact-method">
                                <div class="method-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="method-content">
                                    <h3>Address</h3>
                                    <p>123 Business Ave, Suite 100<br>New York, NY 10001</p>
                                    <p class="method-note">Visit by appointment only</p>
                                </div>
                            </div>
                        </div>

                        <div class="social-links">
                            <h3>Follow Us</h3>
                            <div class="social-icons">
                                <a href="https://facebook.com/pimpbusiness" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                                <a href="https://twitter.com/pimpbusiness" target="_blank" rel="noopener noreferrer" aria-label="Twitter">
                                    <i class="fab fa-twitter"></i>
                                </a>
                                <a href="https://linkedin.com/company/pimp-business" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn">
                                    <i class="fab fa-linkedin-in"></i>
                                </a>
                                <a href="https://instagram.com/pimpbusiness" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                                    <i class="fab fa-instagram"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Form -->
                    <div class="contact-form-wrapper">
                        <h2>Send Us a Message</h2>
                        
                        <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <?= htmlspecialchars($success) ?>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($errors)): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>

                        <form action="<?= Config::url('/contact') ?>" method="POST" class="contact-form" id="contactForm">
                            <div class="form-group">
                                <label for="inquiry_type">Inquiry Type <span class="required">*</span></label>
                                <select name="inquiry_type" id="inquiry_type" required>
                                    <option value="general" <?= $form_data['inquiry_type'] === 'general' ? 'selected' : '' ?>>General Inquiry</option>
                                    <option value="support" <?= $form_data['inquiry_type'] === 'support' ? 'selected' : '' ?>>Customer Support</option>
                                    <option value="business" <?= $form_data['inquiry_type'] === 'business' ? 'selected' : '' ?>>Business Inquiry</option>
                                    <option value="complaint" <?= $form_data['inquiry_type'] === 'complaint' ? 'selected' : '' ?>>File a Complaint</option>
                                    <option value="feedback" <?= $form_data['inquiry_type'] === 'feedback' ? 'selected' : '' ?>>Feedback</option>
                                    <option value="media" <?= $form_data['inquiry_type'] === 'media' ? 'selected' : '' ?>>Media Inquiry</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="name">Your Name <span class="required">*</span></label>
                                <input type="text" name="name" id="name" value="<?= htmlspecialchars($form_data['name']) ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="email">Email Address <span class="required">*</span></label>
                                <input type="email" name="email" id="email" value="<?= htmlspecialchars($form_data['email']) ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="subject">Subject <span class="required">*</span></label>
                                <input type="text" name="subject" id="subject" value="<?= htmlspecialchars($form_data['subject']) ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="message">Message <span class="required">*</span></label>
                                <textarea name="message" id="message" rows="6" required><?= htmlspecialchars($form_data['message']) ?></textarea>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="button button-primary button-full">
                                    <i class="fas fa-paper-plane"></i>
                                    Send Message
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php
    echo Components::call('Footers', 'businessFooter', [$footer_config]);
    ?>

    <?php
    echo Components::call('Footers', 'documentClose', [[
        'includeMainJs' => true
    ]]);
    ?>
</body>
</html>

<?php
echo ob_get_clean();
?>

