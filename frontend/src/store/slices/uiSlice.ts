import { createSlice, PayloadAction } from '@reduxjs/toolkit'
import { UIState, Notification } from '../../types'

const initialState: UIState = {
  isMobileMenuOpen: false,
  isSearchOpen: false,
  isCartOpen: false,
  isLoading: false,
  notifications: [],
}

const uiSlice = createSlice({
  name: 'ui',
  initialState,
  reducers: {
    setMobileMenuOpen: (state, action: PayloadAction<boolean>) => {
      state.isMobileMenuOpen = action.payload
    },
    toggleMobileMenu: (state) => {
      state.isMobileMenuOpen = !state.isMobileMenuOpen
    },
    setSearchOpen: (state, action: PayloadAction<boolean>) => {
      state.isSearchOpen = action.payload
    },
    toggleSearch: (state) => {
      state.isSearchOpen = !state.isSearchOpen
    },
    setCartOpen: (state, action: PayloadAction<boolean>) => {
      state.isCartOpen = action.payload
    },
    toggleCart: (state) => {
      state.isCartOpen = !state.isCartOpen
    },
    setLoading: (state, action: PayloadAction<boolean>) => {
      state.isLoading = action.payload
    },
    addNotification: (state, action: PayloadAction<Omit<Notification, 'id'>>) => {
      const notification: Notification = {
        ...action.payload,
        id: Date.now().toString(),
      }
      state.notifications.push(notification)
    },
    removeNotification: (state, action: PayloadAction<string>) => {
      state.notifications = state.notifications.filter(
        notification => notification.id !== action.payload
      )
    },
    clearNotifications: (state) => {
      state.notifications = []
    },
    // Close all modals/overlays
    closeAllOverlays: (state) => {
      state.isMobileMenuOpen = false
      state.isSearchOpen = false
      state.isCartOpen = false
    },
  },
})

export const {
  setMobileMenuOpen,
  toggleMobileMenu,
  setSearchOpen,
  toggleSearch,
  setCartOpen,
  toggleCart,
  setLoading,
  addNotification,
  removeNotification,
  clearNotifications,
  closeAllOverlays,
} = uiSlice.actions

export default uiSlice.reducer

// Selectors
export const selectIsMobileMenuOpen = (state: { ui: UIState }) => state.ui.isMobileMenuOpen
export const selectIsSearchOpen = (state: { ui: UIState }) => state.ui.isSearchOpen
export const selectIsCartOpen = (state: { ui: UIState }) => state.ui.isCartOpen
export const selectIsLoading = (state: { ui: UIState }) => state.ui.isLoading
export const selectNotifications = (state: { ui: UIState }) => state.ui.notifications
