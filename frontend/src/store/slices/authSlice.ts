import { createSlice, createAsyncThunk, PayloadAction } from '@reduxjs/toolkit'
import { AuthState, User, LoginForm, RegisterForm } from '../../types'
import { authApi } from '../../services/authApi'
import { toast } from 'react-hot-toast'

const initialState: AuthState = {
  user: null,
  token: null,
  isAuthenticated: false,
  isLoading: false,
  error: null,
}

// Async thunks
export const login = createAsyncThunk(
  'auth/login',
  async (credentials: LoginForm, { rejectWithValue }) => {
    try {
      const response = await authApi.login(credentials)
      if (response.success && response.data) {
        // Store token in localStorage
        if (response.data.token) {
          localStorage.setItem('auth_token', response.data.token)
        }
        toast.success('Login successful!')
        return response.data
      }
      throw new Error(response.message || 'Login failed')
    } catch (error: any) {
      const message = error.response?.data?.message || error.message || 'Login failed'
      toast.error(message)
      return rejectWithValue(message)
    }
  }
)

export const register = createAsyncThunk(
  'auth/register',
  async (userData: RegisterForm, { rejectWithValue }) => {
    try {
      const response = await authApi.register(userData)
      if (response.success && response.data) {
        // Store token in localStorage
        if (response.data.token) {
          localStorage.setItem('auth_token', response.data.token)
        }
        toast.success('Registration successful!')
        return response.data
      }
      throw new Error(response.message || 'Registration failed')
    } catch (error: any) {
      const message = error.response?.data?.message || error.message || 'Registration failed'
      toast.error(message)
      return rejectWithValue(message)
    }
  }
)

export const logout = createAsyncThunk(
  'auth/logout',
  async (_, { rejectWithValue }) => {
    try {
      await authApi.logout()
      localStorage.removeItem('auth_token')
      toast.success('Logged out successfully')
    } catch (error: any) {
      // Even if logout fails on server, clear local state
      localStorage.removeItem('auth_token')
      console.error('Logout error:', error)
    }
  }
)

export const getCurrentUser = createAsyncThunk(
  'auth/getCurrentUser',
  async (_, { rejectWithValue }) => {
    try {
      const response = await authApi.getCurrentUser()
      if (response.success && response.data) {
        return response.data.user
      }
      throw new Error(response.message || 'Failed to get user info')
    } catch (error: any) {
      const message = error.response?.data?.message || error.message || 'Failed to get user info'
      return rejectWithValue(message)
    }
  }
)

export const refreshToken = createAsyncThunk(
  'auth/refreshToken',
  async (_, { rejectWithValue }) => {
    try {
      const response = await authApi.refreshToken()
      if (response.success && response.data) {
        if (response.data.token) {
          localStorage.setItem('auth_token', response.data.token)
        }
        return response.data
      }
      throw new Error(response.message || 'Token refresh failed')
    } catch (error: any) {
      localStorage.removeItem('auth_token')
      return rejectWithValue(error.response?.data?.message || 'Token refresh failed')
    }
  }
)

export const initializeAuth = createAsyncThunk(
  'auth/initialize',
  async (_, { dispatch }) => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      try {
        await dispatch(getCurrentUser()).unwrap()
      } catch (error) {
        // If getting user fails, try to refresh token
        try {
          await dispatch(refreshToken()).unwrap()
        } catch (refreshError) {
          // If refresh also fails, clear token
          localStorage.removeItem('auth_token')
        }
      }
    }
  }
)

const authSlice = createSlice({
  name: 'auth',
  initialState,
  reducers: {
    clearError: (state) => {
      state.error = null
    },
    updateUser: (state, action: PayloadAction<Partial<User>>) => {
      if (state.user) {
        state.user = { ...state.user, ...action.payload }
      }
    },
    setLoading: (state, action: PayloadAction<boolean>) => {
      state.isLoading = action.payload
    },
  },
  extraReducers: (builder) => {
    builder
      // Login
      .addCase(login.pending, (state) => {
        state.isLoading = true
        state.error = null
      })
      .addCase(login.fulfilled, (state, action) => {
        state.isLoading = false
        state.user = action.payload.user
        state.token = action.payload.token
        state.isAuthenticated = true
        state.error = null
      })
      .addCase(login.rejected, (state, action) => {
        state.isLoading = false
        state.error = action.payload as string
        state.isAuthenticated = false
        state.user = null
        state.token = null
      })
      
      // Register
      .addCase(register.pending, (state) => {
        state.isLoading = true
        state.error = null
      })
      .addCase(register.fulfilled, (state, action) => {
        state.isLoading = false
        state.user = action.payload.user
        state.token = action.payload.token
        state.isAuthenticated = true
        state.error = null
      })
      .addCase(register.rejected, (state, action) => {
        state.isLoading = false
        state.error = action.payload as string
        state.isAuthenticated = false
        state.user = null
        state.token = null
      })
      
      // Logout
      .addCase(logout.fulfilled, (state) => {
        state.user = null
        state.token = null
        state.isAuthenticated = false
        state.error = null
        state.isLoading = false
      })
      
      // Get current user
      .addCase(getCurrentUser.pending, (state) => {
        state.isLoading = true
      })
      .addCase(getCurrentUser.fulfilled, (state, action) => {
        state.isLoading = false
        state.user = action.payload
        state.isAuthenticated = true
        state.token = localStorage.getItem('auth_token')
        state.error = null
      })
      .addCase(getCurrentUser.rejected, (state, action) => {
        state.isLoading = false
        state.error = action.payload as string
        state.isAuthenticated = false
        state.user = null
        state.token = null
      })
      
      // Refresh token
      .addCase(refreshToken.fulfilled, (state, action) => {
        state.user = action.payload.user
        state.token = action.payload.token
        state.isAuthenticated = true
        state.error = null
      })
      .addCase(refreshToken.rejected, (state) => {
        state.user = null
        state.token = null
        state.isAuthenticated = false
      })
  },
})

export const { clearError, updateUser, setLoading } = authSlice.actions
export default authSlice.reducer

// Selectors
export const selectAuth = (state: { auth: AuthState }) => state.auth
export const selectUser = (state: { auth: AuthState }) => state.auth.user
export const selectIsAuthenticated = (state: { auth: AuthState }) => state.auth.isAuthenticated
export const selectAuthLoading = (state: { auth: AuthState }) => state.auth.isLoading
export const selectAuthError = (state: { auth: AuthState }) => state.auth.error
