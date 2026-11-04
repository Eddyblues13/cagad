import { Link } from 'react-router'
import { useAuth } from '../auth/AuthContext.js'
import useAdminApi from '../hooks/useAdminApi.js'
import useDocumentTitle from '../hooks/useDocumentTitle.js'
import { useConfirm, useToast } from '../feedback/FeedbackContext.js'
import { adminRequest } from '../lib/api.js'
import { timeAgo } from '../lib/format.js'
import { Avatar, PageError, PageHeader, PageLoading, RolePill } from '../components/ui.jsx'

export default function AdminsPage() {
  const { admin: you } = useAuth()
  const { data, error, loading, reload } = useAdminApi('/admins')
  const confirm = useConfirm()
  const toast = useToast()

  useDocumentTitle('Admins')

  async function removeAdmin(account) {
    const accepted = await confirm({
      title: 'Remove this admin?',
      message: `${account.name} will no longer be able to sign in. Posts they created stay on the blog.`,
      confirmLabel: 'Remove admin',
    })
    if (!accepted) return

    try {
      const response = await adminRequest(`/admins/${account.id}`, { method: 'DELETE' })
      toast(response.message)
      reload()
    } catch (deleteError) {
      toast(deleteError.message, 'error')
    }
  }

  return (
    <>
      <PageHeader
        title="Admins"
        subtitle="Everyone who can sign in to this panel. Editors run the blog; super admins also manage accounts."
        actions={
          <Link to="/admin/admins/new" className="btn btn-primary">
            <i className="fa-solid fa-user-plus" aria-hidden="true" /> Add admin
          </Link>
        }
      />

      {error ? (
        <PageError error={error} onRetry={reload} />
      ) : !data ? (
        <PageLoading />
      ) : (
        <div className="admin-card">
          <div className={`table-responsive${loading ? ' is-reloading' : ''}`}>
            <table className="table admin-table align-middle">
              <thead>
                <tr>
                  <th scope="col">Admin</th>
                  <th scope="col" className="d-none d-md-table-cell">
                    Role
                  </th>
                  <th scope="col" className="d-none d-sm-table-cell">
                    Status
                  </th>
                  <th scope="col" className="d-none d-xl-table-cell">
                    Last sign-in
                  </th>
                  <th scope="col" className="d-none d-xl-table-cell text-end">
                    Posts
                  </th>
                  <th scope="col" className="text-end">
                    <span className="visually-hidden">Actions</span>
                  </th>
                </tr>
              </thead>
              <tbody>
                {data.data.map((account) => {
                  const isYou = account.id === you.id

                  return (
                    <tr key={account.id}>
                      <td>
                        <div className="d-flex align-items-center gap-3">
                          <Avatar admin={account} />
                          <div className="min-w-0">
                            <div className="table-title">
                              {account.name}
                              {isYou && <span className="pill pill-outline ms-1">You</span>}
                            </div>
                            <div className="table-sub text-truncate">{account.email}</div>
                            <div className="table-meta d-md-none">
                              <span>{account.role_label}</span>
                              {!account.is_active && <span className="pill pill-red d-sm-none">Deactivated</span>}
                            </div>
                          </div>
                        </div>
                      </td>
                      <td className="d-none d-md-table-cell">
                        <RolePill admin={account} />
                      </td>
                      <td className="d-none d-sm-table-cell">
                        {account.is_active ? (
                          <span className="pill pill-green">
                            <i className="fa-solid fa-circle-check" aria-hidden="true" /> Active
                          </span>
                        ) : (
                          <span className="pill pill-red">
                            <i className="fa-solid fa-ban" aria-hidden="true" /> Deactivated
                          </span>
                        )}
                      </td>
                      <td className="d-none d-xl-table-cell table-sub text-nowrap">{account.last_login_at ? timeAgo(account.last_login_at) : 'Never'}</td>
                      <td className="d-none d-xl-table-cell text-end">
                        <span className="count-pill">{account.posts_count}</span>
                      </td>
                      <td className="text-end">
                        {isYou ? (
                          <Link to="/admin/account" className="btn btn-sm btn-outline-secondary text-nowrap">
                            My account
                          </Link>
                        ) : (
                          <div className="row-actions">
                            <Link to={`/admin/admins/${account.id}/edit`} className="btn btn-sm btn-outline-secondary">
                              Edit
                            </Link>
                            <button type="button" className="btn btn-sm btn-outline-danger btn-icon" aria-label={`Remove ${account.name}`} onClick={() => removeAdmin(account)}>
                              <i className="fa-regular fa-trash-can" aria-hidden="true" />
                            </button>
                          </div>
                        )}
                      </td>
                    </tr>
                  )
                })}
              </tbody>
            </table>
          </div>
        </div>
      )}
    </>
  )
}
