// AI Service API client
const AI_SERVICE_BASE_URL = 'http://localhost:5000'

export interface ChatMessage {
  message: string
  user_context?: {
    user_signals?: {
      viewed: number[]
      wishlisted: number[]
      carted: number[]
    }
  }
}

export interface ChatResponse {
  message: string
  intent: string
  confidence: number
  has_recommendations: boolean
  recommendations?: Array<{
    product_id: number
    score: number
    reason: string
  }>
  entities_detected: Record<string, string>
}

export interface RecommendationRequest {
  gender_context?: 'men' | 'women' | 'kids' | 'unisex' | 'detect'
  season?: 'spring' | 'summer' | 'autumn' | 'winter' | 'all' | 'auto'
  topk?: number
  user_signals?: {
    viewed: number[]
    wishlisted: number[]
    carted: number[]
  }
}

export interface RecommendationResponse {
  products: Array<{
    product_id: number
    score: number
    reason: string
  }>
  model_version: string
  latency_ms: number
}

export const aiApi = {
  // Health check
  healthCheck: async () => {
    const response = await fetch(`${AI_SERVICE_BASE_URL}/health`)
    return response.json()
  },

  // Chat with AI assistant
  chat: async (message: ChatMessage): Promise<ChatResponse> => {
    const response = await fetch(`${AI_SERVICE_BASE_URL}/chat`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(message)
    })
    
    if (!response.ok) {
      throw new Error(`AI service error: ${response.statusText}`)
    }
    
    return response.json()
  },

  // Get product recommendations
  getRecommendations: async (params: RecommendationRequest): Promise<RecommendationResponse> => {
    const response = await fetch(`${AI_SERVICE_BASE_URL}/recommend`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(params)
    })
    
    if (!response.ok) {
      throw new Error(`AI service error: ${response.statusText}`)
    }
    
    return response.json()
  },

  // Explain product recommendation
  explainProduct: async (productId: number) => {
    const response = await fetch(`${AI_SERVICE_BASE_URL}/explain/${productId}`)
    
    if (!response.ok) {
      throw new Error(`AI service error: ${response.statusText}`)
    }
    
    return response.json()
  },

  // Get model information
  getModelInfo: async () => {
    const response = await fetch(`${AI_SERVICE_BASE_URL}/model/info`)
    return response.json()
  }
}

export default aiApi
