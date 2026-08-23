import React from 'react'
import { useParams } from 'react-router-dom'

export const OrderDetailsPage: React.FC = () => {
  const { id } = useParams<{ id: string }>()

  return (
    <div className="container mx-auto px-4 py-8">
      <div className="text-center">
        <h1 className="text-4xl font-bold mb-4">Order Details</h1>
        <p className="text-muted-foreground mb-8">
          Order ID: {id}
        </p>
        <div className="bg-muted/50 rounded-lg p-12">
          <p className="text-lg">
            Order details page coming soon...
          </p>
        </div>
      </div>
    </div>
  )
}
