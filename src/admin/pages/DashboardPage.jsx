import { useState } from 'react'
import { Link } from 'react-router'
import { useAuth } from '../auth/AuthContext.js'
import useAdminApi from '../hooks/useAdminApi.js'
import useDocumentTitle from '../hooks/useDocumentTitle.js'
import { EmptyState, PageError, PageHeader, PageLoading, StatusBadge } from '../components/ui.jsx'
import { format, greeting, plural, timeAgo, today } from '../lib/format.js'

function statTiles({ stats, upcoming_posts: upcoming }) {
  const nextPost = upcoming[0]

  return [
    {
      label: 'Published',
      value: stats.published,
      icon: 'fa-solid fa-circle-check',
      tone: 'tone-green',
      meta: stats.published_this_month > 0 ? `${stats.published_this_month} this month` : 'None yet this month',
      to: '/admin/posts?status=published',
    },
    {
      label: 'Scheduled',
      value: stats.scheduled,
      icon: 'fa-regular fa-clock',
      tone: 'tone-gold',
      meta: nextPost ? `Next on ${format(nextPost.published_at, 'dayMonth')}` : 'Nothing queued',
      to: '/admin/posts?status=scheduled',
    },
    {
      label: 'Drafts',
      value: stats.drafts,
      icon: 'fa-regular fa-file-lines',
      tone: 'tone-slate',
      meta: 'Not visible on the site',
      to: '/admin/posts?status=draft',
    },
    {
      label: 'Categories',
      value: stats.categories,
      icon: 'fa-solid fa-layer-group',
      tone: 'tone-navy',
      meta: `${stats.categories === 1 ? 'topic' : 'topics'} on the blog`,
      to: '/admin/categories',
    },
  ]
}

function ActivityChart({ activity }) {
  // Clean, whole-number gridlines: 0, half and the top of the scale.
  const peak = Math.max(...activity.map((point) => point.count))
  const peakIndex = activity.findIndex((point) => point.count === peak)
  const axisMax = Math.max(2, peak + (peak % 2))

  return (
    <div className="activity-chart">
      <div className="activity-axis" aria-hidden="true">
        <span style={{ '--at': '100%' }}>{axisMax}</span>
        <span style={{ '--at': '50%' }}>{axisMax / 2}</span>
        <span style={{ '--at': '0%' }}>0</span>
      </div>
      <div className="activity-plot">
        {activity.map((point, index) => {
          const postsLabel = plural(point.count, 'post')
          const height = `${Math.round((point.count / axisMax) * 10000) / 100}%`

          return (
            <div key={point.month} className="activity-col" style={{ '--h': height }} tabIndex={0} role="img" aria-label={`${point.month}: ${postsLabel} published`}>
              {peak > 0 && index === peakIndex && (
                <span className="activity-value position-absolute" style={{ bottom: 'calc(var(--h) + 4px)' }} aria-hidden="true">
                  {point.count}
                </span>
              )}
              <span className="activity-bar" />
              <span className="activity-tip" aria-hidden="true">
                <strong>{postsLabel}</strong>
                {point.month}
              </span>
            </div>
          )
        })}
        {peak === 0 && <p className="activity-empty mb-0">Nothing has been published in the last 12 months. Published posts will show up here.</p>}
      </div>
      <div className="activity-months" aria-hidden="true">
        {activity.map((point) => (
          <span key={point.month}>{point.label}</span>
        ))}
      </div>
    </div>
  )
}

function PublishingActivity({ activity }) {
  const [view, setView] = useState('chart')
  const tabs = [
    ['chart', 'fa-solid fa-chart-column', 'Chart'],
    ['table', 'fa-solid fa-table-list', 'Table'],
  ]

  return (
    <section className="admin-card h-100" aria-labelledby="activity-title">
      <div className="admin-card-header">
        <div>
          <h2 className="admin-card-title" id="activity-title">
            Publishing activity
          </h2>
          <p className="admin-card-subtitle">Posts that went live each month over the last 12 months</p>
        </div>
        <div className="nav nav-pills view-toggle" role="tablist">
          {tabs.map(([key, icon, label]) => (
            <button key={key} type="button" role="tab" aria-selected={view === key} className={`nav-link${view === key ? ' active' : ''}`} onClick={() => setView(key)}>
              <i className={`${icon} me-1`} aria-hidden="true" />
              {label}
            </button>
          ))}
        </div>
      </div>
      <div className="admin-card-body">
        {view === 'chart' ? (
          <ActivityChart activity={activity} />
        ) : (
          <div className="table-responsive">
            <table className="table table-sm data-table mb-0">
              <caption className="visually-hidden">Posts published per month</caption>
              <thead>
                <tr>
                  <th scope="col">Month</th>
                  <th scope="col" className="text-end">
                    Posts published
                  </th>
                </tr>
              </thead>
              <tbody>
                {[...activity].reverse().map((point) => (
                  <tr key={point.month}>
                    <td>{point.month}</td>
                    <td className="text-end">{point.count}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </section>
  )
}

export default function DashboardPage() {
  const { admin } = useAuth()
  const { data, error, loading, reload } = useAdminApi('/dashboard')

  useDocumentTitle('Dashboard')

  const header = (
    <PageHeader
      title={`${greeting()}, ${admin.name.split(' ')[0]}`}
      subtitle={`Here’s how the CAGAD blog is doing this ${today()}.`}
      actions={
        <>
          <a href="/blog" className="btn btn-outline-secondary" target="_blank" rel="noopener">
            <i className="fa-solid fa-arrow-up-right-from-square" aria-hidden="true" /> View blog
          </a>
          <Link to="/admin/posts/new" className="btn btn-primary">
            <i className="fa-solid fa-plus" aria-hidden="true" /> New post
          </Link>
        </>
      }
    />
  )

  if (error) {
    return (
      <>
        {header}
        <PageError error={error} onRetry={reload} />
      </>
    )
  }

  if (loading && !data) {
    return (
      <>
        {header}
        <PageLoading />
      </>
    )
  }

  const categoryPeak = Math.max(1, ...data.top_categories.map((category) => category.posts_count))

  return (
    <>
      {header}

      <div className="row g-3 g-xl-4 mb-3 mb-xl-4">
        {statTiles(data).map((tile) => (
          <div key={tile.label} className="col-6 col-xl-3">
            <Link to={tile.to} className="admin-card stat-card">
              <span className={`stat-icon ${tile.tone}`}>
                <i className={tile.icon} aria-hidden="true" />
              </span>
              <span className="min-w-0">
                <span className="stat-label d-block">{tile.label}</span>
                <span className="stat-value d-block">{tile.value.toLocaleString('en-GB')}</span>
                <span className="stat-meta d-block">{tile.meta}</span>
              </span>
            </Link>
          </div>
        ))}
      </div>

      <div className="row g-3 g-xl-4 mb-3 mb-xl-4">
        <div className="col-xl-8">
          <PublishingActivity activity={data.activity} />
        </div>

        <div className="col-xl-4">
          <section className="admin-card h-100" aria-labelledby="upcoming-title">
            <div className="admin-card-header">
              <div>
                <h2 className="admin-card-title" id="upcoming-title">
                  Up next
                </h2>
                <p className="admin-card-subtitle">Scheduled posts go live on their own</p>
              </div>
              {data.upcoming_posts.length > 0 && (
                <Link to="/admin/posts?status=scheduled" className="card-link">
                  View all
                </Link>
              )}
            </div>
            {data.upcoming_posts.length === 0 ? (
              <EmptyState icon="fa-regular fa-calendar" title="Nothing scheduled" compact>
                Publish a post with a future date and it will appear here.
              </EmptyState>
            ) : (
              data.upcoming_posts.map((post) => (
                <Link key={post.id} to={`/admin/posts/${post.id}/edit`} className="list-row">
                  <span className="date-chip" aria-hidden="true">
                    <span className="date-chip-month">{format(post.published_at, 'month')}</span>
                    <span className="date-chip-day">{format(post.published_at, 'day')}</span>
                  </span>
                  <span className="min-w-0">
                    <span className="table-title text-truncate">{post.title}</span>
                    <span className="table-sub d-block">
                      {format(post.published_at, 'shortDayTime')} · {timeAgo(post.published_at)}
                    </span>
                  </span>
                </Link>
              ))
            )}
          </section>
        </div>
      </div>

      <div className="row g-3 g-xl-4">
        <div className="col-xl-8">
          <section className="admin-card h-100" aria-labelledby="recent-title">
            <div className="admin-card-header">
              <div>
                <h2 className="admin-card-title" id="recent-title">
                  Recently updated
                </h2>
                <p className="admin-card-subtitle">The latest changes across all posts</p>
              </div>
              <Link to="/admin/posts" className="card-link">
                All posts
              </Link>
            </div>
            {data.recent_posts.length === 0 ? (
              <EmptyState
                icon="fa-regular fa-newspaper"
                title="No posts yet"
                actions={
                  <Link to="/admin/posts/new" className="btn btn-primary btn-sm">
                    <i className="fa-solid fa-plus" aria-hidden="true" /> New post
                  </Link>
                }
              >
                Write the first post. It stays a private draft until you publish it.
              </EmptyState>
            ) : (
              <div className="table-responsive">
                <table className="table admin-table align-middle">
                  <thead>
                    <tr>
                      <th scope="col">Post</th>
                      <th scope="col" className="d-none d-sm-table-cell">
                        Status
                      </th>
                      <th scope="col" className="d-none d-md-table-cell">
                        Updated
                      </th>
                      <th scope="col" className="text-end">
                        <span className="visually-hidden">Actions</span>
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    {data.recent_posts.map((post) => (
                      <tr key={post.id}>
                        <td>
                          <Link to={`/admin/posts/${post.id}/edit`} className="table-title">
                            {post.title}
                          </Link>
                          <div className="table-meta">
                            <span>{post.category?.name ?? 'Uncategorised'}</span>
                            <span className="d-sm-none">
                              <StatusBadge post={post} />
                            </span>
                          </div>
                        </td>
                        <td className="d-none d-sm-table-cell">
                          <StatusBadge post={post} />
                        </td>
                        <td className="d-none d-md-table-cell table-sub text-nowrap">{timeAgo(post.updated_at)}</td>
                        <td className="text-end">
                          <Link to={`/admin/posts/${post.id}/edit`} className="btn btn-sm btn-outline-secondary">
                            Edit
                          </Link>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            )}
          </section>
        </div>

        <div className="col-xl-4 d-flex flex-column gap-3 gap-xl-4">
          <section className="admin-card" aria-labelledby="categories-title">
            <div className="admin-card-header">
              <div>
                <h2 className="admin-card-title" id="categories-title">
                  Posts by category
                </h2>
                <p className="admin-card-subtitle">Published posts in the top five</p>
              </div>
              <Link to="/admin/categories" className="card-link">
                Manage
              </Link>
            </div>
            <div className="admin-card-body">
              {data.top_categories.length === 0 ? (
                <p className="text-secondary small mb-0">No categories yet.</p>
              ) : (
                <ul className="meter-list">
                  {data.top_categories.map((category) => (
                    <li key={category.id}>
                      <div className="meter-row">
                        <Link to={`/admin/posts?category=${category.id}`} className="meter-name text-decoration-none">
                          {category.name}
                        </Link>
                        <span className="meter-value">{category.posts_count}</span>
                      </div>
                      <div className="meter-track" aria-hidden="true">
                        <span className="meter-fill" style={{ width: `${Math.round((category.posts_count / categoryPeak) * 10000) / 100}%` }} />
                      </div>
                    </li>
                  ))}
                </ul>
              )}
            </div>
          </section>

          <section className="admin-card" aria-labelledby="actions-title">
            <div className="admin-card-header">
              <h2 className="admin-card-title" id="actions-title">
                Quick actions
              </h2>
            </div>
            <div className="admin-card-body">
              <div className="quick-actions">
                <Link to="/admin/posts/new" className="quick-action">
                  <i className="fa-solid fa-pen-nib tone-green" aria-hidden="true" /> Write a post
                </Link>
                <Link to="/admin/categories/new" className="quick-action">
                  <i className="fa-solid fa-folder-plus tone-navy" aria-hidden="true" /> Add a category
                </Link>
                {admin.is_super_admin ? (
                  <Link to="/admin/admins/new" className="quick-action">
                    <i className="fa-solid fa-user-plus tone-gold" aria-hidden="true" /> Add an admin
                  </Link>
                ) : (
                  <a href="/blog" className="quick-action" target="_blank" rel="noopener">
                    <i className="fa-solid fa-globe tone-gold" aria-hidden="true" /> View live blog
                  </a>
                )}
                <Link to="/admin/account" className="quick-action">
                  <i className="fa-solid fa-key tone-slate" aria-hidden="true" /> Change password
                </Link>
              </div>
            </div>
          </section>
        </div>
      </div>
    </>
  )
}
