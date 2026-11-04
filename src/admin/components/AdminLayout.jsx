// The signed-in shell: sidebar (an off-canvas menu below 992px), top bar and footer.
import { useEffect, useState } from 'react'
import { Link, NavLink, Outlet, useLocation, useNavigate, useSearchParams } from 'react-router'
import { useAuth } from '../auth/AuthContext.js'
import Dropdown from './Dropdown.jsx'
import { Avatar } from './ui.jsx'

const YEAR = new Date().getFullYear()

function navSections(admin) {
  return [
    ['Overview', [{ to: '/admin', end: true, icon: 'fa-solid fa-chart-simple', label: 'Dashboard' }]],
    [
      'Content',
      [
        { to: '/admin/posts', icon: 'fa-regular fa-newspaper', label: 'Posts' },
        { to: '/admin/categories', icon: 'fa-solid fa-layer-group', label: 'Categories' },
      ],
    ],
    ['Administration', admin.is_super_admin ? [{ to: '/admin/admins', icon: 'fa-solid fa-user-shield', label: 'Admins' }] : []],
    ['You', [{ to: '/admin/account', icon: 'fa-regular fa-circle-user', label: 'My account' }]],
  ]
}

export default function AdminLayout() {
  const { admin, signOut } = useAuth()
  const [isMenuOpen, setIsMenuOpen] = useState(false)
  const { pathname } = useLocation()
  const [searchParams] = useSearchParams()
  const navigate = useNavigate()
  const isPostsPage = pathname === '/admin/posts'
  const closeMenu = () => setIsMenuOpen(false)

  // Start each page at the top.
  useEffect(() => {
    window.scrollTo(0, 0)
  }, [pathname])

  useEffect(() => {
    if (!isMenuOpen) return undefined

    const onKeyDown = (event) => {
      if (event.key === 'Escape') setIsMenuOpen(false)
    }

    document.body.style.overflow = 'hidden'
    document.addEventListener('keydown', onKeyDown)

    return () => {
      document.body.style.overflow = ''
      document.removeEventListener('keydown', onKeyDown)
    }
  }, [isMenuOpen])

  function searchPosts(event) {
    event.preventDefault()
    const query = String(new FormData(event.currentTarget).get('q') ?? '').trim()
    navigate(query ? `/admin/posts?q=${encodeURIComponent(query)}` : '/admin/posts')
  }

  return (
    <>
      <a href="#main" className="visually-hidden-focusable position-absolute top-0 start-0 m-2 btn btn-primary" style={{ zIndex: 2000 }}>
        Skip to content
      </a>

      <aside className="admin-sidebar">
        <div className={`offcanvas-lg offcanvas-start sidebar-panel${isMenuOpen ? ' show' : ''}`} tabIndex={-1} id="admin-sidebar" aria-label="Admin navigation">
          <div className="d-flex align-items-center">
            <Link to="/admin" className="sidebar-brand" onClick={closeMenu}>
              <span className="brand-chip">
                <img src="/logo.png" alt="" width="38" height="32" />
              </span>
              <span>
                <span className="brand-name">CAGAD</span>
                <span className="brand-sub">Blog Admin</span>
              </span>
            </Link>
            <button type="button" className="btn-close btn-close-white sidebar-close me-3 d-lg-none" aria-label="Close menu" onClick={closeMenu} />
          </div>

          <nav className="sidebar-nav">
            {navSections(admin).map(([section, links]) =>
              links.length === 0 ? null : (
                <div key={section}>
                  <p className="sidebar-label">{section}</p>
                  {links.map((link) => (
                    <NavLink key={link.to} to={link.to} end={link.end} className="sidebar-link" onClick={closeMenu}>
                      <i className={link.icon} aria-hidden="true" /> {link.label}
                    </NavLink>
                  ))}
                </div>
              ),
            )}

            <a href="/blog" className="sidebar-link" target="_blank" rel="noopener">
              <i className="fa-solid fa-globe" aria-hidden="true" /> View live blog
              <i className="fa-solid fa-arrow-up-right-from-square external" aria-hidden="true" />
            </a>
          </nav>

          <div className="sidebar-footer">
            <div className="sidebar-user">
              <Avatar admin={admin} />
              <div className="min-w-0">
                <div className="sidebar-user-name text-truncate">{admin.name}</div>
                <div className="sidebar-user-role">{admin.role_label}</div>
              </div>
            </div>
            <button type="button" className="sidebar-signout" onClick={signOut}>
              <i className="fa-solid fa-arrow-right-from-bracket" aria-hidden="true" /> Sign out
            </button>
          </div>
        </div>
        {isMenuOpen && <div className="offcanvas-backdrop fade show d-lg-none" onClick={closeMenu} />}
      </aside>

      <div className="admin-main">
        <header className="admin-topbar">
          <button type="button" className="icon-btn d-lg-none" aria-label="Open menu" aria-controls="admin-sidebar" aria-expanded={isMenuOpen} onClick={() => setIsMenuOpen(true)}>
            <i className="fa-solid fa-bars" aria-hidden="true" />
          </button>

          <Link to="/admin" className="topbar-brand d-md-none">
            <img src="/logo.png" alt="" width="34" height="28" /> CAGAD
          </Link>

          <form key={isPostsPage ? searchParams.get('q') ?? '' : 'elsewhere'} className="topbar-search d-none d-md-block" role="search" onSubmit={searchPosts}>
            <label htmlFor="topbar-search" className="visually-hidden">
              Search posts
            </label>
            <i className="fa-solid fa-magnifying-glass" aria-hidden="true" />
            <input type="search" id="topbar-search" name="q" className="form-control" placeholder="Search posts…" defaultValue={isPostsPage ? searchParams.get('q') ?? '' : ''} autoComplete="off" />
          </form>

          <div className="topbar-actions">
            <Link to="/admin/posts/new" className="btn btn-primary btn-sm d-none d-sm-inline-flex">
              <i className="fa-solid fa-plus" aria-hidden="true" /> New post
            </Link>
            <Link to="/admin/posts/new" className="icon-btn d-sm-none" aria-label="New post">
              <i className="fa-solid fa-plus" aria-hidden="true" />
            </Link>

            <Dropdown
              buttonClassName="user-menu-btn"
              ariaLabel={`Account menu for ${admin.name}`}
              menuClassName="dropdown-menu-end"
              label={
                <>
                  <Avatar admin={admin} size="sm" />
                  <span className="d-none d-md-inline">{admin.name.split(' ')[0]}</span>
                  <i className="fa-solid fa-chevron-down small text-secondary d-none d-md-inline" aria-hidden="true" />
                </>
              }
            >
              <div className="user-menu-header">
                <div className="fw-semibold text-ink">{admin.name}</div>
                <div className="small text-secondary text-truncate" style={{ maxWidth: 220 }}>
                  {admin.email}
                </div>
              </div>
              <Link className="dropdown-item" to="/admin/account">
                <i className="fa-regular fa-circle-user" aria-hidden="true" /> My account
              </Link>
              <a className="dropdown-item" href="/blog" target="_blank" rel="noopener">
                <i className="fa-solid fa-arrow-up-right-from-square" aria-hidden="true" /> View live blog
              </a>
              <div className="dropdown-divider" />
              <button type="button" className="dropdown-item text-danger" onClick={signOut}>
                <i className="fa-solid fa-arrow-right-from-bracket" aria-hidden="true" /> Sign out
              </button>
            </Dropdown>
          </div>
        </header>

        <main className="admin-content" id="main" tabIndex={-1}>
          <Outlet />
        </main>

        <footer className="admin-footer">© {YEAR} Centre for Advancement of Governance and Development</footer>
      </div>
    </>
  )
}
