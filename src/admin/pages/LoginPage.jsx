import { useState } from 'react'
import { Navigate, useLocation } from 'react-router'
import { useAuth } from '../auth/AuthContext.js'
import { fieldErrors } from '../lib/api.js'
import { FieldError, Notice, PasswordInput } from '../components/ui.jsx'
import useDocumentTitle from '../hooks/useDocumentTitle.js'

const YEAR = new Date().getFullYear()

const features = [
  ['fa-solid fa-pen-nib', 'Write with a rich editor', 'Headings, images, tables and quotes, ready for the site.'],
  ['fa-regular fa-calendar-check', 'Schedule ahead', 'Pick a date and posts go live on their own.'],
  ['fa-solid fa-bolt', 'Fast by default', 'Images are resized and optimised automatically.'],
]

export default function LoginPage() {
  const { admin, status, notice, signIn } = useAuth()
  const location = useLocation()
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [remember, setRemember] = useState(false)
  const [errors, setErrors] = useState({})
  const [formError, setFormError] = useState(null)
  const [isSubmitting, setIsSubmitting] = useState(false)

  useDocumentTitle('Sign in')

  if (admin) {
    const from = location.state?.from
    return <Navigate to={from ? `${from.pathname}${from.search}` : '/admin'} replace />
  }

  async function submit(event) {
    event.preventDefault()
    setIsSubmitting(true)
    setErrors({})
    setFormError(null)

    try {
      await signIn({ email, password, remember })
    } catch (error) {
      setErrors(fieldErrors(error))
      if (error.status !== 422) setFormError(error.message)
      setIsSubmitting(false)
    }
  }

  return (
    <div className="auth-shell">
      <section className="auth-aside">
        <div className="auth-aside-inner">
          <div className="d-flex align-items-center gap-3">
            <span className="brand-chip">
              <img src="/logo.png" alt="" width="38" height="32" />
            </span>
            <span>
              <span className="brand-name">CAGAD</span>
              <span className="brand-sub">Blog Admin</span>
            </span>
          </div>

          <div>
            <h2 className="auth-headline">Publish research, insight and news for the people CAGAD serves.</h2>
            <p className="auth-lead">One place to write, schedule and manage every post on the CAGAD blog.</p>
            <ul className="auth-features">
              {features.map(([icon, title, text]) => (
                <li key={title}>
                  <i className={icon} aria-hidden="true" />
                  <div>
                    <strong>{title}</strong>
                    <span>{text}</span>
                  </div>
                </li>
              ))}
            </ul>
          </div>

          <p className="auth-copyright mb-0">© {YEAR} Centre for Advancement of Governance and Development</p>
        </div>
      </section>

      <main className="auth-main">
        <div className="auth-card">
          <span className="auth-logo">
            <img src="/logo.png" alt="CAGAD" width="52" height="43" />
          </span>
          <h1 className="auth-title">Welcome back</h1>
          <p className="auth-subtitle">Sign in to the CAGAD Blog Admin.</p>

          {notice && !formError && <Notice>{notice}</Notice>}
          {formError && <Notice tone="danger">{formError}</Notice>}

          <form onSubmit={submit} noValidate>
            <div className="mb-3">
              <label htmlFor="email" className="form-label">
                Email
              </label>
              <div className="input-icon">
                <i className="fa-regular fa-envelope" aria-hidden="true" />
                <input
                  type="email"
                  id="email"
                  name="email"
                  value={email}
                  onChange={(event) => setEmail(event.target.value)}
                  className={`form-control${errors.email ? ' is-invalid' : ''}`}
                  placeholder="you@cagad.org"
                  required
                  autoFocus
                  autoComplete="username"
                  autoCapitalize="none"
                  spellCheck="false"
                  aria-invalid={Boolean(errors.email) || undefined}
                  aria-describedby={errors.email ? 'email-error' : undefined}
                />
              </div>
              <FieldError id="email-error" message={errors.email} />
            </div>

            <div className="mb-3">
              <label htmlFor="password" className="form-label">
                Password
              </label>
              <PasswordInput
                id="password"
                name="password"
                value={password}
                onChange={(event) => setPassword(event.target.value)}
                invalid={Boolean(errors.password)}
                required
                autoComplete="current-password"
              />
              <FieldError message={errors.password} />
            </div>

            <div className="form-check mb-4">
              <input className="form-check-input" type="checkbox" id="remember" checked={remember} onChange={(event) => setRemember(event.target.checked)} />
              <label className="form-check-label" htmlFor="remember">
                Keep me signed in on this device
              </label>
            </div>

            <button type="submit" className="btn btn-primary btn-lg w-100" disabled={isSubmitting || status === 'checking'}>
              {isSubmitting ? (
                <>
                  <span className="spinner-border spinner-border-sm" aria-hidden="true" /> Signing in…
                </>
              ) : (
                <>
                  Sign in <i className="fa-solid fa-arrow-right" aria-hidden="true" />
                </>
              )}
            </button>
          </form>

          <p className="auth-help">
            <i className="fa-solid fa-shield-halved me-1" aria-hidden="true" />
            Access is limited to CAGAD staff. Forgotten your password? Ask a super admin to reset it.
          </p>
        </div>
      </main>
    </div>
  )
}
