<?php
/**
 * P.I.M.P - Claim Business
 * Business verification and claiming process
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
    'title' => 'Claim Your Business - P.I.M.P',
    'metaTags' => [
        'description' => 'Verify ownership and claim your business profile on P.I.M.P to access management tools and build customer trust.',
        'keywords' => 'claim business, business verification, ownership verification, PIMP business'
    ],
    'styles' => [
        'views/claim-business.css'
    ],
    'scripts' => [
        'js/claim-business.js'
    ]
]]);
?>

<body>
    <?php
    echo Components::call('Headers', 'businessHeader', [[
        'logo' => Config::imageUrl('logo.png'),
        'logoAlt' => 'P.I.M.P - Business Repository Platform',
        'mainNavItems' => $nav_items,
        'userActions' => [
            ['url' => '/login', 'label' => 'Business Login'],
            ['url' => '/for-business', 'label' => 'For Business', 'separator' => true],
        ],
        'showSearch' => true,
    ]]);
    ?>

    <main class="claim-business-main">
        <div class="container">
            <!-- Progress Steps -->
            <div class="claim-progress">
                <div class="progress-steps">
                    <div class="step active" data-step="1">
                        <div class="step-number">1</div>
                        <div class="step-label">Find Business</div>
                    </div>
                    <div class="step" data-step="2">
                        <div class="step-number">2</div>
                        <div class="step-label">Verify Ownership</div>
                    </div>
                    <div class="step" data-step="3">
                        <div class="step-number">3</div>
                        <div class="step-label">Complete Profile</div>
                    </div>
                    <div class="step" data-step="4">
                        <div class="step-number">4</div>
                        <div class="step-label">Confirmation</div>
                    </div>
                </div>
            </div>

            <!-- Step 1: Find Business -->
            <section class="claim-step active" id="step1">
                <div class="step-header">
                    <h1>Find Your Business</h1>
                    <p>Search for your business to begin the claiming process</p>
                </div>

                <div class="step-content">
                    <form id="searchBusinessForm" class="search-form">
                        <div class="form-group">
                            <label for="businessSearch" class="form-label">Business Name</label>
                            <input type="text" 
                                   id="businessSearch" 
                                   name="businessName" 
                                   class="form-input" 
                                   placeholder="Enter your business name"
                                   required>
                            <div class="form-hint">Enter the exact business name as it appears in our directory</div>
                        </div>

                        <div class="form-group">
                            <label for="businessLocation" class="form-label">Location</label>
                            <input type="text" 
                                   id="businessLocation" 
                                   name="location" 
                                   class="form-input" 
                                   placeholder="City, State or ZIP code"
                                   required>
                        </div>

                        <button type="submit" class="button button-primary button-large">
                            Search for Business
                        </button>
                    </form>

                    <div class="search-results" id="searchResults" style="display: none;">
                        <h3>Search Results</h3>
                        <div class="results-list" id="resultsList">
                            <!-- Results will be populated by JavaScript -->
                        </div>
                        <div class="results-actions">
                            <button class="button button-outline" id="refineSearch">
                                Refine Search
                            </button>
                        </div>
                    </div>
                </div>

                <div class="step-actions">
                    <button class="button button-primary" id="nextStep1" disabled>
                        Next: Verify Ownership
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </section>

            <!-- Step 2: Verify Ownership -->
            <section class="claim-step" id="step2">
                <div class="step-header">
                    <h1>Verify Business Ownership</h1>
                    <p>Choose a verification method to prove you own this business</p>
                </div>

                <div class="step-content">
                    <div class="business-preview" id="businessPreview">
                        <!-- Business preview will be populated by JavaScript -->
                    </div>

                    <div class="verification-methods">
                        <h3>Choose Verification Method</h3>
                        <div class="methods-grid">
                            <div class="method-card" data-method="phone">
                                <div class="method-icon">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="method-content">
                                    <h4>Phone Verification</h4>
                                    <p>We'll call the business phone number on file</p>
                                    <div class="method-details">
                                        <ul>
                                            <li>Instant verification</li>
                                            <li>Call to listed business number</li>
                                            <li>Provide verification code</li>
                                        </ul>
                                    </div>
                                </div>
                                <button class="method-select" data-method="phone">
                                    Select This Method
                                </button>
                            </div>

                            <div class="method-card" data-method="email">
                                <div class="method-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="method-content">
                                    <h4>Email Verification</h4>
                                    <p>We'll email the business email address on file</p>
                                    <div class="method-details">
                                        <ul>
                                            <li>Email sent within minutes</li>
                                            <li>Click verification link</li>
                                            <li>Business domain required</li>
                                        </ul>
                                    </div>
                                </div>
                                <button class="method-select" data-method="email">
                                    Select This Method
                                </button>
                            </div>

                            <div class="method-card" data-method="document">
                                <div class="method-icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <div class="method-content">
                                    <h4>Document Upload</h4>
                                    <p>Upload business documents for manual verification</p>
                                    <div class="method-details">
                                        <ul>
                                            <li>1-2 business days processing</li>
                                            <li>Business license or utility bill</li>
                                            <li>Government-issued ID</li>
                                        </ul>
                                    </div>
                                </div>
                                <button class="method-select" data-method="document">
                                    Select This Method
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Phone Verification Form -->
                    <div class="verification-form" id="phoneVerification" style="display: none;">
                        <h4>Phone Verification</h4>
                        <div class="form-group">
                            <label class="form-label">We'll call: <span id="businessPhone"></span></label>
                            <p>When you receive the call, enter the verification code below:</p>
                            <input type="text" 
                                   id="phoneCode" 
                                   name="phoneCode" 
                                   class="form-input" 
                                   placeholder="Enter verification code"
                                   maxlength="6">
                        </div>
                        <div class="verification-actions">
                            <button class="button button-outline" id="resendPhoneCode">
                                Resend Code
                            </button>
                            <button class="button button-primary" id="verifyPhone">
                                Verify Code
                            </button>
                        </div>
                    </div>

                    <!-- Email Verification Form -->
                    <div class="verification-form" id="emailVerification" style="display: none;">
                        <h4>Email Verification</h4>
                        <div class="form-group">
                            <label class="form-label">We sent an email to: <span id="businessEmail"></span></label>
                            <p>Check your email and click the verification link or enter the code below:</p>
                            <input type="text" 
                                   id="emailCode" 
                                   name="emailCode" 
                                   class="form-input" 
                                   placeholder="Enter verification code"
                                   maxlength="6">
                        </div>
                        <div class="verification-actions">
                            <button class="button button-outline" id="resendEmailCode">
                                Resend Email
                            </button>
                            <button class="button button-primary" id="verifyEmail">
                                Verify Code
                            </button>
                        </div>
                    </div>

                    <!-- Document Upload Form -->
                    <div class="verification-form" id="documentVerification" style="display: none;">
                        <h4>Document Upload</h4>
                        <div class="form-group">
                            <label class="form-label">Required Documents</label>
                            <p>Upload clear photos or scans of the following documents:</p>
                            
                            <div class="document-requirements">
                                <div class="document-item">
                                    <h5>Business License or Registration</h5>
                                    <p>Document must show business name and address</p>
                                    <div class="file-upload">
                                        <input type="file" id="businessLicense" accept=".jpg,.jpeg,.png,.pdf">
                                        <label for="businessLicense" class="file-label">
                                            <i class="fas fa-upload"></i>
                                            <span>Upload Business License</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="document-item">
                                    <h5>Proof of Address</h5>
                                    <p>Utility bill, bank statement, or lease agreement</p>
                                    <div class="file-upload">
                                        <input type="file" id="proofOfAddress" accept=".jpg,.jpeg,.png,.pdf">
                                        <label for="proofOfAddress" class="file-label">
                                            <i class="fas fa-upload"></i>
                                            <span>Upload Proof of Address</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="document-item">
                                    <h5>Government-Issued ID</h5>
                                    <p>Driver's license, passport, or other photo ID</p>
                                    <div class="file-upload">
                                        <input type="file" id="governmentId" accept=".jpg,.jpeg,.png,.pdf">
                                        <label for="governmentId" class="file-label">
                                            <i class="fas fa-upload"></i>
                                            <span>Upload Government ID</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="verification-actions">
                            <button class="button button-primary" id="submitDocuments">
                                Submit for Review
                            </button>
                        </div>
                    </div>
                </div>

                <div class="step-actions">
                    <button class="button button-outline" id="prevStep2">
                        <i class="fas fa-arrow-left"></i>
                        Back
                    </button>
                    <button class="button button-primary" id="nextStep2" disabled>
                        Next: Complete Profile
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </section>

            <!-- Step 3: Complete Profile -->
            <section class="claim-step" id="step3">
                <div class="step-header">
                    <h1>Complete Your Business Profile</h1>
                    <p>Add important details to make your business stand out</p>
                </div>

                <div class="step-content">
                    <form id="businessProfileForm" class="profile-form">
                        <div class="form-section">
                            <h3>Basic Information</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="businessName" class="form-label">Business Name</label>
                                    <input type="text" id="businessName" name="businessName" class="form-input" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="businessCategory" class="form-label">Primary Category</label>
                                    <select id="businessCategory" name="category" class="form-input" required>
                                        <option value="">Select a category</option>
                                        <option value="restaurants">Restaurants & Dining</option>
                                        <option value="retail">Retail & Shopping</option>
                                        <option value="home-services">Home Services</option>
                                        <option value="healthcare">Healthcare</option>
                                        <option value="automotive">Automotive</option>
                                        <option value="professional">Professional Services</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="businessDescription" class="form-label">Business Description</label>
                                <textarea id="businessDescription" name="description" class="form-input" rows="4" 
                                          placeholder="Describe your business, services, and what makes you unique"></textarea>
                                <div class="form-hint">This description will appear on your business profile</div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3>Contact Information</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="businessPhone" class="form-label">Phone Number</label>
                                    <input type="tel" id="businessPhoneInput" name="phone" class="form-input">
                                </div>
                                <div class="form-group">
                                    <label for="businessEmail" class="form-label">Email Address</label>
                                    <input type="email" id="businessEmailInput" name="email" class="form-input">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="businessWebsite" class="form-label">Website</label>
                                <input type="url" id="businessWebsite" name="website" class="form-input" placeholder="https://">
                            </div>

                            <div class="form-group">
                                <label for="businessAddress" class="form-label">Address</label>
                                <textarea id="businessAddress" name="address" class="form-input" rows="3"></textarea>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3>Business Hours</h3>
                            <div class="business-hours" id="businessHours">
                                <!-- Hours will be populated by JavaScript -->
                            </div>
                        </div>

                        <div class="form-section">
                            <h3>Photos & Media</h3>
                            <div class="photo-upload">
                                <label class="form-label">Business Photos</label>
                                <p>Upload photos of your business, products, or services</p>
                                <div class="upload-area" id="photoUploadArea">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <p>Drag & drop photos here or click to browse</p>
                                    <span>JPEG, PNG up to 5MB each</span>
                                    <input type="file" id="businessPhotos" multiple accept=".jpg,.jpeg,.png">
                                </div>
                                <div class="upload-preview" id="photoPreview"></div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="step-actions">
                    <button class="button button-outline" id="prevStep3">
                        <i class="fas fa-arrow-left"></i>
                        Back
                    </button>
                    <button class="button button-primary" id="nextStep3">
                        Next: Confirmation
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </section>

            <!-- Step 4: Confirmation -->
            <section class="claim-step" id="step4">
                <div class="step-header">
                    <div class="success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h1>Claim Request Submitted!</h1>
                    <p>Your business claim is being processed</p>
                </div>

                <div class="step-content">
                    <div class="confirmation-details">
                        <div class="confirmation-item">
                            <strong>Business:</strong>
                            <span id="confirmedBusinessName"></span>
                        </div>
                        <div class="confirmation-item">
                            <strong>Claim ID:</strong>
                            <span id="claimId">CLM-<?= date('YmdHis') ?></span>
                        </div>
                        <div class="confirmation-item">
                            <strong>Submitted:</strong>
                            <span id="submissionDate"><?= date('F j, Y g:i A') ?></span>
                        </div>
                        <div class="confirmation-item">
                            <strong>Status:</strong>
                            <span class="status-pending">Under Review</span>
                        </div>
                    </div>

                    <div class="next-steps">
                        <h3>What Happens Next?</h3>
                        <div class="steps-timeline">
                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <h4>Verification Review</h4>
                                    <p>Our team will review your ownership verification (1-2 business days)</p>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <h4>Profile Activation</h4>
                                    <p>Once verified, your business profile will be activated for management</p>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <h4>Welcome Email</h4>
                                    <p>You'll receive login credentials and access to your business dashboard</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="confirmation-actions">
                        <a href="<?= Config::url('/for-business') ?>" class="button button-outline">
                            Learn About Business Features
                        </a>
                        <a href="<?= Config::url('/') ?>" class="button button-primary">
                            Return to Homepage
                        </a>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <?php
    echo Components::call('Footers', 'businessFooter', [$footer_config]);
    echo Components::call('Footers', 'documentClose');
    ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        initializeClaimProcess();
    });

    function initializeClaimProcess() {
        let currentStep = 1;
        let selectedBusiness = null;
        let selectedMethod = null;

        // Step navigation
        document.getElementById('nextStep1').addEventListener('click', () => navigateToStep(2));
        document.getElementById('nextStep2').addEventListener('click', () => navigateToStep(3));
        document.getElementById('nextStep3').addEventListener('click', () => navigateToStep(4));
        
        document.getElementById('prevStep2').addEventListener('click', () => navigateToStep(1));
        document.getElementById('prevStep3').addEventListener('click', () => navigateToStep(2));

        // Business search
        document.getElementById('searchBusinessForm').addEventListener('submit', function(e) {
            e.preventDefault();
            searchBusiness();
        });

        // Verification method selection
        document.querySelectorAll('.method-select').forEach(button => {
            button.addEventListener('click', function() {
                selectVerificationMethod(this.dataset.method);
            });
        });

        // File upload handling
        initializeFileUploads();

        function navigateToStep(step) {
            // Hide all steps
            document.querySelectorAll('.claim-step').forEach(s => s.classList.remove('active'));
            document.querySelectorAll('.progress-steps .step').forEach(s => s.classList.remove('active'));
            
            // Show current step
            document.getElementById(`step${step}`).classList.add('active');
            document.querySelector(`.progress-steps .step[data-step="${step}"]`).classList.add('active');
            
            currentStep = step;
        }

        async function searchBusiness() {
            const businessName = document.getElementById('businessSearch').value;
            const location = document.getElementById('businessLocation').value;

            try {
                // Show loading state
                const searchBtn = document.querySelector('#searchBusinessForm button');
                searchBtn.disabled = true;
                searchBtn.textContent = 'Searching...';

                // Simulate API call
                setTimeout(() => {
                    const mockResults = [
                        {
                            id: 1,
                            name: businessName,
                            address: '123 Main St, ' + location,
                            phone: '(555) 123-4567',
                            category: 'Professional Services',
                            verified: true
                        }
                    ];

                    displaySearchResults(mockResults);
                    
                    // Reset button
                    searchBtn.disabled = false;
                    searchBtn.textContent = 'Search for Business';
                }, 1500);

            } catch (error) {
                console.error('Search error:', error);
                alert('Error searching for business. Please try again.');
            }
        }

        function displaySearchResults(results) {
            const resultsList = document.getElementById('resultsList');
            const searchResults = document.getElementById('searchResults');
            
            if (results.length === 0) {
                resultsList.innerHTML = '<div class="no-results">No businesses found matching your search.</div>';
            } else {
                resultsList.innerHTML = results.map(business => `
                    <div class="business-result" data-business-id="${business.id}">
                        <div class="business-info">
                            <h4>${business.name}</h4>
                            <p class="business-address">${business.address}</p>
                            <p class="business-category">${business.category}</p>
                        </div>
                        <button class="select-business" data-business='${JSON.stringify(business).replace(/'/g, "&#39;")}'>
                            Select Business
                        </button>
                    </div>
                `).join('');

                // Add event listeners to select buttons
                document.querySelectorAll('.select-business').forEach(button => {
                    button.addEventListener('click', function() {
                        selectedBusiness = JSON.parse(this.dataset.business.replace(/&#39;/g, "'"));
                        selectBusiness(selectedBusiness);
                    });
                });
            }

            searchResults.style.display = 'block';
        }

        function selectBusiness(business) {
            document.getElementById('nextStep1').disabled = false;
            document.getElementById('businessPreview').innerHTML = `
                <div class="selected-business">
                    <h4>Selected Business</h4>
                    <div class="business-details">
                        <strong>${business.name}</strong>
                        <p>${business.address}</p>
                        <p>Phone: ${business.phone}</p>
                    </div>
                </div>
            `;
        }

        function selectVerificationMethod(method) {
            selectedMethod = method;
            
            // Hide all verification forms
            document.querySelectorAll('.verification-form').forEach(form => {
                form.style.display = 'none';
            });
            
            // Show selected method form
            document.getElementById(`${method}Verification`).style.display = 'block';
            
            // Update business info in forms
            if (selectedBusiness) {
                document.getElementById('businessPhone').textContent = selectedBusiness.phone;
                document.getElementById('businessEmail').textContent = 'business@example.com'; // Mock email
            }
            
            document.getElementById('nextStep2').disabled = false;
        }

        function initializeFileUploads() {
            const photoUpload = document.getElementById('businessPhotos');
            const photoPreview = document.getElementById('photoPreview');
            
            photoUpload.addEventListener('change', function(e) {
                const files = Array.from(e.target.files);
                files.forEach(file => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const previewItem = document.createElement('div');
                            previewItem.className = 'preview-item';
                            previewItem.innerHTML = `
                                <img src="${e.target.result}" alt="Preview">
                                <button type="button" class="remove-photo">&times;</button>
                            `;
                            photoPreview.appendChild(previewItem);
                            
                            // Add remove functionality
                            previewItem.querySelector('.remove-photo').addEventListener('click', function() {
                                previewItem.remove();
                            });
                        };
                        reader.readAsDataURL(file);
                    }
                });
            });

            // Drag and drop functionality
            const uploadArea = document.getElementById('photoUploadArea');
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                uploadArea.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(eventName => {
                uploadArea.addEventListener(eventName, highlight, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                uploadArea.addEventListener(eventName, unhighlight, false);
            });

            function highlight() {
                uploadArea.classList.add('highlight');
            }

            function unhighlight() {
                uploadArea.classList.remove('highlight');
            }

            uploadArea.addEventListener('drop', handleDrop, false);

            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                photoUpload.files = files;
                photoUpload.dispatchEvent(new Event('change'));
            }
        }

        // Initialize business hours
        initializeBusinessHours();
    }

    function initializeBusinessHours() {
        const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        const hoursContainer = document.getElementById('businessHours');
        
        hoursContainer.innerHTML = days.map(day => `
            <div class="hours-row">
                <div class="day-label">${day}</div>
                <div class="hours-inputs">
                    <select name="hours[${day}][open]" class="time-select">
                        <option value="">Closed</option>
                        ${generateTimeOptions()}
                    </select>
                    <span class="hours-separator">to</span>
                    <select name="hours[${day}][close]" class="time-select">
                        <option value="">Closed</option>
                        ${generateTimeOptions()}
                    </select>
                </div>
            </div>
        `).join('');
    }

    function generateTimeOptions() {
        let options = '';
        for (let hour = 0; hour < 24; hour++) {
            for (let minute of ['00', '30']) {
                const time24 = `${hour.toString().padStart(2, '0')}:${minute}`;
                const time12 = formatTime12(hour, minute);
                options += `<option value="${time24}">${time12}</option>`;
            }
        }
        return options;
    }

    function formatTime12(hour, minute) {
        const period = hour >= 12 ? 'PM' : 'AM';
        const hour12 = hour % 12 || 12;
        return `${hour12}:${minute} ${period}`;
    }
    </script>
</body>
</html>

<?php echo ob_get_clean(); ?>