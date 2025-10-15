<?php
/**
 * P.I.M.P Business Profile Page
 * Displays detailed business information, reviews, and ratings
 */

require_once __DIR__ . '/../../../components/organisms/Navigation.php';
require_once __DIR__ . '/../../../components/organisms/Footer.php';
require_once __DIR__ . '/../../../components/molecules/Breadcrumb.php';
require_once __DIR__ . '/../../../components/atoms/Button.php';
require_once __DIR__ . '/../../../components/molecules/Tabs.php';

// Sample business data
$business = [
    'name' => 'ABC Construction Co.',
    'rating' => 4.5,
    'reviewCount' => 127,
    'category' => 'Construction Services',
    'address' => '123 Main St, City, ST 12345',
    'phone' => '(555) 123-4567',
    'email' => 'info@abcconstruction.com',
    'website' => 'www.abcconstruction.com',
    'accredited' => true,
    'accreditedSince' => '2015',
    'image' => '/placeholder.svg?height=120&width=120',
    'description' => 'ABC Construction Co. has been serving the community for over 20 years. We specialize in residential and commercial construction, renovations, and remodeling projects.',
    'hours' => [
        'Monday' => '8:00 AM - 6:00 PM',
        'Tuesday' => '8:00 AM - 6:00 PM',
        'Wednesday' => '8:00 AM - 6:00 PM',
        'Thursday' => '8:00 AM - 6:00 PM',
        'Friday' => '8:00 AM - 6:00 PM',
        'Saturday' => '9:00 AM - 3:00 PM',
        'Sunday' => 'Closed',
    ]
];

$reviews = [
    [
        'author' => 'John D.',
        'rating' => 5,
        'date' => '2024-01-15',
        'title' => 'Excellent service!',
        'content' => 'ABC Construction did an amazing job on our home renovation. Professional, on-time, and within budget.'
    ],
    [
        'author' => 'Sarah M.',
        'rating' => 4,
        'date' => '2024-01-10',
        'title' => 'Great experience',
        'content' => 'Very satisfied with the quality of work. Would recommend to others.'
    ],
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($business['name']); ?> - P.I.M.P</title>
    <link rel="stylesheet" href="/assets/styles/main.css">
    <link rel="stylesheet" href="/assets/styles/components.css">
    <link rel="stylesheet" href="/assets/styles/atoms.css">
    <link rel="stylesheet" href="/assets/styles/molecules.css">
    <link rel="stylesheet" href="/assets/styles/organisms.css">
</head>
<body>
    <?php echo Navigation([
        'logo' => 'P.I.M.P',
        'items' => [
            ['label' => 'Find a Business', 'url' => '/search'],
            ['label' => 'File a Complaint', 'url' => '/complaint'],
            ['label' => 'Leave a Review', 'url' => '/review'],
            ['label' => 'Get Accredited', 'url' => '/accreditation'],
            ['label' => 'About', 'url' => '/about'],
        ],
        'variant' => 'horizontal'
    ]); ?>

    <div class="business-profile">
        <div class="container">
            <?php echo Breadcrumb([
                'items' => [
                    ['label' => 'Home', 'url' => '/'],
                    ['label' => 'Search', 'url' => '/search'],
                    ['label' => $business['name'], 'url' => '#']
                ]
            ]); ?>

             Business Header 
            <div class="business-profile__header">
                <div class="business-profile__image">
                    <img src="<?php echo htmlspecialchars($business['image']); ?>" alt="<?php echo htmlspecialchars($business['name']); ?>" />
                </div>
                
                <div class="business-profile__info">
                    <h1><?php echo htmlspecialchars($business['name']); ?></h1>
                    
                    <?php if ($business['accredited']): ?>
                        <div class="business-profile__accredited">
                            <span class="badge badge--success">✓ Accredited Business</span>
                            <span class="business-profile__accredited-since">Since <?php echo htmlspecialchars($business['accreditedSince']); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="business-profile__rating">
                        <div class="stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="star <?php echo $i <= $business['rating'] ? 'star--filled' : ''; ?>">★</span>
                            <?php endfor; ?>
                        </div>
                        <span class="rating-value"><?php echo number_format($business['rating'], 1); ?></span>
                        <span class="review-count">(<?php echo number_format($business['reviewCount']); ?> reviews)</span>
                    </div>
                    
                    <p class="business-profile__category"><?php echo htmlspecialchars($business['category']); ?></p>
                </div>
                
                <div class="business-profile__actions">
                    <?php echo Button([
                        'text' => 'Leave a Review',
                        'url' => '/review?business=' . urlencode($business['name']),
                        'variant' => 'primary',
                        'size' => 'lg'
                    ]); ?>
                    <?php echo Button([
                        'text' => 'File a Complaint',
                        'url' => '/complaint?business=' . urlencode($business['name']),
                        'variant' => 'secondary',
                        'size' => 'lg'
                    ]); ?>
                </div>
            </div>

             Business Content 
            <div class="business-profile__content">
                 Sidebar 
                <aside class="business-profile__sidebar">
                    <div class="business-profile__contact">
                        <h3>Contact Information</h3>
                        <p><strong>Address:</strong><br><?php echo nl2br(htmlspecialchars($business['address'])); ?></p>
                        <p><strong>Phone:</strong><br><?php echo htmlspecialchars($business['phone']); ?></p>
                        <?php if ($business['email']): ?>
                            <p><strong>Email:</strong><br><?php echo htmlspecialchars($business['email']); ?></p>
                        <?php endif; ?>
                        <?php if ($business['website']): ?>
                            <p><strong>Website:</strong><br><a href="http://<?php echo htmlspecialchars($business['website']); ?>" target="_blank"><?php echo htmlspecialchars($business['website']); ?></a></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="business-profile__hours">
                        <h3>Business Hours</h3>
                        <?php foreach ($business['hours'] as $day => $hours): ?>
                            <div class="hours-row">
                                <span class="day"><?php echo $day; ?></span>
                                <span class="hours"><?php echo $hours; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </aside>

                 Main Content 
                <div class="business-profile__main">
                    <section class="business-profile__about">
                        <h2>About This Business</h2>
                        <p><?php echo nl2br(htmlspecialchars($business['description'])); ?></p>
                    </section>

                    <section class="business-profile__reviews">
                        <h2>Customer Reviews</h2>
                        
                        <?php foreach ($reviews as $review): ?>
                            <div class="review-card">
                                <div class="review-card__header">
                                    <div class="review-card__author"><?php echo htmlspecialchars($review['author']); ?></div>
                                    <div class="review-card__date"><?php echo date('M d, Y', strtotime($review['date'])); ?></div>
                                </div>
                                
                                <div class="review-card__rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="star <?php echo $i <= $review['rating'] ? 'star--filled' : ''; ?>">★</span>
                                    <?php endfor; ?>
                                </div>
                                
                                <h3 class="review-card__title"><?php echo htmlspecialchars($review['title']); ?></h3>
                                <p class="review-card__content"><?php echo nl2br(htmlspecialchars($review['content'])); ?></p>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="business-profile__review-cta">
                            <?php echo Button([
                                'text' => 'Write a Review',
                                'url' => '/review?business=' . urlencode($business['name']),
                                'variant' => 'primary'
                            ]); ?>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>

    <?php echo Footer([
        'variant' => 'multi-column',
        'logo' => 'P.I.M.P',
        'sections' => [
            [
                'title' => 'For Consumers',
                'links' => [
                    ['text' => 'Find a Business', 'url' => '/search'],
                    ['text' => 'File a Complaint', 'url' => '/complaint'],
                    ['text' => 'Leave a Review', 'url' => '/review'],
                ]
            ],
            [
                'title' => 'For Businesses',
                'links' => [
                    ['text' => 'Get Accredited', 'url' => '/accreditation'],
                    ['text' => 'List Your Business', 'url' => '/list-business'],
                ]
            ],
            [
                'title' => 'About P.I.M.P',
                'links' => [
                    ['text' => 'About Us', 'url' => '/about'],
                    ['text' => 'Contact', 'url' => '/contact'],
                ]
            ]
        ],
        'copyright' => '© 2025 P.I.M.P. All rights reserved.'
    ]); ?>
</body>
</html>
