import { apiClient } from './api'
import { ApiResponse, Product, PaginatedResponse, SearchParams } from '../types'

export const productsApi = {
  // Get products with filtering and pagination
  getProducts: (params?: SearchParams): Promise<ApiResponse<PaginatedResponse<Product>>> =>
    apiClient.get('/products', { params }),

  // Get single product by slug
  getProduct: (slug: string): Promise<ApiResponse<Product>> =>
    apiClient.get(`/products/${slug}`),

  // Get related products
  getRelatedProducts: (productId: number, limit?: number): Promise<ApiResponse<Product[]>> => {
    const params = limit ? { limit } : {}
    return apiClient.get(`/products/${productId}/related`, { params })
  },

  // Search products
  searchProducts: (query: string, params?: Omit<SearchParams, 'q'>): Promise<ApiResponse<Product[]>> =>
    apiClient.get('/products/search', { params: { q: query, ...params } }),
}
