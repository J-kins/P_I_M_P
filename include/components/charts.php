<?php
/**
 * D3.js Rating Distribution Chart Component
 * 
 * @param array $ratingData Array of rating counts [1 => count, 2 => count, ...]
 * @param string $containerId DOM element ID for the chart
 * @return string HTML output
 */
function rating_distribution_chart(array $ratingData = [], string $containerId = 'rating-distribution-chart'): string {
    // Default data if none provided
    $defaultData = [
        5 => 85,
        4 => 25,
        3 => 10,
        2 => 5,
        1 => 2
    ];
    
    $ratingData = empty($ratingData) ? $defaultData : $ratingData;
    $totalReviews = array_sum($ratingData);
    
    ob_start(); ?>
    <div class="rating-distribution-chart" id="<?= htmlspecialchars($containerId) ?>">
        <h4>Rating Distribution</h4>
        <div class="chart-container">
            <svg width="100%" height="120"></svg>
        </div>
        <div class="chart-legend">
            <?php for ($i = 5; $i >= 1; $i--): ?>
                <?php $percentage = $totalReviews > 0 ? ($ratingData[$i] / $totalReviews) * 100 : 0; ?>
                <div class="legend-item">
                    <span class="stars"><?= str_repeat('★', $i) ?></span>
                    <div class="bar-container">
                        <div class="bar" style="width: <?= $percentage ?>%"></div>
                    </div>
                    <span class="count"><?= $ratingData[$i] ?></span>
                </div>
            <?php endfor; ?>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const ratingData = <?= json_encode($ratingData) ?>;
        const totalReviews = <?= $totalReviews ?>;
        
        // D3.js Bar Chart
        const svg = d3.select('#<?= $containerId ?> svg');
        const margin = {top: 20, right: 30, bottom: 40, left: 40};
        const width = svg.node().getBoundingClientRect().width - margin.left - margin.right;
        const height = 120 - margin.top - margin.bottom;
        
        const g = svg.append('g').attr('transform', `translate(${margin.left},${margin.top})`);
        
        const x = d3.scaleBand()
            .domain([5, 4, 3, 2, 1])
            .range([0, width])
            .padding(0.1);
            
        const y = d3.scaleLinear()
            .domain([0, d3.max(Object.values(ratingData))])
            .range([height, 0]);
        
        // Add bars
        g.selectAll('.bar')
            .data([5, 4, 3, 2, 1])
            .enter().append('rect')
            .attr('class', 'bar')
            .attr('x', d => x(d))
            .attr('y', d => y(ratingData[d]))
            .attr('width', x.bandwidth())
            .attr('height', d => height - y(ratingData[d]))
            .attr('fill', d => {
                const colors = {5: '#28a745', 4: '#8fc93a', 3: '#ffc107', 2: '#fd7e14', 1: '#dc3545'};
                return colors[d];
            });
        
        // Add labels
        g.selectAll('.label')
            .data([5, 4, 3, 2, 1])
            .enter().append('text')
            .attr('class', 'label')
            .attr('x', d => x(d) + x.bandwidth() / 2)
            .attr('y', d => y(ratingData[d]) - 5)
            .attr('text-anchor', 'middle')
            .text(d => ratingData[d]);
        
        // Add x-axis
        g.append('g')
            .attr('transform', `translate(0,${height})`)
            .call(d3.axisBottom(x))
            .selectAll('text')
            .text(d => d + '★');
    });
    </script>
    <?php return ob_get_clean();
}
?>

<?php
/**
 * D3.js Complaint Timeline Chart Component
 * 
 * @param array $complaintData Array of monthly complaint counts
 * @param string $containerId DOM element ID for the chart
 * @return string HTML output
 */
function complaint_timeline_chart(array $complaintData = [], string $containerId = 'complaint-timeline-chart'): string {
    // Generate default data if none provided
    if (empty($complaintData)) {
        $complaintData = [];
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        foreach ($months as $month) {
            $complaintData[$month] = rand(0, 8);
        }
    }
    
    ob_start(); ?>
    <div class="complaint-timeline-chart" id="<?= htmlspecialchars($containerId) ?>">
        <h4>Complaint History (12 Months)</h4>
        <div class="chart-container">
            <svg width="100%" height="200"></svg>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const complaintData = <?= json_encode(array_values($complaintData)) ?>;
        const months = <?= json_encode(array_keys($complaintData)) ?>;
        
        const svg = d3.select('#<?= $containerId ?> svg');
        const margin = {top: 20, right: 30, bottom: 30, left: 40};
        const width = svg.node().getBoundingClientRect().width - margin.left - margin.right;
        const height = 200 - margin.top - margin.bottom;
        
        const g = svg.append('g').attr('transform', `translate(${margin.left},${margin.top})`);
        
        const x = d3.scaleBand()
            .domain(months)
            .range([0, width])
            .padding(0.1);
            
        const y = d3.scaleLinear()
            .domain([0, d3.max(complaintData)])
            .range([height, 0]);
        
        // Add line
        const line = d3.line()
            .x((d, i) => x(months[i]) + x.bandwidth() / 2)
            .y(d => y(d))
            .curve(d3.curveMonotoneX);
        
        g.append('path')
            .datum(complaintData)
            .attr('class', 'complaint-line')
            .attr('d', line)
            .attr('fill', 'none')
            .attr('stroke', '#e74c3c')
            .attr('stroke-width', 2);
        
        // Add points
        g.selectAll('.complaint-point')
            .data(complaintData)
            .enter().append('circle')
            .attr('class', 'complaint-point')
            .attr('cx', (d, i) => x(months[i]) + x.bandwidth() / 2)
            .attr('cy', d => y(d))
            .attr('r', 4)
            .attr('fill', '#e74c3c')
            .append('title')
            .text((d, i) => `${months[i]}: ${d} complaints`);
        
        // Add axes
        g.append('g')
            .attr('transform', `translate(0,${height})`)
            .call(d3.axisBottom(x));
            
        g.append('g')
            .call(d3.axisLeft(y));
    });
    </script>
    <?php return ob_get_clean();
}
?>

<?php
/**
 * BBB Trust Meter Component (D3.js Gauge)
 * 
 * @param int $score Trust score (0-100)
 * @param string $rating Letter rating (A+ through F)
 * @param string $containerId DOM element ID
 * @return string HTML output
 */
function bbb_trust_meter(int $score = 85, string $rating = 'A+', string $containerId = 'trust-meter'): string {
    ob_start(); ?>
    <div class="bbb-trust-meter" id="<?= htmlspecialchars($containerId) ?>">
        <h4>BBB Trust Meter</h4>
        <div class="trust-meter-container">
            <svg width="200" height="120" class="trust-meter-svg"></svg>
            <div class="trust-meter-info">
                <div class="trust-score"><?= $score ?></div>
                <div class="trust-rating bbb-rating-<?= strtolower($rating) ?>"><?= $rating ?></div>
                <div class="trust-label">Trust Score</div>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const score = <?= $score ?>;
        const rating = '<?= $rating ?>';
        
        const svg = d3.select('#<?= $containerId ?> .trust-meter-svg');
        const width = 200;
        const height = 120;
        const radius = Math.min(width, height) / 2;
        
        const g = svg.append('g').attr('transform', `translate(${width / 2},${height})`);
        
        // Background arc
        const backgroundArc = d3.arc()
            .innerRadius(radius - 10)
            .outerRadius(radius)
            .startAngle(-Math.PI / 2)
            .endAngle(Math.PI / 2);
        
        g.append('path')
            .attr('d', backgroundArc)
            .attr('fill', '#e9ecef');
        
        // Score arc
        const scoreArc = d3.arc()
            .innerRadius(radius - 10)
            .outerRadius(radius)
            .startAngle(-Math.PI / 2)
            .endAngle(-Math.PI / 2 + (Math.PI * score / 100));
        
        g.append('path')
            .attr('d', scoreArc)
            .attr('fill', () => {
                if (score >= 80) return '#28a745';
                if (score >= 60) return '#ffc107';
                return '#dc3545';
            });
        
        // Add needle
        const needleAngle = -Math.PI / 2 + (Math.PI * score / 100);
        const needleLength = radius - 15;
        
        g.append('line')
            .attr('x1', 0)
            .attr('y1', 0)
            .attr('x2', needleLength * Math.cos(needleAngle))
            .attr('y2', needleLength * Math.sin(needleAngle))
            .attr('stroke', '#333')
            .attr('stroke-width', 2);
        
        // Add center circle
        g.append('circle')
            .attr('cx', 0)
            .attr('cy', 0)
            .attr('r', 5)
            .attr('fill', '#333');
    });
    </script>
    <?php return ob_get_clean();
}
?>

<?php
/**
 * BBB Customer Sentiment Analysis Component (D3.js Word Cloud)
 * 
 * @param array $sentimentData Word frequency data
 * @param string $containerId DOM element ID
 * @return string HTML output
 */
function bbb_sentiment_analysis(array $sentimentData = [], string $containerId = 'sentiment-analysis'): string {
    // Default sentiment words
    if (empty($sentimentData)) {
        $sentimentData = [
            ['text' => 'professional', 'size' => 40],
            ['text' => 'reliable', 'size' => 35],
            ['text' => 'quality', 'size' => 30],
            ['text' => 'friendly', 'size' => 28],
            ['text' => 'prompt', 'size' => 25],
            ['text' => 'excellent', 'size' => 22],
            ['text' => 'satisfied', 'size' => 20],
            ['text' => 'recommend', 'size' => 18],
            ['text' => 'trustworthy', 'size' => 16],
            ['text' => 'responsive', 'size' => 14]
        ];
    }
    
    ob_start(); ?>
    <div class="bbb-sentiment-analysis" id="<?= htmlspecialchars($containerId) ?>">
        <h4>Customer Sentiment</h4>
        <p>Most common words in customer reviews</p>
        <div class="word-cloud-container">
            <svg width="100%" height="200"></svg>
        </div>
    </div>
    
    <script src="https://d3js.org/d3.v7.min.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/holtzy/D3-graph-gallery@master/LIB/d3.layout.cloud.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const sentimentData = <?= json_encode($sentimentData) ?>;
        
        const svg = d3.select('#<?= $containerId ?> svg');
        const width = svg.node().getBoundingClientRect().width;
        const height = 200;
        
        // Create word cloud
        const layout = d3.layout.cloud()
            .size([width, height])
            .words(sentimentData.map(d => ({...d})))
            .padding(5)
            .rotate(0)
            .fontSize(d => d.size)
            .on('end', draw);
        
        layout.start();
        
        function draw(words) {
            svg.append('g')
                .attr('transform', `translate(${width / 2},${height / 2})`)
                .selectAll('text')
                .data(words)
                .enter().append('text')
                .style('font-size', d => d.size + 'px')
                .style('fill', (d, i) => d3.schemeCategory10[i % 10])
                .attr('text-anchor', 'middle')
                .attr('transform', d => `translate(${d.x},${d.y})rotate(${d.rotate})`)
                .text(d => d.text);
        }
    });
    </script>
    <?php return ob_get_clean();
}
?>


<?php
/**
 * D3.js Chart Components for Admin Dashboard
 */

/**
 * Bar Chart Component
 */
function d3_bar_chart(array $params = []): string {
    $id = $params['id'] ?? 'bar-chart-' . uniqid();
    $data = $params['data'] ?? [];
    $title = $params['title'] ?? 'Bar Chart';
    $width = $params['width'] ?? 400;
    $height = $params['height'] ?? 300;
    $color = $params['color'] ?? '#005596';
    
    ob_start(); ?>
<div class="d3-chart-container">
    <h4 class="chart-title"><?= sanitize_output($title) ?></h4>
    <div id="<?= $id ?>" class="d3-bar-chart" data-config='<?= json_encode([
        'data' => $data,
        'width' => $width,
        'height' => $height,
        'color' => $color
    ]) ?>'></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('<?= $id ?>');
    const config = JSON.parse(container.getAttribute('data-config'));
    
    // Bar chart implementation will be initialized by D3 manager
});
</script>
<?php return ob_get_clean();
}

/**
 * Line Chart Component
 */
function d3_line_chart(array $params = []): string {
    $id = $params['id'] ?? 'line-chart-' . uniqid();
    $data = $params['data'] ?? [];
    $title = $params['title'] ?? 'Line Chart';
    $width = $params['width'] ?? 400;
    $height = $params['height'] ?? 300;
    $color = $params['color'] ?? '#ff6600';
    
    ob_start(); ?>
<div class="d3-chart-container">
    <h4 class="chart-title"><?= sanitize_output($title) ?></h4>
    <div id="<?= $id ?>" class="d3-line-chart" data-config='<?= json_encode([
        'data' => $data,
        'width' => $width,
        'height' => $height,
        'color' => $color
    ]) ?>'></div>
</div>
<?php return ob_get_clean();
}

/**
 * Pie Chart Component
 */
function d3_pie_chart(array $params = []): string {
    $id = $params['id'] ?? 'pie-chart-' . uniqid();
    $data = $params['data'] ?? [];
    $title = $params['title'] ?? 'Pie Chart';
    $width = $params['width'] ?? 300;
    $height = $params['height'] ?? 300;
    $colors = $params['colors'] ?? ['#005596', '#ff6600', '#28a745', '#dc3545', '#6c757d'];
    
    ob_start(); ?>
<div class="d3-chart-container">
    <h4 class="chart-title"><?= sanitize_output($title) ?></h4>
    <div id="<?= $id ?>" class="d3-pie-chart" data-config='<?= json_encode([
        'data' => $data,
        'width' => $width,
        'height' => $height,
        'colors' => $colors
    ]) ?>'></div>
</div>
<?php return ob_get_clean();
}

/**
 * Geo Map Component
 */
function d3_geo_map(array $params = []): string {
    $id = $params['id'] ?? 'geo-map-' . uniqid();
    $data = $params['data'] ?? [];
    $title = $params['title'] ?? 'Regional Distribution';
    $width = $params['width'] ?? 600;
    $height = $params['height'] ?? 400;
    
    ob_start(); ?>
<div class="d3-chart-container">
    <h4 class="chart-title"><?= sanitize_output($title) ?></h4>
    <div id="<?= $id ?>" class="d3-geo-map" data-config='<?= json_encode([
        'data' => $data,
        'width' => $width,
        'height' => $height
    ]) ?>'></div>
</div>
<?php return ob_get_clean();
}
?>


<?php
/**
 * Admin Stat Card with Icons
 */
function admin_stat_card(array $params): string {
    $title = $params['title'] ?? '';
    $value = $params['value'] ?? '';
    $change = $params['change'] ?? null;
    $icon = $params['icon'] ?? 'fas fa-chart-line';
    $color = $params['color'] ?? 'blue';
    $trend = $params['trend'] ?? 'up'; // 'up' or 'down'
    
    ob_start(); ?>
<div class="admin-stat-card admin-stat-card-<?= $color ?>">
    <div class="stat-card-header">
        <h3 class="stat-card-title"><?= sanitize_output($title) ?></h3>
        <div class="stat-card-icon">
            <i class="<?= $icon ?>"></i>
        </div>
    </div>
    
    <div class="stat-card-content">
        <div class="stat-card-value"><?= sanitize_output($value) ?></div>
        
        <?php if ($change !== null): ?>
        <div class="stat-card-change stat-card-change-<?= $trend ?>">
            <i class="fas fa-arrow-<?= $trend === 'up' ? 'up' : 'down' ?>"></i>
            <span><?= abs($change) ?>%</span>
            <span class="change-label">from last month</span>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="stat-card-footer">
        <a href="#" class="stat-card-link">View details</a>
    </div>
</div>
<?php return ob_get_clean();
}

?>