import { createContext, useContext } from 'react'

export const ToastContext = createContext(() => {})
export const ConfirmContext = createContext(() => Promise.resolve(false))

/** toast(message, tone): a message in the corner that hides itself. Tone is "success" or "error". */
export function useToast() {
  return useContext(ToastContext)
}

/**
 * confirm({ title, message, confirmLabel }) opens a confirmation dialog and
 * resolves with true when the admin accepts. Used before anything is deleted.
 */
export function useConfirm() {
  return useContext(ConfirmContext)
}
