import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import ReportForm from './Form';
export default function Create({labels}) { const form=useForm({title:'',report_type:'',location:'',risk_level:'',description:'',corrective_action:'',reported_at:''}); return <><Head title={labels.new_report}/><ReportForm labels={labels} form={form} title={labels.new_report} submitLabel={labels.save} submit={()=>form.post('/hse-reports')}><Link className="react-secondary" href="/hse-reports">{labels.cancel}</Link></ReportForm></>; }
