<?php
/**
 * P.I.M.P Homepage
 * Replicates Better Business Bureau homepage layout
 */

// Include components
require_once __DIR__ . '/../../../components/organisms/Hero.php';
require_once __DIR__ . '/../../../components/molecules/BusinessCard.php';
require_once __DIR__ . '/../../../components/molecules/CategoryCard.php';
require_once __DIR__ . '/../../../components/atoms/Button.php';
require_once __DIR__ . '/../../../components/organisms/Navigation.php';
require_once __DIR__ . '/../../../components/organisms/Footer.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>P.I.M.P - Find Trusted Businesses</title>
    <link rel="stylesheet" href="/assets/styles/main.css">
    <link rel="stylesheet" href="/assets/styles/components.css">
    <link rel="stylesheet" href="/assets/styles/atoms.css">
    <link rel="stylesheet" href="/assets/styles/molecules.css">
    <link rel="stylesheet" href="/assets/styles/organisms.css">
</head>
<body>
     Navigation 
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

     Hero Section 
    <?php echo Hero([
        'title' => 'Find a Better Business',
        'description' => 'P.I.M.P helps people find businesses they can trust.',
        'showSearch' => true,
        'variant' => 'gradient'
    ]); ?>

     Quick Actions 
    <section class="quick-actions">
        <div class="container">
            <div class="quick-actions__grid">
                <div class="quick-actions__card">
                    <h2>Find Accredited Businesses You Can Trust</h2>
                    <p>Search our directory of verified and accredited businesses.</p>
                    <?php echo Button([
                        'text' => 'Start an Order',
                        'url' => '/search',
                        'variant' => 'primary',
                        'size' => 'lg'
                    ]); ?>
                </div>
                
                <div class="quick-actions__card">
                    <h2>File a Complaint</h2>
                    <p>Need to file a complaint? P.I.M.P is here to help. We'll guide you through the process.</p>
                    <?php echo Button([
                        'text' => 'File a Complaint',
                        'url' => '/complaint',
                        'variant' => 'secondary',
                        'size' => 'lg'
                    ]); ?>
                </div>
                
                <div class="quick-actions__card">
                    <h2>Start a Review</h2>
                    <p>Write a review of a business and share your opinions with others.</p>
                    <?php echo Button([
                        'text' => 'Start a Review',
                        'url' => '/review',
                        'variant' => 'secondary',
                        'size' => 'lg'
                    ]); ?>
                </div>
            </div>
        </div>
    </section>

     Featured Content 
    <section class="featured-content">
        <div class="container">
            <div class="featured-content__header">
                <h2>Featured Content</h2>
                <a href="/news" class="featured-content__view-all">View All</a>
            </div>
            
            <div class="featured-content__grid">
                <article class="featured-article">
                    <img src="/placeholder.svg?height=200&width=400" alt="Business Tips" />
                    <h3>P.I.M.P Tip: Staying safe during economic uncertainty</h3>
                    <p>Learn how to protect your business and finances during challenging times.</p>
                    <a href="/article/1" class="featured-article__link">Read More →</a>
                </article>
                
                <article class="featured-article">
                    <img src="/placeholder.svg?height=200&width=400" alt="Scam Alert" />
                    <h3>P.I.M.P Scam Alert: Beware of online shopping scams</h3>
                    <p>Don't fall victim to fake websites and fraudulent sellers.</p>
                    <a href="/article/2" class="featured-article__link">Read More →</a>
                </article>
                
                <article class="featured-article">
                    <img src="/placeholder.svg?height=200&width=400" alt="Consumer Protection" />
                    <h3>P.I.M.P Scam Alert: Protect yourself from identity theft</h3>
                    <p>Essential tips to keep your personal information secure.</p>
                    <a href="/article/3" class="featured-article__link">Read More →</a>
                </article>
            </div>
        </div>
    </section>

     Popular Categories 
    <section class="popular-categories">
        <div class="container">
            <h2>Popular Categories</h2>
            <div class="popular-categories__grid">
                <?php
                $categories = [
                    ['name' => 'Construction Services', 'count' => 1250, 'icon' => '🏗️', 'url' => '/category/construction'],
                    ['name' => 'General Contractor', 'count' => 980, 'icon' => '👷', 'url' => '/category/contractor'],
                    ['name' => 'Auto Repairs', 'count' => 1540, 'icon' => '🔧', 'url' => '/category/auto-repair'],
                    ['name' => 'Business Services', 'count' => 2100, 'icon' => '💼', 'url' => '/category/business-services'],
                    ['name' => 'Roofing Contractors', 'count' => 750, 'icon' => '🏠', 'url' => '/category/roofing'],
                    ['name' => 'Electrician', 'count' => 890, 'icon' => '⚡', 'url' => '/category/electrician'],
                    ['name' => 'Painting Contractors', 'count' => 620, 'icon' => '🎨', 'url' => '/category/painting'],
                    ['name' => 'Plumbing', 'count' => 1120, 'icon' => '🚰', 'url' => '/category/plumbing'],
                ];
                
                foreach ($categories as $category) {
                    echo CategoryCard($category);
                }
                ?>
            </div>
        </div>
    </section>

     Footer 
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
                    ['text' => 'Scam Tracker', 'url' => '/scams'],
                ]
            ],
            [
                'title' => 'For Businesses',
                'links' => [
                    ['text' => 'Get Accredited', 'url' => '/accreditation'],
                    ['text' => 'List Your Business', 'url' => '/list-business'],
                    ['text' => 'Business Resources', 'url' => '/resources'],
                ]
            ],
            [
                'title' => 'About P.I.M.P',
                'links' => [
                    ['text' => 'About Us', 'url' => '/about'],
                    ['text' => 'Contact', 'url' => '/contact'],
                    ['text' => 'Privacy Policy', 'url' => '/privacy'],
                    ['text' => 'Terms of Use', 'url' => '/terms'],
                ]
            ]
        ],
        'copyright' => '© 2025 P.I.M.P. All rights reserved.'
    ]); ?>
</body>
</html>
