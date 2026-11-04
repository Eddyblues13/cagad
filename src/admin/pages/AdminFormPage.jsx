import { useState } from 'react'
import { Link, Navigate, useNavigate, useParams } from 'react-router'
import { useAuth } from '../auth/AuthContext.js'
import useAdminApi from '../hooks/useAdminApi.js'
import useDocumentTitle from '../hooks/useDocumentTitle.js'
import { useToast } from '../feedback/FeedbackContext.js'
import { adminRequest, fieldErrors } from '../lib/api.js'
import { format, timeAgo } from '../lib/format.js'
import { Avatar, FieldError, Notice, PageError, PageHeader, PageLoading, PasswordInput } from '../components/ui.jsx'

const ROLES = [
  ['super_admin', 'Super Admin', 'Everything an editor can do, plus adding, editing and removing admin accounts.'],
  ['editor', 'Editor', 'Writes, schedules and publishes posts, and manages categories.'],
]

function AdminForm({ account }) {
  const navigate = useNavigate()
  const toast = useToast()
  const [fields, setFields] = useState({
    name: account?.name ?? '',
    email: account?.email ?? '',
    role: account?.role ?? 'editor',
    is_active: account?.is_active ?? true,
    password: '',
    password_confirmation: '',
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
      const response = await adminRequest(account ? `/admins/${account.id}` : '/admins', {
        method: account ? 'PUT' : 'POST',
        body: fields,
      })
      toast(response.message)
      navigate('/admin/admins')
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
            <section className="form-section">
              <h2 className="form-section-title">Account details</h2>
              <p className="form-section-text">The name shown in the panel and the email used to sign in.</p>
              <div className="row g-3">
                <div className="col-md-6">
                  <label htmlFor="name" className="form-label">
                    Full name
                  </label>
                  <input type="text" id="name" value={fields.name} onChange={setField('name')} className={`form-control${errors.name ? ' is-invalid' : ''}`} required autoComplete="off" autoFocus={!account} />
                  <FieldError message={errors.name} />
                </div>
                <div className="col-md-6">
                  <label htmlFor="email" className="form-label">
                    Email
                  </label>
                  <input
                    type="email"
                    id="email"
                    value={fields.email}
                    onChange={setField('email')}
                    className={`form-control${errors.email ? ' is-invalid' : ''}`}
                    required
                    autoComplete="off"
                    autoCapitalize="none"
                    spellCheck="false"
                  />
                  <FieldError message={errors.email} />
                </div>
              </div>
            </section>

            <section className="form-section">
              <h2 className="form-section-title">Role</h2>
              <p className="form-section-text">What this person is allowed to do in the panel.</p>
              <div className="row g-3" role="radiogroup" aria-label="Role">
                {ROLES.map(([value, label, description]) => (
                  <div key={value} className="col-md-6">
                    <label className="choice-card">
                      <input className="form-check-input" type="radio" name="role" value={value} checked={fields.role === value} onChange={setField('role')} />
                      <span>
                        <span className="choice-title">{label}</span>
                        <span className="choice-text">{description}</span>
                      </span>
                    </label>
                  </div>
                ))}
              </div>
              <FieldError message={errors.role} />
            </section>

            <section className="form-section">
              <h2 className="form-section-title">Access</h2>
              <div className="switch-row form-switch">
                <label htmlFor="is_active" className="mb-0">
                  <span className="choice-title">Active</span>
                  <span className="choice-text">Deactivated admins can’t sign in, and are signed out straight away.</span>
                </label>
                <input
                  className="form-check-input"
                  type="checkbox"
                  role="switch"
                  id="is_active"
                  checked={fields.is_active}
                  onChange={(event) => setFields((current) => ({ ...current, is_active: event.target.checked }))}
                />
              </div>
              <FieldError message={errors.is_active} />
            </section>

            <section className="form-section">
              <h2 className="form-section-title">{account ? 'Reset password' : 'Password'}</h2>
              <p className="form-section-text">
                {account
                  ? 'Leave both fields empty to keep their current password. Setting a new one signs them out everywhere.'
                  : 'At least 8 characters. Share it with them privately; they can change it on their account page.'}
              </p>
              <div className="row g-3">
                <div className="col-md-6">
                  <label htmlFor="password" className="form-label">
                    {account ? 'New password' : 'Password'}
                  </label>
                  <PasswordInput id="password" value={fields.password} onChange={setField('password')} invalid={Boolean(errors.password)} autoComplete="new-password" required={!account} />
                  <FieldError message={errors.password} />
                </div>
                <div className="col-md-6">
                  <label htmlFor="password_confirmation" className="form-label">
                    Confirm password
                  </label>
                  <div className="input-icon">
                    <i className="fa-solid fa-lock" aria-hidden="true" />
                    <input type="password" id="password_confirmation" value={fields.password_confirmation} onChange={setField('password_confirmation')} className="form-control" autoComplete="new-password" />
                  </div>
                </div>
              </div>
            </section>
          </div>

          <div className="admin-card-footer d-flex justify-content-end gap-2">
            <Link to="/admin/admins" className="btn btn-outline-secondary">
              Cancel
            </Link>
            <button type="submit" className="btn btn-primary" disabled={isSaving}>
              <i className="fa-solid fa-floppy-disk" aria-hidden="true" /> {account ? 'Save changes' : 'Add admin'}
            </button>
          </div>
        </form>
      </div>

      <div className="col-lg-4">
        {account ? (
          <div className="admin-card profile-card">
            <Avatar admin={account} size="lg" />
            <h2 className="profile-name">{account.name}</h2>
            <p className="profile-email">{account.email}</p>
            <dl className="meta-list text-start mt-4 pt-4 border-top">
              <div>
                <dt>Posts created</dt>
                <dd>{account.posts_count}</dd>
              </div>
              <div>
                <dt>Last sign-in</dt>
                <dd>{account.last_login_at ? timeAgo(account.last_login_at) : 'Never'}</dd>
              </div>
              <div>
                <dt>Added</dt>
                <dd>{format(account.created_at, 'date')}</dd>
              </div>
            </dl>
          </div>
        ) : (
          <Notice className="mb-0">No invitation email is sent. Give the new admin their email and password yourself; they can sign in straight away.</Notice>
        )}
      </div>
    </div>
  )
}

export default function AdminFormPage() {
  const { adminId } = useParams()
  const { admin: you } = useAuth()
  const isYou = adminId && Number(adminId) === you.id
  const { data, error, reload } = useAdminApi(adminId && !isYou ? `/admins/${adminId}` : null)
  const account = data?.data

  useDocumentTitle(adminId ? `Edit: ${account?.name ?? 'admin'}` : 'Add admin')

  // Admins change their own details on My account, never here.
  if (isYou) return <Navigate to="/admin/account" replace />

  const header = (
    <PageHeader
      title={adminId ? 'Edit admin' : 'Add admin'}
      subtitle={adminId ? account?.name : 'Give someone access to the blog admin.'}
      back="/admin/admins"
      backLabel="All admins"
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
      {adminId && !account ? <PageLoading /> : <AdminForm key={adminId ?? 'new'} account={account} />}
    </>
  )
}
