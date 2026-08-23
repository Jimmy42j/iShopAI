import { Routes, Route } from 'react-router-dom'
import { useEffect } from 'react'

import { Layout } from './components/layout/Layout'
import { ProtectedRoute } from './components/auth/ProtectedRoute'
import { initializeAuth } from './store/slices/authSlice'
import { useAppDispatch } from './store'

// Pages
import { HomePage } from './pages/HomePage'
import { CategoryPage } from './pages/CategoryPage'
import { ProductPage } from './pages/ProductPage'
import { SearchPage } from './pages/SearchPage'
import { LoginPage } from './pages/auth/LoginPage'
import { RegisterPage } from './pages/auth/RegisterPage'
import { CartPage } from './pages/CartPage'
import { WishlistPage } from './pages/WishlistPage'
import { CheckoutPage } from './pages/CheckoutPage'
import { OrdersPage } from './pages/OrdersPage'
import { OrderDetailsPage } from './pages/OrderDetailsPage'
import { AccountPage } from './pages/AccountPage'
import { NotFoundPage } from './pages/NotFoundPage'

function App() {
  const dispatch = useAppDispatch()

  useEffect(() => {
    // Initialize authentication state on app load
    dispatch(initializeAuth())
  }, [dispatch])

  return (
    <Layout>
      <Routes>
        {/* Public routes */}
        <Route path="/" element={<HomePage />} />
        <Route path="/men" element={<CategoryPage category="men" />} />
        <Route path="/women" element={<CategoryPage category="women" />} />
        <Route path="/kids" element={<CategoryPage category="kids" />} />
        <Route path="/product/:slug" element={<ProductPage />} />
        <Route path="/search" element={<SearchPage />} />
        <Route path="/cart" element={<CartPage />} />
        
        {/* Auth routes */}
        <Route path="/login" element={<LoginPage />} />
        <Route path="/register" element={<RegisterPage />} />
        
        {/* Protected routes */}
        <Route
          path="/wishlist"
          element={
            <ProtectedRoute>
              <WishlistPage />
            </ProtectedRoute>
          }
        />
        <Route
          path="/checkout"
          element={
            <ProtectedRoute>
              <CheckoutPage />
            </ProtectedRoute>
          }
        />
        <Route
          path="/orders"
          element={
            <ProtectedRoute>
              <OrdersPage />
            </ProtectedRoute>
          }
        />
        <Route
          path="/orders/:id"
          element={
            <ProtectedRoute>
              <OrderDetailsPage />
            </ProtectedRoute>
          }
        />
        <Route
          path="/account"
          element={
            <ProtectedRoute>
              <AccountPage />
            </ProtectedRoute>
          }
        />
        
        {/* 404 route */}
        <Route path="*" element={<NotFoundPage />} />
      </Routes>
    </Layout>
  )
}

export default App
