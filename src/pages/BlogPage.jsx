import { useRef } from 'react'
import { useSearchParams } from 'react-router'
import PostCard, { PostCardSkeleton } from '../components/PostCard.jsx'
import useApi from '../hooks/useApi.js'
import usePageMeta from '../hooks/usePageMeta.js'
import { categoriesPath, postsPath } from '../lib/blogApi.js'

const PER_PAGE = 9

export default function BlogPage() {
  const [searchParams, setSearchParams] = useSearchParams()
  const listRef = useRef(null)

  const category = searchParams.get('category') ?? ''
  const search = searchParams.get('q') ?? ''
  const page = Math.max(1, Number.parseInt(searchParams.get('page') ?? '1', 10) || 1)

  const posts = useApi(postsPath({ page, perPage: PER_PAGE, category, search }))
  const categories = useApi(categoriesPath())

  usePageMeta({
    title: 'Blog | CAGAD - Centre for Advancement of Governance and Development',
    description:
      'Analysis, commentary and updates from CAGAD on governance, accountability, security, democracy and sustainable development in Nigeria and Africa.',
    path: '/blog',
  })

  const updateParams = (changes) => {
    const next = new URLSearchParams(searchParams)
    Object.entries(changes).forEach(([key, value]) => {
      if (value) next.set(key, value)
      else next.delete(key)
    })
    setSearchParams(next)
  }

  const goToPage = (nextPage) => {
    updateParams({ page: nextPage > 1 ? String(nextPage) : '' })
    listRef.current?.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }

  const handleSearch = (event) => {
    event.preventDefault()
    const term = new FormData(event.currentTarget).get('q')?.toString().trim() ?? ''
    updateParams({ q: term, page: '' })
  }

  const items = posts.data?.data ?? []
  const meta = posts.data?.meta
  const isFiltered = Boolean(category || search)
  // Lead with the newest post as a large card, but only when it has a cover to show.
  const featureFirst = page === 1 && !isFiltered && Boolean(items[0]?.cover_image)
  const [featured, rest] = featureFirst ? [items[0], items.slice(1)] : [null, items]
  const activeCategory = categories.data?.data.find((c) => c.slug === category)

  return (
    <>
      <section className="page-hero" aria-labelledby="blog-heading">
        <div className="container">
          <p className="page-hero-eyebrow">CAGAD Blog</p>
          <h1 id="blog-heading" className="page-hero-title">Insights &amp; Updates</h1>
          <p className="page-hero-lead">
            Analysis, commentary and news on governance, accountability, security and sustainable development in
            Nigeria and Africa.
          </p>
        </div>
      </section>

      <section className="section-padding pt-5" ref={listRef} aria-label="Blog posts">
        <div className="container">
          <div className="blog-toolbar">
            <nav className="blog-filters" aria-label="Filter posts by category">
              <button
                type="button"
                className={`blog-chip ${category ? '' : 'active'}`}
                aria-pressed={!category}
                onClick={() => updateParams({ category: '', page: '' })}
              >
                All posts
              </button>
              {(categories.data?.data ?? []).map((item) => (
                <button
                  type="button"
                  key={item.id}
                  className={`blog-chip ${category === item.slug ? 'active' : ''}`}
                  aria-pressed={category === item.slug}
                  onClick={() => updateParams({ category: item.slug, page: '' })}
                >
                  {item.name}
                </button>
              ))}
            </nav>

            <form className="blog-search" role="search" onSubmit={handleSearch} key={search}>
              <label htmlFor="blog-search-input" className="visually-hidden">
                Search posts
              </label>
              <input
                id="blog-search-input"
                type="search"
                name="q"
                className="form-control"
                placeholder="Search posts…"
                defaultValue={search}
              />
              <button type="submit" className="btn btn-primary" aria-label="Search">
                <i className="fas fa-search" aria-hidden="true"></i>
              </button>
            </form>
          </div>

          {isFiltered && !posts.loading && !posts.error && (
            <p className="blog-results-note" aria-live="polite">
              {meta?.total ?? 0} {meta?.total === 1 ? 'post' : 'posts'}
              {activeCategory && <> in <strong>{activeCategory.name}</strong></>}
              {search && <> matching <strong>“{search}”</strong></>}
              <button type="button" className="btn btn-link btn-sm" onClick={() => setSearchParams({})}>
                Clear filters
              </button>
            </p>
          )}

          {posts.loading && (
            <div className="row g-4" aria-busy="true" aria-label="Loading posts">
              {Array.from({ length: 6 }, (_, i) => (
                <div className="col-md-6 col-lg-4" key={i}>
                  <PostCardSkeleton />
                </div>
              ))}
            </div>
          )}

          {posts.error && (
            <div className="blog-empty" role="alert">
              <i className="fas fa-plug-circle-exclamation" aria-hidden="true"></i>
              <h2 className="h4">We couldn’t load the blog right now</h2>
              <p>Please check your connection and try again.</p>
              <button type="button" className="btn btn-primary" onClick={posts.retry}>
                Try again
              </button>
            </div>
          )}

          {!posts.loading && !posts.error && items.length === 0 && (
            <div className="blog-empty">
              <i className="far fa-newspaper" aria-hidden="true"></i>
              <h2 className="h4">{isFiltered ? 'No posts match your search' : 'No posts yet'}</h2>
              <p>{isFiltered ? 'Try a different term or category.' : 'Check back soon for analysis and updates from CAGAD.'}</p>
              {isFiltered && (
                <button type="button" className="btn btn-outline-primary" onClick={() => setSearchParams({})}>
                  Show all posts
                </button>
              )}
            </div>
          )}

          {!posts.loading && !posts.error && items.length > 0 && (
            <>
              {featured && (
                <div className="mb-4">
                  <PostCard post={featured} featured headingLevel={2} />
                </div>
              )}

              <div className="row g-4" role="list">
                {rest.map((post) => (
                  <div className="col-md-6 col-lg-4" key={post.id} role="listitem">
                    <PostCard post={post} headingLevel={2} />
                  </div>
                ))}
              </div>

              {meta && meta.last_page > 1 && (
                <nav className="blog-pagination" aria-label="Blog pages">
                  <button
                    type="button"
                    className="btn btn-outline-primary"
                    disabled={meta.current_page <= 1}
                    onClick={() => goToPage(meta.current_page - 1)}
                  >
                    <i className="fas fa-arrow-left me-2" aria-hidden="true"></i>Newer
                  </button>
                  <span className="blog-pagination-status">
                    Page {meta.current_page} of {meta.last_page}
                  </span>
                  <button
                    type="button"
                    className="btn btn-outline-primary"
                    disabled={meta.current_page >= meta.last_page}
                    onClick={() => goToPage(meta.current_page + 1)}
                  >
                    Older<i className="fas fa-arrow-right ms-2" aria-hidden="true"></i>
                  </button>
                </nav>
              )}
            </>
          )}
        </div>
      </section>
    </>
  )
}
