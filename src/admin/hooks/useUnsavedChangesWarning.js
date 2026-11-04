import { useEffect } from 'react'

/** Ask before closing or reloading the tab while a form has unsaved changes. */
export default function useUnsavedChangesWarning(hasUnsavedChanges) {
  useEffect(() => {
    if (!hasUnsavedChanges) return undefined

    const onBeforeUnload = (event) => {
      event.preventDefault()
      event.returnValue = ''
    }

    window.addEventListener('beforeunload', onBeforeUnload)

    return () => window.removeEventListener('beforeunload', onBeforeUnload)
  }, [hasUnsavedChanges])
}
