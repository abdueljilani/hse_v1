import React from 'react';
import { Head, useForm } from '@inertiajs/react';

export default function ProfileEdit({ user, status, labels: l }) {
    const profile = useForm({ name: user.name ?? '', email: user.email ?? '' });
    const password = useForm({ current_password: '', password: '', password_confirmation: '' });
HHH
    return <><Head title={l.profile}/><div className="react-page react-profile-page">
        <header className="react-page-heading"><div><h1>{l.profile}</h1><p>{l.update_profile}</p></div></header>
        {status && <div className="react-message">{status === 'password-updated' ? l.password_updated : l.saved}</div>}
        <section className="react-panel"><h2>{l.profile_information}</h2><form onSubmit={e=>{e.preventDefault();profile.patch('/profile')}}>
            <ProfileField label={l.name} value={profile.data.name} error={profile.errors.name} onChange={v=>profile.setData('name',v)}/>
            <ProfileField label={l.email} type="email" value={profile.data.email} error={profile.errors.email} onChange={v=>profile.setData('email',v)}/>
            <button className="react-primary" disabled={profile.processing}>{profile.processing?'…':l.save}</button>
        </form></section>
        <section className="react-panel"><h2>{l.password}</h2><p>{l.update_password_hint}</p><form onSubmit={e=>{e.preventDefault();password.put('/password',{onSuccess:()=>password.reset()})}}>
            <ProfileField label={l.current_password} type="password" value={password.data.current_password} error={password.errors.current_password} onChange={v=>password.setData('current_password',v)}/>
            <ProfileField label={l.new_password} type="password" value={password.data.password} error={password.errors.password} onChange={v=>password.setData('password',v)}/>
            <ProfileField label={l.confirm_password} type="password" value={password.data.password_confirmation} error={password.errors.password_confirmation} onChange={v=>password.setData('password_confirmation',v)}/>
            <button className="react-primary" disabled={password.processing}>{password.processing?'…':l.save}</button>
        </form></section>
    </div></>;
}

function ProfileField({label,type='text',value,error,onChange}) {
    return <div className="react-profile-field"><label>{label}</label><input type={type} value={value} autoComplete={type==='password'?'current-password':'off'} onChange={e=>onChange(e.target.value)}/>{error&&<small className="react-error">{error}</small>}</div>;
}
