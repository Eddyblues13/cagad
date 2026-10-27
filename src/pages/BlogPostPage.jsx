import { useState } from 'react'
import { Link, useParams } from 'react-router'
import PostCard from '../components/PostCard.jsx'
import useApi from '../hooks/useApi.js'
import usePageMeta from '../hooks/usePageMeta.js'
import { SITE_URL, formatDate, postPath } from '../lib/blogApi.js'

export default function BlogPostPage() {
  const { slug } = useParams()
  const { data, error, loading, retry } = useApi(postPath(slug))
  const post = data?.data
  const related = data?.related ?? []

  usePageMeta({
    title: post ? `${post.title} | CAGAD` : null,
    description: post?.excerpt,
    path: `/blog/${slug}`,
    image: post?.cover_image?.card,
  })

  if (loading) {
    return (
      <div className="container article-loading" aria-busy="true" aria-label="Loading post">
        <div className="skeleton skeleton-line w-25 mb-3"></div>
        <div className="skeleton skeleton-line skeleton-heading mb-4"></div>
        <div className="skeleton article-skeleton-cover mb-4"></div>
        <div className="skeleton skeleton-line"></div>
        <div className="skeleton skeleton-line"></div>
        <div className="skeleton skeleton-line w-75"></div>
      </div>
    )
  }

  if (error) {
    const missing = error.status === 404

    return (
      <div className="container section-padding">
        <div className="blog-empty" role="alert">
          <i className={missing ? 'far fa-file-lines' : 'fas fa-plug-circle-exclamation'} aria-hidden="true"></i>
          <h1 className="h3">{missing ? 'Post not found' : 'We couldn’t load this post'}</h1>
          <p>
            {missing
              ? 'This post may have been moved or unpublished.'
              : 'Please check your connection and try again.'}
          </p>
          <div className="d-flex gap-2 justify-content-center">
            {!missing && (
              <button type="button" className="btn btn-primary" onClick={retry}>
                Try again
              </button>
            )}
            <Link to="/blog" className="btn btn-outline-primary">
              Back to the blog
            </Link>
          </div>
        </div>
      </div>
    )
  }

  return (
    <>
      <article className="article" aria-labelledby="article-title" itemScope itemType="https://schema.org/BlogPosting">
        <header className="article-header">
          <div className="container article-container">
            <nav aria-label="Breadcrumb">
              <ol className="article-breadcrumb">
                <li>
                  <Link to="/blog">Blog</Link>
                </li>
                {post.category && (
                  <li>
                    <Link to={`/blog?category=${post.category.slug}`}>{post.category.name}</Link>
                  </li>
                )}
              </ol>
            </nav>
            <h1 id="article-title" className="article-title" itemProp="headline">
              {post.title}
            </h1>
            <p className="article-meta">
              {post.author && (
                <>
                  <span itemProp="author">{post.author}</span>
                  <span aria-hidden="true"> · </span>
                </>
              )}
              <time dateTime={post.published_at} itemProp="datePublished">
                {formatDate(post.published_at)}
              </time>
              <span aria-hidden="true"> · </span>
              <span>{post.reading_time} min read</span>
            </p>
          </div>
        </header>

        <div className="container article-container">
          {post.cover_image && (
            <figure className="article-cover">
              <img src={post.cover_image.hero} alt={post.cover_image.alt} itemProp="image" />
            </figure>
          )}

          <div className="blog-prose" itemProp="articleBody" dangerouslySetInnerHTML={{ __html: post.body }} />

          <ShareLinks title={post.title} url={`${SITE_URL}/blog/${post.slug}`} />
        </div>
      </article>

      {related.length > 0 && (
        <section className="section-padding bg-light" aria-labelledby="related-heading">
          <div className="container">
            <h2 className="section-title center-title text-center mb-5" id="related-heading">
              Keep reading
            </h2>
            <div className="row g-4 justify-content-center" role="list">
              {related.map((item) => (
                <div className="col-md-6 col-lg-4" key={item.id} role="listitem">
                  <PostCard post={item} />
                </div>
              ))}
            </div>
          </div>
        </section>
      )}
    </>
  )
}

function ShareLinks({ title, url }) {
  const [copied, setCopied] = useState(false)
  const encodedUrl = encodeURIComponent(url)
  const encodedTitle = encodeURIComponent(title)

  const networks = [
    { label: 'X', icon: 'fab fa-x-twitter', href: `https://x.com/intent/post?url=${encodedUrl}&text=${encodedTitle}` },
    { label: 'LinkedIn', icon: 'fab fa-linkedin-in', href: `https://www.linkedin.com/sharing/share-offsite/?url=${encodedUrl}` },
    { label: 'Facebook', icon: 'fab fa-facebook-f', href: `https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}` },
    { label: 'WhatsApp', icon: 'fab fa-whatsapp', href: `https://wa.me/?text=${encodedTitle}%20${encodedUrl}` },
  ]

  const copyLink = async () => {
    try {
      await navigator.clipboard.writeText(url)
      setCopied(true)
      setTimeout(() => setCopied(false), 2000)
    } catch {
      window.prompt('Copy this link:', url)
    }
  }

  return (
    <div className="article-share" aria-label="Share this post">
      <span className="article-share-label">Share</span>
      {networks.map((network) => (
        <a
          key={network.label}
          href={network.href}
          target="_blank"
          rel="noopener noreferrer"
          aria-label={`Share on ${network.label}`}
        >
          <i className={network.icon} aria-hidden="true"></i>
        </a>
      ))}
      <button type="button" onClick={copyLink} aria-label="Copy link to this post">
        <i className={copied ? 'fas fa-check' : 'fas fa-link'} aria-hidden="true"></i>
      </button>
      <span className="visually-hidden" aria-live="polite">
        {copied ? 'Link copied' : ''}
      </span>
    </div>
  )
}
