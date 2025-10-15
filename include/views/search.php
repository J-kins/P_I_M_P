<?php
/**
 * P.I.M.P Search Results Page
 * Displays business search results with filters
 */

require_once __DIR__ . '/../../../components/organisms/Navigation.php';
require_once __DIR__ . '/../../../components/organisms/Footer.php';
require_once __DIR__ . '/../../../components/molecules/SearchBox.php';
require_once __DIR__ . '/../../../components/molecules/BusinessCard.php';
require_once __DIR__ . '/../../../components/molecules/Breadcrumb.php';
require_once __DIR__ . '/../../../components/molecules/Pagination.php';

// Get search parameters
$query = $_GET['query'] ?? '';
$location = $_GET['location'] ?? '';

// Sample business data
$businesses = [
    [
        'name' => 'ABC Construction Co.',
        'rating' => 4.5,
        'reviewCount' => 127,
        'category' => 'Construction Services',
        'address' => '123 Main St, City, ST 12345',
        'phone' => '(555) 123-4567',
        'website' => 'www.abcconstruction.com',
        'accredited' => true,
        'image' => '/placeholder.svg?height=80&width=80',
        'url' => '/business/abc-construction'
    ],
    [
        'name' => 'Quality Auto Repair',
        'rating' => 5.0,
        'reviewCount' => 89,
        'category' => 'Auto Repairs',
        'address' => '456 Oak Ave, City, ST 12345',
        'phone' => '(555) 234-5678',
        'website' => 'www.qualityauto.com',
        'accredited' => true,
        'image' => '/placeholder.svg?height=80&width=80',
        'url' => '/business/quality-auto'
    ],
    [
        'name' => 'Elite Roofing Services',
        'rating' => 4.8,
        'reviewCount' => 203,
        'category' => 'Roofing Contractors',
        'address' => '789 Pine Rd, City, ST 12345',
        'phone' => '(555) 345-6789',
        'website' => 'www.eliteroofing.com',
        'accredited' => true,
        'image' => '/placeholder.svg?height=80&width=80',
        'url' => '/business/elite-roofing'
    ],
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - P.I.M.P</title>
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

    <div class="search-page">
        <div class="container">
             Breadcrumb 
            <?php echo Breadcrumb([
                'items' => [
                    ['label' => 'Home', 'url' => '/'],
                    ['label' => 'Search Results', 'url' => '#']
                ]
            ]); ?>

             Search Box 
            <div class="search-page__search">
                <?php echo SearchBox([
                    'placeholder' => 'Find a business or category',
                    'locationPlaceholder' => 'City, State or ZIP'
                ]); ?>
            </div>

             Results Header 
            <div class="search-page__header">
                <h1>Search Results</h1>
                <?php if ($query): ?>
                    <p class="search-page__query">Showing results for "<strong><?php echo htmlspecialchars($query); ?></strong>"
                    <?php if ($location): ?>
                        near <strong><?php echo htmlspecialchars($location); ?></strong>
                    <?php endif; ?>
                    </p>
                <?php endif; ?>
                <p class="search-page__count"><?php echo count($businesses); ?> businesses found</p>
            </div>

             Filters and Results 
            <div class="search-page__content">
                 Sidebar Filters 
                <aside class="search-page__filters">
                    <h3>Filter Results</h3>
                    
                    <div class="filter-group">
                        <h4>Accreditation</h4>
                        <label><input type="checkbox" checked /> Accredited Only</label>
                    </div>
                    
                    <div class="filter-group">
                        <h4>Rating</h4>
                        <label><input type="checkbox" /> 5 Stars</label>
                        <label><input type="checkbox" /> 4+ Stars</label>
                        <label><input type="checkbox" /> 3+ Stars</label>
                    </div>
                    
                    <div class="filter-group">
                        <h4>Category</h4>
                        <label><input type="checkbox" /> Construction Services</label>
                        <label><input type="checkbox" /> Auto Repairs</label>
                        <label><input type="checkbox" /> Roofing</label>
                        <label><input type="checkbox" /> Electrician</label>
                    </div>
                </aside>

                 Results List 
                <div class="search-page__results">
                    <?php foreach ($businesses as $business): ?>
                        <?php echo BusinessCard($business); ?>
                    <?php endforeach; ?>

                     Pagination 
                    <div class="search-page__pagination">
                        <?php echo Pagination([
                            'currentPage' => 1,
                            'totalPages' => 5,
                            'baseUrl' => '/search'
                        ]); ?>
                    </div>
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
