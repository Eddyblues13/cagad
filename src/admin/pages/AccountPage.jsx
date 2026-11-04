import { useState } from 'react'
import { useAuth } from '../auth/AuthContext.js'
import useAdminApi from '../hooks/useAdminApi.js'
import useDocumentTitle from '../hooks/useDocumentTitle.js'
import { useToast } from '../feedback/FeedbackContext.js'
import { adminRequest, fieldErrors } from '../lib/api.js'
import { format, timeAgo } from '../lib/format.js'
import { Avatar, FieldError, Notice, PageHeader, PasswordInput, RolePill } from '../components/ui.jsx'

const EMPTY_PASSWORDS = { current_password: '', password: '', password_confirmation: '' }

export default function AccountPage() {
  const { admin, setAdmin } = useAuth()
  const { data } = useAdminApi('/account')
  const toast = useToast()
  const [fields, setFields] = useState({ name: admin.name, email: admin.email, ...EMPTY_PASSWORDS })
  const [errors, setErrors] = useState({})
  const [formError, setFormError] = useState(null)
  const [isSaving, setIsSaving] = useState(false)
  const profile = data?.data ?? admin

  useDocumentTitle('My account')

  const setField = (name) => (event) => setFields((current) => ({ ...current, [name]: event.target.value }))

  async function save(event) {
    event.preventDefault()
    setIsSaving(true)
    setErrors({})
    setFormError(null)

    try {
      const response = await adminRequest('/account', { method: 'PUT', body: fields })
      setAdmin(response.data)
      setFields((current) => ({ ...current, name: response.data.name, email: response.data.email, ...EMPTY_PASSWORDS }))
      toast(response.message)
    } catch (error) {
      setErrors(fieldErrors(error))
      if (error.status !== 422) setFormError(error.message)
    } finally {
      setIsSaving(false)
    }
  }

  return (
    <>
      <PageHeader title="My account" subtitle="Update your name, sign-in email and password." />

      <div className="row g-4">
        <div className="col-lg-4">
          <div className="admin-card profile-card">
            <Avatar admin={profile} size="xl" />
            <h2 className="profile-name">{profile.name}</h2>
            <p className="profile-email">{profile.email}</p>
            <RolePill admin={profile} />
            <dl className="meta-list text-start mt-4 pt-4 border-top">
              <div>
                <dt>Posts created</dt>
                <dd>{profile.posts_count ?? '—'}</dd>
              </div>
              <div>
                <dt>Signed in</dt>
                <dd>{profile.last_login_at ? timeAgo(profile.last_login_at) : '—'}</dd>
              </div>
              <div>
                <dt>Member since</dt>
                <dd>{format(profile.created_at, 'monthYear')}</dd>
              </div>
            </dl>
          </div>
        </div>

        <div className="col-lg-8">
          {formError && <Notice tone="danger">{formError}</Notice>}
          <form className="admin-card" onSubmit={save} noValidate>
            <div className="admin-card-body">
              <section className="form-section">
                <h2 className="form-section-title">Profile</h2>
                <p className="form-section-text">Your name is the default byline on new posts.</p>
                <div className="row g-3">
                  <div className="col-md-6">
                    <label htmlFor="name" className="form-label">
                      Full name
                    </label>
                    <input type="text" id="name" value={fields.name} onChange={setField('name')} className={`form-control${errors.name ? ' is-invalid' : ''}`} required autoComplete="name" />
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
                      autoComplete="username"
                      autoCapitalize="none"
                      spellCheck="false"
                    />
                    <FieldError message={errors.email} />
                  </div>
                </div>
              </section>

              <section className="form-section">
                <h2 className="form-section-title">Change password</h2>
                <p className="form-section-text">Leave these empty to keep your current password.</p>
                <div className="row g-3">
                  <div className="col-12">
                    <label htmlFor="current_password" className="form-label">
                      Current password
                    </label>
                    <PasswordInput id="current_password" value={fields.current_password} onChange={setField('current_password')} invalid={Boolean(errors.current_password)} autoComplete="current-password" />
                    <FieldError message={errors.current_password} />
                  </div>
                  <div className="col-md-6">
                    <label htmlFor="password" className="form-label">
                      New password
                    </label>
                    <PasswordInput id="password" icon="fa-solid fa-key" value={fields.password} onChange={setField('password')} invalid={Boolean(errors.password)} autoComplete="new-password" />
                    <FieldError message={errors.password} />
                  </div>
                  <div className="col-md-6">
                    <label htmlFor="password_confirmation" className="form-label">
                      Confirm new password
                    </label>
                    <div className="input-icon">
                      <i className="fa-solid fa-key" aria-hidden="true" />
                      <input type="password" id="password_confirmation" value={fields.password_confirmation} onChange={setField('password_confirmation')} className="form-control" autoComplete="new-password" />
                    </div>
                  </div>
                </div>
              </section>
            </div>

            <div className="admin-card-footer d-flex justify-content-end">
              <button type="submit" className="btn btn-primary" disabled={isSaving}>
                <i className="fa-solid fa-floppy-disk" aria-hidden="true" /> Save changes
              </button>
            </div>
          </form>
        </div>
      </div>
    </>
  )
}
