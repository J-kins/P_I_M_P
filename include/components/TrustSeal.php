<?php
/**
 * BBB Trust Seal Component
 * 
 * @param string $rating BBB rating
 * @param bool $accredited Whether business is accredited
 * @param string $size Size variant (sm, md, lg)
 * @return string HTML output
 */
function bbb_trust_seal(string $rating = 'A+', bool $accredited = true, string $size = 'md'): string {
    ob_start(); ?>
    <div class="bbb-trust-seal bbb-seal-<?= htmlspecialchars($size) ?>">
        <div class="bbb-seal-header">
            <div class="bbb-seal-logo">
                <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
                    <circle cx="20" cy="20" r="18" fill="#8a5cf5" stroke="#fff" stroke-width="2"/>
                    <text x="20" y="25" text-anchor="middle" fill="white" font-weight="bold" font-size="14">BBB</text>
                </svg>
            </div>
            <div class="bbb-seal-info">
                <div class="bbb-seal-rating bbb-rating-<?= strtolower($rating) ?>">
                    <?= htmlspecialchars($rating) ?>
                </div>
                <?php if ($accredited): ?>
                <div class="bbb-seal-accredited">Accredited</div>
                <?php endif; ?>
            </div>
        </div>
        <div class="bbb-seal-footer">
            <span class="bbb-seal-text">Business Profile</span>
        </div>
    </div>
    <?php return ob_get_clean();
}
?>

<?php
/**
 * BBB Business Verification Badge Component
 * 
 * @param array $verificationData Verification information
 * @return string HTML output
 */
function bbb_verification_badge(array $verificationData = []): string {
    $defaultData = [
        'verified' => true,
        'since' => '2018',
        'owner' => 'John Smith',
        'location' => '123 Main St, City, ST 12345',
        'phone' => '(555) 123-4567',
        'email' => 'verified',
        'website' => 'verified'
    ];
    
    $data = array_merge($defaultData, $verificationData);
    
    ob_start(); ?>
    <div class="bbb-verification-badge">
        <div class="verification-header">
            <div class="verification-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" 
                          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <h4>BBB Verified Business</h4>
        </div>
        
        <div class="verification-details">
            <div class="verification-item">
                <span class="label">Business Owner:</span>
                <span class="value"><?= htmlspecialchars($data['owner']) ?></span>
            </div>
            <div class="verification-item">
                <span class="label">Location:</span>
                <span class="value"><?= htmlspecialchars($data['location']) ?></span>
            </div>
            <div class="verification-item">
                <span class="label">Phone:</span>
                <span class="value verified"><?= htmlspecialchars($data['phone']) ?></span>
            </div>
            <?php if ($data['email'] === 'verified'): ?>
            <div class="verification-item">
                <span class="label">Email:</span>
                <span class="value verified">Verified</span>
            </div>
            <?php endif; ?>
            <?php if ($data['website'] === 'verified'): ?>
            <div class="verification-item">
                <span class="label">Website:</span>
                <span class="value verified">Verified</span>
            </div>
            <?php endif; ?>
            <div class="verification-item">
                <span class="label">BBB Member Since:</span>
                <span class="value"><?= htmlspecialchars($data['since']) ?></span>
            </div>
        </div>
    </div>
    <?php return ob_get_clean();
}
?>
