/**
 * P.I.M.P - Chart Component
 * Renders charts using schemas from SchemaLoader
 */

class ChartComponent {
    constructor(element, pimp) {
        this.element = element;
        this.pimp = pimp || window.PIMP;
        this.schemaLoader = window.SchemaLoader;
        this.chart = null;
        this.schema = null;
        this.data = null;
        this.config = {};
        this.init();
    }

    async init() {
        if (!this.element) {
            console.error('ChartComponent: Element not found');
            return;
        }

        // Get schema name from data attribute
        const schemaName = this.element.getAttribute('data-schema');
        const schemaType = this.element.getAttribute('data-schema-type') || 'chart';

        if (schemaName) {
            await this.loadSchema(schemaName, schemaType);
        }

        // Get initial data
        const dataAttr = this.element.getAttribute('data-chart-data');
        if (dataAttr) {
            try {
                this.data = JSON.parse(dataAttr);
            } catch (e) {
                console.error('ChartComponent: Invalid data attribute', e);
            }
        }

        // Get config
        const configAttr = this.element.getAttribute('data-chart-config');
        if (configAttr) {
            try {
                this.config = JSON.parse(configAttr);
            } catch (e) {
                console.error('ChartComponent: Invalid config attribute', e);
            }
        }
    }

    async loadSchema(schemaName, type = 'chart') {
        try {
            if (!this.schemaLoader) {
                console.error('ChartComponent: SchemaLoader not available');
                return;
            }

            this.schema = await this.schemaLoader.loadSchema(schemaName, type);
            return this.schema;
        } catch (error) {
            console.error(`ChartComponent: Failed to load schema ${schemaName}:`, error);
            throw error;
        }
    }

    async renderChart(data, config = {}) {
        if (data) {
            this.data = data;
        }

        if (Object.keys(config).length > 0) {
            this.config = { ...this.config, ...config };
        }

        if (!this.schema) {
            console.error('ChartComponent: No schema loaded');
            return;
        }

        // Merge schema with data and config
        const chartConfig = this.schemaLoader.mergeSchemaWithData(this.schema, {
            data: this.data,
            config: this.config
        });

        // Clear existing chart
        this.clear();

        // Determine chart library based on schema
        const library = chartConfig.library || 'd3';
        
        switch (library) {
            case 'd3':
                await this.renderD3Chart(chartConfig);
                break;
            case 'chartjs':
                await this.renderChartJSChart(chartConfig);
                break;
            case 'plotly':
                await this.renderPlotlyChart(chartConfig);
                break;
            default:
                await this.renderD3Chart(chartConfig);
        }

        // Trigger event
        this.emitEvent('chart:rendered', { chart: this.chart, element: this.element });
    }

    async renderD3Chart(config) {
        // Check if D3 is available
        if (typeof d3 === 'undefined') {
            console.error('ChartComponent: D3.js not loaded');
            return;
        }

        const width = this.config.width || this.element.clientWidth || 800;
        const height = this.config.height || this.element.clientHeight || 400;
        const margin = config.margin || { top: 20, right: 20, bottom: 40, left: 40 };

        // Create SVG
        const svg = d3.select(this.element)
            .append('svg')
            .attr('width', width)
            .attr('height', height);

        const g = svg.append('g')
            .attr('transform', `translate(${margin.left},${margin.top})`);

        const chartWidth = width - margin.left - margin.right;
        const chartHeight = height - margin.top - margin.bottom;

        // Apply schema-based rendering
        if (config.type) {
            switch (config.type) {
                case 'bar':
                    this.renderD3BarChart(g, chartWidth, chartHeight, config);
                    break;
                case 'line':
                    this.renderD3LineChart(g, chartWidth, chartHeight, config);
                    break;
                case 'pie':
                    this.renderD3PieChart(g, chartWidth, chartHeight, config);
                    break;
                default:
                    console.warn(`ChartComponent: Unknown chart type: ${config.type}`);
            }
        }

        this.chart = { svg, g, type: 'd3' };
    }

    renderD3BarChart(g, width, height, config) {
        const data = config.data || this.data || [];

        const x = d3.scaleBand()
            .range([0, width])
            .padding(0.1)
            .domain(data.map(d => d.label || d.name || d.x));

        const y = d3.scaleLinear()
            .range([height, 0])
            .domain([0, d3.max(data, d => d.value || d.y || 0)]);

        g.append('g')
            .attr('transform', `translate(0,${height})`)
            .call(d3.axisBottom(x));

        g.append('g')
            .call(d3.axisLeft(y));

        g.selectAll('.bar')
            .data(data)
            .enter().append('rect')
            .attr('class', 'bar')
            .attr('x', d => x(d.label || d.name || d.x))
            .attr('width', x.bandwidth())
            .attr('y', d => y(d.value || d.y || 0))
            .attr('height', d => height - y(d.value || d.y || 0));
    }

    renderD3LineChart(g, width, height, config) {
        const data = config.data || this.data || [];

        const x = d3.scaleLinear()
            .range([0, width])
            .domain(d3.extent(data, d => d.x || d.label || 0));

        const y = d3.scaleLinear()
            .range([height, 0])
            .domain(d3.extent(data, d => d.y || d.value || 0));

        const line = d3.line()
            .x(d => x(d.x || d.label || 0))
            .y(d => y(d.y || d.value || 0));

        g.append('g')
            .attr('transform', `translate(0,${height})`)
            .call(d3.axisBottom(x));

        g.append('g')
            .call(d3.axisLeft(y));

        g.append('path')
            .datum(data)
            .attr('fill', 'none')
            .attr('stroke', 'steelblue')
            .attr('stroke-width', 2)
            .attr('d', line);
    }

    renderD3PieChart(g, width, height, config) {
        const data = config.data || this.data || [];
        const radius = Math.min(width, height) / 2;

        const pie = d3.pie()
            .value(d => d.value || d.y || 0);

        const arc = d3.arc()
            .innerRadius(0)
            .outerRadius(radius);

        const arcs = g.selectAll('.arc')
            .data(pie(data))
            .enter().append('g')
            .attr('class', 'arc')
            .attr('transform', `translate(${width / 2},${height / 2})`);

        arcs.append('path')
            .attr('d', arc)
            .attr('fill', (d, i) => d3.schemeCategory10[i % 10]);
    }

    async renderChartJSChart(config) {
        if (typeof Chart === 'undefined') {
            console.error('ChartComponent: Chart.js not loaded');
            return;
        }

        const ctx = this.element.getContext ? this.element : this.element.querySelector('canvas')?.getContext('2d');
        if (!ctx) {
            const canvas = document.createElement('canvas');
            this.element.appendChild(canvas);
            ctx = canvas.getContext('2d');
        }

        const chartConfig = {
            type: config.type || 'bar',
            data: {
                labels: (config.data || this.data || []).map(d => d.label || d.name || d.x),
                datasets: [{
                    label: config.label || 'Data',
                    data: (config.data || this.data || []).map(d => d.value || d.y || 0),
                    ...config.datasetOptions
                }]
            },
            options: config.options || {}
        };

        this.chart = new Chart(ctx, chartConfig);
    }

    async renderPlotlyChart(config) {
        if (typeof Plotly === 'undefined') {
            console.error('ChartComponent: Plotly.js not loaded');
            return;
        }

        const data = config.data || this.data || [];
        const layout = config.layout || {};

        await Plotly.newPlot(this.element, data, layout, config.config || {});
        this.chart = { type: 'plotly' };
    }

    updateChart(data) {
        if (!this.chart) {
            console.warn('ChartComponent: No chart to update');
            return;
        }

        this.data = data;

        if (this.chart.type === 'd3') {
            // Re-render D3 chart
            this.renderChart(data);
        } else if (this.chart.type === 'chartjs') {
            // Update Chart.js
            this.chart.data.datasets[0].data = data.map(d => d.value || d.y || 0);
            this.chart.update();
        } else if (this.chart.type === 'plotly') {
            // Update Plotly
            Plotly.redraw(this.element);
        }
    }

    setupChartInteractions() {
        // Add tooltip, zoom, pan interactions based on schema config
        if (this.schema?.interactions) {
            // Implementation depends on chart library
        }
    }

    exportChart(format = 'png') {
        if (!this.chart) {
            console.warn('ChartComponent: No chart to export');
            return;
        }

        if (this.chart.type === 'd3' && this.chart.svg) {
            const svgData = new XMLSerializer().serializeToString(this.chart.svg.node());
            const svgBlob = new Blob([svgData], { type: 'image/svg+xml;charset=utf-8' });
            const url = URL.createObjectURL(svgBlob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `chart.${format}`;
            link.click();
        } else if (this.chart.type === 'chartjs') {
            const url = this.chart.toBase64Image();
            const link = document.createElement('a');
            link.href = url;
            link.download = `chart.${format}`;
            link.click();
        }
    }

    clear() {
        if (this.chart) {
            if (this.chart.type === 'd3' && this.chart.svg) {
                this.chart.svg.remove();
            } else if (this.chart.type === 'chartjs') {
                this.chart.destroy();
            } else if (this.chart.type === 'plotly') {
                Plotly.purge(this.element);
            }
        }

        this.element.innerHTML = '';
        this.chart = null;
    }

    emitEvent(eventName, data) {
        const event = new CustomEvent(eventName, {
            detail: data,
            bubbles: true
        });
        this.element.dispatchEvent(event);

        if (this.pimp?.emitEvent) {
            this.pimp.emitEvent(eventName, data);
        }
    }

    destroy() {
        this.clear();
        this.element = null;
    }
}

// Auto-initialize chart components
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-chart]').forEach(element => {
        if (!element.chartComponent) {
            element.chartComponent = new ChartComponent(element);
        }
    });
});

// Export for module usage
if (typeof window !== 'undefined') {
    window.ChartComponent = ChartComponent;
}

if (typeof module !== 'undefined' && module.exports) {
    module.exports = ChartComponent;
}
