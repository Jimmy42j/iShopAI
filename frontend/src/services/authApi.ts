import { apiClient } from './api'
import { ApiResponse, User, LoginForm, RegisterForm } from '../types'

export const authApi = {
  // Register new user
  register: (userData: RegisterForm): Promise<ApiResponse<{ user: User; token: string }>> =>
    apiClient.post('/auth/register', userData),

  // Login user
  login: (credentials: LoginForm): Promise<ApiResponse<{ user: User; token: string }>> =>
    apiClient.post('/auth/login', credentials),

  // Logout user
  logout: (): Promise<ApiResponse> =>
    apiClient.post('/auth/logout'),

  // Get current user info
  getCurrentUser: (): Promise<ApiResponse<{ user: User }>> =>
    apiClient.get('/auth/me'),

  // Refresh JWT token
  refreshToken: (): Promise<ApiResponse<{ user: User; token: string }>> =>
    apiClient.post('/auth/refresh'),
}
