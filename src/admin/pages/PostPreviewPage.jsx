import { Link, useParams } from 'react-router'
import useAdminApi from '../hooks/useAdminApi.js'
import useDocumentTitle from '../hooks/useDocumentTitle.js'
import { format } from '../lib/format.js'
import { EmptyState, Notice, PageError, PageHeader, PageLoading, StatusBadge } from '../components/ui.jsx'

export default function PostPreviewPage() {
  const { postId } = useParams()
  const { data, error, reload } = useAdminApi(`/posts/${postId}`)
  const post = data?.data

  useDocumentTitle(`Preview: ${post?.title ?? 'post'}`)

  if (error) {
    return (
      <>
        <PageHeader title="Preview" back="/admin/posts" backLabel="All posts" />
        <PageError error={error} onRetry={reload} />
      </>
    )
  }

  if (!post) return <PageLoading />

  return (
    <>
      <PageHeader
        title="Preview"
        back={`/admin/posts/${post.id}/edit`}
        backLabel="Back to editing"
        badge={<StatusBadge post={post} />}
        actions={
          <>
            {post.is_live && (
              <a href={`/blog/${post.slug}`} className="btn btn-outline-secondary" target="_blank" rel="noopener">
                <i className="fa-solid fa-arrow-up-right-from-square" aria-hidden="true" /> View on site
              </a>
            )}
            <Link to={`/admin/posts/${post.id}/edit`} className="btn btn-primary">
              <i className="fa-regular fa-pen-to-square" aria-hidden="true" /> Edit
            </Link>
          </>
        }
      />

      {!post.is_live && (
        <Notice tone="gold" icon="fa-regular fa-eye">
          This is a preview of a {post.status_label.toLowerCase()} post. Visitors can’t see it yet.
          {post.is_scheduled && ` It goes live ${format(post.published_at, 'fullDateTime')}.`}
        </Notice>
      )}

      <article className="admin-card p-4 p-md-5">
        <div className="post-preview">
          {post.category && <p className="eyebrow">{post.category.name}</p>}
          <h1 className="mb-3">{post.title}</h1>
          <p className="text-secondary mb-4">
            {post.author_name && `By ${post.author_name} · `}
            {format(post.published_at, 'longDate') || 'Not yet published'} · {post.reading_time} min read
          </p>

          {post.cover_image && <img src={post.cover_image.full} alt={post.cover_image_alt || post.title} className="cover" />}

          {post.body ? (
            // The backend sanitises post HTML before storing it.
            <div className="prose" dangerouslySetInnerHTML={{ __html: post.body }} />
          ) : (
            <EmptyState icon="fa-regular fa-file-lines" title="This post has no content yet" compact>
              Add the body in the editor before publishing.
            </EmptyState>
          )}
        </div>
      </article>
    </>
  )
}
