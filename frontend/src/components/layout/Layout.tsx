import React from 'react'
import { Header } from './Header'
import { Footer } from './Footer'
import { ChatBot } from '../chat/ChatBot'

interface LayoutProps {
  children: React.ReactNode
}

export const Layout: React.FC<LayoutProps> = ({ children }) => {
  return (
    <div className="min-h-screen bg-background text-foreground">
      <Header />
      <main className="flex-1">
        {children}
      </main>
      <Footer />
      <ChatBot />
    </div>
  )
}
