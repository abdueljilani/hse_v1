import React, { useEffect } from 'react';
import { Head } from '@inertiajs/react';
import Chart from 'chart.js/auto';

const colors = {
    ink: '#171717',
    gold: '#ffb000',
    amber: '#d4ad45',
    gray: '#8c867a',
    blue: '#4f81bd',
};

const detailLabels = {
    id: 'hseDashboardLabels',
    afterDatasetsDraw(chart) {
        const { ctx, chartArea } = chart;
        if (!chartArea) return;

        ctx.save();
        ctx.font = '700 11px Tajawal, Segoe UI, sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';

        chart.data.datasets.forEach((dataset, datasetIndex) => {
            const meta = chart.getDatasetMeta(datasetIndex);
            const values = dataset.data.map((value) => Number(value || 0));
            const total = values.reduce((sum, value) => sum + value, 0);

            meta.data.forEach((element, index) => {
                const value = values[index];
                if (!value || !total) return;

                const label = `${new Intl.NumberFormat().format(value)} · ${((value / total) * 100).toFixed(1)}%`;
                if (chart.config.type === 'bar') {
                    const position = element.tooltipPosition();
                    ctx.fillStyle = colors.ink;
                    ctx.fillText(label, position.x, Math.max(chartArea.top + 10, position.y - 13));
                    return;
                }

                if ((element.circumference / (Math.PI * 2)) < 0.075) return;
                const middle = (element.startAngle + element.endAngle) / 2;
                const radius = element.innerRadius + (element.outerRadius - element.innerRadius) * 0.57;
                const segmentColor = Array.isArray(dataset.backgroundColor)
                    ? dataset.backgroundColor[index]
                    : dataset.backgroundColor;
                ctx.fillStyle = [colors.gold, colors.amber, '#ffcf59', '#c49a36'].includes(segmentColor)
                    ? colors.ink
                    : '#fff';
                ctx.fillText(label, element.x + Math.cos(middle) * radius, element.y + Math.sin(middle) * radius);
            });
        });

        ctx.restore();
    },
};

function buildDoughnut(canvasId, labels, values, palette) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return null;

    return new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: palette,
                borderColor: '#fff',
                borderWidth: 3,
                hoverOffset: 5,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '58%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        boxWidth: 8,
                        padding: 14,
                        color: '#554d3d',
                        font: { family: 'Tajawal, Segoe UI, sans-serif', size: 11 },
                    },
                },
            },
        },
        plugins: [detailLabels],
    });
}

function buildRiskBars(canvasId, labels, values) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return null;

    return new Chart(canvas, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: colors.gold,
                borderRadius: 7,
                maxBarThickness: 46,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { enabled: true },
            },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#eeeae2' } },
                x: { grid: { display: false } },
            },
        },
        plugins: [detailLabels],
    });
}

export default function Dashboard({ stats, chartData, labels, locale, summary, success }) {
    const numberFormat = new Intl.NumberFormat(locale === 'ar' ? 'ar-LY' : 'en-US');
    const date = new Intl.DateTimeFormat(locale === 'ar' ? 'ar-LY' : 'en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    }).format(new Date());

    useEffect(() => {
        const charts = [
            buildDoughnut(
                'reportTypesChart',
                [labels.hazard, labels.incident, labels.near_miss, labels.observation],
                [chartData.types.hazard, chartData.types.incident, chartData.types.near_miss, chartData.types.observation],
                [colors.ink, colors.gold, colors.amber, colors.gray],
            ),
            buildRiskBars(
                'riskLevelsChart',
                [labels.low, labels.medium, labels.high, labels.critical],
                [chartData.risks.low, chartData.risks.medium, chartData.risks.high, chartData.risks.critical],
            ),
            buildDoughnut(
                'reportStatusChart',
                [labels.in_progress, labels.processed, labels.closed],
                [stats.in_progress, stats.processed, stats.closed],
                [colors.gold, colors.blue, colors.ink],
            ),
        ];

        return () => charts.forEach((chart) => chart?.destroy());
    }, [chartData, labels, stats]);

    const cards = [
        { key: 'total', label: labels.total_reports, value: stats.total, note: labels.all_reports_summary, icon: '▤', color: colors.ink },
        { key: 'in_progress', label: labels.in_progress, value: stats.in_progress, note: labels.need_followup_summary, icon: '◷', color: colors.gold },
        { key: 'processed', label: labels.processed, value: stats.processed, note: labels.processed_summary, icon: '⚑', color: colors.amber },
        { key: 'closed', label: labels.closed, value: stats.closed, note: labels.completed_summary, icon: '✓', color: '#282522' },
    ];

    const chartCards = [
        { id: 'reportTypesChart', title: labels.report_types_chart, values: Object.values(chartData.types) },
        { id: 'riskLevelsChart', title: labels.risk_levels_chart, values: Object.values(chartData.risks) },
        { id: 'reportStatusChart', title: labels.report_status_chart, values: [stats.in_progress, stats.processed, stats.closed] },
    ];

    return (
        <>
            <Head title={`${labels.dashboard} | HSE Reporting System`} />
            <div className="react-dashboard">
                <section className="react-dashboard-intro">
                    <div>
                        <h1>{labels.dashboard}</h1>
                        <p>{summary}</p>
                    </div>
                    <div className="react-dashboard-date">{date}</div>
                </section>

                {success && <div className="react-dashboard-success" role="status">{success}</div>}

                <section className="react-dashboard-stats" aria-label={labels.total_reports}>
                    {cards.map((card) => (
                        <article className="react-stat-card" key={card.key} style={{ '--stat-accent': card.color }}>
                            <div className="react-stat-label">
                                <span className="react-stat-icon" aria-hidden="true">{card.icon}</span>
                                {card.label}
                            </div>
                            <div className="react-stat-value">{numberFormat.format(card.value)}</div>
                            <div className="react-stat-note">{card.note}</div>
                        </article>
                    ))}
                </section>

                <section className="react-dashboard-charts" aria-label={labels.dashboard}>
                    {chartCards.map((chart) => (
                        <article className="react-chart-card" key={chart.id}>
                            <div className="react-chart-heading">
                                <h2>{chart.title}</h2>
                                <p>{labels.chart_count_and_percent}</p>
                            </div>
                            <div className="react-chart-container">
                                {chart.values.some((value) => Number(value) > 0)
                                    ? <canvas id={chart.id} role="img" aria-label={chart.title} />
                                    : <div className="react-chart-empty">{labels.no_chart_data}</div>}
                            </div>
                        </article>
                    ))}
                </section>
            </div>
        </>
    );
}
