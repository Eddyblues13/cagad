import { Link, useSearchParams } from 'react-router'
import useAdminApi from '../hooks/useAdminApi.js'
import useDocumentTitle from '../hooks/useDocumentTitle.js'
import { useConfirm, useToast } from '../feedback/FeedbackContext.js'
import { adminRequest } from '../lib/api.js'
import { format, timeAgo } from '../lib/format.js'
import Dropdown from '../components/Dropdown.jsx'
import Pagination from '../components/Pagination.jsx'
import { EmptyState, PageError, PageHeader, PageLoading, StatusBadge } from '../components/ui.jsx'

const STATUS_TABS = [
  ['', 'all', 'All'],
  ['published', 'published', 'Published'],
  ['scheduled', 'scheduled', 'Scheduled'],
  ['draft', 'draft', 'Drafts'],
]

export default function PostsPage() {
  const [searchParams, setSearchParams] = useSearchParams()
  const { data, error, loading, reload } = useAdminApi(`/posts?${searchParams}`)
  const { data: categories } = useAdminApi('/categories')
  const confirm = useConfirm()
  const toast = useToast()

  useDocumentTitle('Posts')

  const activeStatus = searchParams.get('status') ?? ''
  const isFiltered = searchParams.has('q') || searchParams.has('category') || activeStatus !== ''
  const counts = data?.meta.status_counts

  /** Change some filters and go back to the first page. */
  function updateFilters(changes) {
    const params = new URLSearchParams(searchParams)
    Object.entries(changes).forEach(([key, value]) => (value ? params.set(key, value) : params.delete(key)))
    params.delete('page')
    setSearchParams(params)
  }

  function tabLink(status) {
    const params = new URLSearchParams(searchParams)
    if (status) params.set('status', status)
    else params.delete('status')
    params.delete('page')
    return `?${params}`
  }

  async function deletePost(post) {
    const accepted = await confirm({
      title: 'Delete this post?',
      message: `“${post.title}” will be deleted and its cover image removed from Cloudinary. This can’t be undone.`,
      confirmLabel: 'Delete post',
    })
    if (!accepted) return

    try {
      const response = await adminRequest(`/posts/${post.id}`, { method: 'DELETE' })
      toast(response.message)
      reload()
    } catch (deleteError) {
      toast(deleteError.message, 'error')
    }
  }

  return (
    <>
      <PageHeader
        title="Posts"
        subtitle="Write, schedule and publish articles for the CAGAD blog."
        actions={
          <Link to="/admin/posts/new" className="btn btn-primary">
            <i className="fa-solid fa-plus" aria-hidden="true" /> New post
          </Link>
        }
      />

      <div className="admin-card">
        <div className="card-toolbar">
          <nav className="status-tabs" aria-label="Filter posts by status">
            {STATUS_TABS.map(([value, countKey, label]) => (
              <Link key={countKey} to={tabLink(value)} className={`status-tab${activeStatus === value ? ' active' : ''}`} aria-current={activeStatus === value ? 'page' : undefined}>
                {label} <span className="count">{counts?.[countKey] ?? '–'}</span>
              </Link>
            ))}
          </nav>

          <form
            key={searchParams.get('q') ?? ''}
            className="table-filters"
            role="search"
            onSubmit={(event) => {
              event.preventDefault()
              updateFilters({ q: String(new FormData(event.currentTarget).get('q')).trim() })
            }}
          >
            <div className="input-icon filter-search">
              <i className="fa-solid fa-magnifying-glass" aria-hidden="true" />
              <label htmlFor="q" className="visually-hidden">
                Search posts by title
              </label>
              <input type="search" id="q" name="q" defaultValue={searchParams.get('q') ?? ''} className="form-control" placeholder="Search by title…" />
            </div>
            <label htmlFor="category" className="visually-hidden">
              Category
            </label>
            <select id="category" className="form-select filter-select" value={searchParams.get('category') ?? ''} onChange={(event) => updateFilters({ category: event.target.value })}>
              <option value="">All categories</option>
              {categories?.data.map((category) => (
                <option key={category.id} value={category.id}>
                  {category.name}
                </option>
              ))}
            </select>
          </form>
        </div>

        {error ? (
          <div className="p-3">
            <PageError error={error} onRetry={reload} />
          </div>
        ) : !data ? (
          <PageLoading />
        ) : data.data.length === 0 ? (
          isFiltered ? (
            <EmptyState
              icon="fa-solid fa-magnifying-glass"
              title="No posts match"
              actions={
                <Link to="/admin/posts" className="btn btn-outline-secondary btn-sm">
                  Clear filters
                </Link>
              }
            >
              Try another search term, category or status.
            </EmptyState>
          ) : (
            <EmptyState
              icon="fa-regular fa-newspaper"
              title="No posts yet"
              actions={
                <Link to="/admin/posts/new" className="btn btn-primary btn-sm">
                  <i className="fa-solid fa-plus" aria-hidden="true" /> New post
                </Link>
              }
            >
              Write the first one. It stays a private draft until you publish it.
            </EmptyState>
          )
        ) : (
          <>
            <div className={`table-responsive${loading ? ' is-reloading' : ''}`}>
              <table className="table admin-table align-middle">
                <thead>
                  <tr>
                    <th scope="col">Post</th>
                    <th scope="col" className="d-none d-lg-table-cell">
                      Category
                    </th>
                    <th scope="col" className="d-none d-md-table-cell">
                      Status
                    </th>
                    <th scope="col" className="d-none d-xl-table-cell">
                      Publish date
                    </th>
                    <th scope="col" className="d-none d-xl-table-cell">
                      Updated
                    </th>
                    <th scope="col" className="text-end">
                      <span className="visually-hidden">Actions</span>
                    </th>
                  </tr>
                </thead>
                <tbody>
                  {data.data.map((post) => (
                    <tr key={post.id}>
                      <td>
                        <div className="d-flex align-items-center gap-3">
                          {post.cover_image ? (
                            <img src={post.cover_image.thumbnail} alt="" className="cover-thumb" width="64" height="44" loading="lazy" />
                          ) : (
                            <span className="cover-thumb" aria-hidden="true">
                              <i className="fa-regular fa-image" />
                            </span>
                          )}
                          <div className="min-w-0">
                            <Link to={`/admin/posts/${post.id}/edit`} className="table-title">
                              {post.title}
                            </Link>
                            <div className="table-meta">
                              <span className="d-md-none">
                                <StatusBadge post={post} />
                              </span>
                              <span className="slug-text d-none d-sm-inline-block">/blog/{post.slug}</span>
                              <span className="d-lg-none">{post.category?.name ?? 'Uncategorised'}</span>
                            </div>
                          </div>
                        </div>
                      </td>
                      <td className="d-none d-lg-table-cell">{post.category?.name ?? '—'}</td>
                      <td className="d-none d-md-table-cell">
                        <StatusBadge post={post} />
                      </td>
                      <td className="d-none d-xl-table-cell table-sub text-nowrap">{format(post.published_at, 'dateTime') || '—'}</td>
                      <td className="d-none d-xl-table-cell table-sub text-nowrap">{timeAgo(post.updated_at)}</td>
                      <td className="text-end">
                        <div className="row-actions">
                          <Link to={`/admin/posts/${post.id}/edit`} className="btn btn-sm btn-outline-secondary d-none d-sm-inline-flex">
                            Edit
                          </Link>
                          <Dropdown
                            buttonClassName="btn btn-sm btn-outline-secondary btn-icon"
                            ariaLabel={`More actions for ${post.title}`}
                            label={<i className="fa-solid fa-ellipsis" aria-hidden="true" />}
                          >
                            <Link className="dropdown-item d-sm-none" to={`/admin/posts/${post.id}/edit`}>
                              <i className="fa-regular fa-pen-to-square" aria-hidden="true" /> Edit
                            </Link>
                            <Link className="dropdown-item" to={`/admin/posts/${post.id}`}>
                              <i className="fa-regular fa-eye" aria-hidden="true" /> Preview
                            </Link>
                            {post.is_live && (
                              <a className="dropdown-item" href={`/blog/${post.slug}`} target="_blank" rel="noopener">
                                <i className="fa-solid fa-arrow-up-right-from-square" aria-hidden="true" /> View on site
                              </a>
                            )}
                            <div className="dropdown-divider" />
                            <button type="button" className="dropdown-item text-danger" onClick={() => deletePost(post)}>
                              <i className="fa-regular fa-trash-can" aria-hidden="true" /> Delete
                            </button>
                          </Dropdown>
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>

            {data.meta.last_page > 1 && (
              <div className="table-footer">
                <Pagination meta={data.meta} />
              </div>
            )}
          </>
        )}
      </div>
    </>
  )
}
