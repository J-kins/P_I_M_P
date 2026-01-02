<?php
/**
 * P.I.M.P - Business Settings
 * Business account settings management
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
    'title' => 'Business Settings - P.I.M.P Business Dashboard',
    'metaTags' => [
        'description' => 'Manage your business account settings, profile information, and preferences',
        'keywords' => 'business settings, account management, profile settings, PIMP business'
    ],
    'styles' => [
        'views/business-settings.css'
    ],
    'scripts' => [
        'js/business-settings.js'
    ]
]]);
?>

<body class="business-settings-page">
    <?php
    echo Components::call('Headers', 'businessHeader', [[
        'logo' => Config::imageUrl('logo.png'),
        'logoAlt' => 'P.I.M.P - Business Repository Platform',
        'mainNavItems' => $nav_items,
        'userActions' => [
            ['url' => '/business/dashboard', 'label' => 'Dashboard', 'separator' => false],
            ['url' => '/business/reviews', 'label' => 'Reviews', 'separator' => false],
            ['url' => '/business/accreditation', 'label' => 'Accreditation', 'separator' => false],
            ['url' => '/logout', 'label' => 'Logout', 'separator' => true],
        ],
        'showSearch' => false,
    ]]);
    ?>

    <main class="business-settings-main">
        <!-- Page Header -->
        <div class="settings-page-header">
            <div class="container">
                <div class="page-header-content">
                    <h1>Business Settings</h1>
                    <p>Manage your account and business preferences</p>
                </div>
            </div>
        </div>

        <!-- Settings Layout -->
        <section class="settings-content-section">
            <div class="container">
                <div class="settings-layout">
                    <!-- Settings Navigation -->
                    <nav class="settings-nav" id="settingsNav">
                        <ul class="nav-list">
                            <li class="nav-item">
                                <a href="#profile" class="nav-link active" data-tab="profile">
                                    <i class="fas fa-user-circle"></i>
                                    Profile Information
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#contact" class="nav-link" data-tab="contact">
                                    <i class="fas fa-address-book"></i>
                                    Contact Details
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#notifications" class="nav-link" data-tab="notifications">
                                    <i class="fas fa-bell"></i>
                                    Notifications
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#security" class="nav-link" data-tab="security">
                                    <i class="fas fa-shield-alt"></i>
                                    Security
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#billing" class="nav-link" data-tab="billing">
                                    <i class="fas fa-credit-card"></i>
                                    Billing & Plans
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#integrations" class="nav-link" data-tab="integrations">
                                    <i class="fas fa-puzzle-piece"></i>
                                    Integrations
                                </a>
                            </li>
                        </ul>
                    </nav>

                    <!-- Settings Content -->
                    <div class="settings-content">
                        <!-- Profile Information Tab -->
                        <div class="settings-tab active" id="profileTab">
                            <div class="tab-header">
                                <h2>Profile Information</h2>
                                <p>Update your business profile details and branding</p>
                            </div>

                            <form class="settings-form" id="profileForm">
                                <div class="form-section">
                                    <h3>Basic Information</h3>
                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label for="businessName" class="form-label">Business Name</label>
                                            <input type="text" id="businessName" name="business_name" class="form-input" placeholder="Enter your business name">
                                        </div>
                                        <div class="form-group">
                                            <label for="businessType" class="form-label">Business Type</label>
                                            <select id="businessType" name="business_type" class="form-select">
                                                <option value="">Select business type</option>
                                                <option value="sole_proprietorship">Sole Proprietorship</option>
                                                <option value="partnership">Partnership</option>
                                                <option value="corporation">Corporation</option>
                                                <option value="llc">LLC</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="industry" class="form-label">Industry</label>
                                            <select id="industry" name="industry" class="form-select">
                                                <option value="">Select industry</option>
                                                <option value="technology">Technology</option>
                                                <option value="healthcare">Healthcare</option>
                                                <option value="retail">Retail</option>
                                                <option value="professional_services">Professional Services</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="foundedYear" class="form-label">Year Founded</label>
                                            <input type="number" id="foundedYear" name="founded_year" class="form-input" placeholder="YYYY" min="1900" max="2030">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-section">
                                    <h3>Business Description</h3>
                                    <div class="form-group">
                                        <label for="businessDescription" class="form-label">Description</label>
                                        <textarea id="businessDescription" name="business_description" class="form-textarea" rows="5" placeholder="Describe your business, services, and what makes you unique"></textarea>
                                        <div class="char-count">
                                            <span class="current-chars">0</span>/500 characters
                                        </div>
                                    </div>
                                </div>

                                <div class="form-section">
                                    <h3>Branding</h3>
                                    <div class="upload-section">
                                        <div class="upload-item">
                                            <div class="upload-preview">
                                                <img src="" alt="Logo preview" class="logo-preview" id="logoPreview" style="display: none;">
                                                <div class="upload-placeholder" id="logoPlaceholder">
                                                    <i class="fas fa-building"></i>
                                                    <span>Business Logo</span>
                                                </div>
                                            </div>
                                            <div class="upload-controls">
                                                <label class="upload-button button-secondary">
                                                    <i class="fas fa-upload"></i>
                                                    Upload Logo
                                                    <input type="file" id="logoUpload" class="file-input" accept="image/*" hidden>
                                                </label>
                                                <button type="button" class="remove-button button-text" id="removeLogo" style="display: none;">
                                                    Remove
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="save-button button-primary">
                                        Save Changes
                                    </button>
                                    <button type="button" class="cancel-button button-secondary">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Contact Details Tab -->
                        <div class="settings-tab" id="contactTab">
                            <div class="tab-header">
                                <h2>Contact Details</h2>
                                <p>Manage how customers can contact your business</p>
                            </div>

                            <form class="settings-form" id="contactForm">
                                <div class="form-section">
                                    <h3>Primary Contact</h3>
                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label for="contactName" class="form-label">Contact Person</label>
                                            <input type="text" id="contactName" name="contact_name" class="form-input" placeholder="Full name of contact person">
                                        </div>
                                        <div class="form-group">
                                            <label for="contactEmail" class="form-label">Email Address</label>
                                            <input type="email" id="contactEmail" name="contact_email" class="form-input" placeholder="contact@business.com">
                                        </div>
                                        <div class="form-group">
                                            <label for="contactPhone" class="form-label">Phone Number</label>
                                            <input type="tel" id="contactPhone" name="contact_phone" class="form-input" placeholder="+1 (555) 123-4567">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-section">
                                    <h3>Business Location</h3>
                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label for="addressLine1" class="form-label">Address Line 1</label>
                                            <input type="text" id="addressLine1" name="address_line1" class="form-input" placeholder="Street address">
                                        </div>
                                        <div class="form-group">
                                            <label for="addressLine2" class="form-label">Address Line 2</label>
                                            <input type="text" id="addressLine2" name="address_line2" class="form-input" placeholder="Apt, suite, unit, etc.">
                                        </div>
                                        <div class="form-group">
                                            <label for="city" class="form-label">City</label>
                                            <input type="text" id="city" name="city" class="form-input" placeholder="City">
                                        </div>
                                        <div class="form-group">
                                            <label for="state" class="form-label">State/Province</label>
                                            <input type="text" id="state" name="state" class="form-input" placeholder="State or province">
                                        </div>
                                        <div class="form-group">
                                            <label for="zipCode" class="form-label">ZIP/Postal Code</label>
                                            <input type="text" id="zipCode" name="zip_code" class="form-input" placeholder="ZIP or postal code">
                                        </div>
                                        <div class="form-group">
                                            <label for="country" class="form-label">Country</label>
                                            <select id="country" name="country" class="form-select">
                                                <option value="">Select country</option>
                                                <option value="us">United States</option>
                                                <option value="ca">Canada</option>
                                                <option value="uk">United Kingdom</option>
                                                <option value="au">Australia</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-section">
                                    <h3>Social Media & Links</h3>
                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label for="website" class="form-label">Website</label>
                                            <input type="url" id="website" name="website" class="form-input" placeholder="https://yourbusiness.com">
                                        </div>
                                        <div class="form-group">
                                            <label for="facebook" class="form-label">Facebook</label>
                                            <input type="url" id="facebook" name="facebook" class="form-input" placeholder="https://facebook.com/yourbusiness">
                                        </div>
                                        <div class="form-group">
                                            <label for="twitter" class="form-label">Twitter</label>
                                            <input type="url" id="twitter" name="twitter" class="form-input" placeholder="https://twitter.com/yourbusiness">
                                        </div>
                                        <div class="form-group">
                                            <label for="linkedin" class="form-label">LinkedIn</label>
                                            <input type="url" id="linkedin" name="linkedin" class="form-input" placeholder="https://linkedin.com/company/yourbusiness">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="save-button button-primary">
                                        Save Changes
                                    </button>
                                    <button type="button" class="cancel-button button-secondary">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Notifications Tab -->
                        <div class="settings-tab" id="notificationsTab">
                            <div class="tab-header">
                                <h2>Notification Preferences</h2>
                                <p>Choose how and when you want to be notified</p>
                            </div>

                            <form class="settings-form" id="notificationsForm">
                                <div class="form-section">
                                    <h3>Email Notifications</h3>
                                    <div class="toggle-group">
                                        <div class="toggle-item">
                                            <div class="toggle-info">
                                                <h4>New Reviews</h4>
                                                <p>Get notified when customers leave new reviews</p>
                                            </div>
                                            <label class="toggle-switch">
                                                <input type="checkbox" name="email_new_reviews" checked>
                                                <span class="toggle-slider"></span>
                                            </label>
                                        </div>
                                        <div class="toggle-item">
                                            <div class="toggle-info">
                                                <h4>Review Responses</h4>
                                                <p>Notifications when you receive responses to your reviews</p>
                                            </div>
                                            <label class="toggle-switch">
                                                <input type="checkbox" name="email_review_responses" checked>
                                                <span class="toggle-slider"></span>
                                            </label>
                                        </div>
                                        <div class="toggle-item">
                                            <div class="toggle-info">
                                                <h4>Business Updates</h4>
                                                <p>Important updates about your business profile</p>
                                            </div>
                                            <label class="toggle-switch">
                                                <input type="checkbox" name="email_business_updates" checked>
                                                <span class="toggle-slider"></span>
                                            </label>
                                        </div>
                                        <div class="toggle-item">
                                            <div class="toggle-info">
                                                <h4>Promotional Emails</h4>
                                                <p>Special offers, tips, and platform news</p>
                                            </div>
                                            <label class="toggle-switch">
                                                <input type="checkbox" name="email_promotional">
                                                <span class="toggle-slider"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-section">
                                    <h3>Push Notifications</h3>
                                    <div class="toggle-group">
                                        <div class="toggle-item">
                                            <div class="toggle-info">
                                                <h4>Mobile Push</h4>
                                                <p>Receive push notifications on your mobile device</p>
                                            </div>
                                            <label class="toggle-switch">
                                                <input type="checkbox" name="push_mobile" checked>
                                                <span class="toggle-slider"></span>
                                            </label>
                                        </div>
                                        <div class="toggle-item">
                                            <div class="toggle-info">
                                                <h4>Desktop Alerts</h4>
                                                <p>Show notification alerts on your desktop</p>
                                            </div>
                                            <label class="toggle-switch">
                                                <input type="checkbox" name="push_desktop">
                                                <span class="toggle-slider"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-section">
                                    <h3>Notification Frequency</h3>
                                    <div class="radio-group">
                                        <label class="radio-option">
                                            <input type="radio" name="notification_frequency" value="instant" checked>
                                            <span class="radio-checkmark"></span>
                                            <span class="radio-label">Instant - Notify me immediately</span>
                                        </label>
                                        <label class="radio-option">
                                            <input type="radio" name="notification_frequency" value="daily">
                                            <span class="radio-checkmark"></span>
                                            <span class="radio-label">Daily Digest - One email per day</span>
                                        </label>
                                        <label class="radio-option">
                                            <input type="radio" name="notification_frequency" value="weekly">
                                            <span class="radio-checkmark"></span>
                                            <span class="radio-label">Weekly Summary - One email per week</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="save-button button-primary">
                                        Save Preferences
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Security Tab -->
                        <div class="settings-tab" id="securityTab">
                            <div class="tab-header">
                                <h2>Security Settings</h2>
                                <p>Manage your account security and access controls</p>
                            </div>

                            <div class="security-sections">
                                <!-- Password Change -->
                                <div class="security-section">
                                    <h3>Change Password</h3>
                                    <form class="security-form" id="passwordForm">
                                        <div class="form-grid">
                                            <div class="form-group">
                                                <label for="currentPassword" class="form-label">Current Password</label>
                                                <input type="password" id="currentPassword" name="current_password" class="form-input">
                                            </div>
                                            <div class="form-group">
                                                <label for="newPassword" class="form-label">New Password</label>
                                                <input type="password" id="newPassword" name="new_password" class="form-input">
                                                <div class="password-strength">
                                                    <div class="strength-bar"></div>
                                                    <span class="strength-text">Password strength</span>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="confirmPassword" class="form-label">Confirm New Password</label>
                                                <input type="password" id="confirmPassword" name="confirm_password" class="form-input">
                                            </div>
                                        </div>
                                        <div class="form-actions">
                                            <button type="submit" class="save-button button-primary">
                                                Update Password
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Two-Factor Authentication -->
                                <div class="security-section">
                                    <h3>Two-Factor Authentication</h3>
                                    <div class="security-item">
                                        <div class="security-info">
                                            <h4>2FA Protection</h4>
                                            <p>Add an extra layer of security to your account</p>
                                        </div>
                                        <div class="security-action">
                                            <label class="toggle-switch">
                                                <input type="checkbox" name="two_factor_auth">
                                                <span class="toggle-slider"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Session Management -->
                                <div class="security-section">
                                    <h3>Active Sessions</h3>
                                    <div class="sessions-list">
                                        <div class="session-item">
                                            <div class="session-info">
                                                <div class="session-device">
                                                    <i class="fas fa-desktop"></i>
                                                    <span>Chrome on Windows</span>
                                                </div>
                                                <div class="session-details">
                                                    <span>Last active: 2 hours ago</span>
                                                    <span>IP: 192.168.1.1</span>
                                                </div>
                                            </div>
                                            <button type="button" class="revoke-button button-text">
                                                Revoke
                                            </button>
                                        </div>
                                        <div class="session-item">
                                            <div class="session-info">
                                                <div class="session-device">
                                                    <i class="fas fa-mobile-alt"></i>
                                                    <span>Safari on iPhone</span>
                                                </div>
                                                <div class="session-details">
                                                    <span>Last active: 5 days ago</span>
                                                    <span>IP: 192.168.1.2</span>
                                                </div>
                                            </div>
                                            <button type="button" class="revoke-button button-text">
                                                Revoke
                                            </button>
                                        </div>
                                    </div>
                                    <button type="button" class="revoke-all-button button-secondary">
                                        Revoke All Other Sessions
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Billing Tab -->
                        <div class="settings-tab" id="billingTab">
                            <div class="tab-header">
                                <h2>Billing & Plans</h2>
                                <p>Manage your subscription and billing information</p>
                            </div>

                            <div class="billing-content">
                                <!-- Current Plan -->
                                <div class="plan-card">
                                    <div class="plan-header">
                                        <h3>Current Plan</h3>
                                        <span class="plan-badge">Professional</span>
                                    </div>
                                    <div class="plan-details">
                                        <div class="plan-price">
                                            <span class="price">$49</span>
                                            <span class="period">/month</span>
                                        </div>
                                        <ul class="plan-features">
                                            <li><i class="fas fa-check"></i> Up to 5 business locations</li>
                                            <li><i class="fas fa-check"></i> Advanced analytics</li>
                                            <li><i class="fas fa-check"></i> Priority support</li>
                                            <li><i class="fas fa-check"></i> Custom branding</li>
                                        </ul>
                                    </div>
                                    <div class="plan-actions">
                                        <button class="upgrade-button button-primary">
                                            Upgrade Plan
                                        </button>
                                        <button class="cancel-button button-text">
                                            Cancel Subscription
                                        </button>
                                    </div>
                                </div>

                                <!-- Billing History -->
                                <div class="billing-section">
                                    <h3>Billing History</h3>
                                    <div class="billing-table">
                                        <div class="table-header">
                                            <span>Date</span>
                                            <span>Description</span>
                                            <span>Amount</span>
                                            <span>Status</span>
                                        </div>
                                        <div class="table-row">
                                            <span>Jan 15, 2024</span>
                                            <span>Professional Plan</span>
                                            <span>$49.00</span>
                                            <span class="status-paid">Paid</span>
                                        </div>
                                        <div class="table-row">
                                            <span>Dec 15, 2023</span>
                                            <span>Professional Plan</span>
                                            <span>$49.00</span>
                                            <span class="status-paid">Paid</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Payment Method -->
                                <div class="billing-section">
                                    <h3>Payment Method</h3>
                                    <div class="payment-method">
                                        <div class="payment-card">
                                            <i class="fab fa-cc-visa"></i>
                                            <div class="card-info">
                                                <span>Visa ending in 4242</span>
                                                <span>Expires 12/2025</span>
                                            </div>
                                        </div>
                                        <button class="update-payment-button button-secondary">
                                            Update Payment Method
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Integrations Tab -->
                        <div class="settings-tab" id="integrationsTab">
                            <div class="tab-header">
                                <h2>Integrations</h2>
                                <p>Connect with other tools and services</p>
                            </div>

                            <div class="integrations-grid">
                                <div class="integration-card">
                                    <div class="integration-header">
                                        <div class="integration-icon">
                                            <i class="fab fa-google"></i>
                                        </div>
                                        <div class="integration-info">
                                            <h4>Google Analytics</h4>
                                            <p>Track website traffic and user behavior</p>
                                        </div>
                                        <label class="toggle-switch">
                                            <input type="checkbox" name="google_analytics">
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="integration-card">
                                    <div class="integration-header">
                                        <div class="integration-icon">
                                            <i class="fab fa-facebook"></i>
                                        </div>
                                        <div class="integration-info">
                                            <h4>Facebook Pixel</h4>
                                            <p>Track conversions and optimize ads</p>
                                        </div>
                                        <label class="toggle-switch">
                                            <input type="checkbox" name="facebook_pixel" checked>
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="integration-card">
                                    <div class="integration-header">
                                        <div class="integration-icon">
                                            <i class="fas fa-envelope"></i>
                                        </div>
                                        <div class="integration-info">
                                            <h4>Mailchimp</h4>
                                            <p>Email marketing and automation</p>
                                        </div>
                                        <label class="toggle-switch">
                                            <input type="checkbox" name="mailchimp">
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="integration-card">
                                    <div class="integration-header">
                                        <div class="integration-icon">
                                            <i class="fab fa-slack"></i>
                                        </div>
                                        <div class="integration-info">
                                            <h4>Slack</h4>
                                            <p>Team notifications and alerts</p>
                                        </div>
                                        <label class="toggle-switch">
                                            <input type="checkbox" name="slack">
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
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
