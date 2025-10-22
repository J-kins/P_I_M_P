<?php
/**
 * P.I.M.P - User Profile
 * Generic user profile page
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

// These would come from backend API
$profile_data = [
    'name' => 'User Name',
    'email' => 'user@example.com',
    'join_date' => 'January 2024',
    'location' => 'City, State',
    'bio' => 'This is a sample bio. In a real implementation, this would be populated from the user\'s profile.',
    'avatar' => Config::imageUrl('avatars/default.jpg')
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
    'title' => 'My Profile - P.I.M.P',
    'metaTags' => [
        'description' => 'View and manage your P.I.M.P user profile, reviews, and account settings.',
        'keywords' => 'user profile, account settings, PIMP profile'
    ],
    'styles' => [
        'views/user-profile.css'
    ],
    'scripts' => [
        'js/user-profile.js'
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
            ['url' => '/dashboard', 'label' => 'Dashboard'],
            ['url' => '/logout', 'label' => 'Logout', 'separator' => true],
        ],
        'showSearch' => true,
    ]]);
    ?>

    <main class="profile-main">
        <div class="profile-container">
            <!-- Profile Header -->
            <section class="profile-header">
                <div class="profile-avatar">
                    <img src="<?= $profile_data['avatar'] ?>" alt="Profile Avatar" id="profileAvatar">
                    <button class="avatar-edit" id="avatarEditBtn">
                        <i class="fas fa-camera"></i>
                    </button>
                </div>
                <div class="profile-info">
                    <h1 id="profileName"><?= htmlspecialchars($profile_data['name']) ?></h1>
                    <p class="profile-join-date">Member since <?= $profile_data['join_date'] ?></p>
                    <p class="profile-location" id="profileLocation">
                        <i class="fas fa-map-marker-alt"></i>
                        <?= htmlspecialchars($profile_data['location']) ?>
                    </p>
                    <div class="profile-stats">
                        <div class="stat">
                            <span class="stat-number" id="reviewCount">0</span>
                            <span class="stat-label">Reviews</span>
                        </div>
                        <div class="stat">
                            <span class="stat-number" id="helpfulCount">0</span>
                            <span class="stat-label">Helpful</span>
                        </div>
                        <div class="stat">
                            <span class="stat-number" id="followerCount">0</span>
                            <span class="stat-label">Followers</span>
                        </div>
                    </div>
                </div>
                <div class="profile-actions">
                    <button class="button button-outline" id="editProfileBtn">
                        <i class="fas fa-edit"></i>
                        Edit Profile
                    </button>
                    <button class="button button-primary" id="shareProfileBtn">
                        <i class="fas fa-share"></i>
                        Share Profile
                    </button>
                </div>
            </section>

            <!-- Profile Content -->
            <div class="profile-content">
                <!-- Left Column -->
                <div class="profile-left">
                    <!-- Bio Section -->
                    <section class="profile-section">
                        <h2>About Me</h2>
                        <div class="bio-content">
                            <p id="profileBio"><?= htmlspecialchars($profile_data['bio']) ?></p>
                            <button class="edit-bio-btn" id="editBioBtn">
                                <i class="fas fa-pencil-alt"></i>
                                Edit Bio
                            </button>
                        </div>
                    </section>

                    <!-- Recent Reviews -->
                    <section class="profile-section">
                        <div class="section-header">
                            <h2>Recent Reviews</h2>
                            <a href="<?= Config::url('/reviews') ?>" class="view-all">View All</a>
                        </div>
                        <div class="reviews-list" id="reviewsList">
                            <!-- Reviews will be populated by JavaScript -->
                            <div class="empty-state">
                                <i class="fas fa-star"></i>
                                <h3>No reviews yet</h3>
                                <p>Your reviews will appear here once you start writing them</p>
                                <a href="<?= Config::url('/reviews/write') ?>" class="button button-primary">
                                    Write Your First Review
                                </a>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Right Column -->
                <div class="profile-right">
                    <!-- Contact Info -->
                    <section class="profile-section">
                        <h2>Contact Information</h2>
                        <div class="contact-info">
                            <div class="contact-item">
                                <i class="fas fa-envelope"></i>
                                <span id="profileEmail"><?= htmlspecialchars($profile_data['email']) ?></span>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span id="contactLocation"><?= htmlspecialchars($profile_data['location']) ?></span>
                            </div>
                        </div>
                    </section>

                    <!-- Badges -->
                    <section class="profile-section">
                        <h2>Badges & Achievements</h2>
                        <div class="badges-grid" id="badgesGrid">
                            <!-- Badges will be populated by JavaScript -->
                            <div class="empty-state small">
                                <i class="fas fa-award"></i>
                                <p>No badges yet</p>
                            </div>
                        </div>
                    </section>

                    <!-- Social Links -->
                    <section class="profile-section">
                        <h2>Social Profiles</h2>
                        <div class="social-links" id="socialLinks">
                            <!-- Social links will be populated by JavaScript -->
                            <div class="empty-state small">
                                <i class="fas fa-share-alt"></i>
                                <p>No social profiles added</p>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </main>

    <!-- Edit Profile Modal -->
    <div class="modal" id="editProfileModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Profile</h2>
                <button class="modal-close" id="modalClose">&times;</button>
            </div>
            <form id="editProfileForm" class="modal-body">
                <div class="form-group">
                    <label for="editName">Full Name</label>
                    <input type="text" id="editName" name="name" value="<?= htmlspecialchars($profile_data['name']) ?>">
                </div>
                <div class="form-group">
                    <label for="editEmail">Email</label>
                    <input type="email" id="editEmail" name="email" value="<?= htmlspecialchars($profile_data['email']) ?>">
                </div>
                <div class="form-group">
                    <label for="editLocation">Location</label>
                    <input type="text" id="editLocation" name="location" value="<?= htmlspecialchars($profile_data['location']) ?>">
                </div>
                <div class="form-group">
                    <label for="editBio">Bio</label>
                    <textarea id="editBio" name="bio" rows="4"><?= htmlspecialchars($profile_data['bio']) ?></textarea>
                </div>
            </form>
            <div class="modal-footer">
                <button class="button button-outline" id="cancelEdit">Cancel</button>
                <button class="button button-primary" id="saveProfile">Save Changes</button>
            </div>
        </div>
    </div>

    <?php
    echo Components::call('Footers', 'businessFooter', [$footer_config]);
    echo Components::call('Footers', 'documentClose');
    ?>

    <script>
    // Profile data initialization
    const PROFILE_DATA = <?= json_encode($profile_data) ?>;

    document.addEventListener('DOMContentLoaded', function() {
        initializeProfile();
        loadProfileData();
    });

    function initializeProfile() {
        // Modal functionality
        const editProfileBtn = document.getElementById('editProfileBtn');
        const modal = document.getElementById('editProfileModal');
        const modalClose = document.getElementById('modalClose');
        const cancelEdit = document.getElementById('cancelEdit');
        const saveProfile = document.getElementById('saveProfile');

        editProfileBtn.addEventListener('click', () => modal.style.display = 'block');
        modalClose.addEventListener('click', () => modal.style.display = 'none');
        cancelEdit.addEventListener('click', () => modal.style.display = 'none');

        saveProfile.addEventListener('click', function() {
            saveProfileChanges();
        });

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });

        // Share profile functionality
        document.getElementById('shareProfileBtn').addEventListener('click', function() {
            if (navigator.share) {
                navigator.share({
                    title: 'My P.I.M.P Profile',
                    text: `Check out my P.I.M.P profile - ${PROFILE_DATA.name}`,
                    url: window.location.href
                });
            } else {
                // Fallback: copy to clipboard
                navigator.clipboard.writeText(window.location.href).then(() => {
                    alert('Profile link copied to clipboard!');
                });
            }
        });
    }

    async function loadProfileData() {
        try {
            // Show loading states
            showLoadingStates();

            // In a real implementation, this would be an API call
            // const response = await fetch('/api/profile');
            // const data = await response.json();
            
            // For now, we'll simulate API call with timeout
            setTimeout(() => {
                const data = {
                    ...PROFILE_DATA,
                    stats: {
                        reviews: 12,
                        helpful: 45,
                        followers: 23
                    },
                    recentReviews: [],
                    badges: [],
                    socialLinks: []
                };
                
                updateProfileUI(data);
            }, 1000);

        } catch (error) {
            console.error('Error loading profile data:', error);
            showErrorState();
        }
    }

    function showLoadingStates() {
        // Add loading animation to stats
        const statNumbers = document.querySelectorAll('.stat-number');
        statNumbers.forEach(number => {
            number.textContent = '...';
        });
    }

    function updateProfileUI(data) {
        // Update stats
        if (data.stats) {
            document.getElementById('reviewCount').textContent = data.stats.reviews;
            document.getElementById('helpfulCount').textContent = data.stats.helpful;
            document.getElementById('followerCount').textContent = data.stats.followers;
        }

        // Update reviews
        updateReviewsList(data.recentReviews || []);

        // Update badges
        updateBadgesGrid(data.badges || []);

        // Update social links
        updateSocialLinks(data.socialLinks || []);
    }

    function updateReviewsList(reviews) {
        const reviewsList = document.getElementById('reviewsList');
        
        if (reviews.length === 0) {
            return; // Keep the empty state
        }

        reviewsList.innerHTML = reviews.map(review => `
            <div class="review-item">
                <div class="review-header">
                    <h3 class="business-name">${review.businessName}</h3>
                    <div class="review-rating">
                        ${generateStars(review.rating)}
                    </div>
                </div>
                <p class="review-excerpt">${review.excerpt}</p>
                <div class="review-meta">
                    <span class="review-date">${review.date}</span>
                    <span class="review-helpful">${review.helpful} people found this helpful</span>
                </div>
            </div>
        `).join('');
    }

    function updateBadgesGrid(badges) {
        const badgesGrid = document.getElementById('badgesGrid');
        
        if (badges.length === 0) {
            return; // Keep the empty state
        }

        badgesGrid.innerHTML = badges.map(badge => `
            <div class="badge-item">
                <div class="badge-icon">
                    <i class="${badge.icon}"></i>
                </div>
                <span class="badge-name">${badge.name}</span>
            </div>
        `).join('');
    }

    function updateSocialLinks(links) {
        const socialLinks = document.getElementById('socialLinks');
        
        if (links.length === 0) {
            return; // Keep the empty state
        }

        socialLinks.innerHTML = links.map(link => `
            <a href="${link.url}" class="social-link" target="_blank">
                <i class="fab fa-${link.platform}"></i>
                ${link.platform}
            </a>
        `).join('');
    }

    function generateStars(rating) {
        let stars = '';
        for (let i = 1; i <= 5; i++) {
            if (i <= rating) {
                stars += '<i class="fas fa-star"></i>';
            } else if (i - 0.5 === rating) {
                stars += '<i class="fas fa-star-half-alt"></i>';
            } else {
                stars += '<i class="far fa-star"></i>';
            }
        }
        return stars;
    }

    async function saveProfileChanges() {
        const form = document.getElementById('editProfileForm');
        const formData = new FormData(form);
        
        try {
            // Show loading state
            const saveBtn = document.getElementById('saveProfile');
            saveBtn.disabled = true;
            saveBtn.textContent = 'Saving...';

            // In real implementation, this would be an API call
            // const response = await fetch('/api/profile/update', {
            //     method: 'POST',
            //     body: formData
            // });

            // Simulate API call
            setTimeout(() => {
                // Update UI with new data
                PROFILE_DATA.name = formData.get('name');
                PROFILE_DATA.email = formData.get('email');
                PROFILE_DATA.location = formData.get('location');
                PROFILE_DATA.bio = formData.get('bio');

                document.getElementById('profileName').textContent = PROFILE_DATA.name;
                document.getElementById('profileEmail').textContent = PROFILE_DATA.email;
                document.getElementById('profileLocation').textContent = PROFILE_DATA.location;
                document.getElementById('contactLocation').textContent = PROFILE_DATA.location;
                document.getElementById('profileBio').textContent = PROFILE_DATA.bio;

                // Close modal
                document.getElementById('editProfileModal').style.display = 'none';
                
                // Reset button
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save Changes';

                alert('Profile updated successfully!');
            }, 1000);

        } catch (error) {
            console.error('Error saving profile:', error);
            alert('Error updating profile. Please try again.');
        }
    }

    function showErrorState() {
        // Show error message to user
        const reviewsList = document.getElementById('reviewsList');
        reviewsList.innerHTML = `
            <div class="error-state">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>Unable to load profile data</h3>
                <p>Please try refreshing the page</p>
            </div>
        `;
    }
    </script>
</body>
</html>

<?php echo ob_get_clean(); ?>