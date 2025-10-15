<?php
/**
 * P.I.M.P File a Complaint Page
 * Form for filing complaints against businesses
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
    <title>File a Complaint - P.I.M.P</title>
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

    <div class="complaint-page">
        <div class="container">
            <?php echo Breadcrumb([
                'items' => [
                    ['label' => 'Home', 'url' => '/'],
                    ['label' => 'File a Complaint', 'url' => '#']
                ]
            ]); ?>

            <div class="complaint-page__header">
                <h1>File a Complaint</h1>
                <p>P.I.M.P is here to help. We'll guide you through the complaint process and work to resolve your issue.</p>
            </div>

            <div class="complaint-page__content">
                <form class="complaint-form" method="POST" action="/submit-complaint">
                    <section class="form-section">
                        <h2>Your Information</h2>
                        
                        <div class="form-row">
                            <div class="form-field">
                                <label for="firstName">First Name *</label>
                                <?php echo Input([
                                    'id' => 'firstName',
                                    'name' => 'firstName',
                                    'type' => 'text',
                                    'required' => true
                                ]); ?>
                            </div>
                            
                            <div class="form-field">
                                <label for="lastName">Last Name *</label>
                                <?php echo Input([
                                    'id' => 'lastName',
                                    'name' => 'lastName',
                                    'type' => 'text',
                                    'required' => true
                                ]); ?>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-field">
                                <label for="email">Email *</label>
                                <?php echo Input([
                                    'id' => 'email',
                                    'name' => 'email',
                                    'type' => 'email',
                                    'required' => true
                                ]); ?>
                            </div>
                            
                            <div class="form-field">
                                <label for="phone">Phone</label>
                                <?php echo Input([
                                    'id' => 'phone',
                                    'name' => 'phone',
                                    'type' => 'tel'
                                ]); ?>
                            </div>
                        </div>
                    </section>

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
                        
                        <div class="form-field">
                            <label for="businessAddress">Business Address</label>
                            <?php echo Input([
                                'id' => 'businessAddress',
                                'name' => 'businessAddress',
                                'type' => 'text'
                            ]); ?>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-field">
                                <label for="businessPhone">Business Phone</label>
                                <?php echo Input([
                                    'id' => 'businessPhone',
                                    'name' => 'businessPhone',
                                    'type' => 'tel'
                                ]); ?>
                            </div>
                            
                            <div class="form-field">
                                <label for="businessWebsite">Business Website</label>
                                <?php echo Input([
                                    'id' => 'businessWebsite',
                                    'name' => 'businessWebsite',
                                    'type' => 'url'
                                ]); ?>
                            </div>
                        </div>
                    </section>

                    <section class="form-section">
                        <h2>Complaint Details</h2>
                        
                        <div class="form-field">
                            <label for="complaintType">Type of Complaint *</label>
                            <select id="complaintType" name="complaintType" class="input" required>
                                <option value="">Select a type</option>
                                <option value="product">Product Quality</option>
                                <option value="service">Service Quality</option>
                                <option value="billing">Billing/Payment Issue</option>
                                <option value="delivery">Delivery Problem</option>
                                <option value="refund">Refund/Return Issue</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div class="form-field">
                            <label for="transactionDate">Date of Transaction</label>
                            <?php echo Input([
                                'id' => 'transactionDate',
                                'name' => 'transactionDate',
                                'type' => 'date'
                            ]); ?>
                        </div>
                        
                        <div class="form-field">
                            <label for="transactionAmount">Transaction Amount</label>
                            <?php echo Input([
                                'id' => 'transactionAmount',
                                'name' => 'transactionAmount',
                                'type' => 'text',
                                'placeholder' => '$0.00'
                            ]); ?>
                        </div>
                        
                        <div class="form-field">
                            <label for="complaintDescription">Describe Your Complaint *</label>
                            <textarea 
                                id="complaintDescription" 
                                name="complaintDescription" 
                                class="input" 
                                rows="6" 
                                required
                                placeholder="Please provide detailed information about your complaint..."
                            ></textarea>
                        </div>
                        
                        <div class="form-field">
                            <label for="desiredResolution">Desired Resolution *</label>
                            <textarea 
                                id="desiredResolution" 
                                name="desiredResolution" 
                                class="input" 
                                rows="4" 
                                required
                                placeholder="What would you like to see happen to resolve this issue?"
                            ></textarea>
                        </div>
                    </section>

                    <div class="form-actions">
                        <?php echo Button([
                            'text' => 'Submit Complaint',
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
