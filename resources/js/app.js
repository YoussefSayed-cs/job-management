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


const PALETTE_LIGHT = ['#4F46E5', '#2563EB', '#0284C7', '#4338CA', '#D97706', '#E11D48', '#64748B'];
const PALETTE_DARK = ['#6366F1', '#3B82F6', '#38BDF8', '#818CF8', '#F59E0B', '#F43F5E', '#94A3B8'];

const charts = new Map();

function buildChart(canvas) {
    const config = JSON.parse(canvas.dataset.chart);
    const dark = document.documentElement.classList.contains('dark');
    const textColor = dark ? '#94A3B8' : '#475569';
    const gridColor = dark ? '#1E293B' : '#E2E8F0';
    const defaultPalette = dark ? PALETTE_DARK : PALETTE_LIGHT;
    const colors = config.colors && config.colors.length ? config.colors : defaultPalette;
    const isDoughnut = config.type === 'doughnut';

    const dataset = { label: config.label, data: config.values };

    if (isDoughnut) {
        dataset.backgroundColor = config.labels.map((_, i) => colors[i % colors.length]);
        dataset.borderWidth = 0;
    } else if (config.type === 'line') {
        dataset.borderColor = colors[0];
        dataset.backgroundColor = colors[0] + (dark ? '26' : '1A');
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