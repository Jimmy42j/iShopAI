import { apiClient } from './api'
import { ApiResponse, Wishlist } from '../types'

export const wishlistApi = {
  // Get user's wishlist
  getWishlist: (): Promise<ApiResponse<Wishlist>> =>
    apiClient.get('/wishlist'),

  // Add product to wishlist
  addToWishlist: (productId: number): Promise<ApiResponse> =>
    apiClient.post('/wishlist', { product_id: productId }),

  // Remove product from wishlist
  removeFromWishlist: (productId: number): Promise<ApiResponse> =>
    apiClient.delete(`/wishlist/${productId}`),

  // Check if product is in wishlist
  checkWishlistStatus: (productId: number): Promise<ApiResponse<{ product_id: number; in_wishlist: boolean }>> =>
    apiClient.get(`/wishlist/check/${productId}`),

  // Get wishlist statistics
  getWishlistStats: (): Promise<ApiResponse<{
    total_items: number
    total_value: number
    categories: Array<{ category_name: string; category_slug: string; item_count: number; category_value: number }>
    price_range: { min: number; max: number }
  }>> =>
    apiClient.get('/wishlist/stats'),
}
