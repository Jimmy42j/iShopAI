import { createSlice, createAsyncThunk, PayloadAction } from '@reduxjs/toolkit'
import { Wishlist, WishlistItem } from '../../types'
import { wishlistApi } from '../../services/wishlistApi'
import { toast } from 'react-hot-toast'

interface WishlistState {
  wishlist: Wishlist | null
  isLoading: boolean
  error: string | null
}

const initialState: WishlistState = {
  wishlist: null,
  isLoading: false,
  error: null,
}

// Async thunks
export const fetchWishlist = createAsyncThunk(
  'wishlist/fetchWishlist',
  async (_, { rejectWithValue }) => {
    try {
      const response = await wishlistApi.getWishlist()
      if (response.success && response.data) {
        return response.data
      }
      throw new Error(response.message || 'Failed to fetch wishlist')
    } catch (error: any) {
      return rejectWithValue(error.response?.data?.message || error.message)
    }
  }
)

export const addToWishlist = createAsyncThunk(
  'wishlist/addToWishlist',
  async (productId: number, { rejectWithValue }) => {
    try {
      const response = await wishlistApi.addToWishlist(productId)
      if (response.success) {
        toast.success('Added to wishlist!')
        return productId
      }
      throw new Error(response.message || 'Failed to add to wishlist')
    } catch (error: any) {
      const message = error.response?.data?.message || error.message || 'Failed to add to wishlist'
      toast.error(message)
      return rejectWithValue(message)
    }
  }
)

export const removeFromWishlist = createAsyncThunk(
  'wishlist/removeFromWishlist',
  async (productId: number, { rejectWithValue }) => {
    try {
      const response = await wishlistApi.removeFromWishlist(productId)
      if (response.success) {
        toast.success('Removed from wishlist')
        return productId
      }
      throw new Error(response.message || 'Failed to remove from wishlist')
    } catch (error: any) {
      const message = error.response?.data?.message || error.message || 'Failed to remove from wishlist'
      toast.error(message)
      return rejectWithValue(message)
    }
  }
)

export const checkWishlistStatus = createAsyncThunk(
  'wishlist/checkStatus',
  async (productId: number, { rejectWithValue }) => {
    try {
      const response = await wishlistApi.checkWishlistStatus(productId)
      if (response.success && response.data) {
        return { productId, inWishlist: response.data.in_wishlist }
      }
      throw new Error(response.message || 'Failed to check wishlist status')
    } catch (error: any) {
      return rejectWithValue(error.response?.data?.message || error.message)
    }
  }
)

const wishlistSlice = createSlice({
  name: 'wishlist',
  initialState,
  reducers: {
    clearError: (state) => {
      state.error = null
    },
    // Optimistic updates
    optimisticAddToWishlist: (state, action: PayloadAction<{
      product_id: number
      product_name: string
      price: number
      product_slug: string
      category_name: string
      category_slug: string
      rating_avg: number
      rating_count: number
      season: string
      gender_target: string
      brand?: string
      product_image?: string
    }>) => {
      if (!state.wishlist) {
        state.wishlist = {
          wishlist_id: 0,
          items: [],
          total_items: 0,
        }
      }

      const newItem: WishlistItem = {
        id: Date.now(), // Temporary ID
        wishlist_id: state.wishlist.wishlist_id,
        product_id: action.payload.product_id,
        created_at: new Date().toISOString(),
        product_name: action.payload.product_name,
        product_slug: action.payload.product_slug,
        price: action.payload.price,
        rating_avg: action.payload.rating_avg,
        rating_count: action.payload.rating_count,
        season: action.payload.season,
        gender_target: action.payload.gender_target,
        brand: action.payload.brand,
        category_name: action.payload.category_name,
        category_slug: action.payload.category_slug,
        product_image: action.payload.product_image,
      }

      state.wishlist.items.unshift(newItem)
      state.wishlist.total_items = state.wishlist.items.length
    },
    optimisticRemoveFromWishlist: (state, action: PayloadAction<number>) => {
      if (state.wishlist) {
        state.wishlist.items = state.wishlist.items.filter(
          item => item.product_id !== action.payload
        )
        state.wishlist.total_items = state.wishlist.items.length
      }
    },
  },
  extraReducers: (builder) => {
    builder
      // Fetch wishlist
      .addCase(fetchWishlist.pending, (state) => {
        state.isLoading = true
        state.error = null
      })
      .addCase(fetchWishlist.fulfilled, (state, action) => {
        state.isLoading = false
        state.wishlist = action.payload
        state.error = null
      })
      .addCase(fetchWishlist.rejected, (state, action) => {
        state.isLoading = false
        state.error = action.payload as string
      })
      
      // Add to wishlist
      .addCase(addToWishlist.pending, (state) => {
        state.error = null
      })
      .addCase(addToWishlist.fulfilled, (state, action) => {
        // The optimistic update already handled this
        state.error = null
      })
      .addCase(addToWishlist.rejected, (state, action) => {
        state.error = action.payload as string
        // Revert optimistic update if needed
      })
      
      // Remove from wishlist
      .addCase(removeFromWishlist.fulfilled, (state, action) => {
        if (state.wishlist) {
          state.wishlist.items = state.wishlist.items.filter(
            item => item.product_id !== action.payload
          )
          state.wishlist.total_items = state.wishlist.items.length
        }
      })
      .addCase(removeFromWishlist.rejected, (state, action) => {
        state.error = action.payload as string
      })
  },
})

export const {
  clearError,
  optimisticAddToWishlist,
  optimisticRemoveFromWishlist,
} = wishlistSlice.actions

export default wishlistSlice.reducer

// Selectors
export const selectWishlist = (state: { wishlist: WishlistState }) => state.wishlist.wishlist
export const selectWishlistItems = (state: { wishlist: WishlistState }) => 
  state.wishlist.wishlist?.items || []
export const selectWishlistItemCount = (state: { wishlist: WishlistState }) => 
  state.wishlist.wishlist?.total_items || 0
export const selectWishlistLoading = (state: { wishlist: WishlistState }) => state.wishlist.isLoading
export const selectWishlistError = (state: { wishlist: WishlistState }) => state.wishlist.error

// Helper selector to check if a product is in wishlist
export const selectIsInWishlist = (productId: number) => (state: { wishlist: WishlistState }) =>
  state.wishlist.wishlist?.items.some(item => item.product_id === productId) || false
