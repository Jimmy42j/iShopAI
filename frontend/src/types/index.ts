// API Response Types
export interface ApiResponse<T = any> {
  success: boolean
  message?: string
  data?: T
  errors?: Record<string, string[]>
  status?: number
}

export interface PaginationMeta {
  page: number
  limit: number
  total: number
  pages: number
}

export interface PaginatedResponse<T> {
  items: T[]
  pagination: PaginationMeta
}

// User Types
export interface User {
  id: number
  name: string
  email: string
  gender?: 'male' | 'female' | 'other'
  birthdate?: string
  created_at: string
  updated_at: string
}

export interface AuthState {
  user: User | null
  token: string | null
  isAuthenticated: boolean
  isLoading: boolean
  error: string | null
}

// Product Types
export interface Product {
  id: number
  category_id: number
  name: string
  slug: string
  description?: string
  price: number
  rating_avg: number
  rating_count: number
  season: 'spring' | 'summer' | 'autumn' | 'winter' | 'all'
  gender_target: 'men' | 'women' | 'kids' | 'unisex'
  material?: string
  brand?: string
  is_active: boolean
  created_at: string
  updated_at: string
  category_name?: string
  category_slug?: string
  image_url?: string
  image_alt?: string
  primary_image?: string
  images?: ProductImage[]
  variants?: ProductVariant[]
}

export interface ProductImage {
  id: number
  product_id: number
  url: string
  alt_text?: string
  is_primary: boolean
  sort_order: number
}

export interface ProductVariant {
  id: number
  product_id: number
  sku: string
  color?: string
  size?: string
  stock: number
  extra_price: number
  is_active: boolean
}

// Category Types
export interface Category {
  id: number
  slug: string
  name: string
  created_at: string
  product_count?: number
  stats?: CategoryStats
  filters?: CategoryFilters
}

export interface CategoryStats {
  total_products: number
  avg_price: number
  min_price: number
  max_price: number
}

export interface CategoryFilters {
  gender_targets: FilterOption[]
  seasons: FilterOption[]
  brands: FilterOption[]
  materials: FilterOption[]
  price_ranges: PriceRange[]
}

export interface FilterOption {
  [key: string]: any
  count: number
}

export interface PriceRange {
  min: number
  max: number | null
  label: string
  count: number
}

// Cart Types
export interface Cart {
  cart_id: number
  session_id?: string
  items: CartItem[]
  total: number
  item_count: number
  created_at: string
  updated_at: string
}

export interface CartItem {
  id: number
  cart_id: number
  product_id: number
  variant_id?: number
  qty: number
  price_at_add: number
  total: number
  product_name: string
  product_slug: string
  current_price: number
  color?: string
  size?: string
  extra_price: number
  product_image?: string
  created_at: string
  updated_at: string
}

// Wishlist Types
export interface Wishlist {
  wishlist_id: number
  items: WishlistItem[]
  total_items: number
}

export interface WishlistItem {
  id: number
  wishlist_id: number
  product_id: number
  created_at: string
  product_name: string
  product_slug: string
  price: number
  rating_avg: number
  rating_count: number
  season: string
  gender_target: string
  brand?: string
  category_name: string
  category_slug: string
  product_image?: string
}

// Order Types
export interface Order {
  id: number
  user_id: number
  total: number
  status: 'pending' | 'paid' | 'shipped' | 'delivered' | 'cancelled'
  shipping_address_id?: number
  payment_method?: string
  payment_token?: string
  notes?: string
  created_at: string
  updated_at: string
  items: OrderItem[]
  item_count: number
  line1?: string
  line2?: string
  city?: string
  state?: string
  postal_code?: string
  country?: string
}

export interface OrderItem {
  id: number
  order_id: number
  product_id: number
  variant_id?: number
  qty: number
  unit_price: number
  total: number
  product_name: string
  product_slug: string
  product_description?: string
  color?: string
  size?: string
  product_image?: string
  created_at: string
}

// Address Types
export interface Address {
  id: number
  user_id: number
  line1: string
  line2?: string
  city: string
  state: string
  postal_code: string
  country: string
  is_default: boolean
  created_at: string
  updated_at: string
}

// AI Recommendation Types
export interface RecommendationRequest {
  gender_context?: 'men' | 'women' | 'kids' | 'unisex' | 'detect'
  season?: 'spring' | 'summer' | 'autumn' | 'winter' | 'all' | 'auto'
  user_signals?: UserSignals
  topk?: number
}

export interface UserSignals {
  viewed?: number[]
  wishlisted?: number[]
  carted?: number[]
  purchased?: number[]
}

export interface RecommendationResponse {
  products: ProductRecommendation[]
  model_version: string
  latency_ms: number
}

export interface ProductRecommendation {
  product_id: number
  score: number
  reason: string
}

export interface RecommendationExplanation {
  reason: string
  features: RecommendationFeature[]
  model_version: string
}

export interface RecommendationFeature {
  key: string
  weight: number
}

// Search Types
export interface SearchFilters {
  category?: string
  season?: string
  gender_target?: string
  min_price?: number
  max_price?: number
  brands?: string[]
  materials?: string[]
  colors?: string[]
  sizes?: string[]
}

export interface SearchParams extends SearchFilters {
  q?: string
  sort?: 'newest' | 'price_asc' | 'price_desc' | 'rating' | 'popularity' | 'name'
  page?: number
  limit?: number
}

// Theme Types
export type Theme = 'light' | 'dark' | 'system'

export interface ThemeState {
  theme: Theme
  systemTheme: 'light' | 'dark'
}

// UI State Types
export interface UIState {
  isMobileMenuOpen: boolean
  isSearchOpen: boolean
  isCartOpen: boolean
  isLoading: boolean
  notifications: Notification[]
}

export interface Notification {
  id: string
  type: 'success' | 'error' | 'warning' | 'info'
  title: string
  message?: string
  duration?: number
  action?: {
    label: string
    onClick: () => void
  }
}

// Form Types
export interface LoginForm {
  email: string
  password: string
}

export interface RegisterForm {
  name: string
  email: string
  password: string
  confirmPassword: string
  gender?: 'male' | 'female' | 'other'
  birthdate?: string
}

export interface CheckoutForm {
  cart_id: number
  address_id: number
  payment_token?: string
  notes?: string
}

export interface AddressForm {
  line1: string
  line2?: string
  city: string
  state: string
  postal_code: string
  country: string
  is_default?: boolean
}

// Error Types
export interface ApiError {
  message: string
  status: number
  errors?: Record<string, string[]>
}

// Utility Types
export type LoadingState = 'idle' | 'loading' | 'succeeded' | 'failed'

export interface AsyncState<T> {
  data: T | null
  status: LoadingState
  error: string | null
}

// Component Props Types
export interface BaseComponentProps {
  className?: string
  children?: React.ReactNode
}

export interface PageProps {
  title?: string
  description?: string
}

// Route Types
export interface RouteParams {
  slug?: string
  id?: string
  category?: string
}
