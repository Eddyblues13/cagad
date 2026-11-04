import { Link } from 'react-router'
import useAdminApi from '../hooks/useAdminApi.js'
import useDocumentTitle from '../hooks/useDocumentTitle.js'
import { useConfirm, useToast } from '../feedback/FeedbackContext.js'
import { adminRequest } from '../lib/api.js'
import { plural } from '../lib/format.js'
import { EmptyState, PageError, PageHeader, PageLoading } from '../components/ui.jsx'

function truncate(text, length) {
  return text.length > length ? `${text.slice(0, length).trimEnd()}…` : text
}

export default function CategoriesPage() {
  const { data, error, loading, reload } = useAdminApi('/categories')
  const confirm = useConfirm()
  const toast = useToast()

  useDocumentTitle('Categories')

  async function deleteCategory(category) {
    const accepted = await confirm({
      title: 'Delete this category?',
      message: `The “${category.name}” category will be deleted. Its ${plural(category.posts_count, 'post')} will be kept but become uncategorised.`,
      confirmLabel: 'Delete category',
    })
    if (!accepted) return

    try {
      const response = await adminRequest(`/categories/${category.id}`, { method: 'DELETE' })
      toast(response.message)
      reload()
    } catch (deleteError) {
      toast(deleteError.message, 'error')
    }
  }

  return (
    <>
      <PageHeader
        title="Categories"
        subtitle="Group posts by topic. Readers can filter the blog by category."
        actions={
          <Link to="/admin/categories/new" className="btn btn-primary">
            <i className="fa-solid fa-plus" aria-hidden="true" /> New category
          </Link>
        }
      />

      {error ? (
        <PageError error={error} onRetry={reload} />
      ) : !data ? (
        <PageLoading />
      ) : (
        <div className="admin-card">
          {data.data.length === 0 ? (
            <EmptyState
              icon="fa-solid fa-layer-group"
              title="No categories yet"
              actions={
                <Link to="/admin/categories/new" className="btn btn-primary btn-sm">
                  <i className="fa-solid fa-plus" aria-hidden="true" /> New category
                </Link>
              }
            >
              Create a few topics so readers can browse related posts.
            </EmptyState>
          ) : (
            <div className={`table-responsive${loading ? ' is-reloading' : ''}`}>
              <table className="table admin-table align-middle">
                <thead>
                  <tr>
                    <th scope="col">Category</th>
                    <th scope="col" className="d-none d-md-table-cell">
                      Description
                    </th>
                    <th scope="col" className="text-end">
                      Posts
                    </th>
                    <th scope="col" className="text-end">
                      <span className="visually-hidden">Actions</span>
                    </th>
                  </tr>
                </thead>
                <tbody>
                  {data.data.map((category) => (
                    <tr key={category.id}>
                      <td>
                        <Link to={`/admin/categories/${category.id}/edit`} className="table-title">
                          {category.name}
                        </Link>
                        <div className="table-sub">{category.slug}</div>
                      </td>
                      <td className="d-none d-md-table-cell table-sub">{category.description ? truncate(category.description, 110) : '—'}</td>
                      <td className="text-end">
                        <Link to={`/admin/posts?category=${category.id}`} className="count-pill text-decoration-none" title={`View posts in ${category.name}`}>
                          {category.posts_count}
                        </Link>
                      </td>
                      <td className="text-end">
                        <div className="row-actions">
                          <Link to={`/admin/categories/${category.id}/edit`} className="btn btn-sm btn-outline-secondary">
                            Edit
                          </Link>
                          <button type="button" className="btn btn-sm btn-outline-danger btn-icon" aria-label={`Delete ${category.name}`} onClick={() => deleteCategory(category)}>
                            <i className="fa-regular fa-trash-can" aria-hidden="true" />
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>
      )}
    </>
  )
}
