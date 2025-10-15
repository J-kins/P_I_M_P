<?php 

$nav_items = [
    ['url' => '?route=search', 'label' => 'Find a Business', 'active' => true],
    ['url' => '?route=complaint', 'label' => 'File a Complaint', 'active' => false],
    ['url' => '?route=review', 'label' => 'Leave a Review', 'active' => false],
    ['url' => '?route=accreditation', 'label' => 'Get Accredited', 'active' => false],
    ['url' => '?route=about', 'label' => 'About', 'active' => false],
];

// Output document head
echo document_head([
    'title' => 'P . I . M . P',
    'metaTags' => [
        'description' => 'A business repository based on trust',
        'keywords' => 'PHP, UI, UX, Template, Theming, HTML, CSS, JS'
    ]
]);
?>
<body>

    <?php
    // Output header
    echo headerOne([
        'title' => 'Public Interest in Market Practices',
        'navItems' => $nav_items,
        'type' => 'default'
    ]);
    ?>
    
    <div class="about-page">
        <div class="container">
            <?php 
            /*
            echo Breadcrumb([
                'items' => [
                    ['label' => 'Home', 'url' => '/'],
                    ['label' => 'About', 'url' => '#']
                ]
            ]); 
            */
            ?>

            <div class="about-content">
                <h1>About P.I.M.P</h1>
                
                <section class="about-section">
                    <h2>Our Mission</h2>
                    <p>P.I.M.P (Professional Information & Marketplace Platform) is dedicated to fostering trust and transparency in the marketplace. We help consumers find reliable businesses and enable businesses to build their reputation through verified reviews and accreditation.</p>
                </section>

                <section class="about-section">
                    <h2>What We Do</h2>
                    <p>We provide a comprehensive platform where consumers can:</p>
                    <ul>
                        <li>Search for trusted businesses in their area</li>
                        <li>Read verified reviews from real customers</li>
                        <li>File complaints and seek resolution</li>
                        <li>Make informed decisions about where to spend their money</li>
                    </ul>
                    
                    <p>For businesses, we offer:</p>
                    <ul>
                        <li>Accreditation programs to build credibility</li>
                        <li>Tools to manage and respond to customer feedback</li>
                        <li>Increased visibility to potential customers</li>
                        <li>Resources for business improvement</li>
                    </ul>
                </section>

                <section class="about-section">
                    <h2>Our Standards</h2>
                    <p>P.I.M.P maintains high standards for business accreditation. Accredited businesses must:</p>
                    <ul>
                        <li>Operate with integrity and transparency</li>
                        <li>Honor their commitments to customers</li>
                        <li>Respond promptly to customer concerns</li>
                        <li>Maintain proper licensing and insurance</li>
                        <li>Advertise honestly and accurately</li>
                    </ul>
                </section>

                <section class="about-section">
                    <h2>Contact Us</h2>
                    <p>Have questions or need assistance? We're here to help.</p>
                    <p><strong>Email:</strong> info@pimp.org</p>
                    <p><strong>Phone:</strong> 1-800-PIMP-HELP</p>
                    <p><strong>Hours:</strong> Monday-Friday, 8:00 AM - 6:00 PM EST</p>
                </section>
            </div>
        </div>
    </div>

    <?php 
    /*
    echo Footer([
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
    ]); 
    */
    ?>
</body>
    <style>
        .about-page {
            padding: var(--spacing-8) 0;
            min-height: 60vh;
        }
        
        .about-content {
            max-width: 50rem;
            margin: var(--spacing-6) auto;
        }
        
        .about-content h1 {
            font-size: var(--font-size-4xl);
            font-weight: var(--font-weight-bold);
            color: var(--text-primary);
            margin: 0 0 var(--spacing-8) 0;
            text-align: center;
        }
        
        .about-section {
            margin-bottom: var(--spacing-8);
            padding: var(--spacing-6);
            background-color: white;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
        }
        
        .about-section h2 {
            font-size: var(--font-size-2xl);
            font-weight: var(--font-weight-semibold);
            color: var(--text-primary);
            margin: 0 0 var(--spacing-4) 0;
        }
        
        .about-section p {
            color: var(--text-secondary);
            font-size: var(--font-size-base);
            line-height: var(--line-height-relaxed);
            margin: 0 0 var(--spacing-3) 0;
        }
        
        .about-section p:last-child {
            margin-bottom: 0;
        }
        
        .about-section ul {
            margin: var(--spacing-3) 0;
            padding-left: var(--spacing-6);
        }
        
        .about-section li {
            color: var(--text-secondary);
            font-size: var(--font-size-base);
            line-height: var(--line-height-relaxed);
            margin-bottom: var(--spacing-2);
        }
    </style>
</body>
</html>
