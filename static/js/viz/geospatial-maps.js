// Geospatial Maps Integration: Pure D3.js SVG
// Uses SVG template at view/viz/geospatial-maps.svg
// Integratable with backend (fetches GeoJSON, etc.)
// Requires D3.js v7+

document.addEventListener('DOMContentLoaded', function() {
    // 1. Load the SVG base map
    d3.xml('/view/viz/geospatial-maps.svg').then(function(xml) {
        const importedNode = document.importNode(xml.documentElement, true);
        importedNode.id = 'geospatial-svg-map';
        const container = document.getElementById('geospatial-map-container');
        container.innerHTML = '';
        container.appendChild(importedNode);

        const svg = d3.select('#geospatial-svg-map');
        // 2. Add D3 zoom/pan
        svg.call(d3.zoom()
            .scaleExtent([0.5, 10])
            .on('zoom', function(event) {
                svg.select('g').attr('transform', event.transform);
            })
        );

        // 3. Fetch geospatial data from backend
        fetch('/api/geospatial-data')
            .then(res => res.json())
            .then(data => renderGeospatial(svg, data));
    });

    // 4. Render function using D3.js
    function renderGeospatial(svg, data) {
        // Points
        const pointsG = svg.append('g').attr('class', 'd3-points');
        pointsG.selectAll('circle')
            .data(data.points)
            .enter()
            .append('circle')
            .attr('class', 'vector-point')
            .attr('r', 10)
            .attr('cx', d => d.x)
            .attr('cy', d => d.y)
            .on('mouseover', function(e, d) {
                // Show popup or tooltip
            });
        // Lines
        const linesG = svg.append('g').attr('class', 'd3-lines');
        linesG.selectAll('polyline')
            .data(data.lines)
            .enter()
            .append('polyline')
            .attr('class', 'vector-line')
            .attr('points', d => d.points.map(pt => pt.join(',')).join(' '));
        // Polygons
        const polysG = svg.append('g').attr('class', 'd3-polygons');
        polysG.selectAll('polygon')
            .data(data.polygons)
            .enter()
            .append('polygon')
            .attr('class', 'vector-polygon')
            .attr('points', d => d.points.map(pt => pt.join(',')).join(' '));
        // Raster overlays (as rects)
        const rasterG = svg.append('g').attr('class', 'd3-raster');
        rasterG.selectAll('rect')
            .data(data.raster)
            .enter()
            .append('rect')
            .attr('class', d => 'raster-cell raster-' + d.level)
            .attr('x', d => d.x)
            .attr('y', d => d.y)
            .attr('width', d => d.width)
            .attr('height', d => d.height);
    }
});
