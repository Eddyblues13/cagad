import { Navigate, useLocation } from 'react-router'
import { useAuth } from './AuthContext.js'
import { EmptyState, PageLoading } from '../components/ui.jsx'

/** Only signed-in admins get past this; everyone else is sent to the login page. */
export default function RequireAuth({ children }) {
  const { admin, status } = useAuth()
  const location = useLocation()

  if (status === 'checking') {
    return (
      <div className="admin-standalone">
        <PageLoading label="Signing you in…" />
      </div>
    )
  }

  if (status === 'offline') {
    return (
      <div className="admin-standalone">
        <div className="admin-card">
          <EmptyState
            icon="fa-solid fa-plug-circle-xmark"
            title="Can’t reach the blog server"
            actions={
              <button type="button" className="btn btn-primary btn-sm" onClick={() => window.location.reload()}>
                <i className="fa-solid fa-rotate-right" aria-hidden="true" /> Try again
              </button>
            }
          >
            Check your connection. If it keeps happening, the backend may be down.
          </EmptyState>
        </div>
      </div>
    )
  }

  if (!admin) {
    return <Navigate to="/admin/login" replace state={{ from: location }} />
  }

  return children
}
