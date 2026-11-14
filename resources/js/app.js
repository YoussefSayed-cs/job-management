import {
    ArcElement,
    BarController,
    BarElement,
    CategoryScale,
    Chart,
    DoughnutController,
    Filler,
    Legend,
    LineController,
    LineElement,
    LinearScale,
    PointElement,
    Tooltip,
} from 'chart.js';


Chart.register(
    ArcElement,
    BarController,
    BarElement,
    CategoryScale,
    DoughnutController,
    Filler,
    Legend,
    LineController,
    LineElement,
    LinearScale,
    PointElement,
    Tooltip,
);


const PALETTE = ['#6366f1', '#22c55e', '#f59e0b', '#ef4444', '#06b6d4', '#a855f7', '#64748b'];

const charts = new Map();

function buildChart(canvas) {
    const config = JSON.parse(canvas.dataset.chart);
    const dark = document.documentElement.classList.contains('dark');
    const textColor = dark ? '#cbd5e1' : '#475569';
    const gridColor = dark ? '#334155' : '#e2e8f0';
    const colors = config.colors && config.colors.length ? config.colors : PALETTE;
    const isDoughnut = config.type === 'doughnut';

    const dataset = { label: config.label, data: config.values };

    if (isDoughnut) {
        dataset.backgroundColor = config.labels.map((_, i) => colors[i % colors.length]);
        dataset.borderWidth = 0;
    } else if (config.type === 'line') {
        dataset.borderColor = colors[0];
        dataset.backgroundColor = colors[0] + '33';
        dataset.fill = true;
        dataset.tension = 0.3;
        dataset.pointRadius = 2;
    } else {
        dataset.backgroundColor = colors[0];
        dataset.borderRadius = 4;
    }

    const valueScale = {
        beginAtZero: true,
        ticks: { color: textColor, precision: 0 },
        grid: { color: gridColor },
    };
    const categoryScale = {
        ticks: { color: textColor },
        grid: { display: false },
    };

    let scales = {};
    if (!isDoughnut) {
        scales = config.horizontal
            ? { x: valueScale, y: categoryScale }
            : { x: categoryScale, y: valueScale };
    }

    return new Chart(canvas, {
        type: config.type,
        data: { labels: config.labels, datasets: [dataset] },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: config.horizontal ? 'y' : 'x',
            plugins: {
                legend: {
                    display: isDoughnut,
                    position: 'bottom',
                    labels: { color: textColor },
                },
            },
            scales,
        },
    });
}

function renderCharts() {
    charts.forEach((chart) => chart.destroy());
    charts.clear();

    document.querySelectorAll('canvas[data-chart]').forEach((canvas) => {
        charts.set(canvas, buildChart(canvas));
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', renderCharts);
} else {
    renderCharts();
}


document.addEventListener('livewire:navigated', renderCharts);

new MutationObserver(renderCharts).observe(document.documentElement, {
    attributes: true,
    attributeFilter: ['class'],
});