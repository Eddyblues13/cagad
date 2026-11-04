// The blog admin at /admin. Loaded on demand from App.jsx, so none of it (or its
// styles) reaches visitors of the public site. It talks to the Laravel API with
// a bearer token; see lib/api.js.
import { useEffect } from 'react'
import { Route, Routes } from 'react-router'
import './admin.css'
import { useAuth } from './auth/AuthContext.js'
import AuthProvider from './auth/AuthProvider.jsx'
import RequireAuth from './auth/RequireAuth.jsx'
import FeedbackProvider from './feedback/FeedbackProvider.jsx'
import AdminLayout from './components/AdminLayout.jsx'
import LoginPage from './pages/LoginPage.jsx'
import DashboardPage from './pages/DashboardPage.jsx'
import PostsPage from './pages/PostsPage.jsx'
import PostEditorPage from './pages/PostEditorPage.jsx'
import PostPreviewPage from './pages/PostPreviewPage.jsx'
import CategoriesPage from './pages/CategoriesPage.jsx'
import CategoryFormPage from './pages/CategoryFormPage.jsx'
import AdminsPage from './pages/AdminsPage.jsx'
import AdminFormPage from './pages/AdminFormPage.jsx'
import AccountPage from './pages/AccountPage.jsx'
import AdminNotFoundPage from './pages/AdminNotFoundPage.jsx'

function SuperAdminOnly({ children }) {
  const { admin } = useAuth()
  return admin.is_super_admin ? children : <AdminNotFoundPage forbidden />
}

export default function AdminApp() {
  // Admin styling hangs off body.admin, and search engines are kept out.
  useEffect(() => {
    const robots = document.head.querySelector('meta[name="robots"]')
    const previousRobots = robots?.getAttribute('content')

    document.body.classList.add('admin')
    robots?.setAttribute('content', 'noindex, nofollow')

    return () => {
      document.body.classList.remove('admin')
      if (previousRobots) robots.setAttribute('content', previousRobots)
    }
  }, [])

  return (
    <AuthProvider>
      <FeedbackProvider>
        <Routes>
          <Route path="login" element={<LoginPage />} />
          <Route
            element={
              <RequireAuth>
                <AdminLayout />
              </RequireAuth>
            }
          >
            <Route index element={<DashboardPage />} />
            <Route path="posts" element={<PostsPage />} />
            <Route path="posts/new" element={<PostEditorPage />} />
            <Route path="posts/:postId/edit" element={<PostEditorPage />} />
            <Route path="posts/:postId" element={<PostPreviewPage />} />
            <Route path="categories" element={<CategoriesPage />} />
            <Route path="categories/new" element={<CategoryFormPage />} />
            <Route path="categories/:categoryId/edit" element={<CategoryFormPage />} />
            <Route path="admins" element={<SuperAdminOnly><AdminsPage /></SuperAdminOnly>} />
            <Route path="admins/new" element={<SuperAdminOnly><AdminFormPage /></SuperAdminOnly>} />
            <Route path="admins/:adminId/edit" element={<SuperAdminOnly><AdminFormPage /></SuperAdminOnly>} />
            <Route path="account" element={<AccountPage />} />
            <Route path="*" element={<AdminNotFoundPage />} />
          </Route>
        </Routes>
      </FeedbackProvider>
    </AuthProvider>
  )
}
