import { Link } from 'react-router'
import useDocumentTitle from '../hooks/useDocumentTitle.js'
import { EmptyState } from '../components/ui.jsx'

export default function AdminNotFoundPage({ forbidden = false }) {
  useDocumentTitle(forbidden ? 'No access' : 'Page not found')

  return (
    <div className="admin-card">
      <EmptyState
        icon={forbidden ? 'fa-solid fa-lock' : 'fa-regular fa-compass'}
        title={forbidden ? 'Only super admins can see this' : 'Page not found'}
        actions={
          <Link to="/admin" className="btn btn-primary btn-sm">
            Back to the dashboard
          </Link>
        }
      >
        {forbidden ? 'Ask a super admin if you need to manage admin accounts.' : 'The page you asked for isn’t part of the admin.'}
      </EmptyState>
    </div>
  )
}
