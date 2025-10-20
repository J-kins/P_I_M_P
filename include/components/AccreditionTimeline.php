<?php
/**
 * BBB Accreditation Timeline Component
 * 
 * @param array $timelineData Timeline events
 * @return string HTML output
 */
function bbb_accreditation_timeline(array $timelineData = []): string {
    $defaultData = [
        [
            'year' => '2015',
            'event' => 'Business Started',
            'description' => 'Company founded and began operations'
        ],
        [
            'year' => '2016',
            'event' => 'First BBB Accreditation',
            'description' => 'Achieved initial BBB Accreditation'
        ],
        [
            'year' => '2018',
            'event' => 'A+ Rating Achieved',
            'description' => 'Maintained A+ rating for 2 consecutive years'
        ],
        [
            'year' => '2020',
            'event' => 'Expanded Services',
            'description' => 'Added new service lines and locations'
        ],
        [
            'year' => '2024',
            'event' => 'Current Status',
            'description' => 'Maintaining A+ rating with excellent customer feedback'
        ]
    ];
    
    $data = empty($timelineData) ? $defaultData : $timelineData;
    
    ob_start(); ?>
    <div class="bbb-accreditation-timeline">
        <h4>BBB Accreditation Timeline</h4>
        <div class="timeline">
            <?php foreach ($data as $index => $event): ?>
            <div class="timeline-item <?= $index === count($data) - 1 ? 'current' : '' ?>">
                <div class="timeline-marker"></div>
                <div class="timeline-content">
                    <div class="timeline-year"><?= htmlspecialchars($event['year']) ?></div>
                    <div class="timeline-event"><?= htmlspecialchars($event['event']) ?></div>
                    <div class="timeline-description"><?= htmlspecialchars($event['description']) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php return ob_get_clean();
}
?>
