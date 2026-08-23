import { apiClient } from './api'
import { ApiResponse, RecommendationRequest, RecommendationResponse, RecommendationExplanation } from '../types'

export const recommendationsApi = {
  // Get personalized product recommendations
  getRecommendations: (params: RecommendationRequest): Promise<ApiResponse<RecommendationResponse>> =>
    apiClient.post('/ai/recommend', params),

  // Get explanation for a specific product recommendation
  explainProduct: (productId: number): Promise<ApiResponse<RecommendationExplanation>> =>
    apiClient.get(`/ai/explain/${productId}`),
}
