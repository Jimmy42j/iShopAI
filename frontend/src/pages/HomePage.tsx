import React, { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { ArrowRight, Star, TrendingUp } from 'lucide-react'
import { productsApi } from '../services/productsApi'
import { categoriesApi } from '../services/categoriesApi'
import { Product, Category } from '../types'
import { getProductImageUrl } from '../utils/imageUtils'

// Color utility function
const getProductColor = (product: Product, darker = false) => {
  const colorSchemes = {
    men: ['#4f46e5', '#1e40af', '#059669', '#7c2d12', '#374151'],
    women: ['#ec4899', '#be185d', '#c2410c', '#7c3aed', '#dc2626'],
    kids: ['#f59e0b', '#10b981', '#3b82f6', '#ef4444', '#8b5cf6'],
    unisex: ['#6b7280', '#374151', '#1f2937', '#4b5563', '#9ca3af']
  }
  
  const genderColors = colorSchemes[product.gender_target as keyof typeof colorSchemes] || colorSchemes.unisex
  const colorIndex = product.id % genderColors.length
  const baseColor = genderColors[colorIndex]
  
  if (darker) {
    // Return a darker version for gradient
    const hex = baseColor.replace('#', '')
    const r = Math.max(0, parseInt(hex.substr(0, 2), 16) - 30)
    const g = Math.max(0, parseInt(hex.substr(2, 2), 16) - 30)
    const b = Math.max(0, parseInt(hex.substr(4, 2), 16) - 30)
    return `rgb(${r}, ${g}, ${b})`
  }
  
  return baseColor
}

// Emoji utility function
const getProductEmoji = (product: Product) => {
  const name = product.name.toLowerCase()
  
  if (name.includes('shirt') || name.includes('polo')) return '👕'
  if (name.includes('pants') || name.includes('chino') || name.includes('jeans')) return '👖'
  if (name.includes('dress')) return '👗'
  if (name.includes('jacket') || name.includes('coat')) return '🧥'
  if (name.includes('sweater') || name.includes('hoodie')) return '🧶'
  if (name.includes('shorts')) return '🩳'
  if (name.includes('boots') || name.includes('shoes') || name.includes('sneakers') || name.includes('sandals')) return '👟'
  if (name.includes('hat') || name.includes('cap') || name.includes('beanie')) return '🧢'
  if (name.includes('scarf')) return '🧣'
  if (name.includes('underwear') || name.includes('thermal')) return '🩲'
  if (name.includes('pajamas')) return '🩱'
  if (name.includes('backpack')) return '🎒'
  
  // Default based on gender
  if (product.gender_target === 'women') return '👗'
  if (product.gender_target === 'kids') return '🧸'
  return '👕'
}

export const HomePage: React.FC = () => {
  const [products, setProducts] = useState<Product[]>([])
  const [categories, setCategories] = useState<Category[]>([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    const fetchData = async () => {
      try {
        setLoading(true)
        
        // Fetch categories and products in parallel
        const [categoriesResponse, productsResponse] = await Promise.all([
          categoriesApi.getCategories(),
          productsApi.getProducts({ limit: 8 })
        ])

        if (categoriesResponse.success) {
          setCategories(categoriesResponse.data)
        }

        if (productsResponse.success) {
          setProducts(productsResponse.data.items || [])
        }
      } catch (error) {
        console.error('Error fetching data:', error)
      } finally {
        setLoading(false)
      }
    }

    fetchData()
  }, [])

  return (
    <div className="space-y-16">
      {/* Hero Section */}
      <section className="relative bg-gradient-to-r from-primary/10 to-secondary/10 py-20">
        <div className="container mx-auto px-4">
          <div className="max-w-3xl mx-auto text-center">
            <h1 className="text-4xl md:text-6xl font-bold mb-6">
              Fashion for Everyone
            </h1>
            <p className="text-xl text-muted-foreground mb-8">
              Discover the latest trends in clothing with AI-powered recommendations 
              tailored just for you. Shop men's, women's, and kids' fashion.
            </p>
            <div className="flex flex-col sm:flex-row gap-4 justify-center">
              <Link 
                to="/men" 
                className="px-8 py-3 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors"
              >
                Shop Men's
              </Link>
              <Link 
                to="/women" 
                className="px-8 py-3 bg-secondary text-secondary-foreground rounded-lg hover:bg-secondary/90 transition-colors"
              >
                Shop Women's
              </Link>
              <Link 
                to="/kids" 
                className="px-8 py-3 border border-border rounded-lg hover:bg-accent transition-colors"
              >
                Shop Kids'
              </Link>
            </div>
          </div>
        </div>
      </section>

      {/* Features Section */}
      <section className="py-16">
        <div className="container mx-auto px-4">
          <div className="text-center mb-12">
            <h2 className="text-3xl font-bold mb-4">Why Choose Us?</h2>
            <p className="text-muted-foreground max-w-2xl mx-auto">
              Experience shopping like never before with our AI-powered recommendations 
              and curated collections.
            </p>
          </div>
          
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div className="text-center p-6">
              <div className="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                <TrendingUp className="h-8 w-8 text-primary" />
              </div>
              <h3 className="text-xl font-semibold mb-2">AI Recommendations</h3>
              <p className="text-muted-foreground">
                Get personalized product suggestions based on your preferences, 
                season, and style.
              </p>
            </div>
            
            <div className="text-center p-6">
              <div className="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                <Star className="h-8 w-8 text-primary" />
              </div>
              <h3 className="text-xl font-semibold mb-2">Quality Products</h3>
              <p className="text-muted-foreground">
                Curated selection of high-quality clothing from trusted brands 
                and designers.
              </p>
            </div>
            
            <div className="text-center p-6">
              <div className="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                <ArrowRight className="h-8 w-8 text-primary" />
              </div>
              <h3 className="text-xl font-semibold mb-2">Fast Shipping</h3>
              <p className="text-muted-foreground">
                Quick and reliable delivery to get your favorite items to you 
                as soon as possible.
              </p>
            </div>
          </div>
        </div>
      </section>

      {/* Featured Products Section */}
      <section className="py-16">
        <div className="container mx-auto px-4">
          <div className="text-center mb-12">
            <h2 className="text-3xl font-bold mb-4">Featured Products</h2>
            <p className="text-muted-foreground">
              Discover our most popular items, handpicked just for you.
            </p>
          </div>
          
          {loading ? (
            <div className="flex justify-center items-center py-12">
              <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
            </div>
          ) : (
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
              {products.slice(0, 8).map((product) => (
                <div key={product.id} className="group bg-card rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                  <div className="product-image-container">
                    <img 
                      src={product.image_url || getProductImageUrl(product)}
                      alt={product.name}
                      className="product-image"
                      onError={(e) => {
                        const target = e.currentTarget as HTMLImageElement
                        target.src = `https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=400&h=400&fit=crop&crop=center`
                      }}
                    />
                    <div className="absolute inset-0 bg-black/0 hover:bg-black/10 transition-colors duration-300"></div>
                  </div>
                  <div className="p-4">
                    <h3 className="font-semibold mb-2 line-clamp-2">{product.name}</h3>
                    <p className="text-sm text-muted-foreground mb-2 line-clamp-2">{product.description}</p>
                    <div className="flex items-center justify-between">
                      <span className="text-lg font-bold">${product.price}</span>
                      <div className="flex items-center text-sm text-yellow-500">
                        <Star className="h-4 w-4 fill-current" />
                        <span className="ml-1">{product.rating_avg || '4.5'}</span>
                      </div>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          )}
          
          <div className="text-center mt-8">
            <Link 
              to="/products" 
              className="inline-flex items-center px-6 py-3 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors"
            >
              View All Products
              <ArrowRight className="ml-2 h-4 w-4" />
            </Link>
          </div>
        </div>
      </section>

      {/* Categories Section */}
      <section className="py-16 bg-muted/50">
        <div className="container mx-auto px-4">
          <div className="text-center mb-12">
            <h2 className="text-3xl font-bold mb-4">Shop by Category</h2>
            <p className="text-muted-foreground">
              Find exactly what you're looking for in our organized collections.
            </p>
          </div>
          
          {loading ? (
            <div className="flex justify-center items-center py-12">
              <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
            </div>
          ) : (
            <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
              {categories.slice(0, 3).map((category, index) => {
                const colors = [
                  'from-blue-500/20 to-blue-600/20',
                  'from-pink-500/20 to-pink-600/20', 
                  'from-green-500/20 to-green-600/20'
                ]
                return (
                  <Link 
                    key={category.id}
                    to={`/category/${category.slug}`}
                    className="group relative overflow-hidden rounded-lg bg-card hover:shadow-lg transition-shadow"
                  >
                    <div className={`aspect-square bg-gradient-to-br ${colors[index % 3]} flex items-center justify-center`}>
                      <div className="text-center">
                        <h3 className="text-2xl font-bold mb-2">{category.name}</h3>
                        <p className="text-muted-foreground">Discover amazing {category.name.toLowerCase()}</p>
                      </div>
                    </div>
                  </Link>
                )
              })}
            </div>
          )}
        </div>
      </section>

      {/* CTA Section */}
      <section className="py-16">
        <div className="container mx-auto px-4">
          <div className="bg-primary text-primary-foreground rounded-2xl p-8 md:p-12 text-center">
            <h2 className="text-3xl font-bold mb-4">
              Ready to Discover Your Style?
            </h2>
            <p className="text-xl mb-8 opacity-90">
              Join thousands of satisfied customers and start shopping with 
              AI-powered recommendations today.
            </p>
            <Link 
              to="/register" 
              className="inline-flex items-center px-8 py-3 bg-white text-primary rounded-lg hover:bg-gray-100 transition-colors font-semibold"
            >
              Get Started
              <ArrowRight className="ml-2 h-5 w-5" />
            </Link>
          </div>
        </div>
      </section>
    </div>
  )
}
