import React from 'react'
import { useParams } from 'react-router-dom'

export const ProductPage: React.FC = () => {
  const { slug } = useParams<{ slug: string }>()

  return (
    <div className="container mx-auto px-4 py-8">
      <div className="text-center">
        <h1 className="text-4xl font-bold mb-4">Product Details</h1>
        <p className="text-muted-foreground mb-8">
          Product slug: {slug}
        </p>
        <div className="bg-muted/50 rounded-lg p-12">
          <p className="text-lg">
            Product details page coming soon...
          </p>
        </div>
      </div>
    </div>
  )
}
