import { useCallback, useEffect, useMemo, useState } from 'react'
import { AuthContext } from './AuthContext.js'
import { adminRequest, clearToken, onUnauthorized, readToken, storeToken } from '../lib/api.js'

export default function AuthProvider({ children }) {
  const [admin, setAdmin] = useState(null)
  const [status, setStatus] = useState(() => (readToken() ? 'checking' : 'ready'))
  const [notice, setNotice] = useState(null)

  // A token from an earlier visit: confirm it still works and load the admin.
  useEffect(() => {
    if (!readToken()) return undefined

    const controller = new AbortController()

    adminRequest('/account', { signal: controller.signal })
      .then((response) => {
        setAdmin(response.data)
        setStatus('ready')
      })
      .catch((error) => {
        if (error.name !== 'AbortError') setStatus(error.status === 401 ? 'ready' : 'offline')
      })

    return () => controller.abort()
  }, [])

  // Any 401 (expired token, deactivated account) signs the admin out.
  useEffect(() => {
    onUnauthorized((message) => {
      clearToken()
      setAdmin(null)
      setNotice(message || 'Your session has ended. Please sign in again.')
    })

    return () => onUnauthorized(() => {})
  }, [])

  const signIn = useCallback(async ({ email, password, remember }) => {
    const response = await adminRequest('/login', { method: 'POST', body: { email, password, remember } })
    storeToken(response.token, remember)
    setNotice(null)
    setAdmin(response.admin)
  }, [])

  const signOut = useCallback(async () => {
    await adminRequest('/logout', { method: 'POST' }).catch(() => {})
    clearToken()
    setAdmin(null)
    setNotice('You have been signed out.')
  }, [])

  const value = useMemo(
    () => ({ admin, status, notice, signIn, signOut, setAdmin }),
    [admin, status, notice, signIn, signOut],
  )

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}
