import { apiClient } from './api'
import { ApiResponse, Order, PaginatedResponse, CheckoutForm } from '../types'

export const ordersApi = {
  // Get user's orders
  getOrders: (params?: {
    page?: number
    limit?: number
    status?: string
  }): Promise<ApiResponse<{ orders: Order[]; pagination: any }>> =>
    apiClient.get('/orders', { params }),

  // Get single order details
  getOrder: (orderId: number): Promise<ApiResponse<Order>> =>
    apiClient.get(`/orders/${orderId}`),

  // Create order from cart (checkout)
  checkout: (data: CheckoutForm): Promise<ApiResponse<{
    order_id: number
    status: string
    total: number
  }>> =>
    apiClient.post('/orders/checkout', data),
}
