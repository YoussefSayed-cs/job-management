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
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
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
        dataset.backgroundColor = colors[0] + (dark ? '1A' : '12');
        dataset.fill = true;
        dataset.borderWidth = 2;
        dataset.tension = 0.35;
        dataset.borderCapStyle = 'round';
        dataset.borderJoinStyle = 'round';
        dataset.pointRadius = 0;
        dataset.pointHoverRadius = 0;
        dataset.pointHitRadius = 8;
    } else {
        dataset.backgroundColor = colors[0] + (dark ? 'D9' : 'E0');
        dataset.hoverBackgroundColor = dark ? '#818CF8' : '#6366F1';
        dataset.borderRadius = 5;
        dataset.borderSkipped = false;
        dataset.maxBarThickness = 18;
        dataset.barPercentage = 0.72;
        dataset.categoryPercentage = 0.72;
    }

    const valueScale = {
        beginAtZero: true,
        ticks: { color: textColor, precision: 0, padding: 8, font: { size: 11 } },
        grid: {
            color: config.type === 'line' && !dark ? '#F1F5F9' : gridColor,
            drawBorder: false,
            drawTicks: false,
        },
        border: { display: false },
    };
    const categoryScale = {
        ticks: {
            color: textColor,
            padding: 8,
            font: { size: 11 },
            ...(config.type === 'line'
                ? { autoSkip: true, maxTicksLimit: 6, maxRotation: 0, minRotation: 0 }
                : {}),
        },
        grid: { display: false, drawBorder: false },
        border: { display: false },
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
            animation: { duration: reduceMotion ? 0 : 650, easing: 'easeOutQuart' },
            interaction: {
                mode: config.type === 'line' ? 'index' : 'nearest',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: isDoughnut,
                    position: 'bottom',
                    labels: { color: textColor },
                },
                tooltip: {
                    backgroundColor: dark ? '#0F172A' : '#1E293B',
                    titleColor: '#F8FAFC',
                    bodyColor: '#E2E8F0',
                    borderColor: dark ? '#334155' : '#475569',
                    borderWidth: 1,
                    padding: 10,
                    displayColors: false,
                    cornerRadius: 6,
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