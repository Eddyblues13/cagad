import { useEffect } from 'react'
import { Outlet, useLocation } from 'react-router'
import Navbar from './Navbar.jsx'
import Footer from './Footer.jsx'
import BackToTop from './BackToTop.jsx'

export default function SiteLayout() {
  const { pathname, hash } = useLocation()

  useEffect(() => {
    // Import Bootstrap JS for navbar toggler functionality
    import('bootstrap/dist/js/bootstrap.bundle.min.js')
  }, [])

  // Start each new page at the top; in-page #anchors handle their own scrolling.
  useEffect(() => {
    if (!hash) window.scrollTo(0, 0)
  }, [pathname, hash])

  return (
    <>
      <Navbar />
      <main id="main-content" role="main" aria-label="Main content">
        <Outlet />
      </main>
      <Footer />
      <BackToTop />
    </>
  )
}
