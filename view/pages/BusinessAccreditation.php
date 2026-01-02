<?php
/**
 * P.I.M.P - Business Accreditation
 * Accreditation application and status management
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

// Mock accreditation data
$accreditation = [
    'status' => 'pending', // pending, approved, rejected, not_applied
    'application_date' => '2024-01-10',
    'review_date' => '2024-01-25',
    'expiry_date' => '2025-01-25',
    'level' => 'premium',
    'score' => 85,
    'requirements' => [
        ['name' => 'Business Registration', 'status' => 'completed', 'required' => true],
        ['name' => 'Tax Compliance', 'status' => 'completed', 'required' => true],
        ['name' => 'Insurance Coverage', 'status' => 'completed', 'required' => true],
        ['name' => 'Customer Reviews', 'status' => 'completed', 'required' => true],
        ['name' => 'Financial Stability', 'status' => 'pending', 'required' => true],
        ['name' => 'Industry Certifications', 'status' => 'not_started', 'required' => false],
    ],
    'benefits' => [
        'Premium badge on profile',
        'Higher search ranking',
        'Trust indicator for customers',
        'Access to premium analytics',
        'Featured in directory listings'
    ]
];

$status_config = [
    'not_applied' => ['label' => 'Not Applied', 'color' => 'gray', 'icon' => 'fas fa-times-circle'],
    'pending' => ['label' => 'Under Review', 'color' => 'orange', 'icon' => 'fas fa-clock'],
    'approved' => ['label' => 'Accredited', 'color' => 'green', 'icon' => 'fas fa-check-circle'],
    'rejected' => ['label' => 'Application Rejected', 'color' => 'red', 'icon' => 'fas fa-times-circle']
];

ob_start();
?>

<?php
echo Components::call('Headers', 'documentHead', [[
    'title' => 'Business Accreditation - P.I.M.P Business Dashboard',
    'metaTags' => [
        'description' => 'Apply for and manage your P.I.M.P business accreditation status',
        'keywords' => 'business accreditation, trusted business, PIMP accreditation, business verification'
    ],
    'styles' => [
        'views/business-accreditation.css'
    ],
    'scripts' => [
        'js/business-accreditation.js'
    ]
]]);
?>

<body class="business-accreditation-page">
    <?php
    echo Components::call('Headers', 'businessHeader', [[
        'logo' => Config::imageUrl('logo.png'),
        'logoAlt' => 'P.I.M.P - Business Repository Platform',
        'mainNavItems' => $nav_items,
        'userActions' => [
            ['url' => '/business/dashboard', 'label' => 'Dashboard', 'separator' => false],
            ['url' => '/business/reviews', 'label' => 'Reviews', 'separator' => false],
            ['url' => '/business/settings', 'label' => 'Settings', 'separator' => false],
            ['url' => '/logout', 'label' => 'Logout', 'separator' => true],
        ],
        'showSearch' => false,
    ]]);
    ?>

    <main class="business-accreditation-main">
        <!-- Page Header -->
        <div class="accreditation-page-header">
            <div class="container">
                <div class="page-header-content">
                    <h1>Business Accreditation</h1>
                    <p>Get verified and build trust with customers</p>
                </div>
            </div>
        </div>

        <!-- Status Overview -->
        <section class="accreditation-status-section">
            <div class="container">
                <div class="status-card">
                    <div class="status-header">
                        <div class="status-badge status-<?= $accreditation['status'] ?>">
                            <i class="<?= $status_config[$accreditation['status']]['icon'] ?>"></i>
                            <span><?= $status_config[$accreditation['status']]['label'] ?></span>
                        </div>
                        <div class="status-actions">
                            <?php if ($accreditation['status'] === 'not_applied'): ?>
                                <button class="apply-button button-primary">
                                    <i class="fas fa-rocket"></i>
                                    Apply for Accreditation
                                </button>
                            <?php elseif ($accreditation['status'] === 'pending'): ?>
                                <button class="view-application-button button-secondary">
                                    <i class="fas fa-eye"></i>
                                    View Application
                                </button>
                            <?php elseif ($accreditation['status'] === 'approved'): ?>
                                <button class="renew-button button-primary">
                                    <i class="fas fa-sync"></i>
                                    Renew Accreditation
                                </button>
                            <?php elseif ($accreditation['status'] === 'rejected'): ?>
                                <button class="reapply-button button-primary">
                                    <i class="fas fa-redo"></i>
                                    Reapply
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="status-details">
                        <?php if ($accreditation['status'] !== 'not_applied'): ?>
                            <div class="detail-grid">
                                <div class="detail-item">
                                    <label>Application Date</label>
                                    <span><?= date('F j, Y', strtotime($accreditation['application_date'])) ?></span>
                                </div>
                                <?php if ($accreditation['review_date']): ?>
                                    <div class="detail-item">
                                        <label>Review Date</label>
                                        <span><?= date('F j, Y', strtotime($accreditation['review_date'])) ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($accreditation['expiry_date']): ?>
                                    <div class="detail-item">
                                        <label>Expiry Date</label>
                                        <span><?= date('F j, Y', strtotime($accreditation['expiry_date'])) ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($accreditation['score']): ?>
                                    <div class="detail-item">
                                        <label>Accreditation Score</label>
                                        <span class="score-value"><?= $accreditation['score'] ?>%</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($accreditation['status'] === 'pending'): ?>
                            <div class="status-message info">
                                <i class="fas fa-info-circle"></i>
                                <p>Your accreditation application is under review. Our team will contact you if additional information is needed.</p>
                            </div>
                        <?php elseif ($accreditation['status'] === 'rejected'): ?>
                            <div class="status-message error">
                                <i class="fas fa-exclamation-triangle"></i>
                                <p>Your application was rejected. Please review the requirements and submit a new application.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- Requirements Section -->
        <section class="requirements-section">
            <div class="container">
                <div class="section-header">
                    <h2>Accreditation Requirements</h2>
                    <p>Complete these requirements to qualify for accreditation</p>
                </div>

                <div class="requirements-list">
                    <?php foreach ($accreditation['requirements'] as $index => $requirement): ?>
                        <div class="requirement-item <?= $requirement['status'] ?>">
                            <div class="requirement-checkbox">
                                <i class="fas fa-<?= $requirement['status'] === 'completed' ? 'check' : ($requirement['status'] === 'pending' ? 'clock' : 'circle') ?>"></i>
                            </div>
                            <div class="requirement-content">
                                <h4><?= $requirement['name'] ?></h4>
                                <p class="requirement-status"><?= ucfirst(str_replace('_', ' ', $requirement['status'])) ?></p>
                                <?php if (!$requirement['required']): ?>
                                    <span class="optional-badge">Optional</span>
                                <?php endif; ?>
                            </div>
                            <div class="requirement-actions">
                                <?php if ($requirement['status'] === 'not_started'): ?>
                                    <button class="start-requirement button-primary" data-requirement="<?= $index ?>">
                                        Start
                                    </button>
                                <?php elseif ($requirement['status'] === 'pending'): ?>
                                    <button class="view-requirement button-secondary" data-requirement="<?= $index ?>">
                                        Continue
                                    </button>
                                <?php elseif ($requirement['status'] === 'completed'): ?>
                                    <button class="view-details button-secondary" data-requirement="<?= $index ?>">
                                        View Details
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="progress-section">
                    <div class="progress-header">
                        <h3>Application Progress</h3>
                        <span class="progress-percentage">65%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 65%"></div>
                    </div>
                    <p class="progress-text">Complete all required steps to submit your application</p>
                </div>
            </div>
        </section>

        <!-- Benefits Section -->
        <section class="benefits-section">
            <div class="container">
                <div class="section-header">
                    <h2>Accreditation Benefits</h2>
                    <p>Why get accredited with P.I.M.P?</p>
                </div>

                <div class="benefits-grid">
                    <?php foreach ($accreditation['benefits'] as $benefit): ?>
                        <div class="benefit-card">
                            <div class="benefit-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h4><?= $benefit ?></h4>
                            <p>Build customer trust and increase your visibility in search results.</p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Application Form (Initially Hidden) -->
        <section class="application-form-section" id="applicationForm" style="display: none;">
            <div class="container">
                <div class="application-card">
                    <div class="application-header">
                        <h2>Accreditation Application</h2>
                        <button class="close-form button-secondary">
                            <i class="fas fa-times"></i>
                            Cancel
                        </button>
                    </div>

                    <form id="accreditationForm" class="accreditation-form">
                        <div class="form-section">
                            <h3>Business Information</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="businessName" class="form-label">Legal Business Name</label>
                                    <input type="text" id="businessName" name="business_name" class="form-input" required>
                                </div>
                                <div class="form-group">
                                    <label for="registrationNumber" class="form-label">Business Registration Number</label>
                                    <input type="text" id="registrationNumber" name="registration_number" class="form-input" required>
                                </div>
                                <div class="form-group">
                                    <label for="taxId" class="form-label">Tax ID Number</label>
                                    <input type="text" id="taxId" name="tax_id" class="form-input" required>
                                </div>
                                <div class="form-group">
                                    <label for="yearsInBusiness" class="form-label">Years in Business</label>
                                    <select id="yearsInBusiness" name="years_in_business" class="form-select" required>
                                        <option value="">Select years</option>
                                        <option value="1">Less than 1 year</option>
                                        <option value="2">1-2 years</option>
                                        <option value="3">3-5 years</option>
                                        <option value="6">6-10 years</option>
                                        <option value="11">More than 10 years</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3>Financial Information</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="annualRevenue" class="form-label">Annual Revenue</label>
                                    <select id="annualRevenue" name="annual_revenue" class="form-select" required>
                                        <option value="">Select range</option>
                                        <option value="0-50000">$0 - $50,000</option>
                                        <option value="50001-100000">$50,001 - $100,000</option>
                                        <option value="100001-500000">$100,001 - $500,000</option>
                                        <option value="500001-1000000">$500,001 - $1,000,000</option>
                                        <option value="1000001+">$1,000,001+</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="insuranceCoverage" class="form-label">Insurance Coverage</label>
                                    <select id="insuranceCoverage" name="insurance_coverage" class="form-select" required>
                                        <option value="">Select coverage</option>
                                        <option value="none">No Insurance</option>
                                        <option value="basic">Basic Liability</option>
                                        <option value="comprehensive">Comprehensive</option>
                                        <option value="professional">Professional Liability</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3>Document Upload</h3>
                            <div class="upload-section">
                                <div class="upload-item">
                                    <label class="upload-label">
                                        <i class="fas fa-file-pdf"></i>
                                        <span>Business Registration Certificate</span>
                                        <input type="file" accept=".pdf,.jpg,.png" class="file-input" hidden>
                                    </label>
                                    <div class="upload-status">Not uploaded</div>
                                </div>
                                <div class="upload-item">
                                    <label class="upload-label">
                                        <i class="fas fa-file-pdf"></i>
                                        <span>Tax Compliance Certificate</span>
                                        <input type="file" accept=".pdf,.jpg,.png" class="file-input" hidden>
                                    </label>
                                    <div class="upload-status">Not uploaded</div>
                                </div>
                                <div class="upload-item">
                                    <label class="upload-label">
                                        <i class="fas fa-file-pdf"></i>
                                        <span>Insurance Certificate</span>
                                        <input type="file" accept=".pdf,.jpg,.png" class="file-input" hidden>
                                    </label>
                                    <div class="upload-status">Not uploaded</div>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3>Declaration</h3>
                            <div class="declaration">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="declaration" required>
                                    <span class="checkmark"></span>
                                    I declare that all information provided is true and accurate to the best of my knowledge.
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="terms" required>
                                    <span class="checkmark"></span>
                                    I agree to the <a href="/terms/accreditation" target="_blank">P.I.M.P Accreditation Terms</a>.
                                </label>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="submit-application button-primary">
                                <i class="fas fa-paper-plane"></i>
                                Submit Application
                            </button>
                            <button type="button" class="save-draft button-secondary">
                                <i class="fas fa-save"></i>
                                Save Draft
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <?php
    echo Components::call('Footers', 'businessFooter', [$footer_config]);
    echo Components::call('Footers', 'documentClose');
    ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const applyButton = document.querySelector('.apply-button');
        const closeFormButton = document.querySelector('.close-form');
        const applicationFormSection = document.getElementById('applicationForm');
        const accreditationForm = document.getElementById('accreditationForm');
        const fileInputs = document.querySelectorAll('.file-input');

        // Toggle application form
        if (applyButton) {
            applyButton.addEventListener('click', function() {
                applicationFormSection.style.display = 'block';
                applicationFormSection.scrollIntoView({ behavior: 'smooth' });
            });
        }

        if (closeFormButton) {
            closeFormButton.addEventListener('click', function() {
                applicationFormSection.style.display = 'none';
            });
        }

        // File upload handling
        fileInputs.forEach(input => {
            input.addEventListener('change', function() {
                const fileName = this.files[0]?.name || 'Not uploaded';
                const statusElement = this.closest('.upload-item').querySelector('.upload-status');
                statusElement.textContent = fileName;
                statusElement.style.color = 'var(--primary-500)';
            });
        });

        // Requirement buttons
        const requirementButtons = document.querySelectorAll('.start-requirement, .view-requirement, .view-details');
        requirementButtons.forEach(button => {
            button.addEventListener('click', function() {
                const requirementId = this.getAttribute('data-requirement');
                alert(`Opening requirement ${parseInt(requirementId) + 1} details...`);
                // In real implementation, this would open a modal or navigate to requirement page
            });
        });

        // Form submission
        accreditationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate form
            const formData = new FormData(this);
            const declaration = formData.get('declaration');
            const terms = formData.get('terms');
            
            if (!declaration || !terms) {
                alert('Please accept the declaration and terms to continue.');
                return;
            }

            // Show loading state
            const submitButton = this.querySelector('.submit-application');
            const originalText = submitButton.innerHTML;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
            submitButton.disabled = true;

            // Simulate API call
            setTimeout(() => {
                alert('Application submitted successfully! Your application will be reviewed within 3-5 business days.');
                submitButton.innerHTML = originalText;
                submitButton.disabled = false;
                applicationFormSection.style.display = 'none';
                
                // In real implementation, this would update the UI and send data to server
            }, 2000);
        });

        // Save draft functionality
        const saveDraftButton = document.querySelector('.save-draft');
        if (saveDraftButton) {
            saveDraftButton.addEventListener('click', function() {
                alert('Draft saved successfully!');
                // In real implementation, this would save form data to local storage or server
            });
        }

        // Status action buttons
        const statusActionButtons = document.querySelectorAll('.view-application-button, .renew-button, .reapply-button');
        statusActionButtons.forEach(button => {
            button.addEventListener('click', function() {
                const action = this.classList.contains('view-application-button') ? 'view' : 
                             this.classList.contains('renew-button') ? 'renew' : 'reapply';
                
                if (action === 'view') {
                    alert('Opening application details...');
                } else if (action === 'renew') {
                    alert('Starting renewal process...');
                } else {
                    applicationFormSection.style.display = 'block';
                    applicationFormSection.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    });
    </script>
</body>
</html>

<?php echo ob_get_clean(); ?>
