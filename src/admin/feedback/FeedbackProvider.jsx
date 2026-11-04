import { useCallback, useEffect, useRef, useState } from 'react'
import { ConfirmContext, ToastContext } from './FeedbackContext.js'

const TOAST_DURATION = 6000

export default function FeedbackProvider({ children }) {
  const [toasts, setToasts] = useState([])
  const [dialog, setDialog] = useState(null)
  const nextToastId = useRef(0)

  const dismissToast = useCallback((id) => setToasts((list) => list.filter((toast) => toast.id !== id)), [])

  const toast = useCallback(
    (message, tone = 'success') => {
      nextToastId.current += 1
      const id = nextToastId.current
      setToasts((list) => [...list, { id, message, tone }])
      setTimeout(() => dismissToast(id), TOAST_DURATION)
    },
    [dismissToast],
  )

  const confirm = useCallback((options) => new Promise((resolve) => setDialog({ ...options, resolve })), [])

  const closeDialog = useCallback(
    (accepted) => {
      dialog?.resolve(accepted)
      setDialog(null)
    },
    [dialog],
  )

  useEffect(() => {
    if (!dialog) return undefined

    const onKeyDown = (event) => {
      if (event.key === 'Escape') closeDialog(false)
    }

    document.body.classList.add('modal-open')
    document.addEventListener('keydown', onKeyDown)

    return () => {
      document.body.classList.remove('modal-open')
      document.removeEventListener('keydown', onKeyDown)
    }
  }, [dialog, closeDialog])

  return (
    <ToastContext.Provider value={toast}>
      <ConfirmContext.Provider value={confirm}>
        {children}

        <div className="toast-stack" aria-live="polite" aria-atomic="false">
          {toasts.map(({ id, message, tone }) => (
            <div key={id} className={`toast admin-toast show${tone === 'error' ? ' is-error' : ''}`} role={tone === 'error' ? 'alert' : 'status'}>
              <div className="toast-body">
                <span className="toast-icon">
                  <i className={tone === 'error' ? 'fa-solid fa-exclamation' : 'fa-solid fa-check'} aria-hidden="true" />
                </span>
                <div className="flex-grow-1 pt-1">{message}</div>
                <button type="button" className="btn-close btn-sm mt-1" aria-label="Close" onClick={() => dismissToast(id)} />
              </div>
            </div>
          ))}
        </div>

        {dialog && (
          <>
            <div className="modal fade show d-block" tabIndex={-1} role="dialog" aria-modal="true" aria-labelledby="confirm-modal-title" onClick={(event) => event.target === event.currentTarget && closeDialog(false)}>
              <div className="modal-dialog modal-dialog-centered">
                <div className="modal-content">
                  <div className="modal-body p-4">
                    <div className="d-flex gap-3">
                      <span className="confirm-icon">
                        <i className="fa-solid fa-triangle-exclamation" aria-hidden="true" />
                      </span>
                      <div>
                        <h2 className="h5 mb-2" id="confirm-modal-title">{dialog.title || 'Are you sure?'}</h2>
                        <p className="mb-0 text-secondary">{dialog.message}</p>
                      </div>
                    </div>
                  </div>
                  <div className="modal-footer border-0 px-4 pt-0 pb-4">
                    <button type="button" className="btn btn-outline-secondary" onClick={() => closeDialog(false)}>
                      Cancel
                    </button>
                    <button type="button" className="btn btn-danger" onClick={() => closeDialog(true)} autoFocus>
                      {dialog.confirmLabel || 'Delete'}
                    </button>
                  </div>
                </div>
              </div>
            </div>
            <div className="modal-backdrop fade show" />
          </>
        )}
      </ConfirmContext.Provider>
    </ToastContext.Provider>
  )
}
