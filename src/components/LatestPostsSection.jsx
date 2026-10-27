import { Link } from 'react-router'
import FadeIn from './FadeIn.jsx'
import PostCard from './PostCard.jsx'
import useApi from '../hooks/useApi.js'
import { postsPath } from '../lib/blogApi.js'

export default function LatestPostsSection() {
  const { data } = useApi(postsPath({ perPage: 3 }))
  const posts = data?.data ?? []

  // Stay out of the way until there is something to show, so the homepage
  // never displays an empty or broken section if the blog API is unreachable.
  if (posts.length === 0) return null

  return (
    <section id="insights" className="section-padding bg-light" aria-labelledby="insights-heading">
      <div className="container">
        <h2 className="section-title center-title text-center mb-5" id="insights-heading">Latest Insights</h2>
        <div className="row g-4" role="list" aria-label="Latest blog posts">
          {posts.map((post) => (
            <div className="col-md-6 col-lg-4" key={post.id} role="listitem">
              <FadeIn className="h-100">
                <PostCard post={post} />
              </FadeIn>
            </div>
          ))}
        </div>
        <div className="text-center mt-5">
          <Link to="/blog" className="btn btn-primary">
            Read the blog <i className="fas fa-arrow-right ms-2" aria-hidden="true"></i>
          </Link>
        </div>
      </div>
    </section>
  )
}
