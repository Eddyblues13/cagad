// Small building blocks shared by the admin pages, mirroring the classes in admin.css.
import { useState } from 'react'
import { Link } from 'react-router'

export function PageHeader({ title, subtitle, back, backLabel = 'Back', badge, actions }) {
  return (
    <div className="page-header">
      <div className="min-w-0">
        {back && (
          <Link to={back} className="page-back">
            <i className="fa-solid fa-arrow-left" aria-hidden="true" /> {backLabel}
          </Link>
        )}
        <h1 className="page-title">
          <span className="min-w-0">{title}</span>
          {badge}
        </h1>
        {subtitle && <p className="page-subtitle">{subtitle}</p>}
      </div>
      {actions && <div className="page-actions">{actions}</div>}
    </div>
  )
}

/** Draft, Scheduled or Published. */
export function StatusBadge({ post }) {
  return <span className={`status-badge is-${post.status_label.toLowerCase()}`}>{post.status_label}</span>
}

/** Initials on one of five brand colours, picked from the id so each admin keeps theirs. */
export function Avatar({ admin, size }) {
  const classes = ['avatar', size && `avatar-${size}`, `tone-${admin.id % 5}`].filter(Boolean).join(' ')
  return (
    <span className={classes} aria-hidden="true">
      {admin.initials}
    </span>
  )
}

export function RolePill({ admin }) {
  return (
    <span className={`pill ${admin.is_super_admin ? 'pill-navy' : 'pill-gray'}`}>
      {admin.is_super_admin && <i className="fa-solid fa-crown" aria-hidden="true" />} {admin.role_label}
    </span>
  )
}

export function EmptyState({ icon, title, children, actions, compact = false }) {
  return (
    <div className={`empty-state${compact ? ' is-compact' : ''}`}>
      <div className="empty-state-icon" aria-hidden="true">
        <i className={icon} />
      </div>
      <h2 className="empty-state-title">{title}</h2>
      {children && <p className="empty-state-text">{children}</p>}
      {actions}
    </div>
  )
}

const noticeIcons = {
  info: 'fa-solid fa-circle-info',
  danger: 'fa-solid fa-circle-exclamation',
  gold: 'fa-regular fa-clock',
}

export function Notice({ tone = 'info', icon, className = '', children }) {
  return (
    <div className={`notice notice-${tone} ${className}`} role={tone === 'danger' ? 'alert' : undefined}>
      <i className={icon || noticeIcons[tone]} aria-hidden="true" />
      <div>{children}</div>
    </div>
  )
}

export function PageLoading({ label = 'Loading…' }) {
  return (
    <div className="page-loading" role="status">
      <span className="spinner-border spinner-border-sm" aria-hidden="true" />
      <span>{label}</span>
    </div>
  )
}

export function PageError({ error, onRetry }) {
  return (
    <div className="admin-card">
      <EmptyState
        icon="fa-solid fa-triangle-exclamation"
        title={error?.status === 404 ? 'Not found' : 'This page didn’t load'}
        actions={
          error?.status !== 404 &&
          onRetry && (
            <button type="button" className="btn btn-outline-secondary btn-sm" onClick={onRetry}>
              <i className="fa-solid fa-rotate-right" aria-hidden="true" /> Try again
            </button>
          )
        }
      >
        {error?.status === 404 ? 'It may have been deleted.' : error?.message}
      </EmptyState>
    </div>
  )
}

export function FieldError({ id, message }) {
  return message ? (
    <div className="field-error" id={id}>
      {message}
    </div>
  ) : null
}

/** A password field with a show/hide button. */
export function PasswordInput({ id, icon = 'fa-solid fa-lock', invalid = false, ...inputProps }) {
  const [isVisible, setIsVisible] = useState(false)

  return (
    <div className="input-icon">
      <i className={icon} aria-hidden="true" />
      <input
        id={id}
        type={isVisible ? 'text' : 'password'}
        className={`form-control has-toggle${invalid ? ' is-invalid' : ''}`}
        aria-invalid={invalid || undefined}
        {...inputProps}
      />
      <button
        type="button"
        className="password-toggle"
        aria-label={isVisible ? 'Hide password' : 'Show password'}
        aria-pressed={isVisible}
        onClick={() => setIsVisible((visible) => !visible)}
      >
        <i className={isVisible ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye'} aria-hidden="true" />
      </button>
    </div>
  )
}

/** "120 / 500" beside fields with a length limit, red near the limit. */
export function CharCount({ value, max }) {
  return <span className={`char-count${max - value.length <= 20 ? ' text-danger' : ''}`}>{`${value.length} / ${max}`}</span>
}
