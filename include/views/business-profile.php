<?php
/**
 * Updated Business Profile Page with D3.js Visualizations
 */

// Add these to your existing business data
$business['ratingDistribution'] = [
    5 => 85,
    4 => 25,
    3 => 10,
    2 => 5,
    1 => 2
];

$business['complaintHistory'] = [
    'Jan' => 3, 'Feb' => 2, 'Mar' => 4, 'Apr' => 1,
    'May' => 2, 'Jun' => 3, 'Jul' => 1, 'Aug' => 2,
    'Sep' => 0, 'Oct' => 1, 'Nov' => 2, 'Dec' => 1
];

$business['trustScore'] = 87;
$business['responseData'] = [
    'averageResponse' => '2.5 hours',
    'responseRate' => 95,
    'totalComplaints' => 42,
    'resolvedComplaints' => 40
];

// Add to your existing HTML after the business description section
?>
<!-- Add this section after the "About This Business" section -->
<section class="business-profile__analytics">
    <h2>Business Analytics</h2>
    
    <div class="analytics-grid">
        <div class="analytics-card">
            <?= rating_distribution_chart($business['ratingDistribution']) ?>
        </div>
        
        <div class="analytics-card">
            <?= complaint_timeline_chart($business['complaintHistory']) ?>
        </div>
        
        <div class="analytics-card">
            <?= bbb_trust_meter($business['trustScore'], 'A+') ?>
        </div>
        
        <div class="analytics-card">
            <?= bbb_response_time_indicator($business['responseData']) ?>
        </div>
        
        <div class="analytics-card">
            <?= bbb_sentiment_analysis() ?>
        </div>
        
        <div class="analytics-card">
            <?= bbb_verification_badge() ?>
        </div>
        
        <div class="analytics-card">
            <?= bbb_accreditation_timeline() ?>
        </div>
    </div>
</section>

<!-- Add D3.js library -->
<script src="https://d3js.org/d3.v7.min.js"></script>

<style>
.business-profile__analytics {
    margin-top: 3rem;
    padding-top: 2rem;
    border-top: 1px solid #e9ecef;
}

.analytics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-top: 1.5rem;
}

.analytics-card {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Chart specific styles */
.rating-distribution-chart .legend-item {
    display: flex;
    align-items: center;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.rating-distribution-chart .stars {
    width: 60px;
    color: #ffc107;
}

.rating-distribution-chart .bar-container {
    flex: 1;
    height: 8px;
    background: #e9ecef;
    margin: 0 1rem;
    border-radius: 4px;
    overflow: hidden;
}

.rating-distribution-chart .bar {
    height: 100%;
    background: #28a745;
    transition: width 0.3s ease;
}

/* Trust meter styles */
.bbb-trust-meter .trust-meter-container {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

.bbb-trust-meter .trust-meter-info {
    position: absolute;
    text-align: center;
}

.bbb-trust-meter .trust-score {
    font-size: 2rem;
    font-weight: bold;
    color: #333;
}

.bbb-trust-meter .trust-rating {
    font-size: 1.5rem;
    font-weight: bold;
    margin: 0.5rem 0;
}

.bbb-trust-meter .trust-label {
    font-size: 0.875rem;
    color: #666;
}

/* Response indicator styles */
.bbb-response-indicator .response-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.bbb-response-indicator .response-stat {
    text-align: center;
}

.bbb-response-indicator .stat-value {
    font-size: 1.25rem;
    font-weight: bold;
    color: #333;
}

.bbb-response-indicator .stat-label {
    font-size: 0.75rem;
    color: #666;
    margin-top: 0.25rem;
}

.bbb-response-indicator .meter-bar {
    height: 8px;
    background: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
    margin: 0.5rem 0;
}

.bbb-response-indicator .meter-fill {
    height: 100%;
    background: linear-gradient(90deg, #dc3545, #ffc107, #28a745);
    transition: width 0.3s ease;
}

.bbb-response-indicator .meter-labels {
    display: flex;
    justify-content: space-between;
    font-size: 0.75rem;
    color: #666;
}

/* Verification badge styles */
.bbb-verification-badge {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 1.5rem;
}

.bbb-verification-badge .verification-header {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
}

.bbb-verification-badge .verification-icon {
    color: #28a745;
    margin-right: 0.5rem;
}

.bbb-verification-badge .verification-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.bbb-verification-badge .verified {
    color: #28a745;
    font-weight: bold;
}

/* Timeline styles */
.bbb-accreditation-timeline .timeline {
    position: relative;
    padding-left: 2rem;
}

.bbb-accreditation-timeline .timeline-item {
    position: relative;
    margin-bottom: 2rem;
}

.bbb-accreditation-timeline .timeline-marker {
    position: absolute;
    left: -2rem;
    top: 0;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #6c757d;
    border: 2px solid white;
}

.bbb-accreditation-timeline .timeline-item.current .timeline-marker {
    background: #28a745;
}

.bbb-accreditation-timeline .timeline-year {
    font-weight: bold;
    color: #333;
    margin-bottom: 0.25rem;
}

.bbb-accreditation-timeline .timeline-event {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.25rem;
}

.bbb-accreditation-timeline .timeline-description {
    font-size: 0.875rem;
    color: #666;
}
</style>
