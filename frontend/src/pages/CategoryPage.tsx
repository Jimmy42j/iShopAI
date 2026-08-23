import React, { useEffect, useState } from 'react'
import { Star, ShoppingCart, Heart } from 'lucide-react'
import { productsApi } from '../services/productsApi'
import { Product } from '../types'
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

interface CategoryPageProps {
  category: 'men' | 'women' | 'kids'
}

export const CategoryPage: React.FC<CategoryPageProps> = ({ category }) => {
  const [products, setProducts] = useState<Product[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  const categoryNames = {
    men: "Men's",
    women: "Women's",
    kids: "Kids'"
  }

  useEffect(() => {
    const fetchProducts = async () => {
      try {
        setLoading(true)
        setError(null)
        
        console.log('Fetching products for category:', category)
        
        // Fetch all products (we'll filter by category on frontend for now)
        const response = await productsApi.getProducts({ limit: 50 })
        
        console.log('API Response:', response)
        
        if (response.success) {
          // Filter products by category based on gender_target
          const categoryMap = {
            men: 'men',
            women: 'women', 
            kids: 'kids'
          }
          
          const filteredProducts = response.data.items?.filter(product => 
            product.gender_target === categoryMap[category]
          ) || []
          
          console.log('Filtered products:', filteredProducts)
          setProducts(filteredProducts)
        } else {
          setError('Failed to load products')
        }
      } catch (err) {
        console.error('Error fetching products:', err)
        setError('Failed to load products. Please try again.')
      } finally {
        setLoading(false)
      }
    }

    fetchProducts()
  }, [category])

  if (loading) {
    return (
      <div className="container mx-auto px-4 py-8">
        <div className="text-center">
          <h1 className="text-4xl font-bold mb-4">
            {categoryNames[category]} Fashion
          </h1>
          <p className="text-muted-foreground mb-8">
            Discover the latest trends in {categoryNames[category].toLowerCase()} clothing
          </p>
          <div className="flex justify-center items-center py-12">
            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
          </div>
        </div>
      </div>
    )
  }

  if (error) {
    return (
      <div className="container mx-auto px-4 py-8">
        <div className="text-center">
          <h1 className="text-4xl font-bold mb-4">
            {categoryNames[category]} Fashion
          </h1>
          <div className="bg-red-50 border border-red-200 rounded-lg p-8">
            <p className="text-red-600">{error}</p>
            <button 
              onClick={() => window.location.reload()} 
              className="mt-4 px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90"
            >
              Try Again
            </button>
          </div>
        </div>
      </div>
    )
  }

  return (
    <div className="container mx-auto px-4 py-8">
      <div className="text-center mb-12">
        <h1 className="text-4xl font-bold mb-4">
          {categoryNames[category]} Fashion
        </h1>
        <p className="text-muted-foreground mb-8">
          Discover the latest trends in {categoryNames[category].toLowerCase()} clothing
        </p>
      </div>

      {products.length === 0 ? (
        <div className="text-center py-12">
          <p className="text-lg text-muted-foreground">
            No products found for {categoryNames[category].toLowerCase()} category.
          </p>
        </div>
      ) : (
        <>
          <div className="mb-6">
            <p className="text-sm text-muted-foreground">
              Showing {products.length} products
            </p>
          </div>
          
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            {products.map((product) => (
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
                  
                  {/* Quick actions */}
                  <div className="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button className="p-2 bg-white rounded-full shadow-md hover:bg-gray-50">
                      <Heart className="h-4 w-4" />
                    </button>
                  </div>
                </div>
                
                <div className="p-4">
                  <h3 className="font-semibold mb-2 line-clamp-2">{product.name}</h3>
                  <p className="text-sm text-muted-foreground mb-3 line-clamp-2">{product.description}</p>
                  
                  <div className="flex items-center justify-between mb-3">
                    <span className="text-lg font-bold">${product.price}</span>
                    <div className="flex items-center text-sm text-yellow-500">
                      <Star className="h-4 w-4 fill-current" />
                      <span className="ml-1">{product.rating_avg || '4.5'}</span>
                    </div>
                  </div>
                  
                  <button className="w-full flex items-center justify-center gap-2 px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors">
                    <ShoppingCart className="h-4 w-4" />
                    Add to Cart
                  </button>
                </div>
              </div>
            ))}
          </div>
        </>
      )}
    </div>
  )
}