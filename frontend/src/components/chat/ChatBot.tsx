import React, { useState, useRef, useEffect } from 'react'
import { MessageCircle, X, Send, Bot, User, Sparkles, Star } from 'lucide-react'
import { getProductImageUrl } from '../../utils/imageUtils'
import { aiApi } from '../../services/aiApi'

interface ChatMessage {
  id: string
  type: 'user' | 'bot'
  message: string
  timestamp: Date
  recommendations?: Recommendation[]
}

interface Recommendation {
  product_id: number
  score: number
  reason: string
}


export const ChatBot: React.FC = () => {
  const [isOpen, setIsOpen] = useState(false)
  const [isConnected, setIsConnected] = useState(false)
  const [messages, setMessages] = useState<ChatMessage[]>([
    {
      id: '1',
      type: 'bot',
      message: "Hi! I'm your AI fashion assistant. I can help you find the perfect clothing based on your style, season, and preferences. What are you looking for today?",
      timestamp: new Date()
    }
  ])
  const [inputMessage, setInputMessage] = useState('')
  const [isLoading, setIsLoading] = useState(false)
  const messagesEndRef = useRef<HTMLDivElement>(null)

  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' })
  }

  useEffect(() => {
    scrollToBottom()
  }, [messages])

  // Check AI service connection
  useEffect(() => {
    const checkConnection = async () => {
      try {
        const response = await fetch('http://localhost:5000/health')
        setIsConnected(response.ok)
      } catch {
        setIsConnected(false)
      }
    }
    
    checkConnection()
    const interval = setInterval(checkConnection, 10000) // Check every 10 seconds
    
    return () => clearInterval(interval)
  }, [])

  const sendMessage = async () => {
    if (!inputMessage.trim() || isLoading) return

    const userMessage: ChatMessage = {
      id: Date.now().toString(),
      type: 'user',
      message: inputMessage,
      timestamp: new Date()
    }

    setMessages(prev => [...prev, userMessage])
    setInputMessage('')
    setIsLoading(true)

    try {
      const data = await aiApi.chat({
        message: inputMessage,
        user_context: {
          user_signals: {
            viewed: [],
            wishlisted: [],
            carted: []
          }
        }
      })

      const botMessage: ChatMessage = {
        id: (Date.now() + 1).toString(),
        type: 'bot',
        message: data.message,
        timestamp: new Date(),
        recommendations: data.has_recommendations ? data.recommendations : undefined
      }

      setMessages(prev => [...prev, botMessage])
    } catch (error) {
      console.error('Error sending message:', error)
      const errorMessage: ChatMessage = {
        id: (Date.now() + 1).toString(),
        type: 'bot',
        message: "I'm having trouble connecting to my AI brain right now. Make sure the AI service is running on port 5000, then try asking me again! 🤖",
        timestamp: new Date()
      }
      setMessages(prev => [...prev, errorMessage])
    } finally {
      setIsLoading(false)
    }
  }

  const handleKeyPress = (e: React.KeyboardEvent) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault()
      sendMessage()
    }
  }

  // Fetch product details from backend API
  const fetchProductDetails = async (productId: number) => {
    try {
      const response = await fetch(`http://localhost:8000/products`)
      const data = await response.json()
      if (data.success && data.data.items) {
        return data.data.items.find((p: any) => p.id === productId)
      }
    } catch (error) {
      console.error('Error fetching product details:', error)
    }
    return mockProducts[productId] // fallback to mock data
  }

  const mockProducts: { [key: number]: any } = {
    1: { id: 1, name: "Classic Cotton T-Shirt", price: 24.99, rating_avg: 4.5, rating_count: 127, category_id: 1, slug: "classic-cotton-t-shirt", is_active: true, created_at: "", updated_at: "", gender_target: "men", season: "summer" },
    2: { id: 2, name: "Linen Button-Up Shirt", price: 59.99, rating_avg: 4.3, rating_count: 89, category_id: 1, slug: "linen-button-up-shirt", is_active: true, created_at: "", updated_at: "", gender_target: "men", season: "summer" },
    3: { id: 3, name: "Cargo Shorts", price: 39.99, rating_avg: 4.2, rating_count: 156, category_id: 1, slug: "cargo-shorts", is_active: true, created_at: "", updated_at: "", gender_target: "men", season: "summer" },
    11: { id: 11, name: "Floral Summer Dress", price: 69.99, rating_avg: 4.6, rating_count: 189, category_id: 2, slug: "floral-summer-dress", is_active: true, created_at: "", updated_at: "", gender_target: "women", season: "summer" },
    12: { id: 12, name: "Silk Blouse", price: 89.99, rating_avg: 4.5, rating_count: 145, category_id: 2, slug: "silk-blouse", is_active: true, created_at: "", updated_at: "", gender_target: "women", season: "spring" },
    13: { id: 13, name: "High-Waisted Jeans", price: 79.99, rating_avg: 4.7, rating_count: 234, category_id: 2, slug: "high-waisted-jeans", is_active: true, created_at: "", updated_at: "", gender_target: "women", season: "all" },
    21: { id: 21, name: "Dinosaur T-Shirt", price: 19.99, rating_avg: 4.6, rating_count: 234, category_id: 3, slug: "dinosaur-t-shirt", is_active: true, created_at: "", updated_at: "", gender_target: "kids", season: "summer" },
    22: { id: 22, name: "Rainbow Dress", price: 39.99, rating_avg: 4.5, rating_count: 167, category_id: 3, slug: "rainbow-dress", is_active: true, created_at: "", updated_at: "", gender_target: "kids", season: "spring" },
    6: { id: 6, name: "Wool Sweater", price: 89.99, rating_avg: 4.7, rating_count: 94, category_id: 1, slug: "wool-sweater", is_active: true, created_at: "", updated_at: "", gender_target: "men", season: "winter" },
    16: { id: 16, name: "Cashmere Cardigan", price: 129.99, rating_avg: 4.8, rating_count: 87, category_id: 2, slug: "cashmere-cardigan", is_active: true, created_at: "", updated_at: "", gender_target: "women", season: "autumn" }
  }

  return (
    <>
      {/* Chat Toggle Button */}
      {!isOpen && (
        <button
          onClick={() => setIsOpen(true)}
          className="fixed bottom-6 right-6 w-14 h-14 bg-primary text-primary-foreground rounded-full shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center z-50 hover:scale-110"
        >
          <MessageCircle className="h-6 w-6" />
        </button>
      )}

      {/* Chat Window */}
      {isOpen && (
        <div className="fixed bottom-6 right-6 w-96 h-[600px] bg-card dark:bg-gray-800 border border-border dark:border-gray-700 rounded-lg shadow-2xl z-50 flex flex-col">
          {/* Header */}
          <div className="flex items-center justify-between p-4 border-b border-border bg-primary text-primary-foreground rounded-t-lg">
            <div className="flex items-center gap-2">
              <div className="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center">
                <Bot className="h-5 w-5" />
              </div>
              <div>
                <h3 className="font-semibold">AI Fashion Assistant</h3>
                <p className="text-xs opacity-90">
                  {isConnected ? 'Powered by NLU' : 'Connecting...'}
                </p>
              </div>
            </div>
            <button
              onClick={() => setIsOpen(false)}
              className="p-1 hover:bg-white/20 rounded-full transition-colors"
            >
              <X className="h-5 w-5" />
            </button>
          </div>

          {/* Messages */}
          <div className="flex-1 overflow-y-auto p-4 space-y-4">
            {messages.map((message) => (
              <div key={message.id} className={`flex ${message.type === 'user' ? 'justify-end' : 'justify-start'}`}>
                <div className={`max-w-[80%] ${message.type === 'user' 
                  ? 'bg-primary text-primary-foreground' 
                  : 'bg-muted text-foreground dark:text-white'
                } rounded-lg p-3`}>
                  <div className="flex items-start gap-2">
                    {message.type === 'bot' && (
                      <Bot className="h-4 w-4 mt-0.5 flex-shrink-0" />
                    )}
                    {message.type === 'user' && (
                      <User className="h-4 w-4 mt-0.5 flex-shrink-0" />
                    )}
                    <div className="flex-1">
                      <p className="text-sm">{message.message}</p>
                      
                      {/* Product Recommendations */}
                      {message.recommendations && message.recommendations.length > 0 && (
                        <div className="mt-3 space-y-2">
                          <div className="flex items-center gap-1 text-xs opacity-75">
                            <Sparkles className="h-3 w-3" />
                            <span>Recommended for you:</span>
                          </div>
                          {message.recommendations.map((rec) => {
                            const product = mockProducts[rec.product_id]
                            if (!product) return null
                            
                            return (
                              <div key={rec.product_id} className="bg-white/10 dark:bg-black/20 rounded-lg p-3 border border-white/20">
                                <div className="flex items-start gap-3">
                                  <div className="w-16 h-16 rounded-lg overflow-hidden bg-gray-200 dark:bg-gray-700 flex-shrink-0">
                                    <img 
                                      src={product.image_url || getProductImageUrl(product)}
                                      alt={product.name}
                                      className="w-full h-full object-cover"
                                      onError={(e) => {
                                        const target = e.currentTarget as HTMLImageElement
                                        target.src = `https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=400&h=400&fit=crop&crop=center&auto=format&q=80`
                                      }}
                                    />
                                  </div>
                                  <div className="flex-1 min-w-0">
                                    <h4 className="text-sm font-semibold text-white dark:text-white mb-1">{product.name}</h4>
                                    <div className="flex items-center gap-2 mb-1">
                                      <p className="text-sm font-bold text-green-300">${product.price}</p>
                                      <div className="flex items-center gap-1">
                                        <Star className="h-3 w-3 fill-yellow-400 text-yellow-400" />
                                        <span className="text-xs text-white/80">{product.rating_avg || 4.5}</span>
                                      </div>
                                    </div>
                                    <p className="text-xs text-white/70 mb-2">{rec.reason}</p>
                                    <button className="bg-blue-500 hover:bg-blue-600 text-white text-xs px-3 py-1 rounded-md transition-colors">
                                      View Product
                                    </button>
                                  </div>
                                </div>
                              </div>
                            )
                          })}
                        </div>
                      )}
                    </div>
                  </div>
                  <p className="text-xs opacity-50 mt-1">
                    {message.timestamp.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                  </p>
                </div>
              </div>
            ))}
            
            {isLoading && (
              <div className="flex justify-start">
                <div className="bg-muted text-foreground dark:text-white rounded-lg p-3">
                  <div className="flex items-center gap-2">
                    <Bot className="h-4 w-4" />
                    <div className="flex space-x-1">
                      <div className="w-2 h-2 bg-current rounded-full animate-bounce"></div>
                      <div className="w-2 h-2 bg-current rounded-full animate-bounce" style={{ animationDelay: '0.1s' }}></div>
                      <div className="w-2 h-2 bg-current rounded-full animate-bounce" style={{ animationDelay: '0.2s' }}></div>
                    </div>
                  </div>
                </div>
              </div>
            )}
            <div ref={messagesEndRef} />
          </div>

          {/* Input */}
          <div className="p-4 border-t border-border dark:border-gray-700">
            <div className="flex gap-2">
              <input
                type="text"
                value={inputMessage}
                onChange={(e) => setInputMessage(e.target.value)}
                onKeyPress={handleKeyPress}
                placeholder={isConnected ? "Ask me about clothing recommendations..." : "Waiting for AI service..."}
                className="flex-1 px-3 py-2 border border-border dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm bg-background dark:bg-gray-700 text-foreground dark:text-white placeholder:text-muted-foreground dark:placeholder:text-gray-400"
                disabled={isLoading || !isConnected}
              />
              <button
                onClick={sendMessage}
                disabled={!inputMessage.trim() || isLoading || !isConnected}
                className="px-3 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                <Send className="h-4 w-4" />
              </button>
            </div>
            <p className="text-xs text-muted-foreground dark:text-gray-400 mt-2 text-center">
              {isConnected 
                ? 'Try: "Show me summer clothes for men" or "I need a dress for a party"'
                : 'AI service starting... Please wait a moment'
              }
            </p>
          </div>
        </div>
      )}
    </>
  )
}
