import React, { useEffect, useState } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';

const navItems = [
    { href: '/dashboard', key: 'dashboard', icon: '⌂' },
    { href: '/hse-reports', key: 'reports', icon: '▤', reportNotifications: true },
    { href: '/hse-reports/create', key: 'new_report', icon: '+' },
    { href: '/analytics', key: 'analytics', icon: '▥', analytics: true },
    { href: '/admin/users', key: 'manage_users', icon: '♙', admin: true },
    { href: '/admin/join-requests', key: 'join_requests', icon: '♧', admin: true, joinCount: true },
    { href: '/admin/status-history', key: 'status_history', icon: '◷', admin: true },
    { href: '/profile', key: 'profile', icon: '♙' },
];

export default function AppShell({ children }) {
    const { url, props } = usePage();
    const user = props.auth?.user;
    const app = props.app || {};
    const labels = app.labels || {};
    const isAdmin = user?.role === 'admin';
    const canViewReports = isAdmin || user?.role === 'hse_officer';
    const [menuOpen, setMenuOpen] = useState(false);
    const [unreadReports, setUnreadReports] = useState(Number(props.unreadReportNotificationCount || 0));
    const [loggingOut, setLoggingOut] = useState(false);
    const locale = app.locale || 'ar';
    const path = url.split('?')[0];

    useEffect(() => {
        setUnreadReports(Number(props.unreadReportNotificationCount || 0));
    }, [props.unreadReportNotificationCount]);

    useEffect(() => {
        if (!canViewReports) return undefined;
        const update = () => fetch('/notifications/unread-reports', {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        }).then((response) => response.ok ? response.json() : null)
            .then((data) => { if (data && Number.isFinite(Number(data.count))) setUnreadReports(Number(data.count)); })
            .catch(() => {});
        const timer = window.setInterval(update, 30000);
        return () => window.clearInterval(timer);
    }, [canViewReports]);

    useEffect(() => { setMenuOpen(false); }, [url]);

    const visibleItems = navItems.filter((item) => (!item.admin || isAdmin) && (!item.analytics || canViewReports));
    const isActive = (href) => href === '/dashboard' || href === '/profile'
        ? path === href
        : href === '/hse-reports' && path === '/hse-reports/create'
            ? false
        : path === href || path.startsWith(`${href}/`);

    function logout() {
        setLoggingOut(true);
        router.post('/logout', {}, {
            onError: () => setLoggingOut(false),
            onSuccess: () => window.location.reload(),
        });
    }

    return <div className="hse-shell" dir={locale === 'ar' ? 'rtl' : 'ltr'}>
        <Head title={labels.app_name || 'HSE Reporting System'} />
        {menuOpen && <button className="hse-sidebar-overlay" aria-label={labels.close || 'Close'} onClick={() => setMenuOpen(false)} />}
        <aside className={`hse-sidebar${menuOpen ? ' is-open' : ''}`}>
            <div className="hse-brand">
                <img src="/images/logo.png?v=2" alt="" />
                <span>{labels.app_name || 'HSE Reporting System'}</span>
                <button className="hse-mobile-close" onClick={() => setMenuOpen(false)} aria-label={labels.close || 'Close'}>×</button>
            </div>
            <nav className="hse-nav" aria-label={labels.menu || 'Menu'}>
                {visibleItems.map((item) => <Link key={item.href} href={item.href} className={`hse-nav-link${isActive(item.href) ? ' is-active' : ''}`}>
                    <span className="hse-nav-icon" aria-hidden="true">{item.icon}</span>
                    <span>{labels[item.key] || item.key}</span>
                    {item.joinCount && Number(props.pendingJoinRequestCount) > 0 && <span className="hse-nav-badge">{Number(props.pendingJoinRequestCount) > 99 ? '99+' : props.pendingJoinRequestCount}</span>}
                    {item.reportNotifications && unreadReports > 0 && <span className="hse-nav-badge">{unreadReports > 99 ? '99+' : unreadReports}</span>}
                </Link>)}
            </nav>
            <footer className="hse-sidebar-footer">
                <div className="hse-user-name">{user?.name}</div>
                <div className="hse-user-email">{user?.email}</div>
                <a className="hse-language" href={`/language/${locale === 'ar' ? 'en' : 'ar'}`}>
                    <span>{labels.language || 'Language'}</span><span>{locale === 'ar' ? labels.english || 'English' : labels.arabic || 'العربية'}</span>
                </a>
                <button className="hse-logout" onClick={logout}><span aria-hidden="true">↪</span>{labels.logout || 'Logout'}</button>
            </footer>
        </aside>
        <main className="hse-main">
            <header className="hse-mobile-header">
                <button onClick={() => setMenuOpen(true)} aria-label={labels.menu || 'Menu'}>☰</button>
                <strong>{labels.app_name || 'HSE Reporting System'}</strong>
            </header>
            <div className="hse-content">{children}</div>
        </main>
        {loggingOut && <div className="hse-logout-loading" role="status" aria-live="polite"><span className="hse-loading-spinner" /></div>}
    </div>;
}
