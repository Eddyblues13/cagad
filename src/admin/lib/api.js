// Client for the admin endpoints of the Laravel backend (/api/v1/admin). Every
// request carries the bearer token from sign-in; a 401 signs the admin out.
import { API_URL } from '../../lib/blogApi.js'

const ADMIN_API = `${API_URL}/api/v1/admin`
const TOKEN_KEY = 'cagad-admin-token'

// "Keep me signed in" puts the token in localStorage; otherwise sessionStorage,
// so it's gone when the browser closes. If storage is blocked it lives in memory.
let memoryToken = null
let handleUnauthorized = () => {}

export class AdminApiError extends Error {
  constructor(message, status, errors = {}) {
    super(message)
    this.name = 'AdminApiError'
    this.status = status
    this.errors = errors
  }
}

export function readToken() {
  try {
    return localStorage.getItem(TOKEN_KEY) || sessionStorage.getItem(TOKEN_KEY) || memoryToken
  } catch {
    return memoryToken
  }
}

export function storeToken(token, remember) {
  clearToken()
  memoryToken = token
  try {
    ;(remember ? localStorage : sessionStorage).setItem(TOKEN_KEY, token)
  } catch {
    // Storage unavailable: the in-memory copy lasts until the page reloads.
  }
}

export function clearToken() {
  memoryToken = null
  try {
    localStorage.removeItem(TOKEN_KEY)
    sessionStorage.removeItem(TOKEN_KEY)
  } catch {
    // Nothing stored.
  }
}

export function onUnauthorized(callback) {
  handleUnauthorized = callback
}

function authHeaders() {
  const token = readToken()
  return token ? { Authorization: `Bearer ${token}` } : {}
}

/**
 * Call an admin endpoint. `body` may be a plain object (sent as JSON) or a
 * FormData (sent as multipart, for uploads). Resolves with the parsed JSON,
 * or null for 204. Rejects with an AdminApiError carrying any field errors.
 */
export async function adminRequest(path, { method = 'GET', body, signal } = {}) {
  const headers = { Accept: 'application/json', ...authHeaders() }
  let payload = body

  if (body !== undefined && !(body instanceof FormData)) {
    headers['Content-Type'] = 'application/json'
    payload = JSON.stringify(body)
  }

  let response
  try {
    response = await fetch(`${ADMIN_API}${path}`, { method, headers, body: payload, signal })
  } catch (error) {
    if (error.name === 'AbortError') throw error
    throw new AdminApiError('Couldn’t reach the server. Check your connection and try again.', 0)
  }

  if (response.status === 204) return null

  const data = await response.json().catch(() => null)

  if (response.ok) return data

  if (response.status === 401 && headers.Authorization) {
    handleUnauthorized(data?.message)
  }

  throw new AdminApiError(
    data?.message || `Something went wrong (error ${response.status}). Please try again.`,
    response.status,
    data?.errors || {},
  )
}

/**
 * Upload an image dropped or pasted into the post editor, reporting progress.
 * Resolves with the optimised Cloudinary URL.
 */
export function uploadEditorImage(blob, filename, onProgress) {
  return new Promise((resolve, reject) => {
    const xhr = new XMLHttpRequest()
    xhr.open('POST', `${ADMIN_API}/uploads/images`)
    xhr.setRequestHeader('Accept', 'application/json')
    Object.entries(authHeaders()).forEach(([name, value]) => xhr.setRequestHeader(name, value))
    xhr.upload.onprogress = (event) => onProgress?.((event.loaded / event.total) * 100)
    xhr.onerror = () => reject(new Error('network error'))
    xhr.onload = () => {
      let json = {}
      try {
        json = JSON.parse(xhr.responseText)
      } catch {
        // Not JSON.
      }

      if (xhr.status >= 200 && xhr.status < 300 && json.location) {
        resolve(json.location)
        return
      }

      if (xhr.status === 401) handleUnauthorized(json.message)
      reject(new Error(json.errors?.file?.[0] ?? json.message ?? `HTTP ${xhr.status}`))
    }

    const data = new FormData()
    data.append('file', blob, filename)
    xhr.send(data)
  })
}

/** The first error message for each field, e.g. { title: 'The title field is required.' }. */
export function fieldErrors(error) {
  return Object.fromEntries(Object.entries(error?.errors ?? {}).map(([field, messages]) => [field, messages[0]]))
}
