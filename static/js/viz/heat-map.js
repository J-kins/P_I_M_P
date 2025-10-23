// D3.js Heat Map Visualization
// Uses SVG template at view/viz/heat-map.svg
// Integratable with backend (AJAX/fetch for data)

document.addEventListener('DOMContentLoaded', function() {
    const svg = d3.select('#heat-map-svg');
    if (!svg.node()) return;

    fetch('/api/heat-map-data')
        .then(res => res.json())
        .then(data => renderHeatMap(svg, data));

    function renderHeatMap(svg, data) {
        svg.select('.title').text(data.title || 'Heat Map');
        // Y Labels
        const yLabels = svg.select('.y-labels');
        yLabels.selectAll('text').remove();
        (data.yCategories || []).forEach(row => {
            yLabels.append('text')
                .attr('class', 'y-label')
                .attr('x', 90)
                .attr('y', row.y + data.cellHeight / 2)
                .text(row.label);
        });
        // X Labels
        const xLabels = svg.select('.x-labels');
        xLabels.selectAll('text').remove();
        (data.xCategories || []).forEach(col => {
            xLabels.append('text')
                .attr('class', 'x-label')
                .attr('x', col.x + data.cellWidth / 2)
                .attr('y', 520)
                .text(col.label);
        });
        // Cells
        const cellsG = svg.select('.heatmap-cells');
        cellsG.selectAll('rect').remove();
        cellsG.selectAll('text').remove();
        (data.matrix || []).forEach(cell => {
            cellsG.append('rect')
                .attr('x', cell.x)
                .attr('y', cell.y)
                .attr('width', data.cellWidth)
                .attr('height', data.cellHeight)
                .attr('fill', cell.color)
                .attr('data-x', cell.xCategory)
                .attr('data-y', cell.yCategory)
                .attr('data-value', cell.value);
            cellsG.append('text')
                .attr('class', 'cell-label')
                .attr('x', cell.x + data.cellWidth / 2)
                .attr('y', cell.y + data.cellHeight / 2 + 4)
                .text(cell.value);
        });
        // Legend
        const legend = svg.select('.legend');
        legend.selectAll('text').remove();
        legend.append('text').attr('class', 'legend-label').attr('x', 100).attr('y', 545).text(data.minValue);
        legend.append('text').attr('class', 'legend-label').attr('x', 700).attr('y', 545).text(data.maxValue);
        legend.append('text').attr('class', 'legend-label').attr('x', 400).attr('y', 545).text(data.midValue);
    }
});
