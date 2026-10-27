import { Link, useLocation, useNavigate } from 'react-router'

export default function Footer() {
  const { pathname } = useLocation()
  const navigate = useNavigate()

  const handleScrollTo = (e, target) => {
    e.preventDefault()
    if (pathname !== '/') {
      navigate(`/${target}`)
      return
    }
    const el = document.querySelector(target)
    if (el) el.scrollIntoView({ behavior: 'smooth' })
  }

  return (
    <footer role="contentinfo" aria-label="Site footer" itemScope itemType="https://schema.org/WPFooter">
      <div className="container">
        <div className="row">
          <div className="col-lg-4 mb-4 mb-lg-0">
            <h3 className="text-white mb-4">CAGAD</h3>
            <p>
              Advancing good governance and strengthening democratic accountability, security, and
              sustainable development in Africa through research and independent thinking.
            </p>
            <nav className="social-icons mt-4" aria-label="CAGAD social media links">
              <a href="https://www.facebook.com/cagadofficial" target="_blank" rel="noopener noreferrer" aria-label="Follow CAGAD on Facebook">
                <i className="fab fa-facebook-f" aria-hidden="true"></i>
              </a>
              <a href="https://www.instagram.com/cagadofficial" target="_blank" rel="noopener noreferrer" aria-label="Follow CAGAD on Instagram">
                <i className="fab fa-instagram" aria-hidden="true"></i>
              </a>
              <a href="https://x.com/cagad_official" target="_blank" rel="noopener noreferrer" aria-label="Follow CAGAD on X (formerly Twitter)">
                <i className="fab fa-x-twitter" aria-hidden="true"></i>
              </a>
              <a href="https://www.linkedin.com/company/cagad" target="_blank" rel="noopener noreferrer" aria-label="Connect with CAGAD on LinkedIn">
                <i className="fab fa-linkedin-in" aria-hidden="true"></i>
              </a>
            </nav>
          </div>
          <div className="col-md-4 col-lg-2 mb-4 mb-md-0">
            <nav className="footer-links" aria-label="Quick navigation links">
              <h5>Quick Links</h5>
              <ul>
                <li><a href="#home" onClick={(e) => handleScrollTo(e, '#home')}>Home</a></li>
                <li><a href="#about" onClick={(e) => handleScrollTo(e, '#about')}>About Us</a></li>
                <li><a href="#work" onClick={(e) => handleScrollTo(e, '#work')}>Our Work</a></li>
                <li><a href="#focus" onClick={(e) => handleScrollTo(e, '#focus')}>Focus Areas</a></li>
                <li><a href="#programmes" onClick={(e) => handleScrollTo(e, '#programmes')}>Programmes</a></li>
                <li><a href="#faq" onClick={(e) => handleScrollTo(e, '#faq')}>FAQ</a></li>
              </ul>
            </nav>
          </div>
          <div className="col-md-4 col-lg-3 mb-4 mb-md-0">
            <nav className="footer-links" aria-label="Resources links">
              <h5>Resources</h5>
              <ul>
                <li><a href="#">Reports</a></li>
                <li><a href="#">Publications</a></li>
                <li><Link to="/blog">Blog</Link></li>
                <li><a href="#">News &amp; Stories</a></li>
                <li><a href="#">Events</a></li>
              </ul>
            </nav>
          </div>
          <div className="col-md-4 col-lg-3">
            <div className="footer-links" itemScope itemType="https://schema.org/Organization">
              <h5>Contact Us</h5>
              <address>
                <ul>
                  <li>
                    <i className="fas fa-map-marker-alt" aria-hidden="true"></i>
                    <span itemProp="address" itemScope itemType="https://schema.org/PostalAddress">
                      <span itemProp="addressLocality">Abuja</span>, <span itemProp="addressCountry">Nigeria</span>
                    </span>
                  </li>
                  <li>
                    <i className="fas fa-envelope" aria-hidden="true"></i>
                    <a href="mailto:info@cagad.org" itemProp="email" aria-label="Send email to CAGAD">info@cagad.org</a>
                  </li>
                  <li>
                    <i className="fas fa-phone" aria-hidden="true"></i>
                    <a href="tel:+2349123733985" itemProp="telephone" aria-label="Call CAGAD">+2349123733985</a>
                  </li>
                </ul>
              </address>
            </div>
          </div>
        </div>
        <div className="copyright text-center">
          <p>
            <small>&copy; {new Date().getFullYear()} Centre for Advancement of Governance and Development (CAGAD). All rights reserved.</small>
          </p>
        </div>
      </div>
    </footer>
  )
}
