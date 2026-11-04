import { Link, useSearchParams } from 'react-router'

/** Page numbers to show: the first, the last and two either side of the current page. */
function pageWindow(current, last) {
  const pages = []

  for (let page = 1; page <= last; page += 1) {
    if (page === 1 || page === last || Math.abs(page - current) <= 2) {
      pages.push(page)
    } else if (pages[pages.length - 1] !== '…') {
      pages.push('…')
    }
  }

  return pages
}

/** Laravel pagination meta as Bootstrap page links that keep the other query parameters. */
export default function Pagination({ meta }) {
  const [searchParams] = useSearchParams()

  if (!meta || meta.last_page <= 1) return null

  const linkTo = (page) => {
    const params = new URLSearchParams(searchParams)
    if (page > 1) params.set('page', String(page))
    else params.delete('page')
    return `?${params}`
  }

  const current = meta.current_page

  return (
    <nav className="d-flex flex-wrap align-items-center justify-content-between gap-2" aria-label="Pages">
      <p className="small text-secondary mb-0">
        Showing {meta.from}–{meta.to} of {meta.total}
      </p>
      <ul className="pagination">
        <li className={`page-item${current === 1 ? ' disabled' : ''}`}>
          <Link className="page-link" to={linkTo(current - 1)} aria-label="Previous page">
            ‹
          </Link>
        </li>
        {pageWindow(current, meta.last_page).map((page, index) =>
          page === '…' ? (
            <li key={`gap-${index}`} className="page-item disabled">
              <span className="page-link">…</span>
            </li>
          ) : (
            <li key={page} className={`page-item${page === current ? ' active' : ''}`}>
              <Link className="page-link" to={linkTo(page)} aria-current={page === current ? 'page' : undefined}>
                {page}
              </Link>
            </li>
          ),
        )}
        <li className={`page-item${current === meta.last_page ? ' disabled' : ''}`}>
          <Link className="page-link" to={linkTo(current + 1)} aria-label="Next page">
            ›
          </Link>
        </li>
      </ul>
    </nav>
  )
}
