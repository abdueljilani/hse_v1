import React, { useEffect, useRef, useState } from 'react';
import { Head } from '@inertiajs/react';

const listColumns = ['report_number', 'title', 'report_type', 'location', 'risk_level', 'status', 'reported_at'];

export default function PrintReports({ reports = [], singleReport, dataUrl, reportCount = 0, labels: l, generatedAt }) {
    const tableBody = useRef(null);
    const [loadedCount, setLoadedCount] = useState(singleReport ? 1 : 0);
    const [error, setError] = useState('');

    useEffect(() => {
        if (singleReport) {
            const timer = window.setTimeout(() => window.print(), 500);
            return () => window.clearTimeout(timer);
        }

        if (!dataUrl || !tableBody.current) return undefined;

        const abortController = new AbortController();
        let cancelled = false;

        async function loadAndPrint() {
            try {
                const response = await fetch(dataUrl, {
                    headers: { Accept: 'application/x-ndjson', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                    signal: abortController.signal,
                });

                if (!response.ok || !response.body) throw new Error('Report stream unavailable');

                const reader = response.body.getReader();
                const decoder = new TextDecoder();
                let pendingText = '';
                let batchCount = 0;
                let totalLoaded = 0;
                let fragment = document.createDocumentFragment();

                const flushRows = () => {
                    if (fragment.childNodes.length) tableBody.current?.appendChild(fragment);
                    fragment = document.createDocumentFragment();
                    batchCount = 0;
                    if (!cancelled) setLoadedCount(totalLoaded);
                };

                const appendLine = (line) => {
                    if (!line.trim()) return;
                    const report = JSON.parse(line);
                    const row = document.createElement('tr');

                    listColumns.forEach((key) => {
                        const cell = document.createElement('td');
                        let value = report[key] ?? '—';

                        if (key === 'reported_at') {
                            value = value ? new Date(value).toLocaleString() : '—';
                        } else if (['report_type', 'risk_level', 'status'].includes(key)) {
                            value = l[report[key]] || report[key];
                        }

                        cell.textContent = String(value);
                        row.appendChild(cell);
                    });

                    fragment.appendChild(row);
                    batchCount += 1;
                    totalLoaded += 1;
                    if (batchCount >= 100) flushRows();
                };

                while (true) {
                    const { done, value } = await reader.read();
                    pendingText += decoder.decode(value, { stream: !done });
                    const lines = pendingText.split('\n');
                    pendingText = lines.pop() || '';
                    lines.forEach(appendLine);
                    if (batchCount >= 100) flushRows();
                    if (done) break;
                }

                appendLine(pendingText);
                flushRows();

                if (totalLoaded === 0 && tableBody.current) {
                    const row = document.createElement('tr');
                    const cell = document.createElement('td');
                    cell.colSpan = listColumns.length;
                    cell.textContent = l.no_reports || 'No reports';
                    row.appendChild(cell);
                    tableBody.current.appendChild(row);
                }

                if (!cancelled) window.setTimeout(() => window.print(), 300);
            } catch {
                if (!cancelled) setError(l.export_error || 'Unable to prepare reports for printing.');
            }
        }

        loadAndPrint();
        return () => {
            cancelled = true;
            abortController.abort();
        };
    }, [dataUrl, l, singleReport]);

    const items = singleReport ? [singleReport] : reports;
    const value = (report, key) => {
        if (key === 'reported_at') return report.reported_at ? new Date(report.reported_at).toLocaleString() : '—';
        if (['report_type', 'risk_level', 'status'].includes(key)) return l[report[key]] || report[key];
        return report[key] || '—';
    };

    const progressMessage = (l.loaded_reports || 'Prepared :loaded of :total reports')
        .replace(':loaded', String(loadedCount))
        .replace(':total', String(reportCount));

    return <>
        <Head title={`${l.reports} | HSE`} />
        <main className="react-print-page">
            <div className="react-print-toolbar">
                <span>{l.reports}</span>
                <button disabled={!singleReport && (loadedCount < reportCount || Boolean(error))} onClick={() => window.print()}>{l.print}</button>
            </div>
            {error && <p className="react-message">{error}</p>}
            {!singleReport && !error && loadedCount < reportCount && (
                <p className="react-print-progress" role="status">{l.loading_reports} {progressMessage}</p>
            )}
            <article className="react-print-sheet">
                <header>
                    <div>
                        <span>HSE REPORTING SYSTEM</span>
                        <h1>{singleReport ? `${l.report_number}: ${singleReport.report_number}` : l.reports}</h1>
                        <p>{l.app_name}</p>
                    </div>
                    <time>{generatedAt}</time>
                </header>
                {singleReport ? (
                    <div className="react-print-details">
                        {['report_number', 'title', 'report_type', 'location', 'risk_level', 'status', 'reported_at', 'description', 'corrective_action'].map((key) => (
                            <section key={key}><strong>{l[key]}</strong><div>{value(singleReport, key)}</div></section>
                        ))}
                    </div>
                ) : (
                    <>
                        <p className="react-print-count">{reportCount} · {l.reports}</p>
                        <table>
                            <thead><tr>{listColumns.map((key) => <th key={key}>{l[key]}</th>)}</tr></thead>
                            <tbody ref={tableBody} />
                        </table>
                    </>
                )}
            </article>
        </main>
    </>;
}
