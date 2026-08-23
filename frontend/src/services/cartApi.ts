import { apiClient } from './api'
import { ApiResponse, Cart } from '../types'

export const cartApi = {
  // Get cart contents
  getCart: (cartId?: string): Promise<ApiResponse<Cart>> => {
    const params = cartId ? { cart_id: cartId } : {}
    return apiClient.get('/cart', { params })
  },

  // Add item to cart
  addToCart: (data: {
    product_id: number
    variant_id?: number
    qty?: number
    cart_id?: string
  }): Promise<ApiResponse<Cart>> =>
    apiClient.post('/cart', data),

  // Update cart item quantity
  updateCartItem: (itemId: number, data: { qty: number }): Promise<ApiResponse<Cart>> =>
    apiClient.patch(`/cart/${itemId}`, data),

  // Remove item from cart
  removeFromCart: (itemId: number): Promise<ApiResponse> =>
    apiClient.delete(`/cart/${itemId}`),

  // Clear entire cart
  clearCart: (cartId?: string): Promise<ApiResponse> => {
    const params = cartId ? { cart_id: cartId } : {}
    return apiClient.delete('/cart', { params })
  },
}
