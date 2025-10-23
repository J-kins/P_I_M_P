// D3.js Box Plot Visualization
// Uses SVG template at view/viz/box-plot.svg
// Integratable with backend (AJAX/fetch for data)

document.addEventListener('DOMContentLoaded', function() {
    const svg = d3.select('#box-plot-svg');
    if (!svg.node()) return;

    fetch('/api/box-plot-data')
        .then(res => res.json())
        .then(data => renderBoxPlot(svg, data));

    function renderBoxPlot(svg, data) {
        svg.select('.title').text(data.title || 'Box Plot');
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
        // Boxplots
        const boxplotsG = svg.select('.boxplots');
        boxplotsG.selectAll('*').remove();
        (data.groups || []).forEach(group => {
            // Whiskers
            boxplotsG.append('line')
                .attr('class', 'whisker')
                .attr('x1', group.x).attr('y1', group.yMin)
                .attr('x2', group.x).attr('y2', group.yQ1);
            boxplotsG.append('line')
                .attr('class', 'whisker')
                .attr('x1', group.x).attr('y1', group.yQ3)
                .attr('x2', group.x).attr('y2', group.yMax);
            // Box
            boxplotsG.append('rect')
                .attr('class', 'box')
                .attr('x', group.x - group.boxWidth / 2)
                .attr('y', group.yQ3)
                .attr('width', group.boxWidth)
                .attr('height', group.yQ1 - group.yQ3)
                .attr('data-group', group.label)
                .attr('data-min', group.min)
                .attr('data-q1', group.q1)
                .attr('data-median', group.median)
                .attr('data-q3', group.q3)
                .attr('data-max', group.max);
            // Median line
            boxplotsG.append('line')
                .attr('class', 'median-line')
                .attr('x1', group.x - group.boxWidth / 2)
                .attr('y1', group.yMedian)
                .attr('x2', group.x + group.boxWidth / 2)
                .attr('y2', group.yMedian);
            // Outliers
            (group.outliers || []).forEach(outlier => {
                boxplotsG.append('circle')
                    .attr('class', 'outlier')
                    .attr('cx', group.x)
                    .attr('cy', outlier.y)
                    .attr('r', 5)
                    .attr('data-group', group.label)
                    .attr('data-outlier', outlier.value);
            });
            // Group label
            boxplotsG.append('text')
                .attr('class', 'group-label')
                .attr('x', group.x)
                .attr('y', 520)
                .text(group.label);
        });
    }
});
