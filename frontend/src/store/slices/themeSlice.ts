import { createSlice, PayloadAction } from '@reduxjs/toolkit'
import { Theme, ThemeState } from '../../types'

const getSystemTheme = (): 'light' | 'dark' => {
  if (typeof window !== 'undefined') {
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
  }
  return 'light'
}

const getStoredTheme = (): Theme => {
  if (typeof window !== 'undefined') {
    const stored = localStorage.getItem('theme') as Theme
    if (stored && ['light', 'dark', 'system'].includes(stored)) {
      return stored
    }
  }
  return 'system'
}

const initialState: ThemeState = {
  theme: getStoredTheme(),
  systemTheme: getSystemTheme(),
}

const themeSlice = createSlice({
  name: 'theme',
  initialState,
  reducers: {
    setTheme: (state, action: PayloadAction<Theme>) => {
      state.theme = action.payload
      
      // Store in localStorage
      if (typeof window !== 'undefined') {
        localStorage.setItem('theme', action.payload)
        
        // Apply theme to document
        const effectiveTheme = action.payload === 'system' ? state.systemTheme : action.payload
        document.documentElement.classList.toggle('dark', effectiveTheme === 'dark')
      }
    },
    setSystemTheme: (state, action: PayloadAction<'light' | 'dark'>) => {
      state.systemTheme = action.payload
      
      // If current theme is system, apply the system theme
      if (state.theme === 'system' && typeof window !== 'undefined') {
        document.documentElement.classList.toggle('dark', action.payload === 'dark')
      }
    },
    toggleTheme: (state) => {
      const currentEffectiveTheme = state.theme === 'system' ? state.systemTheme : state.theme
      const newTheme: Theme = currentEffectiveTheme === 'light' ? 'dark' : 'light'
      
      state.theme = newTheme
      
      if (typeof window !== 'undefined') {
        localStorage.setItem('theme', newTheme)
        document.documentElement.classList.toggle('dark', newTheme === 'dark')
      }
    },
    initializeTheme: (state) => {
      // This is called on app initialization to set up theme listeners
      if (typeof window !== 'undefined') {
        const effectiveTheme = state.theme === 'system' ? state.systemTheme : state.theme
        document.documentElement.classList.toggle('dark', effectiveTheme === 'dark')
      }
    },
  },
})

export const { setTheme, setSystemTheme, toggleTheme, initializeTheme } = themeSlice.actions
export default themeSlice.reducer

// Selectors
export const selectTheme = (state: { theme: ThemeState }) => state.theme.theme
export const selectSystemTheme = (state: { theme: ThemeState }) => state.theme.systemTheme
export const selectEffectiveTheme = (state: { theme: ThemeState }) => 
  state.theme.theme === 'system' ? state.theme.systemTheme : state.theme.theme
export const selectIsDarkMode = (state: { theme: ThemeState }) => {
  const effectiveTheme = state.theme.theme === 'system' ? state.theme.systemTheme : state.theme.theme
  return effectiveTheme === 'dark'
}
