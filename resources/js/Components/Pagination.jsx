import React from 'react';
import { Link } from '@inertiajs/react';

function pageKind(label = '') {
  const normalized = String(label).replace(/<[^>]*>/g, '').toLowerCase();
  if (/previous|pagination\.previous|&laquo;/.test(normalized)) return 'previous';
  if (/next|pagination\.next|&raquo;/.test(normalized)) return 'next';
  return 'number';
}

function Arrow({ direction }) {
  const path = direction === 'previous' ? 'm9 5 7 7-7 7' : 'm15 5-7 7 7 7';
  return <svg aria-hidden="true" viewBox="0 0 24 24"><path d={path}/></svg>;
}

export default function Pagination({ paginator, preserveScroll = false }) {
  if (!paginator?.links?.length) return null;

  return <nav className="react-pagination" aria-label="Pagination">
    {paginator.links.map((item, index) => {
      const kind = pageKind(item.label);
      const disabled = !item.url;
      const label = kind === 'previous' ? 'Previous page' : kind === 'next' ? 'Next page' : String(item.label).replace(/<[^>]*>/g, '');

      return <Link
        key={`${item.label}-${index}`}
        href={item.url || '#'}
        preserveScroll={preserveScroll}
        className={`react-page-link${item.active ? ' active' : ''}${disabled ? ' disabled' : ''}${kind !== 'number' ? ` react-page-${kind}` : ''}`}
        aria-label={label}
        aria-current={item.active ? 'page' : undefined}
        aria-disabled={disabled || undefined}
        title={kind === 'number' ? undefined : label}
        onClick={event => disabled && event.preventDefault()}
      >
        {kind === 'number' ? <span dangerouslySetInnerHTML={{ __html: item.label }} /> : <Arrow direction={kind} />}
      </Link>;
    })}
  </nav>;
}
