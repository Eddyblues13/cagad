import { Link } from 'react-router'
import { formatDate } from '../lib/blogApi.js'

export default function PostCard({ post, featured = false, headingLevel = 3 }) {
  const Heading = `h${headingLevel}`
  const image = featured ? post.cover_image?.hero : post.cover_image?.card

  return (
    <article className={`blog-card ${featured ? 'blog-card-featured' : ''}`} aria-labelledby={`post-${post.id}-title`}>
      <div className="blog-card-media">
        {image ? (
          <img src={image} alt={post.cover_image.alt} loading={featured ? 'eager' : 'lazy'} />
        ) : (
          <div className="blog-card-placeholder" aria-hidden="true">
            <i className="fas fa-landmark"></i>
          </div>
        )}
      </div>
      <div className="blog-card-body">
        {post.category && <span className="blog-category">{post.category.name}</span>}
        <Heading className="blog-card-title" id={`post-${post.id}-title`}>
          <Link to={`/blog/${post.slug}`} className="stretched-link">
            {post.title}
          </Link>
        </Heading>
        {post.excerpt && <p className="blog-card-excerpt">{post.excerpt}</p>}
        <p className="blog-meta">
          <time dateTime={post.published_at}>{formatDate(post.published_at)}</time>
          <span aria-hidden="true"> · </span>
          <span>{post.reading_time} min read</span>
        </p>
      </div>
    </article>
  )
}

export function PostCardSkeleton() {
  return (
    <div className="blog-card blog-card-skeleton" aria-hidden="true">
      <div className="blog-card-media skeleton"></div>
      <div className="blog-card-body">
        <div className="skeleton skeleton-line w-25"></div>
        <div className="skeleton skeleton-line skeleton-title"></div>
        <div className="skeleton skeleton-line"></div>
        <div className="skeleton skeleton-line w-75"></div>
      </div>
    </div>
  )
}
