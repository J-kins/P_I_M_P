<?php
/**
 * P.I.M.P - Business Registration
 * Business registration and onboarding system
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
    'title' => 'Register Your Business - P.I.M.P',
    'metaTags' => [
        'description' => 'Register your business on P.I.M.P to manage your online presence, respond to reviews, and connect with customers',
        'keywords' => 'business registration, register business, PIMP business, business onboarding'
    ],
    'styles' => [
        'views/business-register.css'
    ],
    'scripts' => [
        'js/business-register.js'
    ]
]]);
?>

<body class="business-register-page">
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

    <main class="business-register-main">
        <!-- Progress Indicator -->
        <div class="registration-progress">
            <div class="container">
                <div class="progress-steps">
                    <div class="step active" data-step="1">
                        <div class="step-number">1</div>
                        <div class="step-label">Business Info</div>
                    </div>
                    <div class="step" data-step="2">
                        <div class="step-number">2</div>
                        <div class="step-label">Contact Details</div>
                    </div>
                    <div class="step" data-step="3">
                        <div class="step-number">3</div>
                        <div class="step-label">Services & Categories</div>
                    </div>
                    <div class="step" data-step="4">
                        <div class="step-number">4</div>
                        <div class="step-label">Account Setup</div>
                    </div>
                    <div class="step" data-step="5">
                        <div class="step-number">5</div>
                        <div class="step-label">Review & Submit</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Registration Form -->
        <section class="registration-section">
            <div class="container">
                <div class="registration-card">
                    <div class="registration-header">
                        <h1>Register Your Business</h1>
                        <p>Join thousands of businesses building trust with customers</p>
                    </div>

                    <form id="businessRegistrationForm" class="registration-form" novalidate>
                        <!-- Step 1: Business Information -->
                        <div class="form-step active" data-step="1">
                            <div class="step-header">
                                <h2>Business Information</h2>
                                <p>Tell us about your business</p>
                            </div>

                            <div class="form-section">
                                <h3>Basic Details</h3>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="businessName" class="form-label">Legal Business Name *</label>
                                        <input type="text" id="businessName" name="business_name" class="form-input" required>
                                        <div class="error-message" id="businessNameError"></div>
                                    </div>
                                    <div class="form-group">
                                        <label for="tradingName" class="form-label">Trading Name (if different)</label>
                                        <input type="text" id="tradingName" name="trading_name" class="form-input">
                                    </div>
                                    <div class="form-group">
                                        <label for="businessType" class="form-label">Business Type *</label>
                                        <select id="businessType" name="business_type" class="form-select" required>
                                            <option value="">Select business type</option>
                                            <option value="sole_proprietorship">Sole Proprietorship</option>
                                            <option value="partnership">Partnership</option>
                                            <option value="corporation">Corporation</option>
                                            <option value="llc">Limited Liability Company (LLC)</option>
                                            <option value="nonprofit">Non-Profit Organization</option>
                                        </select>
                                        <div class="error-message" id="businessTypeError"></div>
                                    </div>
                                    <div class="form-group">
                                        <label for="registrationNumber" class="form-label">Business Registration Number</label>
                                        <input type="text" id="registrationNumber" name="registration_number" class="form-input">
                                        <div class="help-text">Required for accreditation</div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <h3>Business Description</h3>
                                <div class="form-group">
                                    <label for="businessDescription" class="form-label">Description *</label>
                                    <textarea id="businessDescription" name="business_description" class="form-textarea" rows="4" required placeholder="Describe your business, services, and what makes you unique"></textarea>
                                    <div class="char-count">
                                        <span class="current-chars">0</span>/500 characters
                                    </div>
                                    <div class="error-message" id="businessDescriptionError"></div>
                                </div>
                            </div>

                            <div class="form-section">
                                <h3>Year Established</h3>
                                <div class="form-group">
                                    <label for="yearEstablished" class="form-label">Year Business Started *</label>
                                    <input type="number" id="yearEstablished" name="year_established" class="form-input" min="1900" max="2030" required>
                                    <div class="error-message" id="yearEstablishedError"></div>
                                </div>
                            </div>

                            <div class="step-actions">
                                <button type="button" class="next-step button-primary" data-next="2">
                                    Continue to Contact Details
                                    <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Step 2: Contact Details -->
                        <div class="form-step" data-step="2">
                            <div class="step-header">
                                <h2>Contact Details</h2>
                                <p>How can customers reach you?</p>
                            </div>

                            <div class="form-section">
                                <h3>Primary Contact</h3>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="contactName" class="form-label">Contact Person Name *</label>
                                        <input type="text" id="contactName" name="contact_name" class="form-input" required>
                                        <div class="error-message" id="contactNameError"></div>
                                    </div>
                                    <div class="form-group">
                                        <label for="contactEmail" class="form-label">Contact Email *</label>
                                        <input type="email" id="contactEmail" name="contact_email" class="form-input" required>
                                        <div class="error-message" id="contactEmailError"></div>
                                    </div>
                                    <div class="form-group">
                                        <label for="contactPhone" class="form-label">Contact Phone *</label>
                                        <input type="tel" id="contactPhone" name="contact_phone" class="form-input" required>
                                        <div class="error-message" id="contactPhoneError"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <h3>Business Location</h3>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="addressLine1" class="form-label">Address Line 1 *</label>
                                        <input type="text" id="addressLine1" name="address_line1" class="form-input" required>
                                        <div class="error-message" id="addressLine1Error"></div>
                                    </div>
                                    <div class="form-group">
                                        <label for="addressLine2" class="form-label">Address Line 2</label>
                                        <input type="text" id="addressLine2" name="address_line2" class="form-input" placeholder="Suite, unit, building, etc.">
                                    </div>
                                    <div class="form-group">
                                        <label for="city" class="form-label">City *</label>
                                        <input type="text" id="city" name="city" class="form-input" required>
                                        <div class="error-message" id="cityError"></div>
                                    </div>
                                    <div class="form-group">
                                        <label for="state" class="form-label">State/Province *</label>
                                        <input type="text" id="state" name="state" class="form-input" required>
                                        <div class="error-message" id="stateError"></div>
                                    </div>
                                    <div class="form-group">
                                        <label for="zipCode" class="form-label">ZIP/Postal Code *</label>
                                        <input type="text" id="zipCode" name="zip_code" class="form-input" required>
                                        <div class="error-message" id="zipCodeError"></div>
                                    </div>
                                    <div class="form-group">
                                        <label for="country" class="form-label">Country *</label>
                                        <select id="country" name="country" class="form-select" required>
                                            <option value="">Select country</option>
                                            <option value="us">United States</option>
                                            <option value="ca">Canada</option>
                                            <option value="uk">United Kingdom</option>
                                            <option value="au">Australia</option>
                                            <option value="other">Other</option>
                                        </select>
                                        <div class="error-message" id="countryError"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <h3>Online Presence</h3>
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
                                        <label for="instagram" class="form-label">Instagram</label>
                                        <input type="url" id="instagram" name="instagram" class="form-input" placeholder="https://instagram.com/yourbusiness">
                                    </div>
                                    <div class="form-group">
                                        <label for="linkedin" class="form-label">LinkedIn</label>
                                        <input type="url" id="linkedin" name="linkedin" class="form-input" placeholder="https://linkedin.com/company/yourbusiness">
                                    </div>
                                </div>
                            </div>

                            <div class="step-actions">
                                <button type="button" class="prev-step button-secondary" data-prev="1">
                                    <i class="fas fa-arrow-left"></i>
                                    Back to Business Info
                                </button>
                                <button type="button" class="next-step button-primary" data-next="3">
                                    Continue to Services
                                    <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Step 3: Services & Categories -->
                        <div class="form-step" data-step="3">
                            <div class="step-header">
                                <h2>Services & Categories</h2>
                                <p>Help customers find your business</p>
                            </div>

                            <div class="form-section">
                                <h3>Business Categories</h3>
                                <div class="form-group">
                                    <label class="form-label">Select up to 3 primary categories *</label>
                                    <div class="categories-grid" id="categoriesGrid">
                                        <!-- Categories will be populated by JavaScript -->
                                    </div>
                                    <div class="error-message" id="categoriesError"></div>
                                </div>
                            </div>

                            <div class="form-section">
                                <h3>Services Offered</h3>
                                <div class="form-group">
                                    <label for="servicesList" class="form-label">List your main services or products *</label>
                                    <div class="services-input-container">
                                        <input type="text" id="serviceInput" class="form-input" placeholder="Add a service (e.g., Web Design, Consulting)">
                                        <button type="button" class="add-service button-secondary">
                                            <i class="fas fa-plus"></i>
                                            Add
                                        </button>
                                    </div>
                                    <div class="services-list" id="servicesList">
                                        <!-- Services will be added dynamically -->
                                    </div>
                                    <div class="error-message" id="servicesError"></div>
                                </div>
                            </div>

                            <div class="form-section">
                                <h3>Business Hours</h3>
                                <div class="business-hours">
                                    <div class="hours-header">
                                        <span>Day</span>
                                        <span>Opening Time</span>
                                        <span>Closing Time</span>
                                        <span>Closed</span>
                                    </div>
                                    <div class="hours-list" id="businessHours">
                                        <!-- Business hours will be populated by JavaScript -->
                                    </div>
                                </div>
                            </div>

                            <div class="step-actions">
                                <button type="button" class="prev-step button-secondary" data-prev="2">
                                    <i class="fas fa-arrow-left"></i>
                                    Back to Contact Details
                                </button>
                                <button type="button" class="next-step button-primary" data-next="4">
                                    Continue to Account Setup
                                    <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Step 4: Account Setup -->
                        <div class="form-step" data-step="4">
                            <div class="step-header">
                                <h2>Account Setup</h2>
                                <p>Create your business account</p>
                            </div>

                            <div class="form-section">
                                <h3>Login Credentials</h3>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="accountEmail" class="form-label">Account Email *</label>
                                        <input type="email" id="accountEmail" name="account_email" class="form-input" required>
                                        <div class="error-message" id="accountEmailError"></div>
                                    </div>
                                    <div class="form-group">
                                        <label for="password" class="form-label">Password *</label>
                                        <div class="password-input-group">
                                            <input type="password" id="password" name="password" class="form-input" required>
                                            <button type="button" class="password-toggle">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                        <div class="password-strength">
                                            <div class="strength-bar"></div>
                                            <span class="strength-text">Password strength</span>
                                        </div>
                                        <div class="error-message" id="passwordError"></div>
                                    </div>
                                    <div class="form-group">
                                        <label for="confirmPassword" class="form-label">Confirm Password *</label>
                                        <div class="password-input-group">
                                            <input type="password" id="confirmPassword" name="confirm_password" class="form-input" required>
                                            <button type="button" class="password-toggle">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                        <div class="error-message" id="confirmPasswordError"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <h3>Communication Preferences</h3>
                                <div class="preferences-group">
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="email_notifications" checked>
                                        <span class="checkmark"></span>
                                        Send me email notifications about new reviews and messages
                                    </label>
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="marketing_emails">
                                        <span class="checkmark"></span>
                                        Send me tips, best practices, and promotional offers
                                    </label>
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="sms_notifications">
                                        <span class="checkmark"></span>
                                        Send me SMS notifications for urgent matters
                                    </label>
                                </div>
                            </div>

                            <div class="form-section">
                                <h3>Terms & Conditions</h3>
                                <div class="terms-group">
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="terms_agreement" required>
                                        <span class="checkmark"></span>
                                        I agree to the <a href="/terms" target="_blank">Terms of Service</a> and <a href="/privacy" target="_blank">Privacy Policy</a> *
                                    </label>
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="business_verification" required>
                                        <span class="checkmark"></span>
                                        I verify that I am an authorized representative of this business *
                                    </label>
                                    <div class="error-message" id="termsError"></div>
                                </div>
                            </div>

                            <div class="step-actions">
                                <button type="button" class="prev-step button-secondary" data-prev="3">
                                    <i class="fas fa-arrow-left"></i>
                                    Back to Services
                                </button>
                                <button type="button" class="next-step button-primary" data-next="5">
                                    Review & Submit
                                    <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Step 5: Review & Submit -->
                        <div class="form-step" data-step="5">
                            <div class="step-header">
                                <h2>Review & Submit</h2>
                                <p>Please review your information before submitting</p>
                            </div>

                            <div class="review-section">
                                <div class="review-grid">
                                    <div class="review-category">
                                        <h3>Business Information</h3>
                                        <div class="review-item">
                                            <strong>Business Name:</strong>
                                            <span id="reviewBusinessName">-</span>
                                        </div>
                                        <div class="review-item">
                                            <strong>Business Type:</strong>
                                            <span id="reviewBusinessType">-</span>
                                        </div>
                                        <div class="review-item">
                                            <strong>Description:</strong>
                                            <span id="reviewDescription">-</span>
                                        </div>
                                        <div class="review-item">
                                            <strong>Year Established:</strong>
                                            <span id="reviewYearEstablished">-</span>
                                        </div>
                                    </div>

                                    <div class="review-category">
                                        <h3>Contact Details</h3>
                                        <div class="review-item">
                                            <strong>Contact Person:</strong>
                                            <span id="reviewContactName">-</span>
                                        </div>
                                        <div class="review-item">
                                            <strong>Email:</strong>
                                            <span id="reviewContactEmail">-</span>
                                        </div>
                                        <div class="review-item">
                                            <strong>Phone:</strong>
                                            <span id="reviewContactPhone">-</span>
                                        </div>
                                        <div class="review-item">
                                            <strong>Address:</strong>
                                            <span id="reviewAddress">-</span>
                                        </div>
                                    </div>

                                    <div class="review-category">
                                        <h3>Services & Categories</h3>
                                        <div class="review-item">
                                            <strong>Categories:</strong>
                                            <span id="reviewCategories">-</span>
                                        </div>
                                        <div class="review-item">
                                            <strong>Services:</strong>
                                            <span id="reviewServices">-</span>
                                        </div>
                                        <div class="review-item">
                                            <strong>Business Hours:</strong>
                                            <span id="reviewHours">-</span>
                                        </div>
                                    </div>

                                    <div class="review-category">
                                        <h3>Account Details</h3>
                                        <div class="review-item">
                                            <strong>Account Email:</strong>
                                            <span id="reviewAccountEmail">-</span>
                                        </div>
                                        <div class="review-item">
                                            <strong>Notifications:</strong>
                                            <span id="reviewNotifications">-</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="step-actions">
                                <button type="button" class="prev-step button-secondary" data-prev="4">
                                    <i class="fas fa-arrow-left"></i>
                                    Back to Account Setup
                                </button>
                                <button type="submit" class="submit-registration button-primary">
                                    <i class="fas fa-check"></i>
                                    Submit Registration
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Registration Benefits -->
                <div class="registration-benefits">
                    <h2>Why Register Your Business?</h2>
                    <div class="benefits-grid">
                        <div class="benefit-item">
                            <div class="benefit-icon">
                                <i class="fas fa-star"></i>
                            </div>
                            <h3>Build Trust</h3>
                            <p>Showcase verified reviews and build credibility with potential customers</p>
                        </div>
                        <div class="benefit-item">
                            <div class="benefit-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <h3>Grow Your Business</h3>
                            <p>Increase visibility and reach new customers through our platform</p>
                        </div>
                        <div class="benefit-item">
                            <div class="benefit-icon">
                                <i class="fas fa-comments"></i>
                            </div>
                            <h3>Engage Customers</h3>
                            <p>Respond to reviews and manage your online reputation effectively</p>
                        </div>
                        <div class="benefit-item">
                            <div class="benefit-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h3>Get Accredited</h3>
                            <p>Apply for P.I.M.P accreditation to stand out from competitors</p>
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
