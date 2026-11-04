import { lazy, Suspense } from 'react'
import { Route, Routes } from 'react-router'
import SiteLayout from './components/SiteLayout.jsx'
import HomePage from './pages/HomePage.jsx'
import BlogPage from './pages/BlogPage.jsx'
import BlogPostPage from './pages/BlogPostPage.jsx'
import NotFoundPage from './pages/NotFoundPage.jsx'

// The blog admin is only downloaded when someone opens /admin.
const AdminApp = lazy(() => import('./admin/AdminApp.jsx'))

function App() {
  return (
    <Routes>
      <Route
        path="admin/*"
        element={
          <Suspense fallback={null}>
            <AdminApp />
          </Suspense>
        }
      />
      <Route element={<SiteLayout />}>
        <Route index element={<HomePage />} />
        <Route path="blog" element={<BlogPage />} />
        <Route path="blog/:slug" element={<BlogPostPage />} />
        <Route path="*" element={<NotFoundPage />} />
      </Route>
    </Routes>
  )
}

export default App
