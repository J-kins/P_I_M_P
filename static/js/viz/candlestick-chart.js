// D3.js Candlestick Chart Visualization
// Uses SVG template at view/viz/candlestick-chart.svg
// Integratable with backend (AJAX/fetch for data)

document.addEventListener('DOMContentLoaded', function() {
    const svg = d3.select('#candlestick-chart-svg');
    if (!svg.node()) return;

    fetch('/api/candlestick-data')
        .then(res => res.json())
        .then(data => renderCandlestick(svg, data));

    function renderCandlestick(svg, data) {
        svg.select('.title').text(data.title || 'Candlestick Chart');
        // Y Ticks
        const yGrid = svg.select('.y-grid-labels');
        yGrid.selectAll('g').remove();
        (data.yTicks || []).forEach(tick => {
            yGrid.append('line')
                .attr('class', 'grid-line')
                .attr('x1', 100).attr('y1', tick.y)
                .attr('x2', 700).attr('y2', tick.y);
            yGrid.append('text')
                .attr('class', 'y-label')
                .attr('x', 90).attr('y', tick.y + 5)
                .text(tick.label);
        });
        // Candles
        const candlesG = svg.select('.candlesticks');
        candlesG.selectAll('*').remove();
        (data.candles || []).forEach(candle => {
            // Wick
            candlesG.append('line')
                .attr('class', 'wick')
                .attr('x1', candle.x).attr('y1', candle.yHigh)
                .attr('x2', candle.x).attr('y2', candle.yLow)
                .attr('data-high', candle.high)
                .attr('data-low', candle.low);
            // Body
            candlesG.append('rect')
                .attr('class', 'candlestick-body ' + (candle.close > candle.open ? 'bullish' : 'bearish'))
                .attr('x', candle.x - candle.width / 2)
                .attr('y', Math.min(candle.yOpen, candle.yClose))
                .attr('width', candle.width)
                .attr('height', Math.abs(candle.yOpen - candle.yClose))
                .attr('data-open', candle.open)
                .attr('data-close', candle.close)
                .attr('data-time', candle.time)
                .attr('data-volume', candle.volume);
        });
    }
});
