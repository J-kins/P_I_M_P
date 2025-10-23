// D3.js Binomial Distribution Visualization
// Uses SVG template at view/viz/binomial-distribution.svg
// Integratable with backend (AJAX/fetch for data)

document.addEventListener('DOMContentLoaded', function() {
    const svg = d3.select('#binomial-distribution-svg');
    if (!svg.node()) return;

    fetch('/api/binomial-distribution-data')
        .then(res => res.json())
        .then(data => renderBinomial(svg, data));

    function renderBinomial(svg, data) {
        svg.select('.title').text(data.title || 'Binomial Distribution');
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
        const barsG = svg.select('.binomial-bars');
        barsG.selectAll('rect').remove();
        barsG.selectAll('text').remove();
        (data.bars || []).forEach(bar => {
            barsG.append('rect')
                .attr('class', 'bar')
                .attr('x', bar.x)
                .attr('y', bar.y)
                .attr('width', bar.width)
                .attr('height', bar.height)
                .attr('data-k', bar.k)
                .attr('data-probability', bar.probability)
                .attr('fill', bar.color || '#2196f3');
            barsG.append('text')
                .attr('class', 'bar-label')
                .attr('x', bar.x + bar.width / 2)
                .attr('y', 520)
                .text(bar.k);
        });
    }
});
