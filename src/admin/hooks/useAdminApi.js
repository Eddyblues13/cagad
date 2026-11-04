import { useCallback, useEffect, useState } from 'react'
import { adminRequest } from '../lib/api.js'

/**
 * Load an admin API path, re-loading when it changes. Pass null to skip.
 * After reload() the previous data stays on screen until the new response
 * arrives, so lists don't flash empty after a delete.
 */
export default function useAdminApi(path) {
  const [result, setResult] = useState({ path: null, attempt: 0, data: null, error: null })
  const [attempt, setAttempt] = useState(0)

  useEffect(() => {
    if (!path) return undefined

    const controller = new AbortController()

    adminRequest(path, { signal: controller.signal })
      .then((data) => setResult({ path, attempt, data, error: null }))
      .catch((error) => {
        if (error.name !== 'AbortError') {
          setResult({ path, attempt, data: null, error })
        }
      })

    return () => controller.abort()
  }, [path, attempt])

  const reload = useCallback(() => setAttempt((n) => n + 1), [])
  const isSamePath = result.path === path

  return {
    data: isSamePath ? result.data : null,
    error: isSamePath ? result.error : null,
    loading: Boolean(path) && !(isSamePath && result.attempt === attempt),
    reload,
  }
}
