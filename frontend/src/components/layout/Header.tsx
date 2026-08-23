import React from 'react'
import { Link } from 'react-router-dom'
import { ShoppingCart, Heart, User, Search, Menu, Sun, Moon } from 'lucide-react'
import { useAppSelector, useAppDispatch } from '../../store'
import { selectIsAuthenticated, selectUser } from '../../store/slices/authSlice'
import { selectCartItemCount } from '../../store/slices/cartSlice'
import { selectWishlistItemCount } from '../../store/slices/wishlistSlice'
import { toggleTheme } from '../../store/slices/themeSlice'
import { useTheme } from '../providers/ThemeProvider'
import { cn } from '../../utils/cn'

export const Header: React.FC = () => {
  const dispatch = useAppDispatch()
  const isAuthenticated = useAppSelector(selectIsAuthenticated)
  const user = useAppSelector(selectUser)
  const cartItemCount = useAppSelector(selectCartItemCount)
  const wishlistItemCount = useAppSelector(selectWishlistItemCount)
  const { effectiveTheme } = useTheme()

  const handleThemeToggle = () => {
    dispatch(toggleTheme())
  }

  return (
    <header className="sticky top-0 z-50 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
      <div className="container mx-auto px-4">
        <div className="flex h-16 items-center justify-between">
          {/* Logo */}
          <Link to="/" className="flex items-center space-x-2">
            <div className="h-8 w-8 rounded-full bg-primary"></div>
            <span className="text-xl font-bold">ClothingStore</span>
          </Link>

          {/* Navigation */}
          <nav className="hidden md:flex items-center space-x-8">
            <Link 
              to="/men" 
              className="text-sm font-medium transition-colors hover:text-primary"
            >
              Men
            </Link>
            <Link 
              to="/women" 
              className="text-sm font-medium transition-colors hover:text-primary"
            >
              Women
            </Link>
            <Link 
              to="/kids" 
              className="text-sm font-medium transition-colors hover:text-primary"
            >
              Kids
            </Link>
          </nav>

          {/* Actions */}
          <div className="flex items-center space-x-4">
            {/* Search */}
            <button className="p-2 hover:bg-accent rounded-md">
              <Search className="h-5 w-5" />
            </button>

            {/* Theme Toggle */}
            <button 
              onClick={handleThemeToggle}
              className="p-2 hover:bg-accent rounded-md"
            >
              {effectiveTheme === 'dark' ? (
                <Sun className="h-5 w-5" />
              ) : (
                <Moon className="h-5 w-5" />
              )}
            </button>

            {/* Wishlist */}
            {isAuthenticated && (
              <Link to="/wishlist" className="relative p-2 hover:bg-accent rounded-md">
                <Heart className="h-5 w-5" />
                {wishlistItemCount > 0 && (
                  <span className="absolute -top-1 -right-1 h-5 w-5 rounded-full bg-primary text-primary-foreground text-xs flex items-center justify-center">
                    {wishlistItemCount}
                  </span>
                )}
              </Link>
            )}

            {/* Cart */}
            <Link to="/cart" className="relative p-2 hover:bg-accent rounded-md">
              <ShoppingCart className="h-5 w-5" />
              {cartItemCount > 0 && (
                <span className="absolute -top-1 -right-1 h-5 w-5 rounded-full bg-primary text-primary-foreground text-xs flex items-center justify-center">
                  {cartItemCount}
                </span>
              )}
            </Link>

            {/* User Menu */}
            {isAuthenticated ? (
              <Link to="/account" className="p-2 hover:bg-accent rounded-md">
                <User className="h-5 w-5" />
              </Link>
            ) : (
              <Link 
                to="/login" 
                className="px-4 py-2 text-sm font-medium bg-primary text-primary-foreground rounded-md hover:bg-primary/90"
              >
                Sign In
              </Link>
            )}

            {/* Mobile Menu */}
            <button className="md:hidden p-2 hover:bg-accent rounded-md">
              <Menu className="h-5 w-5" />
            </button>
          </div>
        </div>
      </div>
    </header>
  )
}
