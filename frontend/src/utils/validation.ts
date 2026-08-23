import { z } from 'zod'

// Auth validation schemas
export const loginSchema = z.object({
  email: z.string().email('Please enter a valid email address'),
  password: z.string().min(1, 'Password is required'),
})

export const registerSchema = z.object({
  name: z.string().min(2, 'Name must be at least 2 characters').max(255, 'Name is too long'),
  email: z.string().email('Please enter a valid email address'),
  password: z.string().min(8, 'Password must be at least 8 characters'),
  confirmPassword: z.string(),
  gender: z.enum(['male', 'female', 'other']).optional(),
  birthdate: z.string().optional(),
}).refine((data) => data.password === data.confirmPassword, {
  message: "Passwords don't match",
  path: ['confirmPassword'],
})

// Address validation schema
export const addressSchema = z.object({
  line1: z.string().min(1, 'Address line 1 is required'),
  line2: z.string().optional(),
  city: z.string().min(1, 'City is required'),
  state: z.string().min(1, 'State is required'),
  postal_code: z.string().min(1, 'Postal code is required'),
  country: z.string().min(1, 'Country is required'),
  is_default: z.boolean().optional(),
})

// Checkout validation schema
export const checkoutSchema = z.object({
  cart_id: z.number().positive('Invalid cart ID'),
  address_id: z.number().positive('Please select a shipping address'),
  payment_token: z.string().optional(),
  notes: z.string().optional(),
})

// Search validation schema
export const searchSchema = z.object({
  q: z.string().optional(),
  category: z.string().optional(),
  season: z.enum(['spring', 'summer', 'autumn', 'winter', 'all']).optional(),
  gender_target: z.enum(['men', 'women', 'kids', 'unisex']).optional(),
  min_price: z.number().min(0).optional(),
  max_price: z.number().min(0).optional(),
  sort: z.enum(['newest', 'price_asc', 'price_desc', 'rating', 'popularity', 'name']).optional(),
  page: z.number().min(1).optional(),
  limit: z.number().min(1).max(50).optional(),
})

// Product review validation schema
export const reviewSchema = z.object({
  rating: z.number().min(1, 'Please select a rating').max(5, 'Rating cannot exceed 5 stars'),
  title: z.string().min(1, 'Review title is required').max(100, 'Title is too long'),
  comment: z.string().min(10, 'Review must be at least 10 characters').max(1000, 'Review is too long'),
})

// Contact form validation schema
export const contactSchema = z.object({
  name: z.string().min(2, 'Name must be at least 2 characters'),
  email: z.string().email('Please enter a valid email address'),
  subject: z.string().min(1, 'Subject is required'),
  message: z.string().min(10, 'Message must be at least 10 characters'),
})

// Newsletter subscription schema
export const newsletterSchema = z.object({
  email: z.string().email('Please enter a valid email address'),
})

// Utility functions for validation
export const validateEmail = (email: string): boolean => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(email)
}

export const validatePassword = (password: string): {
  isValid: boolean
  errors: string[]
} => {
  const errors: string[] = []
  
  if (password.length < 8) {
    errors.push('Password must be at least 8 characters long')
  }
  
  if (!/[A-Z]/.test(password)) {
    errors.push('Password must contain at least one uppercase letter')
  }
  
  if (!/[a-z]/.test(password)) {
    errors.push('Password must contain at least one lowercase letter')
  }
  
  if (!/\d/.test(password)) {
    errors.push('Password must contain at least one number')
  }
  
  if (!/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
    errors.push('Password must contain at least one special character')
  }
  
  return {
    isValid: errors.length === 0,
    errors,
  }
}

export const validatePhoneNumber = (phone: string): boolean => {
  const phoneRegex = /^\+?[\d\s\-\(\)]{10,}$/
  return phoneRegex.test(phone)
}

export const validatePostalCode = (postalCode: string, country = 'US'): boolean => {
  const patterns: Record<string, RegExp> = {
    US: /^\d{5}(-\d{4})?$/,
    CA: /^[A-Za-z]\d[A-Za-z] \d[A-Za-z]\d$/,
    UK: /^[A-Za-z]{1,2}\d[A-Za-z\d]? \d[A-Za-z]{2}$/,
    // Add more patterns as needed
  }
  
  const pattern = patterns[country.toUpperCase()]
  return pattern ? pattern.test(postalCode) : true // Default to valid if pattern not found
}

// Type inference helpers
export type LoginFormData = z.infer<typeof loginSchema>
export type RegisterFormData = z.infer<typeof registerSchema>
export type AddressFormData = z.infer<typeof addressSchema>
export type CheckoutFormData = z.infer<typeof checkoutSchema>
export type SearchFormData = z.infer<typeof searchSchema>
export type ReviewFormData = z.infer<typeof reviewSchema>
export type ContactFormData = z.infer<typeof contactSchema>
export type NewsletterFormData = z.infer<typeof newsletterSchema>
