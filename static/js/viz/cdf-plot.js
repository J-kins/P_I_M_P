// D3.js CDF Plot Visualization
// Uses SVG template at view/viz/cdf-plot.svg
// Integratable with backend (AJAX/fetch for data)

document.addEventListener('DOMContentLoaded', function() {
    const svg = d3.select('#cdf-plot-svg');
    if (!svg.node()) return;

    fetch('/api/cdf-plot-data')
        .then(res => res.json())
        .then(data => renderCDF(svg, data));

    function renderCDF(svg, data) {
        svg.select('.title').text(data.title || 'CDF Plot');
        // Y Ticks
        const yGrid = svg.select('.y-grid-labels');
        yGrid.selectAll('g').remove();
        (data.yTicks || []).forEach(tick => {
            yGrid.append('line')
                .attr('class', 'grid-line')
                .attr('x1', 100).attr('y1', tick.y)
                .attr('x2', 700).attr('y2', tick.y);
            yGrid.append('text')
                .attr('class', 'label')
                .attr('x', 90).attr('y', tick.y + 5)
                .text(tick.label);
        });
        // CDF Curve
        svg.select('.cdf-curve').attr('d', data.cdfCurvePath);
        // Empirical CDF (optional)
        if (data.cdfStepPath) svg.select('.cdf-step').attr('d', data.cdfStepPath);
        // Guides
        if (data.selectedX && data.selectedY) {
            svg.selectAll('.guide-line').remove();
            svg.append('line')
                .attr('class', 'guide-line')
                .attr('x1', data.selectedX).attr('y1', 500)
                .attr('x2', data.selectedX).attr('y2', data.selectedY);
            svg.append('line')
                .attr('class', 'guide-line')
                .attr('x1', 100).attr('y1', data.selectedY)
                .attr('x2', data.selectedX).attr('y2', data.selectedY);
            svg.append('text')
                .attr('class', 'label')
                .attr('x', data.selectedX + 40)
                .attr('y', data.selectedY - 10)
                .text(data.selectedProbability);
        }
    }
});
