import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import Pagination from '../../Components/Pagination';

export default function ReportsIndex({ reports, filters, labels:l, canDelete, userId, success }) {
  const v = (key) => l[key] || key;
  const submit = (e) => { e.preventDefault(); router.get('/hse-reports', Object.fromEntries(new FormData(e.currentTarget)), { preserveState:true, replace:true }); };
  const exportQuery = new URLSearchParams(Object.fromEntries(Object.entries(filters || {}).filter(([,value]) => value))).toString();
  return <><Head title={v('reports')} /><div className="react-page">
    <header className="react-page-heading"><h1>{v('reports')}</h1><Link className="react-primary" href="/hse-reports/create">ï¼‹ {v('new_report')}</Link></header>
    {success && <div className="react-message">{success}</div>}
    <form onSubmit={submit} className="react-report-filters">
      <input type="search" name="search" defaultValue={filters?.search || ''} placeholder={v('search_placeholder')} />
      <Select name="report_type" value={filters?.report_type} options={['hazard','incident','near_miss','observation']} all={v('all_types')} labels={l}/>
      <Select name="risk_level" value={filters?.risk_level} options={['low','medium','high','critical']} all={v('all_risks')} labels={l}/>
      <Select name="status" value={filters?.status} options={['in_progress','processed','closed']} all={v('all_statuses')} labels={l}/>
      <button className="react-primary react-search-button" aria-label={v('search')} title={v('search')}><svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 5 5"/></svg></button>
    </form>
    <div className="react-table-wrap"><table><thead><tr>{['report_number','title','report_type','location','risk_level','status','reported_at','actions'].map(k=><th key={k}>{v(k)}</th>)}</tr></thead><tbody>
      {reports.data.length ? reports.data.map(r=><tr key={r.id}><td>{r.report_number}</td><td>{r.title}</td><td>{v(r.report_type)}</td><td>{r.location}</td><td>{v(r.risk_level)}</td><td><span className={`react-badge ${r.status}`}>{v(r.status)}</span></td><td>{r.reported_at ? new Date(r.reported_at).toLocaleString() : 'â€”'}</td><td><div className="react-row-actions"><Link className="react-small-link" href={`/hse-reports/${r.id}`}>{v('view_details')}</Link>{(canDelete || Number(r.user_id)===Number(userId)) && <button className="react-small-danger" onClick={()=>confirm(v('confirm_delete_report'))&&router.delete(`/hse-reports/${r.id}`)}>{v('delete_report')}</button>}</div></td></tr>) : <tr><td colSpan="8" className="react-empty">{v('no_reports')}</td></tr>}
    </tbody></table></div>
    <Pagination paginator={reports} preserveScroll />
    <section className="react-export"><div><strong>{v('reports')}</strong><small>{v('export_all_hint')}</small></div><div className="react-actions"><a href={`/hse-reports/export/excel${exportQuery ? `?${exportQuery}` : ''}`}>{v('export_excel')}</a><a href={`/hse-reports/export/pdf${exportQuery ? `?${exportQuery}` : ''}`} target="_blank" rel="noreferrer">{v('export_pdf')}</a></div></section>
  </div></>;
}
function Select({name,value,options,all,labels}) { return <select name={name} defaultValue={value||''}><option value="">{all}</option>{options.map(k=><option key={k} value={k}>{labels[k]}</option>)}</select>; }
