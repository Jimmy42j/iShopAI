import React, { createContext, useContext, useEffect } from 'react'
import { useAppDispatch, useAppSelector } from '../../store'
import { setSystemTheme, initializeTheme, selectTheme, selectSystemTheme, selectEffectiveTheme } from '../../store/slices/themeSlice'
import { Theme } from '../../types'

interface ThemeContextType {
  theme: Theme
  systemTheme: 'light' | 'dark'
  effectiveTheme: 'light' | 'dark'
  setTheme: (theme: Theme) => void
  toggleTheme: () => void
}

const ThemeContext = createContext<ThemeContextType | undefined>(undefined)

export const useTheme = () => {
  const context = useContext(ThemeContext)
  if (context === undefined) {
    throw new Error('useTheme must be used within a ThemeProvider')
  }
  return context
}

interface ThemeProviderProps {
  children: React.ReactNode
}

export const ThemeProvider: React.FC<ThemeProviderProps> = ({ children }) => {
  const dispatch = useAppDispatch()
  const theme = useAppSelector(selectTheme)
  const systemTheme = useAppSelector(selectSystemTheme)
  const effectiveTheme = useAppSelector(selectEffectiveTheme)

  useEffect(() => {
    // Initialize theme on mount
    dispatch(initializeTheme())

    // Listen for system theme changes
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)')
    
    const handleSystemThemeChange = (e: MediaQueryListEvent) => {
      dispatch(setSystemTheme(e.matches ? 'dark' : 'light'))
    }

    mediaQuery.addEventListener('change', handleSystemThemeChange)

    return () => {
      mediaQuery.removeEventListener('change', handleSystemThemeChange)
    }
  }, [dispatch])

  const setTheme = (newTheme: Theme) => {
    dispatch({ type: 'theme/setTheme', payload: newTheme })
  }

  const toggleTheme = () => {
    dispatch({ type: 'theme/toggleTheme' })
  }

  const value: ThemeContextType = {
    theme,
    systemTheme,
    effectiveTheme,
    setTheme,
    toggleTheme,
  }

  return (
    <ThemeContext.Provider value={value}>
      {children}
    </ThemeContext.Provider>
  )
}
