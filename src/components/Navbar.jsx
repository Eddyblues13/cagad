import { useState, useEffect, useRef } from 'react'

export default function Navbar() {
  const [scrolled, setScrolled] = useState(false)
  const [collapsed, setCollapsed] = useState(true)
  const [aboutOpen, setAboutOpen] = useState(false)
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
    setAboutOpen(false)
    if (targetId === '#') return
    const el = document.querySelector(targetId)
    if (el) {
      el.scrollIntoView({ behavior: 'smooth' })
    }
  }

  const aboutLinks = [
    { href: '#about', label: 'Who We Are' },
    { href: '#leadership', label: 'Board & Team' },
  ]

  const navLinks = [
    { href: '#home', label: 'Home' },
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
              className="d-inline-block"
              loading="eager"
              itemProp="image"
            />
            <span className="brand-rc">RC NO. 9471761</span>
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
              <li className="nav-item" role="none">
                <a
                  className="nav-link"
                  href="#home"
                  onClick={(e) => handleNavClick(e, '#home')}
                  role="menuitem"
                  itemProp="url"
                  aria-label="Navigate to Home section"
                >
                  <span itemProp="name">Home</span>
                </a>
              </li>

              <li
                className={`nav-item dropdown ${aboutOpen ? 'show' : ''}`}
                role="none"
                onMouseEnter={() => setAboutOpen(true)}
                onMouseLeave={() => setAboutOpen(false)}
              >
                <a
                  className="nav-link dropdown-toggle"
                  href="#about"
                  onClick={(e) => {
                    e.preventDefault()
                    setAboutOpen((o) => !o)
                  }}
                  role="menuitem"
                  aria-haspopup="true"
                  aria-expanded={aboutOpen}
                  aria-label="About Us menu"
                >
                  <span itemProp="name">About Us</span>
                </a>
                <ul className={`dropdown-menu ${aboutOpen ? 'show' : ''}`} role="menu" aria-label="About Us submenu">
                  {aboutLinks.map((link) => (
                    <li key={link.href} role="none">
                      <a
                        className="dropdown-item"
                        href={link.href}
                        onClick={(e) => handleNavClick(e, link.href)}
                        role="menuitem"
                      >
                        {link.label}
                      </a>
                    </li>
                  ))}
                </ul>
              </li>

              {navLinks.slice(1).map((link) => (
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
