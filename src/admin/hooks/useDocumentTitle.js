import { useEffect } from 'react'

/** "Posts · CAGAD Blog Admin" in the browser tab while the page is open. */
export default function useDocumentTitle(title) {
  useEffect(() => {
    if (!title) return undefined

    const previous = document.title
    document.title = `${title} · CAGAD Blog Admin`

    return () => {
      document.title = previous
    }
  }, [title])
}
