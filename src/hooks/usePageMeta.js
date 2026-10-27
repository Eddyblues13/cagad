import { useEffect } from 'react'
import { SITE_URL } from '../lib/blogApi.js'

function setTag(selector, attribute, value) {
  const element = document.head.querySelector(selector)
  if (!element) return () => {}

  const previous = element.getAttribute(attribute)
  element.setAttribute(attribute, value)
  return () => element.setAttribute(attribute, previous)
}

/**
 * Point the document title, description, canonical URL and social tags in
 * index.html at the current page, restoring the homepage values on unmount.
 * Without this, every blog URL would declare the homepage as its canonical.
 */
export default function usePageMeta({ title, description, path, image }) {
  useEffect(() => {
    if (!title) return undefined

    const previousTitle = document.title
    const url = `${SITE_URL}${path}`
    document.title = title

    const restore = [
      setTag('link[rel="canonical"]', 'href', url),
      setTag('meta[property="og:url"]', 'content', url),
      setTag('meta[property="og:title"]', 'content', title),
      setTag('meta[name="twitter:title"]', 'content', title),
    ]

    if (description) {
      restore.push(
        setTag('meta[name="description"]', 'content', description),
        setTag('meta[property="og:description"]', 'content', description),
        setTag('meta[name="twitter:description"]', 'content', description),
      )
    }

    if (image) {
      restore.push(
        setTag('meta[property="og:image"]', 'content', image),
        setTag('meta[name="twitter:image"]', 'content', image),
      )
    }

    return () => {
      document.title = previousTitle
      restore.forEach((undo) => undo())
    }
  }, [title, description, path, image])
}
