<?php
/**
 * P.I.M.P Contact Page
 * Contact form and information
 */

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - P.I.M.P</title>
    <link rel="stylesheet" href="/assets/styles/main.css">
    <link rel="stylesheet" href="/assets/styles/components.css">
    <link rel="stylesheet" href="/assets/styles/atoms.css">
    <link rel="stylesheet" href="/assets/styles/molecules.css">
    <link rel="stylesheet" href="/assets/styles/organisms.css">
    <link rel="stylesheet" href="/template/P_I_M_P/styles/pimp.css">
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

    <div class="contact-page">
        <div class="container">
            <?php 
            /*
            echo Breadcrumb([
                'items' => [
                    ['label' => 'Home', 'url' => '/'],
                    ['label' => 'Contact', 'url' => '#']
                ]
            ]); 
            */
            ?>

            <div class="contact-header">
                <h1>Contact Us</h1>
                <p>Have a question or need assistance? We're here to help.</p>
            </div>

            <div class="contact-content">
                <div class="contact-info">
                    <div class="contact-info-card">
                        <h3>📧 Email</h3>
                        <p>info@pimp.org</p>
                        <p class="contact-info-note">We typically respond within 24 hours</p>
                    </div>
                    
                    <div class="contact-info-card">
                        <h3>📞 Phone</h3>
                        <p>1-800-PIMP-HELP</p>
                        <p class="contact-info-note">Monday-Friday, 8:00 AM - 6:00 PM EST</p>
                    </div>
                    
                    <div class="contact-info-card">
                        <h3>📍 Address</h3>
                        <p>123 Business Plaza<br>Suite 500<br>New York, NY 10001</p>
                    </div>
                </div>

                <div class="contact-form-wrapper">
                    <h2>Send Us a Message</h2>
                    <form class="contact-form" method="POST" action="/submit-contact">
                        <div class="form-row">
                            <div class="form-field">
                                <label for="name">Name *</label>
                                <?php echo Input([
                                    'id' => 'name',
                                    'name' => 'name',
                                    'type' => 'text',
                                    'required' => true
                                ]); ?>
                            </div>
                            
                            <div class="form-field">
                                <label for="email">Email *</label>
                                <?php echo Input([
                                    'id' => 'email',
                                    'name' => 'email',
                                    'type' => 'email',
                                    'required' => true
                                ]); ?>
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <label for="subject">Subject *</label>
                            <?php echo Input([
                                'id' => 'subject',
                                'name' => 'subject',
                                'type' => 'text',
                                'required' => true
                            ]); ?>
                        </div>
                        
                        <div class="form-field">
                            <label for="message">Message *</label>
                            <textarea 
                                id="message" 
                                name="message" 
                                class="input" 
                                rows="6" 
                                required
                                placeholder="How can we help you?"
                            ></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <?php echo Button([
                                'text' => 'Send Message',
                                'type' => 'submit',
                                'variant' => 'primary',
                                'size' => 'lg'
                            ]); ?>
                        </div>
                    </form>
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

    <style>
        .contact-page {
            padding: var(--spacing-8) 0;
            min-height: 60vh;
        }
        
        .contact-header {
            text-align: center;
            max-width: 40rem;
            margin: var(--spacing-6) auto;
        }
        
        .contact-header h1 {
            font-size: var(--font-size-4xl);
            font-weight: var(--font-weight-bold);
            color: var(--text-primary);
            margin: 0 0 var(--spacing-3) 0;
        }
        
        .contact-header p {
            color: var(--text-secondary);
            font-size: var(--font-size-lg);
            margin: 0;
        }
        
        .contact-content {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: var(--spacing-8);
            margin-top: var(--spacing-8);
        }
        
        .contact-info {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-4);
        }
        
        .contact-info-card {
            padding: var(--spacing-5);
            background-color: white;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
        }
        
        .contact-info-card h3 {
            font-size: var(--font-size-lg);
            font-weight: var(--font-weight-semibold);
            color: var(--text-primary);
            margin: 0 0 var(--spacing-3) 0;
        }
        
        .contact-info-card p {
            color: var(--text-secondary);
            font-size: var(--font-size-base);
            margin: 0 0 var(--spacing-1) 0;
        }
        
        .contact-info-note {
            color: var(--text-muted);
            font-size: var(--font-size-sm);
            margin-top: var(--spacing-2);
        }
        
        .contact-form-wrapper {
            padding: var(--spacing-6);
            background-color: white;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
        }
        
        .contact-form-wrapper h2 {
            font-size: var(--font-size-2xl);
            font-weight: var(--font-weight-semibold);
            color: var(--text-primary);
            margin: 0 0 var(--spacing-6) 0;
        }
        
        @media (max-width: 768px) {
            .contact-content {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>
