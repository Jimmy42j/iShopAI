import { apiClient } from './api'
import { ApiResponse, Category } from '../types'

export const categoriesApi = {
  // Get all categories
  getCategories: (): Promise<ApiResponse<Category[]>> =>
    apiClient.get('/categories'),

  // Get single category by slug with stats and filters
  getCategory: (slug: string): Promise<ApiResponse<Category>> =>
    apiClient.get(`/categories/${slug}`),
}
