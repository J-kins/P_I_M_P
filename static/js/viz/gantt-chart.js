// D3.js Gantt Chart Visualization
// Uses SVG template at view/viz/gantt-chart.svg
// Integratable with backend (AJAX/fetch for data)

document.addEventListener('DOMContentLoaded', function() {
    const svg = d3.select('#gantt-chart-svg');
    if (!svg.node()) return;

    fetch('/api/gantt-chart-data')
        .then(res => res.json())
        .then(data => renderGantt(svg, data));

    function renderGantt(svg, data) {
        svg.select('.title').text(data.title || 'Gantt Chart');
        // Y Ticks
        const yGrid = svg.select('.y-grid-labels');
        yGrid.selectAll('g').remove();
        (data.yTicks || []).forEach(tick => {
            yGrid.append('line')
                .attr('class', 'grid-line')
                .attr('x1', 120).attr('y1', tick.y)
                .attr('x2', 850).attr('y2', tick.y);
            yGrid.append('text')
                .attr('class', 'bar-label')
                .attr('x', 110).attr('y', tick.y + 5)
                .text(tick.label);
        });
        // Gantt Bars
        const barsG = svg.select('.gantt-bars');
        barsG.selectAll('*').remove();
        (data.tasks || []).forEach(task => {
            barsG.append('rect')
                .attr('class', 'task-bar')
                .attr('x', task.xStart)
                .attr('y', task.y)
                .attr('width', task.width)
                .attr('height', task.height)
                .attr('data-task', task.name)
                .attr('data-start', task.start)
                .attr('data-end', task.end)
                .attr('data-duration', task.duration)
                .attr('data-progress', task.progress)
                .attr('data-dependencies', task.dependencies)
                .attr('data-assignee', task.assignee)
                .attr('data-notes', task.notes)
                .attr('data-collapsible', task.collapsible);
            barsG.append('rect')
                .attr('class', 'progress-bar')
                .attr('x', task.xStart)
                .attr('y', task.y)
                .attr('width', task.width * task.progress / 100)
                .attr('height', task.height);
            barsG.append('text')
                .attr('class', 'progress-label')
                .attr('x', task.xStart + (task.width * task.progress / 200))
                .attr('y', task.y + task.height / 2 + 4)
                .text(task.progress + '%');
        });
        // Milestones
        const milestonesG = svg.select('.milestones');
        milestonesG.selectAll('*').remove();
        (data.milestones || []).forEach(milestone => {
            milestonesG.append('polygon')
                .attr('points', milestone.points)
                .attr('class', 'milestone-marker')
                .attr('data-label', milestone.label)
                .attr('data-date', milestone.date);
            milestonesG.append('text')
                .attr('class', 'date-label')
                .attr('x', milestone.x)
                .attr('y', milestone.y + 35)
                .text(milestone.label);
        });
        // Today Line
        svg.selectAll('.today-line').remove();
        if (data.todayX) {
            svg.append('line')
                .attr('class', 'today-line')
                .attr('x1', data.todayX)
                .attr('y1', 100)
                .attr('x2', data.todayX)
                .attr('y2', 500)
                .attr('stroke', '#e91e63')
                .attr('stroke-width', 3)
                .attr('stroke-dasharray', '8 4');
        }
        // Dependencies
        const depsG = svg.select('.dependencies');
        depsG.selectAll('*').remove();
        (data.dependencies || []).forEach(dep => {
            depsG.append('line')
                .attr('class', 'dependency-arrow')
                .attr('x1', dep.x1)
                .attr('y1', dep.y1)
                .attr('x2', dep.x2)
                .attr('y2', dep.y2);
        });
    }
});
