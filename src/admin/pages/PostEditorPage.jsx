import { Fragment, useEffect, useRef, useState } from 'react'
import { Link, useNavigate, useParams } from 'react-router'
import { useAuth } from '../auth/AuthContext.js'
import { useConfirm, useToast } from '../feedback/FeedbackContext.js'
import useAdminApi from '../hooks/useAdminApi.js'
import useDocumentTitle from '../hooks/useDocumentTitle.js'
import useUnsavedChangesWarning from '../hooks/useUnsavedChangesWarning.js'
import { adminRequest, fieldErrors } from '../lib/api.js'
import { format, slugify, timeAgo, toDateTimeInput } from '../lib/format.js'
import CoverImageField from '../components/CoverImageField.jsx'
import RichTextEditor from '../components/RichTextEditor.jsx'
import { CharCount, FieldError, Notice, PageError, PageHeader, PageLoading, StatusBadge } from '../components/ui.jsx'

const STATUSES = [
  ['draft', 'fa-regular fa-file-lines', 'Draft'],
  ['published', 'fa-solid fa-globe', 'Published'],
]

function fieldsFrom(post, defaultAuthor) {
  return {
    title: post?.title ?? '',
    slug: post?.slug ?? '',
    excerpt: post?.excerpt ?? '',
    status: post?.status ?? 'draft',
    published_at: toDateTimeInput(post?.published_at),
    category_id: post?.category_id ? String(post.category_id) : '',
    author_name: post ? (post.author_name ?? '') : defaultAuthor,
    cover_image_alt: post?.cover_image_alt ?? '',
  }
}

function invalid(errors, field) {
  return errors[field] ? ' is-invalid' : ''
}

function PostForm({ initialPost, categories, defaultAuthor }) {
  const navigate = useNavigate()
  const toast = useToast()
  const confirm = useConfirm()
  const [post, setPost] = useState(initialPost)
  const [fields, setFields] = useState(() => fieldsFrom(initialPost, defaultAuthor))
  const [coverFile, setCoverFile] = useState(null)
  const [removeCover, setRemoveCover] = useState(false)
  const [errors, setErrors] = useState({})
  const [formError, setFormError] = useState(null)
  const [editorError, setEditorError] = useState(null)
  const [isSaving, setIsSaving] = useState(false)
  const [isDirty, setIsDirty] = useState(false)
  const formRef = useRef(null)
  const editorRef = useRef(null)
  const isEditing = Boolean(post)

  useUnsavedChangesWarning(isDirty && !isSaving)

  // Ctrl/Cmd + S saves from anywhere on the page.
  useEffect(() => {
    const onKeyDown = (event) => {
      if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 's') {
        event.preventDefault()
        formRef.current?.requestSubmit()
      }
    }

    document.addEventListener('keydown', onKeyDown)
    return () => document.removeEventListener('keydown', onKeyDown)
  }, [])

  const markDirty = () => setIsDirty(true)
  const setField = (name) => (event) => {
    setFields((current) => ({ ...current, [name]: event.target.value }))
    markDirty()
  }

  async function save(event) {
    event.preventDefault()
    if (isSaving) return

    setIsSaving(true)
    setErrors({})
    setFormError(null)

    const editor = editorRef.current

    try {
      // Don't save while an image is still uploading, or it would be lost.
      if (editor?.getBody().querySelector('img[src^="blob:"], img[src^="data:"]')) {
        await editor.uploadImages()
      }

      const data = new FormData()
      Object.entries(fields).forEach(([name, value]) => data.append(name, value))
      data.append('body', editor ? editor.getContent() : (document.getElementById('body')?.value ?? ''))
      if (coverFile) data.append('cover_image', coverFile)
      if (removeCover) data.append('remove_cover', '1')
      // Files only arrive on POST requests, so edits are sent as POST with _method=PUT.
      if (isEditing) data.append('_method', 'PUT')

      const response = await adminRequest(isEditing ? `/posts/${post.id}` : '/posts', { method: 'POST', body: data })

      toast(response.message)
      setIsDirty(false)

      if (!isEditing) {
        navigate(`/admin/posts/${response.data.id}/edit`, { replace: true })
        return
      }

      setPost(response.data)
      setFields(fieldsFrom(response.data, defaultAuthor))
      setCoverFile(null)
      setRemoveCover(false)
    } catch (error) {
      setErrors(fieldErrors(error))
      setFormError(error.status === 422 ? 'The post wasn’t saved. Fix the highlighted fields and try again.' : error.message)
    } finally {
      setIsSaving(false)
    }
  }

  async function deletePost() {
    const accepted = await confirm({
      title: 'Delete this post?',
      message: `“${post.title}” will be deleted and its cover image removed from Cloudinary. This can’t be undone.`,
      confirmLabel: 'Delete post',
    })
    if (!accepted) return

    try {
      const response = await adminRequest(`/posts/${post.id}`, { method: 'DELETE' })
      setIsDirty(false)
      toast(response.message)
      navigate('/admin/posts')
    } catch (error) {
      toast(error.message, 'error')
    }
  }

  const saveLabel = isEditing ? 'Save changes' : 'Save post'
  const saveButtonContent = isSaving ? (
    <>
      <span className="spinner-border spinner-border-sm" aria-hidden="true" /> Saving…
    </>
  ) : (
    <>
      <i className="fa-solid fa-floppy-disk" aria-hidden="true" /> {saveLabel}
    </>
  )

  return (
    <>
      <PageHeader
        title={isEditing ? 'Edit post' : 'New post'}
        subtitle={isEditing ? null : 'Drafts stay private until you publish them.'}
        back="/admin/posts"
        backLabel="All posts"
        badge={isEditing && <StatusBadge post={post} />}
        actions={
          <>
            {isEditing && (
              <Link to={`/admin/posts/${post.id}`} className="btn btn-outline-secondary">
                <i className="fa-regular fa-eye" aria-hidden="true" /> Preview
              </Link>
            )}
            {post?.is_live && (
              <a href={`/blog/${post.slug}`} className="btn btn-outline-secondary" target="_blank" rel="noopener">
                <i className="fa-solid fa-arrow-up-right-from-square" aria-hidden="true" /> View on site
              </a>
            )}
            <button type="submit" form="post-form" className="btn btn-primary" disabled={isSaving}>
              {saveButtonContent}
            </button>
          </>
        }
      />

      {post?.is_scheduled && (
        <Notice tone="gold">
          Scheduled. This post goes live on <strong>{format(post.published_at, 'fullDateTime')}</strong> ({timeAgo(post.published_at)}).
        </Notice>
      )}

      {formError && <Notice tone="danger">{formError}</Notice>}

      <form ref={formRef} id="post-form" onSubmit={save} noValidate>
        <div className="row g-4">
          <div className="col-lg-7 col-xl-8">
            <div className="admin-card mb-4">
              <div className="admin-card-body">
                <div className="mb-3">
                  <label htmlFor="title" className="form-label">
                    Title
                  </label>
                  <input
                    type="text"
                    id="title"
                    value={fields.title}
                    onChange={setField('title')}
                    className={`form-control title-input${invalid(errors, 'title')}`}
                    maxLength={255}
                    required
                    placeholder="A clear, specific headline"
                    autoFocus={!isEditing}
                  />
                  <FieldError message={errors.title} />
                </div>

                <div className="mb-3">
                  <label htmlFor="slug" className="form-label">
                    URL
                  </label>
                  <div className="input-group has-validation">
                    <span className="input-group-text d-none d-sm-flex">{window.location.host}/blog/</span>
                    <span className="input-group-text d-sm-none">/blog/</span>
                    <input
                      type="text"
                      id="slug"
                      value={fields.slug}
                      onChange={setField('slug')}
                      className={`form-control${invalid(errors, 'slug')}`}
                      maxLength={255}
                      placeholder={slugify(fields.title) || 'generated-from-the-title'}
                      aria-describedby="slug-help"
                    />
                  </div>
                  <FieldError message={errors.slug} />
                  <div id="slug-help" className="form-text">
                    Leave empty to build it from the title.
                    {post?.is_live && <strong> This post is live, so changing the URL breaks links that were already shared.</strong>}
                  </div>
                </div>

                <div>
                  <div className="d-flex justify-content-between align-items-baseline gap-2">
                    <label htmlFor="excerpt" className="form-label">
                      Excerpt <span className="optional">(optional)</span>
                    </label>
                    <CharCount value={fields.excerpt} max={500} />
                  </div>
                  <textarea
                    id="excerpt"
                    rows={3}
                    maxLength={500}
                    value={fields.excerpt}
                    onChange={setField('excerpt')}
                    className={`form-control${invalid(errors, 'excerpt')}`}
                    aria-describedby="excerpt-help"
                  />
                  <FieldError message={errors.excerpt} />
                  <div id="excerpt-help" className="form-text">
                    Shown on blog cards and in search results. Leave empty to use the opening of the post.
                  </div>
                </div>
              </div>
            </div>

            <div className="admin-card editor-card">
              <div className="admin-card-header">
                <div>
                  <h2 className="admin-card-title">
                    <label htmlFor="body" className="m-0">
                      Content
                    </label>
                  </h2>
                  <p className="admin-card-subtitle">Images you add or paste are uploaded to Cloudinary automatically.</p>
                </div>
              </div>
              {(errors.body || editorError) && (
                <Notice tone="danger" className="m-3 mb-0">
                  {errors.body || editorError}
                </Notice>
              )}
              <RichTextEditor
                id="body"
                initialValue={post?.body ?? ''}
                editorRef={editorRef}
                onDirty={markDirty}
                onSave={() => formRef.current?.requestSubmit()}
                onLoadError={(error) => setEditorError(error.message)}
              />
            </div>
          </div>

          <div className="col-lg-5 col-xl-4">
            <div className="d-grid gap-4">
              <section className="admin-card" aria-labelledby="publish-title">
                <div className="admin-card-header">
                  <h2 className="admin-card-title" id="publish-title">
                    Publish
                  </h2>
                </div>
                <div className="admin-card-body">
                  <fieldset className="mb-3">
                    <legend className="form-label">Status</legend>
                    <div className="segmented">
                      {STATUSES.map(([value, icon, label]) => (
                        <Fragment key={value}>
                          <input type="radio" className="btn-check" name="status" id={`status-${value}`} value={value} checked={fields.status === value} onChange={setField('status')} />
                          <label className="btn" htmlFor={`status-${value}`}>
                            <i className={icon} aria-hidden="true" /> {label}
                          </label>
                        </Fragment>
                      ))}
                    </div>
                    <FieldError message={errors.status} />
                  </fieldset>

                  <div className="mb-3">
                    <label htmlFor="published_at" className="form-label">
                      Publish date <span className="optional">(optional)</span>
                    </label>
                    <input
                      type="datetime-local"
                      id="published_at"
                      value={fields.published_at}
                      onChange={setField('published_at')}
                      className={`form-control${invalid(errors, 'published_at')}`}
                      aria-describedby="published-at-help"
                    />
                    <FieldError message={errors.published_at} />
                    <div id="published-at-help" className="form-text">
                      Empty means now. A future date schedules a published post. Times are Lagos time (WAT).
                    </div>
                  </div>

                  <button type="submit" className="btn btn-primary w-100" disabled={isSaving}>
                    {saveButtonContent}
                  </button>
                  <p className="form-text text-center mb-0 mt-2">
                    Tip: press <kbd>Ctrl</kbd> + <kbd>S</kbd> (<kbd>⌘</kbd> + <kbd>S</kbd> on a Mac) to save.
                  </p>
                </div>
              </section>

              <section className="admin-card" aria-labelledby="organise-title">
                <div className="admin-card-header">
                  <h2 className="admin-card-title" id="organise-title">
                    Organise
                  </h2>
                </div>
                <div className="admin-card-body">
                  <div className="mb-3">
                    <label htmlFor="category_id" className="form-label">
                      Category
                    </label>
                    <select id="category_id" value={fields.category_id} onChange={setField('category_id')} className={`form-select${invalid(errors, 'category_id')}`}>
                      <option value="">Uncategorised</option>
                      {categories.map((category) => (
                        <option key={category.id} value={category.id}>
                          {category.name}
                        </option>
                      ))}
                    </select>
                    <FieldError message={errors.category_id} />
                  </div>

                  <div>
                    <label htmlFor="author_name" className="form-label">
                      Author
                    </label>
                    <input
                      type="text"
                      id="author_name"
                      value={fields.author_name}
                      onChange={setField('author_name')}
                      className={`form-control${invalid(errors, 'author_name')}`}
                      maxLength={120}
                      aria-describedby="author-help"
                    />
                    <FieldError message={errors.author_name} />
                    <div id="author-help" className="form-text">
                      The byline readers see, e.g. a fellow or a guest writer.
                    </div>
                  </div>
                </div>
              </section>

              <section className="admin-card" aria-labelledby="cover-title">
                <div className="admin-card-header">
                  <h2 className="admin-card-title" id="cover-title">
                    Cover image
                  </h2>
                </div>
                <div className="admin-card-body">
                  <CoverImageField
                    currentUrl={removeCover ? null : post?.cover_image?.editor}
                    file={coverFile}
                    onFileChange={(file) => {
                      setCoverFile(file)
                      setRemoveCover(false)
                      markDirty()
                    }}
                    error={errors.cover_image}
                  />

                  <div className={post?.cover_image ? 'mb-3' : ''}>
                    <label htmlFor="cover_image_alt" className="form-label">
                      Image description
                    </label>
                    <input
                      type="text"
                      id="cover_image_alt"
                      value={fields.cover_image_alt}
                      onChange={setField('cover_image_alt')}
                      className={`form-control${invalid(errors, 'cover_image_alt')}`}
                      maxLength={255}
                      aria-describedby="alt-help"
                    />
                    <FieldError message={errors.cover_image_alt} />
                    <div id="alt-help" className="form-text">
                      Describes the image for screen readers and search engines.
                    </div>
                  </div>

                  {post?.cover_image && !coverFile && (
                    <div className="form-check">
                      <input
                        className="form-check-input"
                        type="checkbox"
                        id="remove_cover"
                        checked={removeCover}
                        onChange={(event) => {
                          setRemoveCover(event.target.checked)
                          markDirty()
                        }}
                      />
                      <label className="form-check-label" htmlFor="remove_cover">
                        Remove the cover image
                      </label>
                    </div>
                  )}
                </div>
              </section>

              {isEditing && (
                <>
                  <section className="admin-card" aria-labelledby="details-title">
                    <div className="admin-card-header">
                      <h2 className="admin-card-title" id="details-title">
                        Details
                      </h2>
                    </div>
                    <div className="admin-card-body">
                      <dl className="meta-list">
                        <div>
                          <dt>Created by</dt>
                          <dd>{post.created_by ?? '—'}</dd>
                        </div>
                        <div>
                          <dt>Created</dt>
                          <dd>{format(post.created_at, 'dateTime')}</dd>
                        </div>
                        <div>
                          <dt>Last saved</dt>
                          <dd>{timeAgo(post.updated_at)}</dd>
                        </div>
                        <div>
                          <dt>Reading time</dt>
                          <dd>{post.reading_time} min</dd>
                        </div>
                      </dl>
                    </div>
                  </section>

                  <section className="admin-card" aria-labelledby="delete-title">
                    <div className="admin-card-body">
                      <h2 className="admin-card-title mb-1" id="delete-title">
                        Delete post
                      </h2>
                      <p className="small text-secondary mb-3">Removes the post and its cover image. This can’t be undone.</p>
                      <button type="button" className="btn btn-outline-danger btn-sm w-100" onClick={deletePost}>
                        <i className="fa-regular fa-trash-can" aria-hidden="true" /> Delete post
                      </button>
                    </div>
                  </section>
                </>
              )}
            </div>
          </div>
        </div>
      </form>
    </>
  )
}

export default function PostEditorPage() {
  const { postId } = useParams()
  const { admin } = useAuth()
  const post = useAdminApi(postId ? `/posts/${postId}` : null)
  const categories = useAdminApi('/categories')

  useDocumentTitle(postId ? `Edit: ${post.data?.data.title ?? 'post'}` : 'New post')

  const error = post.error || categories.error

  if (error) {
    return (
      <>
        <PageHeader title={postId ? 'Edit post' : 'New post'} back="/admin/posts" backLabel="All posts" />
        <PageError error={error} onRetry={post.error ? post.reload : categories.reload} />
      </>
    )
  }

  if ((postId && !post.data) || !categories.data) {
    return <PageLoading />
  }

  return <PostForm key={postId ?? 'new'} initialPost={postId ? post.data.data : null} categories={categories.data.data} defaultAuthor={admin.name} />
}
