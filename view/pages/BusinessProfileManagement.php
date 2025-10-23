<?php
/**
 * P.I.M.P - Business Profile Management
 * Comprehensive business profile editing and management
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
    'title' => 'Business Profile Management - P.I.M.P',
    'metaTags' => [
        'description' => 'Manage your business profile, update information, add photos, and optimize your P.I.M.P listing.',
        'keywords' => 'business profile, profile management, business information, PIMP business'
    ],
    'styles' => [
        'views/business-profile-management.css'
    ],
    'scripts' => [
        'js/business-profile-management.js'
    ]
]]);
?>

<body class="business-management">
    <?php
    echo Components::call('Headers', 'businessHeader', [[
        'logo' => Config::imageUrl('logo.png'),
        'logoAlt' => 'P.I.M.P - Business Repository Platform',
        'mainNavItems' => $nav_items,
        'userActions' => [
            ['url' => '/business/dashboard', 'label' => 'Dashboard'],
            ['url' => '/logout', 'label' => 'Logout', 'separator' => true],
        ],
        'showSearch' => true,
    ]]);
    ?>

    <main class="management-main">
        <div class="management-container">
            <!-- Sidebar -->
            <aside class="management-sidebar">
                <nav class="management-nav">
                    <ul class="nav-menu">
                        <li class="nav-item">
                            <a href="<?= Config::url('/business/dashboard') ?>" class="nav-link">
                                <i class="fas fa-tachometer-alt"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item active">
                            <a href="<?= Config::url('/business/profile') ?>" class="nav-link">
                                <i class="fas fa-building"></i>
                                Business Profile
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= Config::url('/business/reviews') ?>" class="nav-link">
                                <i class="fas fa-star"></i>
                                Reviews
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= Config::url('/business/analytics') ?>" class="nav-link">
                                <i class="fas fa-chart-bar"></i>
                                Analytics
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= Config::url('/business/accreditation') ?>" class="nav-link">
                                <i class="fas fa-shield-alt"></i>
                                Accreditation
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= Config::url('/business/settings') ?>" class="nav-link">
                                <i class="fas fa-cog"></i>
                                Settings
                            </a>
                        </li>
                    </ul>
                </nav>
            </aside>

            <!-- Main Content -->
            <div class="management-content">
                <!-- Header -->
                <div class="content-header">
                    <h1>Business Profile Management</h1>
                    <p>Manage your business information, photos, and profile settings</p>
                </div>

                <!-- Profile Sections -->
                <div class="profile-sections">
                    <!-- Basic Information -->
                    <section class="profile-section" id="basic-info">
                        <div class="section-header">
                            <h2>Basic Information</h2>
                            <button class="edit-section" data-section="basic-info">Edit</button>
                        </div>
                        <div class="section-content">
                            <form id="basicInfoForm" class="profile-form">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="businessName" class="form-label">Business Name *</label>
                                        <input type="text" id="businessName" name="name" class="form-input" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="businessCategory" class="form-label">Primary Category *</label>
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
                                    <label for="businessDescription" class="form-label">Business Description *</label>
                                    <textarea id="businessDescription" name="description" class="form-input" rows="4" 
                                              placeholder="Describe your business, services, and what makes you unique" required></textarea>
                                    <div class="form-hint">This description appears on your public profile</div>
                                </div>

                                <div class="form-group">
                                    <label for="businessTags" class="form-label">Business Tags</label>
                                    <input type="text" id="businessTags" name="tags" class="form-input" 
                                           placeholder="Add tags separated by commas">
                                    <div class="form-hint">Tags help customers find your business (e.g., "family-owned", "eco-friendly")</div>
                                </div>

                                <div class="form-actions">
                                    <button type="button" class="button button-outline cancel-edit">Cancel</button>
                                    <button type="submit" class="button button-primary">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </section>

                    <!-- Contact Information -->
                    <section class="profile-section" id="contact-info">
                        <div class="section-header">
                            <h2>Contact Information</h2>
                            <button class="edit-section" data-section="contact-info">Edit</button>
                        </div>
                        <div class="section-content">
                            <form id="contactInfoForm" class="profile-form">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="businessPhone" class="form-label">Phone Number *</label>
                                        <input type="tel" id="businessPhone" name="phone" class="form-input" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="businessEmail" class="form-label">Email Address *</label>
                                        <input type="email" id="businessEmail" name="email" class="form-input" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="businessWebsite" class="form-label">Website</label>
                                    <input type="url" id="businessWebsite" name="website" class="form-input" placeholder="https://">
                                </div>

                                <div class="form-group">
                                    <label for="businessAddress" class="form-label">Address *</label>
                                    <div class="address-fields">
                                        <input type="text" id="streetAddress" name="street" class="form-input" placeholder="Street Address" required>
                                        <div class="form-row">
                                            <input type="text" id="city" name="city" class="form-input" placeholder="City" required>
                                            <input type="text" id="state" name="state" class="form-input" placeholder="State" required>
                                            <input type="text" id="zipCode" name="zipcode" class="form-input" placeholder="ZIP Code" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <button type="button" class="button button-outline cancel-edit">Cancel</button>
                                    <button type="submit" class="button button-primary">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </section>

                    <!-- Business Hours -->
                    <section class="profile-section" id="business-hours">
                        <div class="section-header">
                            <h2>Business Hours</h2>
                            <button class="edit-section" data-section="business-hours">Edit</button>
                        </div>
                        <div class="section-content">
                            <form id="hoursForm" class="profile-form">
                                <div class="hours-container" id="businessHours">
                                    <!-- Hours will be populated by JavaScript -->
                                </div>
                                <div class="form-actions">
                                    <button type="button" class="button button-outline cancel-edit">Cancel</button>
                                    <button type="submit" class="button button-primary">Save Hours</button>
                                </div>
                            </form>
                        </div>
                    </section>

                    <!-- Services & Products -->
                    <section class="profile-section" id="services-products">
                        <div class="section-header">
                            <h2>Services & Products</h2>
                            <button class="edit-section" data-section="services-products">Edit</button>
                        </div>
                        <div class="section-content">
                            <form id="servicesForm" class="profile-form">
                                <div class="form-group">
                                    <label class="form-label">Services Offered</label>
                                    <div class="services-list" id="servicesList">
                                        <!-- Services will be populated by JavaScript -->
                                    </div>
                                    <button type="button" class="button button-outline button-small" id="addService">
                                        <i class="fas fa-plus"></i>
                                        Add Service
                                    </button>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Products Offered</label>
                                    <div class="products-list" id="productsList">
                                        <!-- Products will be populated by JavaScript -->
                                    </div>
                                    <button type="button" class="button button-outline button-small" id="addProduct">
                                        <i class="fas fa-plus"></i>
                                        Add Product
                                    </button>
                                </div>

                                <div class="form-actions">
                                    <button type="button" class="button button-outline cancel-edit">Cancel</button>
                                    <button type="submit" class="button button-primary">Save Services</button>
                                </div>
                            </form>
                        </div>
                    </section>

                    <!-- Photos & Media -->
                    <section class="profile-section" id="photos-media">
                        <div class="section-header">
                            <h2>Photos & Media</h2>
                            <button class="edit-section" data-section="photos-media">Edit</button>
                        </div>
                        <div class="section-content">
                            <div class="media-management">
                                <div class="media-upload">
                                    <h3>Upload Photos</h3>
                                    <div class="upload-area" id="photoUploadArea">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <p>Drag & drop photos here or click to browse</p>
                                        <span>JPEG, PNG up to 5MB each</span>
                                        <input type="file" id="businessPhotos" multiple accept=".jpg,.jpeg,.png">
                                    </div>
                                </div>

                                <div class="media-gallery">
                                    <h3>Photo Gallery</h3>
                                    <div class="gallery-grid" id="photoGallery">
                                        <!-- Photos will be populated by JavaScript -->
                                        <div class="empty-gallery">
                                            <i class="fas fa-images"></i>
                                            <p>No photos uploaded yet</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="profile-photo">
                                    <h3>Profile Photo</h3>
                                    <div class="profile-photo-upload">
                                        <div class="current-photo" id="currentProfilePhoto">
                                            <img src="<?= Config::imageUrl('businesses/default.jpg') ?>" alt="Current Profile Photo">
                                        </div>
                                        <div class="upload-controls">
                                            <input type="file" id="profilePhotoUpload" accept=".jpg,.jpeg,.png">
                                            <label for="profilePhotoUpload" class="button button-outline">
                                                <i class="fas fa-camera"></i>
                                                Change Photo
                                            </label>
                                            <button class="button button-outline" id="removeProfilePhoto">
                                                Remove Photo
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Social Media -->
                    <section class="profile-section" id="social-media">
                        <div class="section-header">
                            <h2>Social Media Links</h2>
                            <button class="edit-section" data-section="social-media">Edit</button>
                        </div>
                        <div class="section-content">
                            <form id="socialMediaForm" class="profile-form">
                                <div class="social-links">
                                    <div class="social-link-item">
                                        <div class="social-icon">
                                            <i class="fab fa-facebook-f"></i>
                                        </div>
                                        <input type="url" name="facebook" class="form-input" placeholder="Facebook URL">
                                    </div>
                                    <div class="social-link-item">
                                        <div class="social-icon">
                                            <i class="fab fa-twitter"></i>
                                        </div>
                                        <input type="url" name="twitter" class="form-input" placeholder="Twitter URL">
                                    </div>
                                    <div class="social-link-item">
                                        <div class="social-icon">
                                            <i class="fab fa-instagram"></i>
                                        </div>
                                        <input type="url" name="instagram" class="form-input" placeholder="Instagram URL">
                                    </div>
                                    <div class="social-link-item">
                                        <div class="social-icon">
                                            <i class="fab fa-linkedin-in"></i>
                                        </div>
                                        <input type="url" name="linkedin" class="form-input" placeholder="LinkedIn URL">
                                    </div>
                                    <div class="social-link-item">
                                        <div class="social-icon">
                                            <i class="fab fa-youtube"></i>
                                        </div>
                                        <input type="url" name="youtube" class="form-input" placeholder="YouTube URL">
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <button type="button" class="button button-outline cancel-edit">Cancel</button>
                                    <button type="submit" class="button button-primary">Save Social Links</button>
                                </div>
                            </form>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </main>

    <!-- Success Modal -->
    <div class="modal" id="successModal">
        <div class="modal-content">
            <div class="modal-icon success">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3>Profile Updated Successfully</h3>
            <p>Your business profile changes have been saved.</p>
            <button class="button button-primary" id="closeSuccessModal">OK</button>
        </div>
    </div>

    <?php
    echo Components::call('Footers', 'businessFooter', [$footer_config]);
    echo Components::call('Footers', 'documentClose');
    ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        initializeProfileManagement();
        loadBusinessProfile();
    });

    function initializeProfileManagement() {
        // Edit section functionality
        document.querySelectorAll('.edit-section').forEach(button => {
            button.addEventListener('click', function() {
                const sectionId = this.dataset.section;
                enableSectionEditing(sectionId);
            });
        });

        // Cancel edit functionality
        document.querySelectorAll('.cancel-edit').forEach(button => {
            button.addEventListener('click', function() {
                const section = this.closest('.profile-section');
                disableSectionEditing(section.id);
            });
        });

        // Form submissions
        document.querySelectorAll('.profile-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                saveSectionChanges(this);
            });
        });

        // Initialize business hours
        initializeBusinessHours();

        // Initialize services and products
        initializeServicesProducts();

        // Initialize photo upload
        initializePhotoUpload();

        // Modal functionality
        document.getElementById('closeSuccessModal').addEventListener('click', function() {
            document.getElementById('successModal').style.display = 'none';
        });
    }

    function enableSectionEditing(sectionId) {
        const section = document.getElementById(sectionId);
        const form = section.querySelector('form');
        
        // Enable all form inputs
        form.querySelectorAll('input, select, textarea').forEach(input => {
            input.disabled = false;
        });
        
        // Show form actions
        section.querySelector('.form-actions').style.display = 'flex';
        
        // Change edit button to viewing mode
        const editButton = section.querySelector('.edit-section');
        editButton.textContent = 'Viewing';
        editButton.disabled = true;
    }

    function disableSectionEditing(sectionId) {
        const section = document.getElementById(sectionId);
        const form = section.querySelector('form');
        
        // Disable all form inputs
        form.querySelectorAll('input, select, textarea').forEach(input => {
            input.disabled = true;
        });
        
        // Hide form actions
        section.querySelector('.form-actions').style.display = 'none';
        
        // Reset edit button
        const editButton = section.querySelector('.edit-section');
        editButton.textContent = 'Edit';
        editButton.disabled = false;
        
        // Reload data to reset any changes
        loadSectionData(sectionId);
    }

    async function loadBusinessProfile() {
        try {
            // Show loading state
            showLoadingState();

            // Simulate API call
            setTimeout(() => {
                const mockData = {
                    basicInfo: {
                        name: 'Quality Home Services LLC',
                        category: 'home-services',
                        description: 'Professional home repair and renovation services with over 15 years of experience. We specialize in kitchen and bathroom remodeling, flooring installation, and general home repairs.',
                        tags: 'family-owned, licensed, insured, eco-friendly'
                    },
                    contactInfo: {
                        phone: '(555) 123-4567',
                        email: 'contact@qualityhomeservices.com',
                        website: 'https://qualityhomeservices.com',
                        street: '123 Main Street',
                        city: 'Anytown',
                        state: 'CA',
                        zipcode: '12345'
                    },
                    hours: {
                        monday: { open: '09:00', close: '17:00' },
                        tuesday: { open: '09:00', close: '17:00' },
                        wednesday: { open: '09:00', close: '17:00' },
                        thursday: { open: '09:00', close: '17:00' },
                        friday: { open: '09:00', close: '17:00' },
                        saturday: { open: '10:00', close: '14:00' },
                        sunday: { open: '', close: '' }
                    },
                    services: [
                        'Kitchen Remodeling',
                        'Bathroom Renovation',
                        'Flooring Installation',
                        'Painting Services',
                        'General Repairs'
                    ],
                    products: [
                        'Custom Cabinetry',
                        'Countertop Installation',
                        'Tile Work'
                    ],
                    socialMedia: {
                        facebook: 'https://facebook.com/qualityhomeservices',
                        instagram: 'https://instagram.com/qualityhomeservices'
                    }
                };

                updateProfileUI(mockData);
                hideLoadingState();

            }, 1000);

        } catch (error) {
            console.error('Error loading business profile:', error);
            showErrorState('Failed to load business profile data');
        }
    }

    function updateProfileUI(data) {
        // Update basic information
        if (data.basicInfo) {
            document.getElementById('businessName').value = data.basicInfo.name;
            document.getElementById('businessCategory').value = data.basicInfo.category;
            document.getElementById('businessDescription').value = data.basicInfo.description;
            document.getElementById('businessTags').value = data.basicInfo.tags;
        }

        // Update contact information
        if (data.contactInfo) {
            document.getElementById('businessPhone').value = data.contactInfo.phone;
            document.getElementById('businessEmail').value = data.contactInfo.email;
            document.getElementById('businessWebsite').value = data.contactInfo.website;
            document.getElementById('streetAddress').value = data.contactInfo.street;
            document.getElementById('city').value = data.contactInfo.city;
            document.getElementById('state').value = data.contactInfo.state;
            document.getElementById('zipCode').value = data.contactInfo.zipcode;
        }

        // Update business hours
        if (data.hours) {
            updateBusinessHours(data.hours);
        }

        // Update services and products
        if (data.services) {
            updateServicesList(data.services);
        }
        if (data.products) {
            updateProductsList(data.products);
        }

        // Update social media
        if (data.socialMedia) {
            updateSocialMedia(data.socialMedia);
        }

        // Disable all sections initially
        document.querySelectorAll('.profile-section').forEach(section => {
            disableSectionEditing(section.id);
        });
    }

    function initializeBusinessHours() {
        const days = [
            { id: 'monday', label: 'Monday' },
            { id: 'tuesday', label: 'Tuesday' },
            { id: 'wednesday', label: 'Wednesday' },
            { id: 'thursday', label: 'Thursday' },
            { id: 'friday', label: 'Friday' },
            { id: 'saturday', label: 'Saturday' },
            { id: 'sunday', label: 'Sunday' }
        ];

        const hoursContainer = document.getElementById('businessHours');
        
        hoursContainer.innerHTML = days.map(day => `
            <div class="hours-row">
                <div class="day-label">${day.label}</div>
                <div class="hours-inputs">
                    <select name="hours[${day.id}][open]" class="time-select" disabled>
                        <option value="">Closed</option>
                        ${generateTimeOptions()}
                    </select>
                    <span class="hours-separator">to</span>
                    <select name="hours[${day.id}][close]" class="time-select" disabled>
                        <option value="">Closed</option>
                        ${generateTimeOptions()}
                    </select>
                </div>
            </div>
        `).join('');
    }

    function updateBusinessHours(hours) {
        Object.keys(hours).forEach(day => {
            const openSelect = document.querySelector(`select[name="hours[${day}][open]"]`);
            const closeSelect = document.querySelector(`select[name="hours[${day}][close]"]`);
            
            if (openSelect && closeSelect) {
                openSelect.value = hours[day].open || '';
                closeSelect.value = hours[day].close || '';
            }
        });
    }

    function initializeServicesProducts() {
        document.getElementById('addService').addEventListener('click', function() {
            addListItem('servicesList', 'service');
        });

        document.getElementById('addProduct').addEventListener('click', function() {
            addListItem('productsList', 'product');
        });
    }

    function addListItem(containerId, type) {
        const container = document.getElementById(containerId);
        const itemCount = container.children.length;
        const itemId = `${type}_${Date.now()}`;
        
        const listItem = document.createElement('div');
        listItem.className = 'list-item';
        listItem.innerHTML = `
            <input type="text" name="${type}s[]" class="form-input" placeholder="Enter ${type} name">
            <button type="button" class="remove-item" data-item="${itemId}">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        container.appendChild(listItem);
        
        // Add remove functionality
        listItem.querySelector('.remove-item').addEventListener('click', function() {
            this.parentElement.remove();
        });
    }

    function updateServicesList(services) {
        const container = document.getElementById('servicesList');
        container.innerHTML = '';
        
        services.forEach(service => {
            addListItemWithValue('servicesList', 'service', service);
        });
    }

    function updateProductsList(products) {
        const container = document.getElementById('productsList');
        container.innerHTML = '';
        
        products.forEach(product => {
            addListItemWithValue('productsList', 'product', product);
        });
    }

    function addListItemWithValue(containerId, type, value) {
        const container = document.getElementById(containerId);
        const itemId = `${type}_${Date.now()}`;
        
        const listItem = document.createElement('div');
        listItem.className = 'list-item';
        listItem.innerHTML = `
            <input type="text" name="${type}s[]" class="form-input" value="${value}" disabled>
            <button type="button" class="remove-item" data-item="${itemId}">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        container.appendChild(listItem);
    }

    function updateSocialMedia(links) {
        Object.keys(links).forEach(platform => {
            const input = document.querySelector(`input[name="${platform}"]`);
            if (input && links[platform]) {
                input.value = links[platform];
            }
        });
    }

    function initializePhotoUpload() {
        // Photo upload functionality would be implemented here
        // Similar to previous implementations
    }

    async function saveSectionChanges(form) {
        try {
            const formData = new FormData(form);
            const section = form.closest('.profile-section');
            
            // Show loading state
            const saveButton = form.querySelector('button[type="submit"]');
            const originalText = saveButton.textContent;
            saveButton.disabled = true;
            saveButton.textContent = 'Saving...';

            // Simulate API call
            setTimeout(() => {
                // Show success message
                document.getElementById('successModal').style.display = 'block';
                
                // Reset button
                saveButton.disabled = false;
                saveButton.textContent = originalText;
                
                // Disable section editing
                disableSectionEditing(section.id);
                
            }, 1000);

        } catch (error) {
            console.error('Error saving section:', error);
            alert('Error saving changes. Please try again.');
        }
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

    function showLoadingState() {
        document.querySelectorAll('.form-input').forEach(input => {
            input.placeholder = 'Loading...';
        });
    }

    function hideLoadingState() {
        // Remove loading placeholders
    }

    function showErrorState(message) {
        alert(message);
    }
    </script>
</body>
</html>

<?php echo ob_get_clean(); ?>