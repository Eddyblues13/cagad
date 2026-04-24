import { useState, useEffect, useRef } from 'react'

export default function Navbar() {
  const [scrolled, setScrolled] = useState(false)
  const [collapsed, setCollapsed] = useState(true)
  const navRef = useRef(null)

  useEffect(() => {
    const handleScroll = () => {
      setScrolled(window.scrollY > 100)
    }
    window.addEventListener('scroll', handleScroll)
    return () => window.removeEventListener('scroll', handleScroll)
  }, [])

  const handleNavClick = (e, targetId) => {
    e.preventDefault()
    setCollapsed(true)
    if (targetId === '#') return
    const el = document.querySelector(targetId)
    if (el) {
      el.scrollIntoView({ behavior: 'smooth' })
    }
  }

  const navLinks = [
    { href: '#home', label: 'Home' },
    { href: '#about', label: 'About Us' },
    { href: '#work', label: 'Our Work' },
    { href: '#focus', label: 'Focus Areas' },
    { href: '#programmes', label: 'Programmes' },
    { href: '#faq', label: 'FAQ' },
    { href: '#join', label: 'Join Us' },
  ]

  return (
    <header role="banner">
      <nav
        ref={navRef}
        className="navbar navbar-expand-lg navbar-dark fixed-top"
        role="navigation"
        aria-label="Primary navigation"
        itemScope
        itemType="https://schema.org/SiteNavigationElement"
        style={{
          backgroundColor: scrolled ? 'rgba(0, 51, 102, 0.95)' : 'var(--deep-blue)',
          padding: scrolled ? '10px 0' : '15px 0',
        }}
      >
        <div className="container">
          <a
            className="navbar-brand"
            href="/"
            onClick={(e) => handleNavClick(e, '#home')}
            aria-label="CAGAD - Centre for Advancement of Governance and Development - Home"
            itemProp="url"
          >
            <img
              src="/logo.png"
              alt="CAGAD - Centre for Advancement of Governance and Development Logo"
              width="90"
              height="70"
              className="d-inline-block align-text-top me-2"
              loading="eager"
              itemProp="image"
            />
          </a>
          <button
            className="navbar-toggler"
            type="button"
            onClick={() => setCollapsed(!collapsed)}
            aria-expanded={!collapsed}
            aria-controls="navbarNav"
            aria-label="Toggle navigation menu"
          >
            <span className="navbar-toggler-icon"></span>
          </button>
          <div className={`collapse navbar-collapse ${collapsed ? '' : 'show'}`} id="navbarNav">
            <ul className="navbar-nav ms-auto" role="menubar">
              {navLinks.map((link) => (
                <li className="nav-item" key={link.href} role="none">
                  <a
                    className="nav-link"
                    href={link.href}
                    onClick={(e) => handleNavClick(e, link.href)}
                    role="menuitem"
                    itemProp="url"
                    aria-label={`Navigate to ${link.label} section`}
                  >
                    <span itemProp="name">{link.label}</span>
                  </a>
                </li>
              ))}
            </ul>
          </div>
        </div>
      </nav>
    </header>
  )
}
