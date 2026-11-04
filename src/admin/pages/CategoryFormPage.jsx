import { useState } from 'react'
import { Link, useNavigate, useParams } from 'react-router'
import useAdminApi from '../hooks/useAdminApi.js'
import useDocumentTitle from '../hooks/useDocumentTitle.js'
import { useToast } from '../feedback/FeedbackContext.js'
import { adminRequest, fieldErrors } from '../lib/api.js'
import { slugify } from '../lib/format.js'
import { CharCount, FieldError, Notice, PageError, PageHeader, PageLoading } from '../components/ui.jsx'

function CategoryForm({ category }) {
  const navigate = useNavigate()
  const toast = useToast()
  const [fields, setFields] = useState({
    name: category?.name ?? '',
    slug: category?.slug ?? '',
    description: category?.description ?? '',
  })
  const [errors, setErrors] = useState({})
  const [formError, setFormError] = useState(null)
  const [isSaving, setIsSaving] = useState(false)

  const setField = (name) => (event) => setFields((current) => ({ ...current, [name]: event.target.value }))

  async function save(event) {
    event.preventDefault()
    setIsSaving(true)
    setErrors({})
    setFormError(null)

    try {
      const response = await adminRequest(category ? `/categories/${category.id}` : '/categories', {
        method: category ? 'PUT' : 'POST',
        body: fields,
      })
      toast(response.message)
      navigate('/admin/categories')
    } catch (error) {
      setErrors(fieldErrors(error))
      if (error.status !== 422) setFormError(error.message)
      setIsSaving(false)
    }
  }

  return (
    <div className="row g-4">
      <div className="col-lg-8">
        {formError && <Notice tone="danger">{formError}</Notice>}
        <form className="admin-card" onSubmit={save} noValidate>
          <div className="admin-card-body">
            <div className="mb-3">
              <label htmlFor="name" className="form-label">
                Name
              </label>
              <input
                type="text"
                id="name"
                value={fields.name}
                onChange={setField('name')}
                className={`form-control${errors.name ? ' is-invalid' : ''}`}
                maxLength={100}
                required
                autoFocus
                placeholder="e.g. Policy Briefs"
              />
              <FieldError message={errors.name} />
            </div>

            <div className="mb-3">
              <label htmlFor="slug" className="form-label">
                Slug <span className="optional">(optional)</span>
              </label>
              <input
                type="text"
                id="slug"
                value={fields.slug}
                onChange={setField('slug')}
                className={`form-control${errors.slug ? ' is-invalid' : ''}`}
                maxLength={120}
                placeholder={slugify(fields.name) || 'built-from-the-name'}
                aria-describedby="slug-help"
              />
              <FieldError message={errors.slug} />
              <div id="slug-help" className="form-text">
                Used in links that filter the blog. Leave empty to build it from the name.
              </div>
            </div>

            <div>
              <div className="d-flex justify-content-between align-items-baseline gap-2">
                <label htmlFor="description" className="form-label">
                  Description <span className="optional">(optional)</span>
                </label>
                <CharCount value={fields.description} max={500} />
              </div>
              <textarea
                id="description"
                rows={4}
                maxLength={500}
                value={fields.description}
                onChange={setField('description')}
                className={`form-control${errors.description ? ' is-invalid' : ''}`}
              />
              <FieldError message={errors.description} />
            </div>
          </div>

          <div className="admin-card-footer d-flex justify-content-end gap-2">
            <Link to="/admin/categories" className="btn btn-outline-secondary">
              Cancel
            </Link>
            <button type="submit" className="btn btn-primary" disabled={isSaving}>
              <i className="fa-solid fa-floppy-disk" aria-hidden="true" /> Save category
            </button>
          </div>
        </form>
      </div>

      <div className="col-lg-4">
        <Notice className="mb-0">
          Only categories with at least one published post appear on the public blog, so you can set topics up before writing for them.
        </Notice>
      </div>
    </div>
  )
}

export default function CategoryFormPage() {
  const { categoryId } = useParams()
  const { data, error, reload } = useAdminApi(categoryId ? `/categories/${categoryId}` : null)
  const category = data?.data

  useDocumentTitle(categoryId ? `Edit: ${category?.name ?? 'category'}` : 'New category')

  const header = (
    <PageHeader
      title={categoryId ? 'Edit category' : 'New category'}
      subtitle={categoryId ? category?.name : 'Add a topic readers can browse.'}
      back="/admin/categories"
      backLabel="All categories"
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

  return (
    <>
      {header}
      {categoryId && !category ? <PageLoading /> : <CategoryForm key={categoryId ?? 'new'} category={category} />}
    </>
  )
}
