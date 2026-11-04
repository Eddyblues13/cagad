import { createContext, useContext } from 'react'

export const AuthContext = createContext(null)

/**
 * { admin, status, notice, signIn, signOut, setAdmin }. `status` is "checking"
 * while a stored token is verified, "offline" if the API couldn't be reached,
 * otherwise "ready". `notice` explains why the admin was last signed out.
 */
export function useAuth() {
  return useContext(AuthContext)
}
