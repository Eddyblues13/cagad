import { Link } from 'react-router'
import usePageMeta from '../hooks/usePageMeta.js'

export default function NotFoundPage() {
  usePageMeta({ title: 'Page not found | CAGAD', path: '/404' })

  return (
    <div className="container section-padding">
      <div className="blog-empty">
        <i className="far fa-compass" aria-hidden="true"></i>
        <h1 className="h3">Page not found</h1>
        <p>The page you’re looking for doesn’t exist or has moved.</p>
        <div className="d-flex gap-2 justify-content-center">
          <Link to="/" className="btn btn-primary">
            Go to the homepage
          </Link>
          <Link to="/blog" className="btn btn-outline-primary">
            Read the blog
          </Link>
        </div>
      </div>
    </div>
  )
}
