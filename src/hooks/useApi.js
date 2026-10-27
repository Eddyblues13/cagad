import { useCallback, useEffect, useState } from 'react'
import { fetchJson } from '../lib/blogApi.js'

/**
 * Fetch an API path and re-fetch whenever it changes. Pass null to skip.
 * Results are keyed by path, so a new path reads as "loading" straight away
 * without setting state synchronously inside the effect.
 */
export default function useApi(path) {
  const [result, setResult] = useState({ path: null, attempt: 0, data: null, error: null })
  const [attempt, setAttempt] = useState(0)

  useEffect(() => {
    if (!path) return undefined

    const controller = new AbortController()

    fetchJson(path, controller.signal)
      .then((data) => setResult({ path, attempt, data, error: null }))
      .catch((error) => {
        if (error.name !== 'AbortError') {
          setResult({ path, attempt, data: null, error })
        }
      })

    return () => controller.abort()
  }, [path, attempt])

  const retry = useCallback(() => setAttempt((n) => n + 1), [])
  const isCurrent = result.path === path && result.attempt === attempt

  return {
    data: isCurrent ? result.data : null,
    error: isCurrent ? result.error : null,
    loading: Boolean(path) && !isCurrent,
    retry,
  }
}
