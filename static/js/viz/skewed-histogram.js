// D3.js Skewed Histogram Visualization
// Uses SVG template at view/viz/skewed-histogram.svg
// Integratable with backend (AJAX/fetch for data)

document.addEventListener('DOMContentLoaded', function() {
    const svg = d3.select('#skewed-histogram-svg');
    if (!svg.node()) return;

    fetch('/api/skewed-histogram-data')
        .then(res => res.json())
        .then(data => renderHistogram(svg, data));

    function renderHistogram(svg, data) {
        svg.select('.title').text(data.title || 'Skewed Histogram');
        // Y Ticks
        const yGrid = svg.select('.y-grid-labels');
        yGrid.selectAll('g').remove();
        (data.yTicks || []).forEach(tick => {
            yGrid.append('line')
                .attr('class', 'grid-line')
                .attr('x1', 100).attr('y1', tick.y)
                .attr('x2', 700).attr('y2', tick.y);
            yGrid.append('text')
                .attr('class', 'bar-label')
                .attr('x', 90).attr('y', tick.y + 5)
                .text(tick.label);
        });
        // Bars
        const barsG = svg.select('.histogram-bars');
        barsG.selectAll('rect').remove();
        (data.bins || []).forEach(bin => {
            barsG.append('rect')
                .attr('class', 'bar')
                .attr('x', bin.x)
                .attr('y', bin.y)
                .attr('width', bin.width)
                .attr('height', bin.height)
                .attr('data-bin', bin.index)
                .attr('data-range', bin.range)
                .attr('data-count', bin.count)
                .attr('fill', bin.color || '#ff9800');
        });
        // Bin Labels
        const xLabels = svg.select('.x-bin-labels');
        xLabels.selectAll('text').remove();
        (data.bins || []).forEach(bin => {
            xLabels.append('text')
                .attr('class', 'bar-label')
                .attr('x', bin.x + bin.width / 2)
                .attr('y', 520)
                .text(bin.range);
        });
        // Normal Overlay
        if (data.normalCurvePath) {
            svg.select('.normal-overlay').attr('d', data.normalCurvePath);
        }
    }
});
