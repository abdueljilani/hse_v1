import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';

export default function UserForm({ user, labels: l, currentUserId }) {
  const form=useForm({name:user?.name||'',email:user?.email||'',role:user?.role||'employee',password:'',password_confirmation:''});
  const reset=useForm({}); const editing=!!user;
  const submit=e=>{e.preventDefault();editing?form.put(`/admin/users/${user.id}`):form.post('/admin/users')};
  return <><Head title={editing?l.edit_user:l.create_user}/><div className="react-page react-form-page"><h1>{editing?l.edit_user:l.create_user}</h1><p>{l.enter_details}</p>
    <form onSubmit={submit}><div className="react-form-grid">
      {[['name',l.name,'text',true],['email',l.email,'email',true],['password',editing?`${l.password} (${l.leave_blank})`:l.password,'password',!editing],['password_confirmation',l.confirm_password,'password',!editing]].map(([key,label,type,required])=><div key={key}><label>{label}</label><input type={type} required={required} autoComplete="off" value={form.data[key]} onChange={e=>form.setData(key,e.target.value)}/>{form.errors[key]&&<small className="react-error">{form.errors[key]}</small>}</div>)}
      <div><label>{l.role}</label><select value={form.data.role} onChange={e=>form.setData('role',e.target.value)}>{['employee','hse_officer','admin'].map(k=><option key={k} value={k}>{l[k]}</option>)}</select>{form.errors.role&&<small className="react-error">{form.errors.role}</small>}</div>
    </div><div className="react-actions" style={{marginTop:18}}><button className="react-primary" disabled={form.processing}>{editing?l.save_changes:l.save}</button><Link className="react-secondary" href="/admin/users">{l.cancel}</Link></div></form>
    {editing&&user.id!==currentUserId&&<form onSubmit={e=>{e.preventDefault();if(confirm(l.confirm_reset_password))reset.post(`/admin/users/${user.id}/reset-password`)}} style={{marginTop:20}}><button className="react-secondary" disabled={reset.processing}>{l.reset_password}</button></form>}
  </div></>;
}
