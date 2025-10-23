// D3.js Bar Chart Integration
// Uses SVG template at view/viz/bar-chart.svg
// Integratable with backend (AJAX/fetch for data)

document.addEventListener('DOMContentLoaded', function() {
    const svg = d3.select('#bar-chart-svg');
    if (!svg.node()) return;

    // Example: Fetch data from backend
    fetch('/api/bar-chart-data')
        .then(res => res.json())
        .then(data => renderBarChart(svg, data));

    function renderBarChart(svg, data) {
        // Set chart title
        svg.select('.title').text(data.title || 'Bar Chart');

        // Y Ticks
        const yTicks = data.yTicks || [];
        const yGrid = svg.select('.y-grid-labels');
        yGrid.selectAll('g').remove();
        yTicks.forEach(tick => {
            yGrid.append('line')
                .attr('class', 'grid-line')
                .attr('x1', 100).attr('y1', tick.y)
                .attr('x2', 700).attr('y2', tick.y);
            yGrid.append('text')
                .attr('class', 'y-label')
                .attr('x', 90).attr('y', tick.y + 5)
                .text(tick.label);
        });

        // Bars
        const barsG = svg.select('.bars-xlabels');
        barsG.selectAll('g.bar').remove();
        data.bars.forEach(bar => {
            const g = barsG.append('g').attr('class', 'bar');
            g.append('rect')
                .attr('x', bar.x)
                .attr('y', bar.y)
                .attr('width', bar.width)
                .attr('height', bar.height)
                .attr('fill', bar.color || '#2196f3');
            g.append('text')
                .attr('x', bar.x + bar.width / 2)
                .attr('y', 520)
                .attr('class', 'x-label')
                .text(bar.label);
        });
    }
});
