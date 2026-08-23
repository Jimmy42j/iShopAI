import { createSlice, createAsyncThunk, PayloadAction } from '@reduxjs/toolkit'
import { Cart, CartItem } from '../../types'
import { cartApi } from '../../services/cartApi'
import { toast } from 'react-hot-toast'

interface CartState {
  cart: Cart | null
  isLoading: boolean
  error: string | null
  isOpen: boolean
}

const initialState: CartState = {
  cart: null,
  isLoading: false,
  error: null,
  isOpen: false,
}

// Async thunks
export const fetchCart = createAsyncThunk(
  'cart/fetchCart',
  async (cartId?: string, { rejectWithValue }) => {
    try {
      const response = await cartApi.getCart(cartId)
      if (response.success && response.data) {
        return response.data
      }
      throw new Error(response.message || 'Failed to fetch cart')
    } catch (error: any) {
      return rejectWithValue(error.response?.data?.message || error.message)
    }
  }
)

export const addToCart = createAsyncThunk(
  'cart/addToCart',
  async (
    params: {
      product_id: number
      variant_id?: number
      qty?: number
      cart_id?: string
    },
    { rejectWithValue }
  ) => {
    try {
      const response = await cartApi.addToCart(params)
      if (response.success && response.data) {
        toast.success('Item added to cart!')
        return response.data
      }
      throw new Error(response.message || 'Failed to add item to cart')
    } catch (error: any) {
      const message = error.response?.data?.message || error.message || 'Failed to add item to cart'
      toast.error(message)
      return rejectWithValue(message)
    }
  }
)

export const updateCartItem = createAsyncThunk(
  'cart/updateCartItem',
  async (
    params: { itemId: number; qty: number },
    { rejectWithValue }
  ) => {
    try {
      const response = await cartApi.updateCartItem(params.itemId, { qty: params.qty })
      if (response.success && response.data) {
        if (params.qty === 0) {
          toast.success('Item removed from cart')
        } else {
          toast.success('Cart updated')
        }
        return response.data
      }
      throw new Error(response.message || 'Failed to update cart item')
    } catch (error: any) {
      const message = error.response?.data?.message || error.message || 'Failed to update cart item'
      toast.error(message)
      return rejectWithValue(message)
    }
  }
)

export const removeFromCart = createAsyncThunk(
  'cart/removeFromCart',
  async (itemId: number, { rejectWithValue }) => {
    try {
      const response = await cartApi.removeFromCart(itemId)
      if (response.success) {
        toast.success('Item removed from cart')
        return itemId
      }
      throw new Error(response.message || 'Failed to remove item from cart')
    } catch (error: any) {
      const message = error.response?.data?.message || error.message || 'Failed to remove item from cart'
      toast.error(message)
      return rejectWithValue(message)
    }
  }
)

export const clearCart = createAsyncThunk(
  'cart/clearCart',
  async (cartId?: string, { rejectWithValue }) => {
    try {
      const response = await cartApi.clearCart(cartId)
      if (response.success) {
        toast.success('Cart cleared')
        return null
      }
      throw new Error(response.message || 'Failed to clear cart')
    } catch (error: any) {
      const message = error.response?.data?.message || error.message || 'Failed to clear cart'
      toast.error(message)
      return rejectWithValue(message)
    }
  }
)

const cartSlice = createSlice({
  name: 'cart',
  initialState,
  reducers: {
    clearError: (state) => {
      state.error = null
    },
    setCartOpen: (state, action: PayloadAction<boolean>) => {
      state.isOpen = action.payload
    },
    toggleCart: (state) => {
      state.isOpen = !state.isOpen
    },
    // Optimistic updates for better UX
    optimisticAddToCart: (state, action: PayloadAction<{
      product_id: number
      product_name: string
      price: number
      qty: number
      variant_id?: number
      color?: string
      size?: string
    }>) => {
      if (!state.cart) {
        state.cart = {
          cart_id: 0,
          items: [],
          total: 0,
          item_count: 0,
          created_at: new Date().toISOString(),
          updated_at: new Date().toISOString(),
        }
      }

      const { product_id, variant_id, qty, product_name, price, color, size } = action.payload
      
      // Check if item already exists
      const existingItemIndex = state.cart.items.findIndex(
        item => item.product_id === product_id && item.variant_id === variant_id
      )

      if (existingItemIndex >= 0) {
        // Update existing item
        state.cart.items[existingItemIndex].qty += qty
        state.cart.items[existingItemIndex].total = 
          state.cart.items[existingItemIndex].qty * state.cart.items[existingItemIndex].price_at_add
      } else {
        // Add new item
        const newItem: CartItem = {
          id: Date.now(), // Temporary ID
          cart_id: state.cart.cart_id,
          product_id,
          variant_id,
          qty,
          price_at_add: price,
          total: price * qty,
          product_name,
          product_slug: '',
          current_price: price,
          color,
          size,
          extra_price: 0,
          created_at: new Date().toISOString(),
          updated_at: new Date().toISOString(),
        }
        state.cart.items.push(newItem)
      }

      // Recalculate totals
      state.cart.total = state.cart.items.reduce((sum, item) => sum + item.total, 0)
      state.cart.item_count = state.cart.items.reduce((sum, item) => sum + item.qty, 0)
    },
    optimisticRemoveFromCart: (state, action: PayloadAction<number>) => {
      if (state.cart) {
        state.cart.items = state.cart.items.filter(item => item.id !== action.payload)
        state.cart.total = state.cart.items.reduce((sum, item) => sum + item.total, 0)
        state.cart.item_count = state.cart.items.reduce((sum, item) => sum + item.qty, 0)
      }
    },
    optimisticUpdateCartItem: (state, action: PayloadAction<{ itemId: number; qty: number }>) => {
      if (state.cart) {
        const item = state.cart.items.find(item => item.id === action.payload.itemId)
        if (item) {
          if (action.payload.qty === 0) {
            // Remove item
            state.cart.items = state.cart.items.filter(item => item.id !== action.payload.itemId)
          } else {
            // Update quantity
            item.qty = action.payload.qty
            item.total = item.qty * item.price_at_add
          }
          
          // Recalculate totals
          state.cart.total = state.cart.items.reduce((sum, item) => sum + item.total, 0)
          state.cart.item_count = state.cart.items.reduce((sum, item) => sum + item.qty, 0)
        }
      }
    },
  },
  extraReducers: (builder) => {
    builder
      // Fetch cart
      .addCase(fetchCart.pending, (state) => {
        state.isLoading = true
        state.error = null
      })
      .addCase(fetchCart.fulfilled, (state, action) => {
        state.isLoading = false
        state.cart = action.payload
        state.error = null
      })
      .addCase(fetchCart.rejected, (state, action) => {
        state.isLoading = false
        state.error = action.payload as string
      })
      
      // Add to cart
      .addCase(addToCart.pending, (state) => {
        state.isLoading = true
        state.error = null
      })
      .addCase(addToCart.fulfilled, (state, action) => {
        state.isLoading = false
        state.cart = action.payload
        state.error = null
      })
      .addCase(addToCart.rejected, (state, action) => {
        state.isLoading = false
        state.error = action.payload as string
      })
      
      // Update cart item
      .addCase(updateCartItem.fulfilled, (state, action) => {
        state.cart = action.payload
      })
      .addCase(updateCartItem.rejected, (state, action) => {
        state.error = action.payload as string
      })
      
      // Remove from cart
      .addCase(removeFromCart.fulfilled, (state, action) => {
        if (state.cart) {
          state.cart.items = state.cart.items.filter(item => item.id !== action.payload)
          state.cart.total = state.cart.items.reduce((sum, item) => sum + item.total, 0)
          state.cart.item_count = state.cart.items.reduce((sum, item) => sum + item.qty, 0)
        }
      })
      .addCase(removeFromCart.rejected, (state, action) => {
        state.error = action.payload as string
      })
      
      // Clear cart
      .addCase(clearCart.fulfilled, (state) => {
        state.cart = null
      })
      .addCase(clearCart.rejected, (state, action) => {
        state.error = action.payload as string
      })
  },
})

export const {
  clearError,
  setCartOpen,
  toggleCart,
  optimisticAddToCart,
  optimisticRemoveFromCart,
  optimisticUpdateCartItem,
} = cartSlice.actions

export default cartSlice.reducer

// Selectors
export const selectCart = (state: { cart: CartState }) => state.cart.cart
export const selectCartItems = (state: { cart: CartState }) => state.cart.cart?.items || []
export const selectCartTotal = (state: { cart: CartState }) => state.cart.cart?.total || 0
export const selectCartItemCount = (state: { cart: CartState }) => state.cart.cart?.item_count || 0
export const selectCartLoading = (state: { cart: CartState }) => state.cart.isLoading
export const selectCartError = (state: { cart: CartState }) => state.cart.error
export const selectCartOpen = (state: { cart: CartState }) => state.cart.isOpen
