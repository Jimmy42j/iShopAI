// Utility functions for generating product images

export const getProductImageUrl = (product: { id: number; name: string; gender_target?: string; category_name?: string }) => {
  const name = product.name.toLowerCase()
  
  // Map product types to specific Unsplash photo IDs for consistent, high-quality images
  const imageMap: { [key: string]: string } = {
    // Men's clothing
    'classic': 'photo-1521572163474-6864f9cf17ab', // Classic t-shirt
    'linen': 'photo-1594938298603-c8148c4dae35', // Linen shirt
    'cargo': 'photo-1594938374637-6b5e0b6d5b7e', // Cargo shorts
    'polo': 'photo-1586790170083-2f9ceadc732d', // Polo shirt
    'chino': 'photo-1594938374637-6b5e0b6d5b7e', // Chino pants
    'wool': 'photo-1434389677669-e08b4cac3105', // Wool sweater
    'denim': 'photo-1551698618-1dfe5d97d256', // Denim jacket
    'flannel': 'photo-1594938374637-6b5e0b6d5b7e', // Flannel shirt
    'winter': 'photo-1551698618-1dfe5d97d256', // Winter coat
    'thermal': 'photo-1594938374637-6b5e0b6d5b7e', // Thermal underwear
    
    // Women's clothing
    'floral': 'photo-1595777457583-95e059d581b8', // Floral dress
    'silk': 'photo-1594938374637-6b5e0b6d5b7e', // Silk blouse
    'high-waisted': 'photo-1594938374637-6b5e0b6d5b7e', // High-waisted jeans
    'crop': 'photo-1594938374637-6b5e0b6d5b7e', // Crop top
    'maxi': 'photo-1595777457583-95e059d581b8', // Maxi skirt
    'cashmere': 'photo-1594938374637-6b5e0b6d5b7e', // Cashmere cardigan
    'leather': 'photo-1594938374637-6b5e0b6d5b7e', // Leather boots
    'plaid': 'photo-1594938374637-6b5e0b6d5b7e', // Plaid scarf
    
    // Kids' clothing
    'dinosaur': 'photo-1503944583220-79d8926ad5e2', // Kids t-shirt
    'rainbow': 'photo-1503944583220-79d8926ad5e2', // Rainbow dress
    'overalls': 'photo-1503944583220-79d8926ad5e2', // Denim overalls
    'swim': 'photo-1503944583220-79d8926ad5e2', // Swim shorts
    'sandals': 'photo-1503944583220-79d8926ad5e2', // Sandals
    'hoodie': 'photo-1503944583220-79d8926ad5e2', // Hoodie
    'beanie': 'photo-1503944583220-79d8926ad5e2', // Knit beanie
    'snow': 'photo-1503944583220-79d8926ad5e2', // Snow boots
    'pajamas': 'photo-1503944583220-79d8926ad5e2', // Fleece pajamas
    
    // Unisex
    'baseball': 'photo-1588117472013-59bb13edafec', // Baseball cap
    'canvas': 'photo-1549298916-b41d501d3772', // Canvas sneakers
    'backpack': 'photo-1553062407-98eeb64c6a62' // Backpack
  }
  
  // Find matching image based on product name
  let imageId = 'photo-1441986300917-64674bd600d8' // Default clothing image
  
  for (const [key, id] of Object.entries(imageMap)) {
    if (name.includes(key)) {
      imageId = id
      break
    }
  }
  
  // Return Unsplash image URL with consistent sizing
  return `https://images.unsplash.com/${imageId}?w=400&h=400&fit=crop&crop=center&auto=format&q=80`
}

export const getCategoryImageUrl = (category: { id: number; name: string; slug: string }) => {
  const categoryColors = {
    men: '4f46e5',
    women: 'ec4899', 
    kids: 'f59e0b'
  }
  
  const color = categoryColors[category.slug as keyof typeof categoryColors] || '6b7280'
  return `https://via.placeholder.com/600x600/${color}/ffffff?text=${encodeURIComponent(category.name.toUpperCase())}`
}

// Fallback image generator
export const generateFallbackImage = (text: string, color: string = '6b7280') => {
  return `https://via.placeholder.com/400x400/${color}/ffffff?text=${encodeURIComponent(text)}`
}
