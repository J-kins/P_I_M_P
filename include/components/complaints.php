<?php
/**
 * BBB Response Time Indicator Component
 * 
 * @param array $responseData Response time statistics
 * @return string HTML output
 */
function bbb_response_time_indicator(array $responseData = []): string {
    $defaultData = [
        'averageResponse' => '2.5 hours',
        'responseRate' => 95,
        'totalComplaints' => 42,
        'resolvedComplaints' => 40
    ];
    
    $data = array_merge($defaultData, $responseData);
    $resolutionRate = ($data['resolvedComplaints'] / $data['totalComplaints']) * 100;
    
    ob_start(); ?>
    <div class="bbb-response-indicator">
        <h4>Business Response</h4>
        
        <div class="response-stats">
            <div class="response-stat">
                <div class="stat-value"><?= $data['averageResponse'] ?></div>
                <div class="stat-label">Average Response Time</div>
            </div>
            
            <div class="response-stat">
                <div class="stat-value"><?= $data['responseRate'] ?>%</div>
                <div class="stat-label">Response Rate</div>
            </div>
            
            <div class="response-stat">
                <div class="stat-value"><?= number_format($resolutionRate, 1) ?>%</div>
                <div class="stat-label">Complaints Resolved</div>
            </div>
        </div>
        
        <div class="response-meter">
            <div class="meter-labels">
                <span>Slow</span>
                <span>Fast</span>
            </div>
            <div class="meter-bar">
                <div class="meter-fill" style="width: <?= min($data['responseRate'], 100) ?>%"></div>
            </div>
        </div>
        
        <div class="response-details">
            <p>This business typically responds to complaints within <?= $data['averageResponse'] ?>.</p>
            <p><?= $data['resolvedComplaints'] ?> of <?= $data['totalComplaints'] ?> complaints have been resolved.</p>
        </div>
    </div>
    <?php return ob_get_clean();
}
?>
