// Client for the CAGAD Laravel backend (see /backend). Set VITE_API_URL to the
// backend's address, e.g. https://api.cagad.org. In development it defaults to
// the local `php artisan serve` address.
const API_URL = (
  import.meta.env.VITE_API_URL || (import.meta.env.DEV ? 'http://localhost:8000' : '')
).replace(/\/+$/, '')

export const SITE_URL = 'https://cagad.org'

export class ApiError extends Error {
  constructor(message, status) {
    super(message)
    this.name = 'ApiError'
    this.status = status
  }
}

export async function fetchJson(path, signal) {
  const response = await fetch(`${API_URL}/api/v1${path}`, {
    headers: { Accept: 'application/json' },
    signal,
  })

  if (!response.ok) {
    throw new ApiError(`Request failed with status ${response.status}`, response.status)
  }

  return response.json()
}

export function postsPath({ page = 1, perPage, category, search } = {}) {
  const params = new URLSearchParams()
  if (page > 1) params.set('page', String(page))
  if (perPage) params.set('per_page', String(perPage))
  if (category) params.set('category', category)
  if (search) params.set('search', search)
  const query = params.toString()
  return `/posts${query ? `?${query}` : ''}`
}

export const postPath = (slug) => `/posts/${encodeURIComponent(slug)}`

export const categoriesPath = () => '/categories'

const dateFormatter = new Intl.DateTimeFormat('en-GB', {
  day: 'numeric',
  month: 'long',
  year: 'numeric',
})

export function formatDate(isoString) {
  return isoString ? dateFormatter.format(new Date(isoString)) : ''
}
