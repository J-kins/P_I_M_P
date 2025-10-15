<?php
/**
 * P.I.M.P Leave a Review Page
 * Form for submitting business reviews
 */

require_once __DIR__ . '/../../../components/organisms/Navigation.php';
require_once __DIR__ . '/../../../components/organisms/Footer.php';
require_once __DIR__ . '/../../../components/molecules/Breadcrumb.php';
require_once __DIR__ . '/../../../components/atoms/Button.php';
require_once __DIR__ . '/../../../components/atoms/Input.php';

$businessName = $_GET['business'] ?? '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave a Review - P.I.M.P</title>
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

    <div class="review-page">
        <div class="container">
            <?php echo Breadcrumb([
                'items' => [
                    ['label' => 'Home', 'url' => '/'],
                    ['label' => 'Leave a Review', 'url' => '#']
                ]
            ]); ?>

            <div class="review-page__header">
                <h1>Leave a Review</h1>
                <p>Share your experience with others. Your review helps consumers make informed decisions.</p>
            </div>

            <div class="review-page__content">
                <form class="review-form" method="POST" action="/submit-review">
                    <section class="form-section">
                        <h2>Business Information</h2>
                        
                        <div class="form-field">
                            <label for="businessName">Business Name *</label>
                            <?php echo Input([
                                'id' => 'businessName',
                                'name' => 'businessName',
                                'type' => 'text',
                                'value' => $businessName,
                                'required' => true
                            ]); ?>
                        </div>
                    </section>

                    <section class="form-section">
                        <h2>Your Rating</h2>
                        
                        <div class="form-field">
                            <label>Overall Rating *</label>
                            <div class="star-rating-input">
                                <input type="radio" name="rating" value="5" id="star5" required />
                                <label for="star5">★</label>
                                <input type="radio" name="rating" value="4" id="star4" />
                                <label for="star4">★</label>
                                <input type="radio" name="rating" value="3" id="star3" />
                                <label for="star3">★</label>
                                <input type="radio" name="rating" value="2" id="star2" />
                                <label for="star2">★</label>
                                <input type="radio" name="rating" value="1" id="star1" />
                                <label for="star1">★</label>
                            </div>
                        </div>
                    </section>

                    <section class="form-section">
                        <h2>Your Review</h2>
                        
                        <div class="form-field">
                            <label for="reviewTitle">Review Title *</label>
                            <?php echo Input([
                                'id' => 'reviewTitle',
                                'name' => 'reviewTitle',
                                'type' => 'text',
                                'placeholder' => 'Summarize your experience',
                                'required' => true
                            ]); ?>
                        </div>
                        
                        <div class="form-field">
                            <label for="reviewContent">Your Experience *</label>
                            <textarea 
                                id="reviewContent" 
                                name="reviewContent" 
                                class="input" 
                                rows="8" 
                                required
                                placeholder="Tell us about your experience with this business..."
                            ></textarea>
                        </div>
                    </section>

                    <section class="form-section">
                        <h2>Your Information</h2>
                        
                        <div class="form-row">
                            <div class="form-field">
                                <label for="reviewerName">Your Name *</label>
                                <?php echo Input([
                                    'id' => 'reviewerName',
                                    'name' => 'reviewerName',
                                    'type' => 'text',
                                    'required' => true
                                ]); ?>
                            </div>
                            
                            <div class="form-field">
                                <label for="reviewerEmail">Your Email *</label>
                                <?php echo Input([
                                    'id' => 'reviewerEmail',
                                    'name' => 'reviewerEmail',
                                    'type' => 'email',
                                    'required' => true
                                ]); ?>
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <label>
                                <input type="checkbox" name="displayName" value="1" checked />
                                Display my name publicly with this review
                            </label>
                        </div>
                    </section>

                    <div class="form-actions">
                        <?php echo Button([
                            'text' => 'Submit Review',
                            'type' => 'submit',
                            'variant' => 'primary',
                            'size' => 'lg'
                        ]); ?>
                        <?php echo Button([
                            'text' => 'Cancel',
                            'url' => '/',
                            'variant' => 'secondary',
                            'size' => 'lg'
                        ]); ?>
                    </div>
                </form>
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
