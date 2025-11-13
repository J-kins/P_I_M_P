// D3.js Density Plot Visualization
// Uses SVG template at view/viz/density-plot.svg
// Integratable with backend (AJAX/fetch for data)

document.addEventListener('DOMContentLoaded', function() {
    const svg = d3.select('#density-plot-svg');
    if (!svg.node()) return;

    fetch('/api/density-plot-data')
        .then(res => res.json())
        .then(data => renderDensity(svg, data));

    function renderDensity(svg, data) {
        svg.select('.title').text(data.title || 'Density Plot');
        // Y Ticks
        const yGrid = svg.select('.y-grid-labels');
        yGrid.selectAll('g').remove();
        (data.yTicks || []).forEach(tick => {
            yGrid.append('line')
                .attr('class', 'grid-line')
                .attr('x1', 100).attr('y1', tick.y)
                .attr('x2', 700).attr('y2', tick.y);
            yGrid.append('text')
                .attr('class', 'group-label')
                .attr('x', 90).attr('y', tick.y + 5)
                .text(tick.label);
        });
        // Density Curves & Areas
        const curvesG = svg.select('.density-curves');
        curvesG.selectAll('*').remove();
        (data.groups || []).forEach(group => {
            curvesG.append('path')
                .attr('class', 'density-area')
                .attr('d', group.areaPath)
                .attr('fill', group.areaColor);
            curvesG.append('path')
                .attr('class', 'density-curve')
                .attr('d', group.curvePath)
                .attr('stroke', group.curveColor);
            curvesG.append('text')
                .attr('class', 'group-label')
                .attr('x', group.labelX)
                .attr('y', 80)
                .text(group.label);
        });
    }
});
